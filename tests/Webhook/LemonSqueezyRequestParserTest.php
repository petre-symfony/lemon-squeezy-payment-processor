<?php

namespace App\Tests\Webhook;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class LemonSqueezyRequestParserTest extends WebTestCase {
	use ResetDatabase;
	
	public function testOrderCreatedWebhook(): void {
		$client = static::createClient();
		$crawler = $client->request('GET', '/');

		$this->assertResponseIsSuccessful('Webhook failed!');
	}
}
