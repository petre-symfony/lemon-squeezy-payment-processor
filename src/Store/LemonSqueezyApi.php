<?php

namespace App\Store;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class LemonSqueezyApi {
	public function __construct(
		#[Target('lemonSqueezyClient')]
		private HttpClientInterface $client,
		private ShoppingCart $cart
	) {
	}

	public function createCheckoutUrl(?User $user) {
		if ($this->cart->isEmpty()) {
			throw new \LogicException('Nothing to checkout!');
		}

		$products = $this->cart->getProducts();
		$variantId = $products[0]->getLsVariantId();

		$attributes = [];

		if ($user) {
			$attributes['checkout_data']['email'] = $user->getEmail();
			$attributes['checkout_data']['name'] = $user->getFirstName();
		}

		if (count($products) === 1) {
			$attributes['checkout_data']['variant_quantities'] = [
				[
					'variant_id' => $variantId,
					'quantity' => $this->cart->getProductQuantity($products[0])
				]
			];
		} else {
			$attributes['custom_price'] = $this->cart->getTotal();
			$description = '';
			foreach ($products as $product) {
				$description .=
					$product->getName() . ' for $' . number_format($product->getPrice() / 100, 2)
					. ' x ' . $this->cart->getProductQuantity($product) . '<br>';
			}
			$attributes['product_options'] = [
				'name' => 'E-Lemonade',
				'description' => $description
			];
		}

		$attributes['product_options']['redirect_url'] =
			$this->generateUrl('app_order_success', [], UrlGeneratorInterface::ABSOLUTE_URL);

		$response = $this->client->request(Request::METHOD_POST, 'checkouts', [
			'json' => [
				'data' => [
					'type' => 'checkouts',
					'attributes' => $attributes,
					'relationships' => [
						'store' => [
							'data' => [
								'type' => 'stores',
								'id' => $this->getParameter('env(LEMON_SQUEEZY_STORE_ID)')
							]
						],
						'variant' => [
							'data' => [
								'type' => 'variants',
								'id' => $variantId
							]
						]
					]
				]
			]
		]);

		$lsCheckout = $response->toArray();

		return $lsCheckout['data']['attributes']['url'];
	}
}