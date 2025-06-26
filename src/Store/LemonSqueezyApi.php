<?php

namespace App\Store;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class LemonSqueezyApi {
	public function __construct(
		#[Target('lemonSqueezyClient')]
		private HttpClientInterface $client,
		private ShoppingCart $cart,
		private UrlGeneratorInterface $urlGenerator,
		#[Autowire('%env(LEMON_SQUEEZY_STORE_ID)%')]
		private string $storeId
	) {
	}

	public function createCheckoutUrl(User $user) {
		if ($this->cart->isEmpty()) {
			throw new \LogicException('Nothing to checkout!');
		}

		$products = $this->cart->getProducts();
		$variantId = $products[0]->getLsVariantId();

		$attributes = [];
		$attributes['checkout_data']['email'] = $user->getEmail();
		$attributes['checkout_data']['name'] = $user->getFirstName();

		$attributes['checkout_data']['custom']['user_id'] = $user->getId();

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
			$this->urlGenerator->generate('app_order_success', [], UrlGeneratorInterface::ABSOLUTE_URL);

		$response = $this->client->request(Request::METHOD_POST, 'checkouts', [
			'json' => [
				'data' => [
					'type' => 'checkouts',
					'attributes' => $attributes,
					'relationships' => [
						'store' => [
							'data' => [
								'type' => 'stores',
								'id' => $this->storeId
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

		dd($response->getContent(false));

		$lsCheckout = $response->toArray();

		return $lsCheckout['data']['attributes']['url'];
	}

	public function retrieveStoreUrl(): string {
		$response = $this->client->request(Request::METHOD_GET, 'stores/' . $this->storeId);
		$lsStore = $response->toArray();

		return $lsStore['data']['attributes']['url'];
	}

	public function listOrders(User $user): array {
		$lsCustomerId = $user->getLsCustomerId();
		if(!$lsCustomerId) {
			return [];
		}
		$lsCustomer = $this->retrieveCustomer($lsCustomerId);

		$response = $this->client->request(Request::METHOD_GET, 'orders', [
			'query' => [
				'filter' => [
					'store_id' => $this->storeId,
					'user_email' => $lsCustomer['data']['attributes']['email']
				],
				'page' => [
					'size' => 5
				]
			]
		]);

		return $response->toArray();
	}

	public function retrieveCustomer(string $customerId): array {
		$response = $this->client->request(Request::METHOD_GET, 'customers/' . $customerId);

		return $response->toArray();
	}
}