<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Sender;

use Tlapnet\Notifications\Notification\Notification;

interface ISender
{

	public function send(Notification $notification): void;

}
