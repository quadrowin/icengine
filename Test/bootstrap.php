<?php

date_default_timezone_set ('UTC');
		
require __DIR__ . '/../IcEngine.php';
IcEngine::init (
	__DIR__ . '/../../',
	IcEngine::path () . 'Model/Bootstrap/UnitTest.php'
);
Loader::load ('Loader_Auto');
Loader_Auto::register ();
			
Loader::addPath ('includes', IcEngine::root () . 'includes/');