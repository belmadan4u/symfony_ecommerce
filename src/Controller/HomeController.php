<?php
namespace App\Controller;

use App\Enum\ProductStatus;
use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;

class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/customer/{_locale}', name: 'app_homepage', locale: 'fr')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

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

        return $this->render('index.html.twig', [
            'categories' => $categories,
            'products'=> $products,
            'pagination' => $pagination
        ]);
    }

    #[Route('/admin/{_locale}', name:'admin_dashboard', locale:'fr')]
    public function index_admin(): Response{
        $nbOfProductsByCategory = [];
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        $montantVentes12lastMonth = $this->entityManager->getRepository(Order::class)->MontantOfOrdersForTheLast12Months();
        
        foreach( $categories as $category) {
            $nbOfProductsByCategory[$category->getName()] = $category->getProducts()->count();
        }

        $dernieresCommandes = $this->entityManager->getRepository(Order::class)->FiveLastOrder();
        $produits = $this->entityManager->getRepository(Product::class)->findAll();
        $nbPdDispo= 0;
        $nbPdRupture= 0;
        $nbPdPrecommande = 0;

        foreach ($produits as $produit) {
            if($produit->getStatus() == ProductStatus::disponible) {
                $nbPdDispo++;
            }else if($produit->getStatus() == ProductStatus::precommande) {
                $nbPdPrecommande++;
            }else if($produit->getStatus() == ProductStatus::rupture) {
                $nbPdRupture++;
            }
        }
        return $this->render('index_admin.html.twig',[
            'nbOfProductsByCategory' => $nbOfProductsByCategory,
            'montantVentes12lastMonth' => $montantVentes12lastMonth,
            'dernieresCommandes'=> $dernieresCommandes,
            'nbPdDispo'=> $nbPdDispo,
            'nbPdPrecommande'=> $nbPdPrecommande,
            'nbPdRupture'=> $nbPdRupture,
        ]);
    }

    #[Route('/customer/orders', name:'orders_user')]
    public function ordersOfUser(LoggerInterface $logger): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->getOrdersByUser($this->getUser());
        
        return $this->render('orders/orders_user.html.twig',[
            'orders'=> $orders
        ]);
        
    }
    #[Route('/')]
    public function indexNoLocale(): Response
    {
        if ($this->getUser() !== null) {
            $roles = $this->getUser()->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('admin_dashboard');
            } elseif (in_array('ROLE_CUSTOMER', $roles)) {
                return $this->redirectToRoute('app_homepage');
            }
        } else{
            return $this->redirectToRoute('app_homepage');
        }  
    }
}