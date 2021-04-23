<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
     /**
      * This method returns the stripe payment form
      * @Route("/commande/create-checkout-session/{reference}", name="stripe_create_session")
      */
     public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
     { 
          $YOUR_DOMAIN = 'http://localhost:8000';
          $products_for_stripe = [];
          $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

          if(!$order){
              return new JsonResponse(['error' => 'order']);
          }

          foreach ($order->getOrderDetails()->getValues() as $orderDetail) {
               $productObject = $entityManager->getRepository(Product::class)->findOneByName($orderDetail->getProduct());
               $products_for_stripe[] = [
                    'price_data' => [
                         'currency' => 'eur',
                         'unit_amount' => $orderDetail->getPrice(),
                         'product_data' => [
                              'name' => $productObject->getName(),
                              'images' => ['http://' . $_SERVER['HTTP_HOST'] . "/uploads/" . $productObject->getIllustration()]
                         ]
                    ],
                    'quantity' => $orderDetail->getQuantity()
               ];
          }

          $products_for_stripe[] = [
               'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $order->getCarrierPrice(),
                    'product_data' => [
                         'name' => $order->getCarrierName(),
                         'images' => ['http://' . $_SERVER['HTTP_HOST']]
                    ]
               ],
               'quantity' => 1
          ];
     
          $apiKey = $this->getParameter('stripe_public_key');
          Stripe::setApiKey($apiKey);
          $checkout_session = Session::create([
               'customer_email' => $this->getUser()->getEmail(),
               'payment_method_types' => ['card'],
               'line_items' => [$products_for_stripe],
               'mode' => 'payment',
               'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
               'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
          ]);

          $order->setStripeSessionId($checkout_session->id);
          $entityManager->persist($order);
          $entityManager->flush();

          return new JsonResponse(['id' => $checkout_session->id]);
     }
}
