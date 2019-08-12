<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Notifier;

use Tlapnet\Notifications\Notification\Notification;

interface INotifier
{

	public function notify(Notification $notification): void;

}
