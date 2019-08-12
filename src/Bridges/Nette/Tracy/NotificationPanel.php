<?php declare(strict_types = 1);

namespace Tlapnet\Notifications\Bridges\Nette\Tracy;

use Tracy\Dumper;
use Tracy\IBarPanel;

final class NotificationPanel implements IBarPanel
{

	private const ICON_ACTIVE = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABOElEQVQ4jY2TsUoDQRCGv9m9QiSIWImFHAiXVEHbKJJK8B0ULCxNZ2NhIb6DpSA+hK9heXcQSGlllcLmdixuEi/JLrjN3M7+8w3/7J4oSmzVzjuFHjDvhyZERYBLFGcKA+BDIK+dj+qigMr5beBO4E1gBLwr3FbOZzGAdC1YpyeFe2BLLK8wF3gAXoo1O+uAXYWpwF6kWalw0g/NT9KCwn6iGKAANmysAAR2DEQkOtpbSQOA3EBoJ3Ya5EmADfBU/8Qr0fKjJEDhABjLusIglr+snV+ZkbPumcAEGMbf5bLJGLjqPqzFxxlwoxAEAhC6A9R2H6TVTxSGC0BmolLgGejZ8I4ELoBDaYtfBaZ29i0wW9qL/Uy18znwqHAu8AVcF6GZbQhTAIDK+YHAMfBZhKZMzSUJ+O/6BZL5X+Up2hApAAAAAElFTkSuQmCC';
	private const ICON_INACTIVE = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABPklEQVQ4jY2TvUoDQRSFv7MECRJSpBILCQiSSqxVJJXgA2SnUbCw8qezsbAQ38FSEKvdPICvYZlGSGlllTrHwkncTTY/FwYG5tzvnntnRsZURUhDAjSAUZZn40oRkCxIrtnu2P4A2hG2HiCkYdP2raQ3SYfAu+2rtJfWqgAqthArPQH3tuuSALA9Ah6Al7yfl9opObDdBK6BuiTsP7ikhqQbSRurWtgCWlN70UGMPWCujRJAUrOqz4K2scpBewkA23PnU0Ac4NFMQmnFW6kG2N4GurMCScVZnIU0tIrnSaxek3QH7M8mF/e2u8B58WFNNsfAJTAurGKMgbGkBCgVmlzLwPYz/1PeBU4l7cTkV+ArzuHH9nDqrOozhTS0gUfbJ5K+gYssz4ZzwkUAgLSXdiQd2P7M+/mgUrQMsG78Aq9neQ/JsBN1AAAAAElFTkSuQmCC';

	/** @var TraceableNotifier */
	private $senderNotifier;

	/** @var TraceableNotifier */
	private $queueNotifier;

	public function __construct(TraceableNotifier $senderNotifier, TraceableNotifier $queueNotifier)
	{
		$this->senderNotifier = $senderNotifier;
		$this->queueNotifier = $queueNotifier;
	}

	public function getTab(): string
	{
		$queuedNotificationsCount = count($this->queueNotifier->getNotifications());
		$sentNotificationsCount = count($this->senderNotifier->getNotifications());
		$total = $queuedNotificationsCount + $sentNotificationsCount;
		$countInfo = sprintf('&nbsp;Q:%d|S:%d', $queuedNotificationsCount, $sentNotificationsCount);
		$icon = sprintf('<img height="16" src="data:image/png;base64,%s" />', $total !== 0 ? self::ICON_ACTIVE : self::ICON_INACTIVE);

		return '<span class="tracy-label">'
			. $icon
			. ($total !== 0 ? $countInfo : '')
			. '</span>';
	}

	public function getPanel(): string
	{
		$panel = '<div class="tracy-inner">';
		$panel .= '<h1>Notifications</h1>';

		// Latest notifications
		if ($this->queueNotifier->getNotifications() !== []) {
			$panel .= $this->queuedNotificationsTable();
		}

		if ($this->senderNotifier->getNotifications() !== []) {
			$panel .= $this->sentNotificationsTable();
		}

		$panel .= '</div>';

		return $panel;
	}

	private function sentNotificationsTable(): string
	{
		$html = sprintf('<h2>Sent notifications (%d)</h2>', count($this->senderNotifier->getNotifications()));

		// Table
		$html .= '<table>';
		$html .= '<tr><th>Notification</th></tr>';

		foreach ($this->senderNotifier->getNotifications() as $n) {
			$html .= '<tr>';
			$html .= sprintf('<td>%s</td>', Dumper::toHtml($n));
			$html .= '</tr>';
		}

		$html .= '</table>';

		return $html;
	}

	private function queuedNotificationsTable(): string
	{
		$html = sprintf('<h2>Queued notifications (%d)</h2>', count($this->queueNotifier->getNotifications()));

		// Table
		$html .= '<table>';
		$html .= '<tr><th>Notification</th></tr>';

		foreach ($this->queueNotifier->getNotifications() as $n) {
			$html .= '<tr>';
			$html .= sprintf('<td>%s</td>', Dumper::toHtml($n));
			$html .= '</tr>';
		}

		$html .= '</table>';

		return $html;
	}

}
