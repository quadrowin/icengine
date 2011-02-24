<?php

error_reporting (E_ALL);

include 'D:\Work\htdocs\vipgeo\config\mongo.php';
include 'D:\Work\htdocs\vipgeo\IcEngine\Class\Data\Provider\Mongo.php';

$provider = new Data_Provider_Mongo ($config);

echo '<pre>';

for ($i = 0; $i < 10; $i++)
{
	$r = $provider->add ('Test_' . $i, $i);
	echo 'add Test_' . $i . ': ';
	var_dump ($r);
}

$keys = $provider->keys ('Test_*');

echo 'keys = ';
print_r ($keys);

$result = $provider->getMulti ($keys);
echo 'getMulti = ';
print_r ($result);

for ($i = 0; $i < 10; $i++)
{
	$v = $provider->get ('Test_' . $i);
	$provider->set ('Test_' . $i, $v + 1);
	$v = $provider->get ('Test_' . $i);
	echo "Test_$i => $v \r\n";
}

echo 'finish';
