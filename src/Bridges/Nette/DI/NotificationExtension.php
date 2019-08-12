<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Bridges\Nette\DI;

use Contributte\DI\Helper\ExtensionDefinitionsHelper;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;
use Tlapnet\Notifications\Bridges\Nette\Tracy\NotificationPanel;
use Tlapnet\Notifications\Bridges\Nette\Tracy\TraceableNotifier;
use Tlapnet\Notifications\NotificationManager;
use Tlapnet\Notifications\Notifier\QueueNotifier;
use Tlapnet\Notifications\Notifier\SenderNotifier;
use Tlapnet\Notifications\Queue\QueueFlusher;
use Tlapnet\Notifications\Sender\SenderContainer;

/**
 * @property-read stdClass $config
 */
class NotificationExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'debugMode' => Expect::bool(false),
			'queueHandler' => Expect::type('mixed')->required(),
			'senders' => Expect::array(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;
		$definitionsHelper = new ExtensionDefinitionsHelper($this->compiler);

		// Add senders
		$container = $builder->addDefinition($this->prefix('senderContainer'))
			->setType(SenderContainer::class);

		foreach ($config->senders as $notification => $sender) {
			$container->addSetup('add', [$notification, new Statement($sender)]);
		}

		// Queue handler
		$queueHandlerPrefix = $this->prefix('queueHandler');
		$queueHandler = $definitionsHelper->getDefinitionFromConfig($config->queueHandler, $queueHandlerPrefix);

		// Notifiers
		$senderNotifier = $config->debugMode
			? new Statement(TraceableNotifier::class, [new Statement(SenderNotifier::class)])
			: new Statement(SenderNotifier::class);
		$queueNotifier = $config->debugMode
			? new Statement(TraceableNotifier::class, [new Statement(QueueNotifier::class)])
			: new Statement(QueueNotifier::class);

		$builder->addDefinition($this->prefix('senderNotifier'))
			->setFactory($senderNotifier)
			->setAutowired(false);
		$builder->addDefinition($this->prefix('queueNotifier'))
			->setFactory($queueNotifier)
			->setAutowired(false);

		// Notification panel for debug mode
		if ($config->debugMode) {
			$builder->addDefinition($this->prefix('notificationPanel'))
				->setFactory(NotificationPanel::class, [
					$senderNotifier,
					$queueNotifier,
				]);
		}

		// Notification manager and Queue flusher
		$builder->addDefinition($this->prefix('notificationManager'))
			->setFactory(NotificationManager::class, [
				$senderNotifier,
				$queueNotifier,
			]);
		$builder->addDefinition($this->prefix('queueFlusher'))
			->setFactory(QueueFlusher::class, [
				$queueHandler,
				$senderNotifier,
			]);
	}

	/**
	 * Show notification panel in tracy
	 */
	public function afterCompile(ClassType $class): void
	{
		$config = $this->config;

		if ($config->debugMode) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody(
				'$this->getService(?)->addPanel($this->getService(?));',
				['tracy.bar', $this->prefix('notificationPanel')]
			);
		}
	}

}
