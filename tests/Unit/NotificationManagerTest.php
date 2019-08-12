<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Notifications\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;
use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\NotificationManager;
use Tlapnet\Notifications\Notifier\INotifier;

class NotificationManagerTest extends TestCase
{

	/** @var NotificationManager */
	private $manager;

	protected function setUp(): void
	{
		$fakeNotifier = new class implements INotifier
		{

			public function notify(Notification $notification): void
			{
				throw new Exception('Notifier called');
			}

		};

		parent::setUp();
		$this->manager = new NotificationManager($fakeNotifier, $fakeNotifier);
	}

	public function testQueue(): void
	{
		$this->expectException(Throwable::class);
		$this->expectExceptionMessage('Notifier called');

		$notification = new class extends Notification
		{

		};

		$this->manager->queue($notification);
	}

	public function testSend(): void
	{
		$this->expectException(Throwable::class);
		$this->expectExceptionMessage('Notifier called');

		$notification = new class extends Notification
		{

		};

		$this->manager->send($notification);
	}

}
