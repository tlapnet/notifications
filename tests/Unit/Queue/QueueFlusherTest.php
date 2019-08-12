<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Notifications\Unit\Queue;

use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Tlapnet\Notifications\Exception\NotifyFailedException;
use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Notifier\INotifier;
use Tlapnet\Notifications\Queue\IQueueHandler;
use Tlapnet\Notifications\Queue\QueueFlusher;

final class QueueFlusherTest extends TestCase
{

	public function testIsEmpty(): void
	{
		/** @var IQueueHandler|Mock $queue */
		$queue = Mockery::mock(IQueueHandler::class)
			->shouldReceive('getSize')
			->once()
			->andReturn(0)
			->getMock();

		$qf = new QueueFlusher($queue, Mockery::mock(INotifier::class));

		$this->assertTrue($qf->isEmpty());
	}

	public function testIsNotEmpty(): void
	{
		/** @var IQueueHandler|Mock $queue */
		$queue = Mockery::mock(IQueueHandler::class)
			->shouldReceive('getSize')
			->once()
			->andReturn(15)
			->getMock();

		$qf = new QueueFlusher($queue, Mockery::mock(INotifier::class));

		$this->assertFalse($qf->isEmpty());
	}

	public function testFlush(): void
	{
		$successNotification = new class extends Notification {

		};

		$failedNotification = new class extends Notification {

		};

		/** @var INotifier|Mock $notifier */
		$notifier = Mockery::mock(INotifier::class)
			->shouldReceive('notify')
			->withArgs([$failedNotification])
			->once()
			->andThrow(NotifyFailedException::class)
			->getMock()
			->shouldReceive('notify')
			->withArgs([$successNotification])
			->once()
			->getMock();

		/** @var IQueueHandler|Mock $queue */
		$queue = Mockery::mock(IQueueHandler::class)
			->shouldReceive('peek')
			->once()
			->withArgs([5])
			->andReturn([201 => $successNotification, 666 => $failedNotification])
			->getMock()
			->shouldReceive('remove')
			->withArgs([[201 => $successNotification]])
			->once()
			->getMock()
			->shouldReceive('requeue')
			->withArgs([[666 => $failedNotification]])
			->once()
			->getMock();

		$qf = new QueueFlusher($queue, $notifier);
		$qf->flush(5);
		$this->assertTrue(true);
	}

}
