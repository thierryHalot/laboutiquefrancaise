<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAdressController extends AbstractController
{
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * This method returns the view corresponding to the user's addresses
     * @Route("/compte/adresses", name="account_address")
     */
    public function index(): Response
    {
        return $this->render('account/address.html.twig');
    }

    /**
     * This method allows you to create a new delivery address for a user
     * @Route("/compte/ajouter-une-adresses", name="account_address_add")
     */
    public function add(Cart $cart, Request $request)
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            if ($cart->get()) {
                return $this->redirectToRoute('order');
            } else {
                return $this->redirectToRoute('account_address');
            }
        }
        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This method allows you to update a delivery address corresponding to a user
     * @Route("/compte/modifier-une-adresses/{id}", name="account_address_edit")
     */
    public function edit(Request $request, $id)
    {
        $address = $this->entityManager->getRepository(Address::class)->find($id);
        $form = $this->createForm(AddressType::class, $address);

        if (is_null($address) || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_address');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            return $this->redirectToRoute('account_address');
        }
        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This method allows you to delete a user's delivery address
     * @Route("/compte/supprimer-une-adresses/{id}", name="account_address_delete")
     */
    public function delete(Request $request, $id)
    {
        $address = $this->entityManager->getRepository(Address::class)->find($id);

        if ($address && $address->getUser() == $this->getUser()) {
            $this->entityManager->remove($address);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('account_address');
    }
}
