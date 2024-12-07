<?php

declare(strict_types=1);

namespace OCA\Downloader\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCA\Text\Event\LoadEditor;


class Application extends App implements IBootstrap {
	public const APP_ID = 'downloader';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
