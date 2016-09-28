<?php

	use ArcheeNic\HackBrainTime\Secure;

	/** @TODO: Suppress implicit require by using autoloader */
	include_once(__DIR__.'/secure.php');
	new Secure('post,files');