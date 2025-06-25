<?php

namespace App\Tests\Webhook;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LemonSqueezyRequestParserTest extends WebTestCase {
	use ResetDatabase;

	public function testOrderCreatedWebhook(): void {
		$client = static::createClient();

		$user = UserFactory::new()->create([
			'email' => 'test@example.com',
			'plainPassword' => 'testpass',
			'firstName' => 'Test'
		]);

		$json = file_get_contents(__DIR__ . '/../Fixtures/order_created.json');

		$client->request('POST', '/webhook/lemon-squeezy', [], [], [], $json);

		$this->assertResponseIsSuccessful('Webhook failed!');
		$this->assertNotNull($user->getLsCustomerId(), 'LemonSqueezy customer id not set');
		$this->assertEquals(6118393, $user->getLsCustomerId(), "LemonSqueezy customer id mismatch");
	}
}
