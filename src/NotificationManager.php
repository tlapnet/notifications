<?php declare(strict_types = 1);

namespace Tlapnet\Notifications;

use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Notifier\INotifier;

class NotificationManager
{

	/** @var INotifier */
	protected $senderNotifier;

	/** @var INotifier */
	protected $queueNotifier;

	public function __construct(INotifier $senderNotifier, INotifier $queueNotifier)
	{
		$this->senderNotifier = $senderNotifier;
		$this->queueNotifier = $queueNotifier;
	}

	public function send(Notification $notification): void
	{
		$this->senderNotifier->notify($notification);
	}

	public function queue(Notification $notification): void
	{
		$this->queueNotifier->notify($notification);
	}

}
