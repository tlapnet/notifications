<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Notifications\Fixtures\Queue;

use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Queue\IQueueHandler;

class TestQueueHandler implements IQueueHandler
{

	public function getSize(): int
	{
		// TODO: Implement getSize() method.
	}

	public function enqueue(Notification $notification): void
	{
		// TODO: Implement enqueue() method.
	}

	/**
	 * @return Notification[]
	 */
	public function peek(int $limit): array // phpcs:ignore
	{
		return [];
	}

	/**
	 * @param Notification[] $notifications
	 */
	public function requeue(array $notifications): void
	{
		// TODO: Implement requeue() method.
	}

	/**
	 * @param Notification[] $notifications
	 */
	public function remove(array $notifications): void
	{
		// TODO: Implement remove() method.
	}

	public function clear(): void
	{
		// TODO: Implement clear() method.
	}

}
