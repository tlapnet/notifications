<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Sender;

use Tlapnet\Notifications\Exception\InvalidArgumentException;

final class SenderContainer
{

	/** @var ISender[] */
	private $senders = [];

	public function add(string $notification, ISender $s): void
	{
		$this->senders[$notification] = $s;
	}

	public function get(string $notification): ISender
	{
		if (!array_key_exists($notification, $this->senders)) {
			throw new InvalidArgumentException(sprintf('No sender for notification "%s"', $notification));
		}

		return $this->senders[$notification];
	}

}
