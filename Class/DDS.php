<?php

/**
 * DDS Default Data Source
 * 
 * Easy way to call querys to DB like
 * DDS::execute ($query)
 * 
 * @author yury.s
 *
 */


class DDS
{

	/**
	 * 
	 * @var Data_Source_Abstract
	 */
	protected static $_source;
	
	/**
	 * 
	 * @param string $string
	 * @return string
	 */
	public static function escape ($string)
	{
	    return mysql_real_escape_string ($string);
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param Query_Options $options
	 * @return Data_Source_Abstract
	 */
	public static function execute (Query $query, $options = null)
	{
		return self::$_source->execute ($query, $options);
	}
	
	/**
	 * 
	 * @return Data_Source_Abstract
	 */
	public static function getDataSource ()
	{
		return self::$_source;
	}
	
	public static function initAsMysqlAddition ()
	{
		self::$_source = new Data_Source_Mysql ();
	}
	
	/**
	 * @return boolean
	 */
	public static function inited ()
	{
		return (bool) self::$_source;
	}
	
	/**
	 * Используемая схема моделей
	 * @return Model_Scheme_Abstract
	 */
	public static function modelScheme ()
	{
	    return self::$_source->getDataMapper ()->getModelScheme ();
	}
	
	/**
	 * 
	 * @param Data_Source_Abstract $source
	 */
	public static function setDataSource (Data_Source_Abstract $source)
	{
		self::$_source = $source;
	}
	
}