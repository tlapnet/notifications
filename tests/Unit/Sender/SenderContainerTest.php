<?php declare(strict_types = 1);

namespace Tests\Tlapnet\Notifications\Unit\Sender;

use Mockery;
use PHPUnit\Framework\TestCase;
use Tlapnet\Notifications\Exception\InvalidArgumentException;
use Tlapnet\Notifications\Sender\ISender;
use Tlapnet\Notifications\Sender\SenderContainer;

final class SenderContainerTest extends TestCase
{

	public function testNonexistentSender(): void
	{
		$container = new SenderContainer();

		$this->expectException(InvalidArgumentException::class);
		$container->get('abc');
	}

	public function testExistentSender(): void
	{
		$container = new SenderContainer();

		$sender = Mockery::mock(ISender::class);
		$container->add('abc', $sender);
		$this->assertSame($sender, $container->get('abc'));
	}

	public function testOverrideSender(): void
	{
		$container = new SenderContainer();

		$sender1 = Mockery::mock(ISender::class);
		$container->add('abc', $sender1);

		$sender2 = Mockery::mock(ISender::class);
		$container->add('abc', $sender2);

		$this->assertSame($sender2, $container->get('abc'));
	}

}
