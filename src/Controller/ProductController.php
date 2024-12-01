<?php
namespace App\Controller;

use App\Enum\OrderStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use \Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\ProduitFormType;
use App\Entity\Image;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Category;
class ProductController extends AbstractController
{
    private $entityManager;
    private $logger;


    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }
    
    #[Route('/product/add', name: 'add_product')]
    public function addProduct(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();

        $form = $this->createForm(ProduitFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form['images'] as $imageForm) {
                $image = new Image();
                $this->logger->info('Form data: ' . json_encode($form->getData()));
                $imageFile = $imageForm->get('image')->getData();
                try {
                    $imageBinary = file_get_contents($imageFile->getRealPath());
                    $imageBase64 = base64_encode($imageBinary);
                    
                    $image->setUrl('data:image/jpeg;base64,'.$imageBase64);
                    $image->setProduct($product);
                    $em->persist($image);
                } catch (\Exception $e) {
                    $this->logger->error('Error processing image file: ' . $e->getMessage());
                }
                
            }

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Le produit a été ajouté avec succés');

            return $this->redirectToRoute('admin_prod_list'); 
        } else {
            $this->logger->error('Form not valid');
        }

        return $this->render('product/admin_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/list', name:'admin_prod_list')]
    public function productList(Request $request, PaginatorInterface $paginator): Response 
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        foreach ($products as $product) {
            foreach ($product->getImages() as $image) {
                $image->setUrl(stream_get_contents($image->getUrl())); 
            }
        }

        $pagination = $paginator->paginate(
            $products, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('product/product_list_admin.html.twig', [
            'products'=> $products,
            'pagination' => $pagination
        ]);
    }

    #[Route('/product/modify/{id}', name: 'admin_prod_modify')]
    public function productModify(Request $request, int $id, LoggerInterface $logger): Response 
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        foreach ($product->getImages() as $image) {
            $image->setUrl(stream_get_contents($image->getUrl())); 
        }

        $form = $this->createForm(ProduitFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form['images'] as $imageForm) {
                $image = new Image();
                $this->logger->info('Form data: ' . json_encode($form->getData()));
                $imageFile = $imageForm->get('image')->getData();
                try {
                    $imageBinary = file_get_contents($imageFile->getRealPath());
                    $imageBase64 = base64_encode($imageBinary);
                    
                    $image->setUrl('data:image/jpeg;base64,'.$imageBase64);
                    $image->setProduct($product);
                    $this->entityManager->persist($image);
                } catch (\Exception $e) {
                    $this->logger->error('Error processing image file: ' . $e->getMessage());
                }
                
            }

            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le produit a été modifié avec succés');

            return $this->redirectToRoute('admin_prod_list'); 
        } else {
            $this->logger->error('Form not valid');
        }

        return $this->render('product/product_modify_admin.html.twig', [ 
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'admin_prod_delete')]
    public function productDelete(int $id): Response 
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
    
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        foreach ($product->getOrderItem() as $orderItem) {
            $order = $orderItem->getOfOrder(); 
            if (in_array($order->getStatus(), [OrderStatus::enPreparation, OrderStatus::expediee])) {
                $this->addFlash('error', 'Le produit ne peut pas être supprimé car il est dans une commande non achevée.');
                return $this->redirectToRoute('admin_prod_list');
            }
        }

        foreach ($product->getImages() as $image) {
            $this->entityManager->remove($image);
        }
        
        // Supprimez le produit
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        $this->addFlash('success', 'Le produit a été supprimé avec succès.');

        return $this->redirectToRoute('admin_prod_list');
        
    }

    #[Route('/product/show/{id}/{_locale}', name: 'app_product', locale: 'fr')]
    public function productShow(string $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        foreach ($product->getImages() as $imgProd) {
            $imgProd->setUrl(stream_get_contents($imgProd->getUrl())); 
            
        }

        return $this->render('product/index.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/search', name: '')]
    public function searchProducts(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $searchTerm = $request->query->get('q', '');
        $products = $em->getRepository(Product::class)->findBySearchTerm($searchTerm);
        
        $results = [];
        foreach ($products as $product) {
            $imagesProd = [];
            foreach ($product->getImages() as $productImg) {
                $imagesProd[] = stream_get_contents($productImg->getUrl());
            }
            
            $categories = [];
            foreach ($product->getCategory() as $category) {
                $categories[] = $category->getName();
            }
            
            $results[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
                'status' => $product->getStatus()->value,
                'images' => $imagesProd,
                'category' => $categories,
            ];
        }

        return new JsonResponse($results);
    }


    #[Route(path: "/product/delete-image/{id}", name:"product_delete_image", methods:["POST"])]
    public function deleteImage($id, EntityManagerInterface $em, Request $request)
    {
        $image = $em->getRepository(Image::class)->find($id);
        $this->logger->info('Image ID reçu : ' . $id);

        if (!$image) {
            $this->logger->error('Image introuvable avec l\'ID : ' . $id);
            return new JsonResponse(['success' => false, 'message' => 'Image non trouvée'], 404);
        }
        
        try {

            $em->remove($image);
            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false], 500);
        }
    }

    #[Route(path: "/product/category/add", name:"product_category_add", methods:["POST"])]
    public function addCategory(EntityManagerInterface $em, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'];
        
        if ($name) {
            $category = new Category();
            $category->setName($name);

            $em->persist($category);
            $em->flush();

            return new JsonResponse([
                'status' => 'Category added successfully!', 
                'id' => $category->getId(),
                'name' => $category->getName()
            ], 200);        
        }

        return new JsonResponse(['error' => 'Category name is required.'], 400);
    }
    
}
