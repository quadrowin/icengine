<?php

date_default_timezone_set ('UTC');

$_SERVER ['PHPUNIT'] = 1;

require __DIR__ . '/../Core.php';
\Ice\Core::init (
	__DIR__ . '/../../',
	\Ice\Core::path () . 'App/Model/Bootstrap/UnitTest.php'
);
Loader::load ('Loader_Auto');
Loader_Auto::register ();

Loader::addPath ('includes', \Ice\Core::root () . 'includes/');