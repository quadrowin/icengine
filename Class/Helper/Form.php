<?php

/**
 * 
 * @desc	Обработка данных с формы.
 *
 */

class Helper_Form
{
	
	/**
	 * @desc	Пример полей
	 * @var	array
	 */
	private $_simpleFields = array (
		'email'		=> array (
			'type'	=> 'string',
			'minLength'	=> 5,
			'maxLength'	=> 40,
			'validators'	=> array (
				'Registration_Email'
			),
			'value'		=> 'input'
		),
		'password'	=> array (
			'type'	=> 'string',
			'minLength'	=> 6,
			'maxLength'	=> 250,
			'value'	=> 'input',
			'validators'	=> array (
				'Registration_Password'
			)
		),
		'ip'	=> array (
			'maxTries'	=> 10,
			'value'		=> array ('Request', 'ip'),
			'validators'	=> array (
				'Registration_Ip_Limit'
			)
		)
	);
	
	/**
	 * Фильтрация значений
	 * @param array $data
	 * @param array $scheme
	 */
	public static function filter (array &$data, array $scheme)
	{
		Loader::load ('Filter_Manager');
		$obj_data = (object) $data;
		$obj_scheme = (object) $scheme;
		
		foreach ($scheme as $field => $info)
		{
			if ($info ['value'] == 'ignore')
			{
				continue ;
			}
			
			$filters = isset ($info ['filters']) ? 
				(array) $info ['filters'] :
				array ();
				
			foreach ($filters as $filter)
			{
				$result = Filter_Manager::filterEx (
					$filter, $field, $obj_data, $obj_scheme
				);
				
				if ($result !== true)
				{
					return $result;
				}
			}
		}
		
		$data = (array) $obj_data;
		
		return true;
	}
	
	/**
	 * @desc	Чтение данных из инпута согласно правилам.
	 * 
	 * @param	Data_Transport $input
	 * 		Входной поток.
	 * @param array $fields
	 * 		Поля.
	 * @return array
	 * 		Прочитанные поля.
	 */
	public static function receiveFields (Data_Transport $input, array $fields)
	{
		$data = array ();
		
		foreach ($fields as $field => $info)
		{
			if (
				$info ['value'] == 'input' ||
				$info ['value'] == 'input,ignore'
			)
			{
				$data [$field] = $input->receive ($field);
			}
			elseif (is_array ($info ['value']))
			{
				$data [$field] = call_user_func ($info ['value']);
			}
		}
		
		return $data;
	}
	
	/**
	 * Удаление из выборки полей, отмеченных как игнорируемые.
	 * @param array $data
	 * @param array $fields
	 */
	public static function unsetIngored (array &$data, array $fields)
	{
		foreach ($data as $key => $value)
		{
			if (
				!isset ($fields [$key]) || 
				$fields [$key]['value'] == 'ignore' ||
				$fields [$key]['value'] == 'input,ignore'
			)
			{
				unset ($data [$key]);
			}
		}
	}
	
	/**
	 * Проверка корректности данных с формы.
	 * 
	 * @param array $data
	 * 		Данные, полученные с формы.
	 * @param array $fields
	 * 		Данные по полям.
	 * @return true|string
	 * 		true если данные прошли валидацию полностью.
	 * 		Иначе - код ошибки.
	 */
	public static function validate (array &$data, array $scheme)
	{
		Loader::load ('Data_Validator_Manager');
		$obj_data = (object) $data;
		$obj_scheme = (object) $scheme;
		
		foreach ($scheme as $field => $info)
		{
			if ($info ['value'] == 'ignore')
			{
				continue ;
			}
			
			$validators = isset ($info ['validators']) ? 
				(array) $info ['validators'] :
				array ();
				
			foreach ($validators as $validator)
			{
				$result = Data_Validator_Manager::validateEx (
					$validator, $field, $obj_data, $obj_scheme
				);
				
				if ($result !== true)
				{
					return $result;
				}
			}
		}
		
		return true;
	}
	
}