<?php
namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use \Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CreditCardRepository;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function viewCart(): Response
    {
        return $this->render('cart/index.html.twig');
    }

    #[Route('/cart/vider', name:'vider_cart')]
    public function vider_cart(SessionInterface $session): Response
    {
        $session->remove('cart');

        return $this->redirectToRoute('app_cart', [
            'message' => 'Votre panier a été vidé avec succès.'
        ]);
    }

    #[Route('/cart/add/{idProd}', name: 'add_to_cart', methods: ['post'])]
    public function addToCart(int $idProd, Request $request, EntityManagerInterface $em, SessionInterface $session): JsonResponse
    {
        $product = $em->getRepository(Product::class)->find($idProd);

        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? (int) 1 ;

        $cart = $session->get('cart', []);
        
        if (!isset($cart[$idProd])) {
            $cart[$idProd] = [
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice()
                ],
                 'quantity' => $quantity
                ];
        } else {
            $cart[$idProd]['quantity'] += $quantity;
        }

        $cartItemCount = array_sum(array_column($cart, 'quantity'));
        $cartTotal = array_reduce($cart, function ($total, $item) {
            return is_array($item) && isset($item['product'], $item['quantity'])
                ? $total + ($item['product']['price'] * $item['quantity'])
                : $total;
        }, 0);
        

        $cart['cartCount'] = $cartItemCount;
        $cart['cartTotal'] = $cartTotal;

        $session->set('cart', $cart);

        return new JsonResponse([
            'cart' => $session->get('cart', [])
        ]);
    }

    #[Route('/cart/remove/{idProd}', name: 'remove_from_cart', methods: ['post'])]
    public function removeFromCart(int $idProd, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);

        if (!isset($cart[$idProd])) {
            return new JsonResponse(['error' => 'Produit non trouvé dans le panier'], 404);
        }

        unset($cart[$idProd]);

        $productItems = array_filter($cart, fn($key) => is_numeric($key), ARRAY_FILTER_USE_KEY);

        $cartItemCount = array_sum(array_column($productItems, 'quantity'));
        $cartTotal = array_reduce($productItems, function ($total, $item) {
            return $total + ($item['product']['price'] * $item['quantity']);
        }, 0);

        $cart['cartCount'] = $cartItemCount;
        $cart['cartTotal'] = $cartTotal;

        $session->set('cart', $cart);

        return new JsonResponse(['cart' => $cart]);
    }

    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(CreditCardRepository $cardRepo): Response
    {
        $user = $this->getUser();
        if(!$user){
            $this->addFlash('info', 'Vous devez vous connecter pour pouvoir finaliser votre commande');
            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('add_credit_card');
    }

    #[Route("/cart/update", name: "update_cart_prod_quantity", methods: ["POST"])]
    public function updateCartQuantity(LoggerInterface $logger, Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'];
        $newQuantity = $data['quantity'];

        $logger->info("Request data: " . json_encode($data));

        // Fetch the cart and log it for debugging
        $cart = $session->get('cart', []);
        $logger->info("Current cart: " . json_encode($cart));

        // Update quantity if the product exists in the cart
        if (isset($cart[$productId]) && is_array($cart[$productId]) && $newQuantity > 0) {
            $cart[$productId]['quantity'] = $newQuantity;  
        }

        // Recalculate cart total and item count
        $cartTotal = 0;
        $cartCount = 0;
        foreach ($cart as $item) {
            if (is_array($item) && isset($item['product']['price'], $item['quantity'])) {
                $cartTotal += $item['product']['price'] * $item['quantity'];
                $cartCount += $item['quantity'];
            }
        }

        // Update cart metadata
        $cart['cartCount'] = $cartCount;
        $cart['cartTotal'] = $cartTotal;
        $session->set('cart', $cart);

        return new JsonResponse(['cart' => $cart]);
    }

    #[Route("/cart/remove", name: "remove_cart_prod_quantity", methods: ["POST"])]
    public function removeItem(LoggerInterface $logger, Request $request, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        $logger->info("Request cart: " . json_encode($cart));
        $productId = json_decode($request->getContent(), true)['productId'];
        $logger->info("productId: " . $productId);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);

            $cartCount = 0;
            $cartTotal = 0.0;

            foreach ($cart as $key => $item) {
                if (is_array($item)) {
                    $cartCount += $item['quantity'];
                    $cartTotal += $item['product']['price'] * $item['quantity'];
                }
            }

            $cart['cartCount'] = $cartCount;
            $cart['cartTotal'] = $cartTotal;

            $session->set('cart', $cart);
        }

        return new JsonResponse(['cart' => $cart]);
    }


}
