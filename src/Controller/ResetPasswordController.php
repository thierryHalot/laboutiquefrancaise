<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Service\MailService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;
    private $mailService;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }


    /**
     * This method sends an email to the user with a link allowing him to change his password (generates a token valid for 3 hours)
     * @Route("/mot-de-passe-oublié", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')) {

            $email = $request->get('email');
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($email);
            if ($user) {
                //1 : Save in database the reset_password request with user, token, createdAt.
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                $resetPassword->setToken(uniqid());
                $resetPassword->setCreatedAt(new DateTime());
                $this->entityManager->persist($resetPassword);
                $this->entityManager->flush();

                //2 : Send an email to the user with a link to update their password  
                $url = "http://".$_SERVER['SERVER_NAME'].$this->generateUrl('update_password',['token' => $resetPassword->getToken()]);
            
                $content = "Bonjour".$user->getFirstName()."</br>Vous avez demandé à réinitialisé votre mot de passe sur la boutique française.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe </a>";
                $this->mailService->send($user->getEmail(),$user->getFullName(), "Réinitialisée votre mot de passe sur la boutique française",$content);
                $this->addFlash("notice","Vous allez reçevoir dans quelques secondes un mail avec la procédure pour réinitialiser votre mot de passe.");
            }else{
                $this->addFlash("notice","Cette adresse email est inconnu.");
                return $this->redirectToRoute('reset_password');
            }  
        }
        return $this->render('reset_password/index.html.twig');
    }

    /**
     * This method allows to modify the password of the user with respect to a token (the token is valid 3h)
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request,UserPasswordEncoderInterface $encoder, $token): Response
    {
        $resetPassword = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$resetPassword) {
            return $this->redirectToRoute('reset_password');
        }
        
        $now = new DateTime();
        if($now > $resetPassword->getCreatedAt()->modify('+ 3 hour'))
        {
            $this->addFlash("notice","Votre demande de mot de passe a espiré.Merci de la renouveller.");
            return $this->redirectToRoute('reset_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            $newPassword = $form->get('new_password')->getData();
            $user = $resetPassword->getUser();
            $user->setPassword($encoder->encodePassword($user, $newPassword));
            $this->entityManager->flush();
            $this->addFlash("notice","Votre mot de passe a bien été mis à jour");
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('reset_password/update.html.twig',[
            'form'=> $form->createView(),
        ]);
    }
}
