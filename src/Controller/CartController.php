<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cartService->getCart(),
            'total'=>$cartService->getTotal()
        ]);
    }

    #[Route('/cart/add/{id}/{quantity}', name: 'app_cart_add')]
    #[Route('/cart/addfromcart/{id}/{quantity}', name: 'app_cart_add_from_cart')]
    public function add(Request $request,Product $product, $quantity, CartService $cartService): Response
    {
        $cartService->addProduct($product, $quantity);
        $routeName = $request->attributes->get("_route");

        $redirection = 'app_home';

        if($routeName ==="app_cart_add_from_cart" ){

            $redirection ='app_cart';
        }
        return $this->redirectToRoute($redirection);
    }

    #[Route('/cart/removeone/{id}', name: 'app_cart_remove_one')]
    public function removeOne(CartService $cartService, Product $product): Response
    {
        $cartService->removeProduct($product);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/removewhole/{id}', name: 'app_cart_remove_whole')]
    public function removewhole(CartService $cartService, Product $product): Response
    {
        $cartService->removeProductRow($product);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/empty', name: 'app_cart_empty')]
    public function emptyCart(CartService $cartService): Response
    {
        $cartService->emptyCart();

        return $this->redirectToRoute('app_cart');
    }
}