<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Queue;

use Tlapnet\Notifications\Notification\Notification;

interface IQueueHandler
{

	public function getSize(): int;

	public function enqueue(Notification $notification): void;

	/**
	 * Get items from beginning without deleting
	 *
	 * @return Notification[]
	 */
	public function peek(int $limit): array;

	/**
	 * Return to queue
	 *
	 * @param Notification[] $notifications
	 */
	public function requeue(array $notifications): void;

	/**
	 * Remove from queue
	 *
	 * @param Notification[] $notifications
	 */
	public function remove(array $notifications): void;

	/**
	 * Purges whole queue
	 */
	public function clear(): void;

}
