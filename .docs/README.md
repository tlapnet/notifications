# Notifications

## Content

- [Setup](#setup)
- [Example](#example-usage)
- [Sender](#sender)
- [Notification](#notification)
- [Queue flusher](#queue-flusher)
- [Notification manager](#notificationmanager)

## Setup

Install package

```bash
composer require tlapnet/notifications
```

Register extension

```yaml
extensions:
	notifications: Notifications\Bridges\Nette\DI\NotificationExtension
```

Config accepts 3 options:

* debugMode (optional) - adds panel to tracy bar with config info + sent/queued notification during request
* queueHandler (required) - your implementation of queue. You can pass anything to QueueHandler service
* senders (required) - Here you associate which notification is sent by which sender

example:

```yaml
notifications:
	debugMode: %debugMode%
	queueHandler: App\Model\Notifications\Queue\Queue({
		email: App\Model\Notifications\Notification\EmailNotification
	})
	senders:
		App\Model\Notifications\Notification\EmailNotification: App\Model\Notifications\Sender\EmailSender
```

### Example usage

Get `NotificationManager` from container and use it this way:

```php
$emailNotification = new EmailNotification('foo@bar.cz', 'Subject');
$emailNotification->setBody('This is message!');

// Sends immediately using assigned sender from config
$notificationManager->send($emailNotification);

// Queues for sending later using assigned sender from config
$notificationManager->queue($emailNotification);
```

Example of sending notifications from queue (for example by cron). Get `QueueFlusher` from container and use it this way:

```php
if ($queueFlusher->isEmpty) return;

// Tries to send 5 notifications from queue
$queueFlusher->flush(5);

```

When debug mode is on you will see both notifications in Tracy bar.

### Sender

Example of email sender using [contributte/mailing](https://github.com/contributte/mailing). Senders should throw `Notifications\Exception\NotifyFailedException` when Notification was not send.

```php
<?php declare(strict_types = 1);

namespace App\Model\Notifications\Sender;

use App\Model\Notifications\Notification\EmailNotification;
use Contributte\Mailing\IMailBuilderFactory;
use Tlapnet\Notifications\Notification\Notification;
use Tlapnet\Notifications\Sender\ISender;

final class EmailSender implements ISender
{

	/** @var IMailBuilderFactory */
	private $mailBuilderFactory;

	public function __construct(IMailBuilderFactory $mailBuilderFactory)
	{
		$this->mailBuilderFactory = $mailBuilderFactory;
	}

	/**
	 * @param EmailNotification $notification
	 */
	public function send(Notification $notification): void
	{
		// Create mail and fill with data
		$mail = $this->mailBuilderFactory->create();
		$mail->setSubject($notification->getSubject());
		$mail->setTemplateFile($notification->getTemplatePath());
		$mail->setParameters($notification->getData());

		foreach ($notification->getRecipients() as $recipient) {
			$mail->addTo($recipient);
		}

		// Send it
		$mail->send();
		// You should throw NotifyFailedException here in case notification was not sent
		
	}

}

```

### Notification

Example email notification with From/To Array interface for easy storing in queue.

```php
<?php declare(strict_types = 1);

namespace App\Model\Notifications\Notification;

use Tlapnet\Notifications\Notification\Notification;

class EmailNotification extends Notification
{

	/** @var string */
	protected $subject;

	/** @var string */
	protected $templatePath;

	/** @var string[] */
	protected $recipients;

	/** @var mixed[] */
	protected $data;

	/**
	 * @param string[] $recipients
	 */
	public function __construct(array $recipients, string $subject, string $templatePath)
	{
		$this->recipients = $recipients;
		$this->subject = $subject;
		$this->templatePath = $templatePath;
	}

	public function getSubject(): string
	{
		return $this->subject;
	}

	public function getTemplatePath(): string
	{
		return $this->templatePath;
	}

	/**
	 * @return string[]
	 */
	public function getRecipients(): array
	{
		return $this->recipients;
	}

	/**
	 * @return mixed[]
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @param mixed[] $data
	 */
	public function setData(array $data): void
	{
		$this->data = $data;
	}

	/**
	 * @return mixed[]
	 */
	public function toArray(): array
	{
		return [
			'subject' => $this->subject,
			'recipients' => $this->recipients,
			'templatePath' => $this->templatePath,
			'data' => $this->data,
		];
	}

	/**
	 * @param mixed[] $array
     * @return static
	 */
	public static function fromArray(array $array): self
	{
		$self = new static(
			$array['recipients'],
			$array['subject'],
			$array['templatePath']
		);

		$self->data = $array['data'];
		return $self;
	}

}

```

### Queue flusher

Queue flusher offers interface for interacting with queue and sending notifications.

Methods:
* `isEmpty()` - returns boolean when no queued notifications
* `clear()` - deletes whole queue without sending notifications
* `flush(int $limit = 24)` - tries to send notifications from queue. It asks queue to remove those that were successfully sent and requeue those that were not. Queue flusher is aware which notification was successfully sent by catching `Notifications\Exception\NotifyFailedException` from senders.

### IQueueHandler alias Queue

This interface is straightforward and has following methods which are used to interact with `QueueFlusher` and `QueueNotifier`.

* `getSize(): int` - returns how many notifications are queued in queue
* `enqueue(Notification $notification)` - adds notification to queue
* `peek(int $limit): array` - returns notifications to be sent from queue without removing them from queue
* `requeue(array $notifications)` - accepts notifications that were not successfully sent to be queue again/marked as unsent
* `remove(array $notifications)` - accepts successfully sent notifications to be removed from queue
* `clear()` - wipes/purges queue without sending anything

## NotificationManager
This is main class for sending notifications. It has two methods:
* `send(Notification $notification)` - sends notification immediately using assigned sender from config
* `queue(Notification $notification)` - queues notification for sending later using assigned sender from config
