<?php

namespace App\Tests\Webhook;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LemonSqueezyRequestParserTest extends WebTestCase {
	public function testSomething(): void {
		$client = static::createClient();
		$crawler = $client->request('GET', '/');

		$this->assertResponseIsSuccessful();
		$this->assertSelectorTextContains('h1', 'Hello World');
	}
}
