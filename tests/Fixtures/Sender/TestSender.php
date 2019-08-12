<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Notifications\Fixtures\Sender;

use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Sender\ISender;

class TestSender implements ISender
{

	public function send(Notification $notification): void
	{
	}

}
