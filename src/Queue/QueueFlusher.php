<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Queue;

use Tlapnet\Notifications\Exception\NotifyFailedException;
use Tlapnet\Notifications\Notifier\INotifier;

class QueueFlusher
{

	/** @var IQueueHandler */
	private $handler;

	/** @var INotifier */
	private $senderNotifier;

	public function __construct(IQueueHandler $handler, INotifier $senderNotifier)
	{
		$this->handler = $handler;
		$this->senderNotifier = $senderNotifier;
	}

	/**
	 * Checks if queue is empty
	 */
	public function isEmpty(): bool
	{
		return $this->handler->getSize() === 0;
	}

	/**
	 * Sends notifications from queue and remove them if successfully notified
	 */
	public function flush(int $limit = 24): void
	{
		$notifications = $this->handler->peek($limit);
		$successful = [];
		$failed = [];

		foreach ($notifications as $id => $notification) {
			try {
				$this->senderNotifier->notify($notification);
				$successful[$id] = $notification;
			} catch (NotifyFailedException $e) {
				$failed[$id] = $notification;
			}
		}

		$this->handler->remove($successful);
		$this->handler->requeue($failed);
	}

	/**
	 * Clears whole queue without sending notifications
	 */
	public function clear(): void
	{
		$this->handler->clear();
	}

}
