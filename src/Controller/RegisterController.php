<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegisterType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;
    private $mailService;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }
    /**
     * This method allows you to register a new user
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $notification = null;
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());
            if (!$search_email) {
                # code...
                $pswd = $encoder->encodePassword($user,$user->getPassword());
                $user->setPassword($pswd);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $content = "Bonjour ".$user->getFirstname()."<br/>Bienvenue sur la première boutique dédié au made in france.<br/><br/>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
                $this->mailService->send($user->getEmail(),$user->getFirstname(),'Bienvenue sur la boutique française', $content);

            }else{
                $notification = "Votre inscription s'est correctement déroulée, vous pouvez dès a present vous connecter à votre compte.";
            }
            $notification = "L'émail que vous avez renseigner existe déjà.";
        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification 
        ]);
    }
}
