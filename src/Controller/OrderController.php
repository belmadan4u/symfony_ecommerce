<?php
namespace App\Controller;

use App\Enum\OrderStatus;
use Proxies\__CG__\App\Entity\OrderItem;
use Proxies\__CG__\App\Entity\Product;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use \Doctrine\ORM\EntityManagerInterface;
use App\Entity\Order;

class OrderController extends AbstractController
{
    #[Route('/orders', name: 'admin_show_orders')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(Order::class)->findAll();

        return $this->render('admin_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/order/withCard/{idCard}', name: 'order_with_card')]
    public function order_with_card(LoggerInterface $logger, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {   
        $order = new Order();
        $order->setOfUser($this->getUser());
        $order->setStatus(OrderStatus::enPreparation);
        $order->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($order);
        $cart = json_decode(json_encode($session->get('cart')), true);

        $logger->info("cart : " . json_encode($session->get('cart')));

        foreach ($cart as $productCart) {
            // Skip non-product items like 'cartCount' and 'cartTotal'
            if (!is_array($productCart) || !isset($productCart['product'], $productCart['quantity'])) {
                continue;
            }

            $product = $entityManager->getRepository(Product::class)->find($productCart['product']['id']);
            $product->setStock($product->getStock() - $productCart['quantity']);


            $orderItem = new OrderItem();
            $orderItem->setOfOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setProductPrice($productCart['product']['price']);
            $orderItem->setQuantity($productCart['quantity']);

            $entityManager->persist($orderItem);
            
        }

        $entityManager->flush();

        $session->remove('cart');
        $this->addFlash('success', 'Votre commande a été passée avec succès');
        return $this->redirectToRoute('orders_user');   
    }
}
