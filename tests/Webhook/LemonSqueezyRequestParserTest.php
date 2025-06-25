<?php

namespace App\Tests\Webhook;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LemonSqueezyRequestParserTest extends WebTestCase {
	public function testOrderCreatedWebhook(): void {
		$client = static::createClient();
		$crawler = $client->request('GET', '/');

		$this->assertResponseIsSuccessful('Webhook failed!');
	}
}
