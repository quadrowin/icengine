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
			'filters'		=> array (
				'Trim',
				'LowerCase'
			),
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
			'filters'		=> array (
				'Trim'
			),
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
		),
		'date'	=> array (
			'value'	=> array (
				// При добавлении
				'add'	=> array ('Helper_Date', 'toUnix'),
				// При редактировании
				'edit'	=> 'ignore'
			)
		)
	);
	
	/**
	 * Фильтрация значений
	 * @param Objective $data
	 * 		Данные для фильтрации
	 * @param array $scheme
	 */
	public static function filter (Objective $data, array $scheme)
	{
		Loader::load ('Filter_Manager');
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
				$data->$field = Filter_Manager::filterEx (
					$filter, $field, $data, $obj_scheme
				);
			}
		}
	}
	
	/**
	 * @desc	Чтение данных из инпута согласно правилам.
	 * 
	 * @param	Data_Transport $input
	 * 		Входной поток.
	 * @param array $fields
	 * 		Поля.
	 * @param Temp_Content $tc
	 * 		Временный контент
	 * @return Objective
	 * 		Прочитанные поля.
	 */
	public static function receiveFields (Data_Transport $input, array $fields, 
		Temp_Content $tc = null)
	{
		$data = new Objective ();
		
		foreach ($fields as $field => $info)
		{
			$value = &$info ['value'];
			
			if (is_array ($value))
			{
				if (count ($value) == 2 && isset ($value [0], $value [1]))
				{
					$data [$field] = call_user_func ($info ['value']);
				}
				elseif (isset ($value ['add']) && $tc && !$tc->rowId)
				{
					$data [$field] = call_user_func (
						$value ['add'][0],
						$value ['add'][1]
					);
				}
				elseif (isset ($value ['edit']) && $tc && $tc->rowId)
				{
					$data [$field] = call_user_func (
						$value ['edit'][0],
						$value ['edit'][1]
					);
				}
			}
			elseif (
				$info ['value'] == 'input' ||
				$info ['value'] == 'input,ignore'
			)
			{
				$data [$field] = $input->receive ($field);
			}
			
			unset ($value);
		}
		
		return $data;
	}
	
	/**
	 * Удаление из выборки полей, отмеченных как игнорируемые.
	 * @param Objective $data
	 * @param array $scheme
	 */
	public static function unsetIngored (Objective $data, array $scheme)
	{
		foreach ($data as $key => $value)
		{
			if (
				!isset ($scheme [$key]) || 
				$scheme [$key]['value'] == 'ignore' ||
				$scheme [$key]['value'] == 'input,ignore'
			)
			{
				Debug::vardump ($key, $scheme [$key]);
				unset ($data [$key]);
			}
		}
	}
	
	/**
	 * Проверка корректности данных с формы.
	 * 
	 * @param Objective $data
	 * 		Данные, полученные с формы.
	 * @param array $fields
	 * 		Данные по полям.
	 * @return true|array
	 * 		true если данные прошли валидацию полностью.
	 * 		Иначе - массив
	 * 		array ("Поле" => "Ошибка")
	 */
	public static function validate (Objective $data, array $scheme)
	{
		Loader::load ('Data_Validator_Manager');
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
					$validator, $field, $data, $obj_scheme
				);
				
				if ($result !== true)
				{
					return array ($field => $result);
				}
			}
		}
		
		return true;
	}
	
}