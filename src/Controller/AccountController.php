<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * This method returns the view corresponding to the user's account management
     * @Route("/compte", name="account")
     */
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }
    
}
