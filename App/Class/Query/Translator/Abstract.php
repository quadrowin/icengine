<?php

namespace Ice;

/**
 *
 * @desc Абстрактный класс транслятора
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Query_Translator_Abstract
{

	/**
	 * @desc Модели
	 * @var Model_Map
	 */
	protected $_models;

	/**
	 * @desc Транслирует запрос.
	 * @param Query $query Запрос.
	 * @return mixed Результат трансляции.
	 */
	public function translate (Query $query, Model_Map $models)
	{
		$type = $query->type ();
		$type =
			strtoupper (substr ($type, 0, 1)) .
			strtolower (substr ($type, 1));

		$this->_models = $models;

		$translated_query = call_user_func (
			array ($this, '_render' . $type),
			$query
		);

		Loader::load ('Query_Translator_Result');
		return new Query_Translator_Result (
			$query, $translated_query, $this
		);
	}

}
