<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Notifier;

use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Sender\SenderContainer;

class SenderNotifier implements INotifier
{

	/** @var SenderContainer */
	private $senderContainer;

	public function __construct(SenderContainer $senderContainer)
	{
		$this->senderContainer = $senderContainer;
	}

	public function notify(Notification $notification): void
	{
		$this->senderContainer->get(get_class($notification))
			->send($notification);
	}

}
