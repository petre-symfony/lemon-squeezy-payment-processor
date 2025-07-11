<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Store\LemonSqueezyApi;
use App\Store\ShoppingCart;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrderController extends AbstractController {
	#[Route('/cart', name: 'app_order_cart')]
	public function cart(ShoppingCart $cart): Response {
		return $this->render('order/cart.html.twig', [
			'cart' => $cart,
		]);
	}

	#[Route('/cart/product/{slug:product}/add', name: 'app_cart_product_add', methods: ['POST'])]
	public function addProductToCart(Request $request, Product $product, ShoppingCart $cart): Response {
		$quantity = $request->request->getInt('quantity', 1);
		$quantity = $quantity > 0 ? $quantity : 1;
		$cart->addProduct($product, $quantity);

		$this->addFlash('success', 'Yummy lemonade has been added to your cart!');

		return $this->redirectToRoute('app_order_cart');
	}

	#[Route('/cart/product/{slug:product}/delete', name: 'app_cart_product_delete', methods: ['POST'])]
	public function deleteProductFromCart(Product $product, ShoppingCart $cart): Response {
		$cart->deleteProduct($product);

		$this->addFlash('success', 'Yummy lemonade has been deleted from your cart! Too sour?');

		return $this->redirectToRoute('app_order_cart');
	}

	#[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
	public function clearCart(ShoppingCart $cart): Response {
		$cart->clear();

		$this->addFlash('success', 'Cart cleared!');

		return $this->redirectToRoute('app_order_cart');
	}

	#[Route('/checkout', name: 'app_order_checkout')]
	public function checkout(
		LemonSqueezyApi $lsApi,
		#[CurrentUser] User $user
	): Response {
		$lsCheckoutUrl = $lsApi->createCheckoutUrl($user);

		return $this->redirect($lsCheckoutUrl);
	}

	#[Route('/checkout/success', name: 'app_order_success')]
	public function success(
		Request $request,
		ShoppingCart $cart,
		LemonSqueezyApi $lsApi
	): Response {
		$referer = $request->headers->get('referer');
		$lsStoreUrl = $lsApi->retrieveStoreUrl();

		if (!str_starts_with($referer, $lsStoreUrl)) {
			return $this->redirectToRoute('app_homepage');
		}
		if ($cart->isEmpty()) {
			return  $this->redirectToRoute('app_homepage');
		}

		$cart->clear();
		$this->addFlash('success', 'Thanks for your order!');

		return $this->redirectToRoute('app_homepage');
	}

	#[Route('/checkout/create', name: 'app_order_checkout_create', methods: ['POST'])]
	public function createCheckout(
		LemonSqueezyApi $lsApi,
		#[CurrentUser] User $user
	): Response {
		return $this->json([
			'targetUrl' => $lsApi->createCheckoutUrl($user, true)
		]);
	}

	#[Route('/checkout/handle', name: 'app_order_checkout_handle', methods: ['POST'])]
	public function handleCheckout(
		Request $request,
		EntityManagerInterface $entityManager,
		#[CurrentUser] User $user
	): Response {
		$userId = $request->request->get('userId');
		if ($userId !== (string) $user->getId()) {
			throw $this->createAccessDeniedException(
				sprintf(
					'Current user ID "%s" does not match the user ID "%s" of the order',
					$user->getId(),
					$userId
				)
			);
		}

		$lsCustomerId = $request->request->get('lsCustomerId');
		$user->setLsCustomerId($lsCustomerId);

		$entityManager->flush();

		return $this->json([]);
	}
}
