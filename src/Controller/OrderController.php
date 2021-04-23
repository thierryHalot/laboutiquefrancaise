<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * This method returns the order form view
     * @Route("/commande", name="order")
     */
    public function index(Cart $cart, Request $request): Response
    {
        $addresses = $this->getUser()->getAddresses()->getValues();
        if (empty($addresses)) {
            return $this->redirectToRoute('account_address_add');
        }
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser(),
        ]);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

    /**
     * This method returns the order summary view before payment
     * @Route("/commande/recapitulatif", name="order_recap", methods={"POST"})
     */
    public function add(Cart $cart, Request $request): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //save my order
            $carrier = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();

            $delivery_content = $delivery->getFirstname() . ' ' . $delivery->getLastName();
            $delivery_content .= '<br/>' . $delivery->getPhone();
            if ($delivery->getCompany()) {
                $delivery_content .= '<br/>' . $delivery->getCompany();
            }
            $delivery_content .= '<br/>' . $delivery->getAddress();
            $delivery_content .= '<br/>' . $delivery->getPostal() . ' ' . $delivery->getCity();
            $delivery_content .= '<br/>' . $delivery->getCountry();

            $order = new Order();
            $date = new DateTime();
            $reference = $date->format('dmY').'-'.uniqid();
            $order->setUser($this->getUser());
            $order->setReference($reference);
            $order->setCreatedAt(new DateTime());
            $order->setCarrierName($carrier->getName());
            $order->setCarrierPrice($carrier->getPrice());
            $order->setDelivery($delivery_content);
            $order->setState(0); //non delivré
            $this->entityManager->persist($order);

            //save my products OrderDetail()
            foreach ($cart->getFull() as $product) {
                $orderDetail = new OrderDetails();
                $entity = $product['product'];
                $quantity = $product['quantity'];
                $orderDetail->setMyOrder($order);
                $orderDetail->setProduct($entity->getName());
                $orderDetail->setQuantity($quantity);
                $orderDetail->setPrice($entity->getPrice());
                $orderDetail->setTotal($entity->getPrice() * $quantity);
                $this->entityManager->persist($orderDetail);
            }

            $this->entityManager->flush();

            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carrier' => $carrier,
                'delivery' => $delivery_content,
                'reference' => $order->getReference(),
                'stripePublicKey' => $this->getParameter('stripe_secret_key')
            ]);
        }

        return $this->redirectToRoute('cart');
    }
}
