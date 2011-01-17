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
        	'value'	    => array ('Request', 'ip'),
            'validators'	=> array (
        		'Registration_Ip_Limit'
            )
        )
    );
	
	/**
	 * @desc	Чтение данных из инпута согласно правилам
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
			if ($info ['value'] == 'input')
			{
				$data [$field] = $input->receive ($field);
			}
			elseif (is_array ($info ['value']))
			{
				$data [$field] = call_user_func ($info ['value']);
			}
			elseif ($info ['value'] == 'ignore')
			{
				$data [$field] = $input->receive ($field);
			}
		}
		
		return $data;
	}
	
	/**
	 * Убрать из выборки поля, отмеченные как игнорируемые.
	 * @param array $data
	 * @param array $fields
	 */
	public static function unsetIngored (array &$data, array $fields)
	{
		foreach ($data as $key => $value)
		{
			if (!isset ($fields [$key]) || $fields [$key]['value'] == 'ignore')
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
	 * @throws Zend_Exception
	 * @return true|string
	 * 		true если данные прошли валидацию полностью.
	 * 		Иначе - код ошибки.
	 */
	public static function validate (array &$data, array $fields)
	{
		$obj_data = (object) $data;
		
		foreach ($fields as $name => $info)
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
	        	$validator = 'Data_Validator_' . $validator;
	        	
    	        if (!Loader::load ($validator))
    	        {
    	            Loader::load ('Zend_Exception');
    	            throw new Zend_Exception (
    	            	'Unable to load registration validator: ' . $validator);
    	            return self::FAIL;
    	        }
    	        
    	        $result = call_user_func (
    	            array ($validator, 'validate'),
    	            $obj_data, $name, $info
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
	
}