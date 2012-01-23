<?php

namespace Ice;

/**
 *
 * @desc Менеджер рансляторов запросов.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Query_Translator_Manager
{

	/**
	 * @desc Подключенные трансляторы.
	 * @var array
	 */
	protected $_translators = array ();

	/**
	 * @desc Возвращает объект транслятора по имени.
	 * @param string $name Название транслятора.
	 * @return Query_Translator_Abstract
	 */
	public function byName ($name)
	{
		$class = __NAMESPACE__ . '\\Query_Translator_' . $name;
		return $this->get ($class);
	}

	/**
	 *
	 * @param type $class
	 * @return Query_Translator_Abstract
	 */
	public function get ($class)
	{
		if (!isset ($this->_translators [$class]))
		{
			Loader::load ('Ice\\Query_Translator_Abstract');
			Loader::load ($class);
			$this->_translators [$class] = new $class ();
		}

		return $this->_translators [$class];
	}

}