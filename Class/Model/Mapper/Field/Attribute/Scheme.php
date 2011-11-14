<?php

class Model_Mapper_Field_Attribute_Scheme
{
	protected static $_config;

	public static function config ()
	{
		if (!self::$_config)
		{
			self::$_config = Config_Manager::get (
				__CLASS__,
				self::$_config
			);

			if (!self::$_config)
			{
				self::$_config = array ();
			}
		}

		return self::$_config;
	}

	public static function validate ($field_name, $attribute_name)
	{
		$config = self::config ();

		return isset ($config->scheme [$field_name]) &&
			in_array ($attribute_name, $config [$field_name]);
	}
}