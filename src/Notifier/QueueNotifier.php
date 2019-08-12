<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Notifier;

use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Queue\IQueueHandler;

class QueueNotifier implements INotifier
{

	/** @var IQueueHandler */
	private $handler;

	public function __construct(IQueueHandler $handler)
	{
		$this->handler = $handler;
	}

	public function notify(Notification $notification): void
	{
		$this->handler->enqueue($notification);
	}

}
