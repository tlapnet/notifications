<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Bridges\Nette\Tracy;

use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Notifier\INotifier;

class TraceableNotifier implements INotifier
{

	/** @var INotifier */
	private $notifier;

	/** @var Notification[] */
	private $notifications = [];

	public function __construct(INotifier $notifier)
	{
		$this->notifier = $notifier;
	}

	public function notify(Notification $notification): void
	{
		// Log action
		$this->notifications[] = $notification;

		// Delegate to original notifier
		$this->notifier->notify($notification);
	}

	/**
	 * @return Notification[]
	 */
	public function getNotifications(): array
	{
		return $this->notifications;
	}

}
