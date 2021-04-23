<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;
    private $mailService;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }
    /**
     * This method returns the view corresponding to a successful payment, sends an email to the user to confirm the order 
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        if (!$order || $order->getUser() != $order->getUser()) {
            return $this->redirectToRoute('home');
        }
        if ($order->getState() == 0) {
            $cart->remove();
            $order->setState(1);
            $this->entityManager->flush();
           
            $content = "Bonjour ".$order->getUser()->getFirstname()."<br/>.Merci pour votre commande <br/><br/>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
            $this->mailService->send($order->getUser()->getEmail(),$order->getUser()->getFirstname(),'Votre commande la boutique française est bien valider', $content);
        }
        return $this->render('order_success/index.html.twig', [
            'controller_name' => 'OrderValidateController',
            'order' => $order
        ]);
    }
}
