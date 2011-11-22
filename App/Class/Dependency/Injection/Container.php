<?php

class Dependency_Injection_Container
{
	protected static $_parameters;

	protected static $_definitions;

	private static function _parseParameter ($param)
	{
		if (
			$param [0] == '%' &&
			$param [strlen ($param) - 1] == '%'
		)
		{
			$param = self::$_parameters [substr ($param, 1, -1)];
		}
		elseif ($param [0] == '@')
		{
			$param = self::get (substr ($param, 1));
		}

		return $param;
	}

	public static function setParameter ($param, $value)
	{
		self::$_parameters [$param] = $value;
	}

	public static function getParameter ($param)
	{
		return isset (self::$_parameters [$param])
			? self::$_parameters [$param]
			: null;
	}

	public static function setDefinition ($name, $params)
	{
		self::$_definitions [$name] = $params;
	}

	public static function getDefinition ($name)
	{
		return isset (self::$_definitions [$name])
			? self::$_definitions [$name]
			: null;
	}

	public static function get ($name)
	{
		$class_name = self::_parseParameter (
			self::$_definitions [$name]['class']
		);

		$factory_name = self::_parseParameter (
			self::$_definitions [$name]['factory']
		);

		list ($factory_name, $factory_method) = explode (
			'::', $factory_name
		);

		$args = self::$_definitions [$name]['arguments'];
		foreach ($args as &$arg)
		{
			$arg = self::_parseParameter ($arg);
		}

		Loader::load ($factory_name);
		$object = call_user_func_array (
			array ($factory_name, $factory_method),
			$args
		);

		return $object;


	}
}