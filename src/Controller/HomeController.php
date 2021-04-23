<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Header;
use App\Entity\Product;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * This methode redirect to the home Page
     * @Route("/", name="home")
     */
    public function index(MailService $test, Cart $cart): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findByIsBest(true);
        $headers = $this->entityManager->getRepository(Header::class)->findAll();

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'headers' => $headers
        ]);
    }
}
