<?php

namespace App\RemoteEvent;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('lemon-squeezy')]
final class LemonSqueezyWebhookConsumer implements ConsumerInterface {
	public function __construct(
		private EntityManagerInterface $entityManager
	) {
	}

	public function consume(RemoteEvent $event): void {
		$payload = $event->getPayload();

		//$this->getUser will not work in webhooks as no authenticated user in that processAdd comment
		$userId = $payload['meta']['custom_data']['user_id'] ?? null;

		if(!$userId){
			throw new \InvalidArgumentException(
				sprintf('User id not found in LemonSqueezy webhook "%s"', $event->getId())
			);
		}

		$user = $this->entityManager->getRepository(User::class)->find($userId);

		if (!$user) {
			throw new EntityNotFoundException(
				sprintf('User "%s" not found for LeminSqueezy webhook "%s"', $userId, $event->getId()));
		}

		match ($event->getName()) {
			'order_created' => $this->handleOrderCreatedEvent($event, $user),
			default => throw new \LogicException('Unsupported LemonSqueezy event: "%s"', $event->getId())
		};
	}
}
