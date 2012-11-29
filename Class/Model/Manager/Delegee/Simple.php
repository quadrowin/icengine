<?php

/**
 * Класс для создания простых моделей
 * 
 * @author goorus, morph
 */
class Model_Manager_Delegee_Simple
{
	/**
	 * Возвращает новою модель
     * 
	 * @param string $model
	 * @param string $key
	 * @param mixed $object
	 */
	public function get($modelName, $key, $object)
	{
        $params = is_array($object) ? $object : array();
        $model = new $modelName($params);
		return $model;
	}
}