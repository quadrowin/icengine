<?php

date_default_timezone_set ('UTC');

$_SERVER ['PHPUNIT'] = 1;

require __DIR__ . '/../Core.php';
\Ice\Core::init (
	__DIR__ . '/../../',
	'Ice\\Bootstrap_UnitTest',
	__DIR__ . '/../App/Model/Bootstrap/UnitTest.php'
);
\Ice\Loader::load ('Loader_Auto');
\Ice\Loader_Auto::register ();

//\Ice\Loader::addPath ('Vendor', \Ice\Core::root () . 'includes/');