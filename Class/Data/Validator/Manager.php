<?php

/**
 * Менеджер валидаторов данных
 * 
 * @author morph
 * @Service("dataValidatorManager")
 */
class Data_Validator_Manager extends Manager_Abstract
{
	/**
	 * Валидаторы
     * 
	 * @var array <Data_Validator_Abstract>
	 */
	private $validators = array();

	/**
	 * Получить валидатор по имени
     * 
	 * @param string $name
	 * @return Data_Validator_Abstract
	 */
	public function get($name)
	{
		if (isset ($this->validators[$name])) {
			return $this->validators[$name];
		}
		$className = 'Data_Validator_' . $name;
		$validator = new $className;
        $this->validators[$name] = $validator;
        return $validator;
	}

	/**
	 * Валидация строки
     * 
	 * @param string $name Валидатор
	 * @param mixed $data
	 * @return true|string
	 */
	public function validate($name, $data)
	{
		return $this->get($name)->validate($data);
	}

	/**
	 * Проверка данных валидатором с использованием схемы
	 *
	 * @param string $name
	 * 		Валидатор.
	 * @param string $field
	 * 		Проверяемое поле
	 * @param stdClass $data
	 * @param stdClass|Objective $scheme
	 * @return true|string
	 * 		true, если данные прошли валидацию.
	 * 		Иначе - строкове представление ошибки в виде:
	 * 		"Имя_Валидатора/ошибка"
	 */
	public function validateEx($name, $field, $data, $scheme)
	{
		return $this->get($name)->validateEx($field, $data, $scheme);
	}
}