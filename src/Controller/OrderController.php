<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Item;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/order')]
class OrderController extends AbstractController
{
    #[Route('s', name: 'app_order_get_all')]
    public function index(OrderRepository $orderRepository): Response
    {
        $response = [
            "orders"=>$orderRepository->findAll(),
        ];

        return $this->json($response, 200, [], ["groups" => "order:display_all"]);
    }

    #[Route('/show/{id}', name: 'app_order_show')]
    public function show(Order $order): Response
    {
        $items = [];

        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();

            $items[] = [
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $item->getQuantity(),
            ];
        }

        $response = [
            "order" => [
                "id" => $order->getId(),
                "profile" => [
                    "username" => $order->getProfile()->getUsername(),
                ],
                "items" => $items,
            ],
        ];

        return $this->json($response, 200, [], ["groups" => "order:display_1"]);
    }

    #[Route('/delete/{id}', name: 'app_order_delete')]
    public function delete(Order $order, EntityManagerInterface $manager): Response
    {
        $manager->remove($order);
        $manager->flush();

        return $this->json('Order deleted successfully', 200);
    }

    #[Route('/show/cart', name: 'app_cart')]
    public function cart(): Response
    {
        $cart = $this->getUser()->getProfile()->getCart();

        if (!$cart) {
            return $this->json("No cart");
        }

        $items = [];
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $items[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $item->getQuantity(),
            ];
        }

        $response = [
            "cart" => [
                "cart_id" => $cart->getId(),
                "profile" => [
                    "username" => $cart->getProfile()->getUsername(),
                ],
                "items" => $items,
            ],
        ];

        return $this->json($response, 200, [], ["groups" => "cart:display"]);
    }

    #[Route('/add/{id}', name: 'app_order_add_product')]
    public function add(Product $product, EntityManagerInterface $manager, CartRepository $cartRepository): Response
    {
        if (!$product) {
            return $this->json('Bad QR code, please retry.');
        }

        $profile = $this->getUser()->getProfile();
        $cart = $profile->getCart();

        if (!$cart) {
            $cart = new Cart();
            $cart->setProfile($profile);
        }

        $existingItem = $cart->getItemByProduct($product);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + 1);
        } else {
            $item = new Item();
            $item->setProduct($product);
            $item->setQuantity(1);
            $cart->addItem($item);
        }

        $manager->persist($cart);
        foreach ($cart->getItems() as $item) {
            $manager->persist($item);
        }
        $manager->flush();

        return $this->json('Product added to cart.');
    }

    #[Route('/remove/{id}', name: 'app_remove_product')]
    public function remove(Product $product, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $cart = $user->getProfile()->getCart();

        if (!$cart) {return $this->json('No cart found', 404);}

        $item = $cart->getItemByProduct($product);

        if (!$item) {return $this->json('Product not found in cart', 404);}

        if ($item->getQuantity() > 1) {
            $item->setQuantity($item->getQuantity() - 1);
            $manager->persist($item);
        } else {
            $cart->removeItem($item);
            $manager->remove($item);
        }

        $manager->flush();

        return $this->json('Product removed from cart', 200);
    }

    #[Route('/payment', name: 'app_order_payment')]
    public function payment(EntityManagerInterface $manager): Response
    {
        $profile = $this->getUser()->getProfile();
        $cart = $profile->getCart();

        if (!$cart) {
            return $this->json('No cart found', 404);
        }

        $newOrder = new Order();
        $newOrder->setDate(new \DateTime());
        $newOrder->setProfile($profile);

        foreach ($cart->getItems() as $item) {
            $newOrder->addItem($item);

            $cart->removeItem($item);
        }

        $manager->persist($newOrder);
        $manager->flush();

        return $this->json('Commande payée', 200);
    }
}
