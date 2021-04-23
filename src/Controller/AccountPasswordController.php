<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * This method allows you to change the user's password from his account
     * @Route("/compte/modifier-mon-mot-de-passe", name="account_password")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $oldPassword = $form->get('old_password')->getData();
            
            if($encoder->isPasswordValid($user, $oldPassword)){
                $newPassword = $form->get('new_password')->getData();
                $user->setPassword($encoder->encodePassword($user,$newPassword));
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $notification = "Votre mot de passe à bien été mis à jour.";
            }else{
                $notification = "Votre mot de passe actuel n'est pas le bon.";
            }
        }

        return $this->render('account/password.html.twig',[
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
