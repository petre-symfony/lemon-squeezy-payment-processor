<?php

namespace App\Tests\Webhook;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LemonSqueezyRequestParserTest extends WebTestCase {
	use ResetDatabase;

	public function testOrderCreatedWebhook(): void {
		$client = static::createClient();

		UserFactory::new()->create([
			'email' => 'test@example.com',
			'plainPassword' => 'testpass',
			'firstName' => 'Test'
		]);

		$crawler = $client->request('POST', '/webhook/lemon-squeezy', [], [], [], $json);

		$this->assertResponseIsSuccessful('Webhook failed!');
	}
}
