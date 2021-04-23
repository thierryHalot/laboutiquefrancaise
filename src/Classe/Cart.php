<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private $session;
    private $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    /**
     * Returns an array corresponding to the user's cart.
     * The key corresponds to the product id.
     * The value corresponds to the product quantity.
     * @return array
     */
    public function get()
    {
        return (!is_null($this->session->get('cart'))) ? $this->session->get('cart') : [];
    }

    /**
     * Returns an array containing all products selected by the user.
     * The key['product'] corresponds to the product object.
     * The key['quantity'] corresponds to the product quantity.
     * @return array
     */
    public function getFull()
    {
        $cartComplete = [];
        foreach ($this->get() as $id => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($id);
            if (is_null($product)) {
                $this->delete($id);
                continue;
            }
            $cartComplete[] = [
                'product' => $product,
                'quantity' => (int) $quantity
            ];
        }
        return $cartComplete;
    }

    /**
     * This method allows you to add a product to the user's shopping cart.
     * The cart is stored in session.
     * @param [int] $id
     */
    public function add($id)
    {
        $cart = $this->session->get('cart', []);
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $this->session->set('cart', $cart);
    }

    /**
     * This method allows you to reduce the quantity of a product contained in the cart.
     * @param [int] $id
     * @return array
     */
    public function decrease($id)
    {
        $cart = $this->session->get('cart', []);
        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }
        return $this->session->set('cart', $cart);
    }

    /**
     * This method allows you to delete all the contents of the user's cart
     */
    public function remove()
    {
        $this->session->remove('cart');
    }

    /**
     * This method allows you to remove a product from your cart
     * @param [int] $id
     * @return array
     */
    public function delete($id)
    {
        $cart = $this->session->get('cart', []);
        unset($cart[$id]);
        return $this->session->set('cart', $cart);
    }
}
