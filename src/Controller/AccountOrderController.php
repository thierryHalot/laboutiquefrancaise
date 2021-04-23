<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountOrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * This method returns the view of all the user's commands
     * @Route("/compte/mes-commandes", name="account_order")
     */
    public function index(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findSuccessOrder($this->getUser());
       
        return $this->render('account/order.html.twig',[
            "orders" => $orders
        ]);
    }

     /**
      * This method returns the details of an order made by the user
     * @Route("/compte/mes-commandes/{reference}", name="account_order_show")
     */
    public function show($reference): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order || $order->getUser() != $this->getUser()) {
            $this->redirectToRoute('account_order');
        }
        return $this->render('account/order_show.html.twig',[
            "order" => $order
        ]);
    }
}
