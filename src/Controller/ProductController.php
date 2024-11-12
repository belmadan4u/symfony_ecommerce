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
class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/product/add', name: 'add_product')]
    public function addProduct(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();

        $form = $this->createForm(ProduitFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            foreach ($imageFiles as $imageFile) {
                $image = new Image();

                $imageBinary = file_get_contents($imageFile->getRealPath());
                $imageBase64 = base64_encode($imageBinary);
                
                $image->setUrl($imageBase64);
                $image->setProduct($product);
                $em->persist($image);
                
            }

            $em->persist($product);
            $em->flush();
            
            return $this->redirectToRoute('admin_prod_list'); 
        }

        return $this->render('product/admin_add.html.twig', [
            'addProductForm' => $form->createView(),
        ]);
    }

    #[Route('/product/list', name:'admin_prod_list')]
    public function productList(Request $request, PaginatorInterface $paginator): Response 
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        foreach ($products as $product) {
            foreach ($product->getImages() as $image) {
                $image->setUrl('data:image/jpeg;base64,' .stream_get_contents($image->getUrl())); 
            }
        }

        $pagination = $paginator->paginate(
            $products, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4 /*limit per page*/
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
            $image->setUrl('data:image/jpeg;base64,' .stream_get_contents($image->getUrl())); 
        }

        $form = $this->createForm(ProduitFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $product = $form->getData();
                $logger->debug('Product data', [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'description' => $product->getDescription(),
                    'stock' => $product->getStock(),
                    'status' => $product->getStatus()->name, 
                ]);

                /*$imageFiles = $form->get('images')->getData();

                if (!empty($imageFiles)) {
                    foreach ($imageFiles as $imageFile) {
                        if ($imageFile->isValid()) {
                            $image = new Image();
                            $imageBinary = file_get_contents($imageFile->getRealPath());
                            $imageBase64 = base64_encode($imageBinary);

                            $image->setUrl($imageBase64);
                            $image->setProduct($product);
                            $this->entityManager->persist($image);
                        }
                    }
                }*/
    
                $this->entityManager->persist($product);
                $this->entityManager->flush();
    
                return $this->redirectToRoute('admin_prod_list'); 
            } else {
                foreach ($form->all() as $field) {
                    if (!$field->isValid()) {
                        $fieldErrors = $field->getErrors(); 
                        foreach ($fieldErrors as $fieldError) {
                            $logger->error(sprintf('Error in field %s: %s', $field->getName(), $fieldError->getMessage()));
                        }
                    }
                }
                
            }
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

        if($product->getOrderItem() != null && in_array( $product->getOrderItem()->getOfOrder()->getStatus(), [OrderStatus::enPreparation, OrderStatus::expediee],)  ) {
            return $this->redirectToRoute('admin_prod_list', [
                'message' => 'Le produit ne peut pas être supprimé car il est dans une commande non achevée.'
            ]);
        }else{
            foreach ($product->getImages() as $image) {
                $this->entityManager->remove($image);
            }
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_prod_list', [
                'message' => 'Le produit a été supprimé avec succès.'
            ]);
           
        }
        
    }

    #[Route('/product/{id}', name: 'app_product')]
    public function productShow(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        foreach ($product->getImages() as $imgProd) {
            $imgProd->setUrl('data:image/jpeg;base64,' .stream_get_contents($imgProd->getUrl())); 
            
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
                $imagesProd[] = 'data:image/jpeg;base64,' .stream_get_contents($productImg->getUrl());
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

    
}
