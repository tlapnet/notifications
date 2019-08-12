<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Notifications\Unit\Bridges\Nette\DI;

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use PHPUnit\Framework\TestCase;
use Tests\Tlapnet\Notifications\Fixtures\Queue\TestQueueHandler;
use Tests\Tlapnet\Notifications\Fixtures\Sender\TestSender;
use Tlapnet\Notifications\Bridges\Nette\DI\NotificationExtension;
use Tlapnet\Notifications\Bridges\Nette\Tracy\NotificationPanel;
use Tlapnet\Notifications\Bridges\Nette\Tracy\TraceableNotifier;
use Tlapnet\Notifications\NotificationManager;
use Tlapnet\Notifications\Notifier\QueueNotifier;
use Tlapnet\Notifications\Notifier\SenderNotifier;
use Tlapnet\Notifications\Queue\IQueueHandler;
use Tlapnet\Notifications\Queue\QueueFlusher;
use Tlapnet\Notifications\Sender\SenderContainer;

class NotificationExtensionTest extends TestCase
{

	public function testRegistration(): void
	{
		$loader = new ContainerLoader(__DIR__ . '/../../../../../temp/tests', true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addConfig([
				'notifications' => [
					'senders' => [
						'test' => TestSender::class,
					],
					'queueHandler' => TestQueueHandler::class,
				],
			]);
			$compiler->addExtension('notifications', new NotificationExtension());
		}, microtime(true));
		/** @var Container $container */
		$container = new $class();

		$this->assertInstanceOf(TestQueueHandler::class, $container->getByType(IQueueHandler::class));
		$this->assertInstanceOf(NotificationManager::class, $container->getByType(NotificationManager::class));
		$this->assertInstanceOf(QueueFlusher::class, $container->getByType(QueueFlusher::class));

		$this->assertInstanceOf(SenderContainer::class, $container->getByType(SenderContainer::class));
		$senderContainer = $container->getByType(SenderContainer::class);
		$this->assertInstanceOf(TestSender::class, $senderContainer->get('test'));

		$this->assertInstanceOf(SenderNotifier::class, $container->getService('notifications.senderNotifier'));
		$this->assertInstanceOf(QueueNotifier::class, $container->getService('notifications.queueNotifier'));
	}

	public function testRegistrationWithDebug(): void
	{
		$loader = new ContainerLoader(__DIR__ . '/../../../../../temp/tests', true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addConfig([
				'notifications' => [
					'debugMode' => true,
					'queueHandler' => TestQueueHandler::class,
				],
			]);
			$compiler->addExtension('notifications', new NotificationExtension());
		}, microtime(true));
		/** @var Container $container */
		$container = new $class();

		$this->assertInstanceOf(TraceableNotifier::class, $container->getService('notifications.senderNotifier'));
		$this->assertInstanceOf(TraceableNotifier::class, $container->getService('notifications.queueNotifier'));

		$this->assertInstanceOf(NotificationPanel::class, $container->getByType(NotificationPanel::class));
	}

}
