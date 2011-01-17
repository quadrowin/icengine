<?php

class Model_Scheme_Abstract
{
    
    const DEFAULT_KEY_FIELD = 'id';
	
	/**
	 * Префикс по умолчанию для всех таблиц
	 * @var string
	 */
	public $defaultPrefix = 'ice_';
	
	/**
	 * Префиксы для таблиц
	 * @var array <string>
	 */
	public $prefixes = array (
	
	);
	
	/**
	 * Другие имена таблиц
	 * @var array <string>
	 */
	public $renames = array (
		
	);
	
	/**
	 * Ключевые поля моделей
	 * @var array <string>
	 */
	public $keyFields = array (
	
	);
	
	/**
	 * Получение реального имени таблицы в БД
	 * 
	 * @param string $model
	 * 		Имя модели или экземпляр класса модели
	 * @return string
	 * 		Действительное имя таблицы
	 */
	public function get ($model)
	{	
	    $model = strtolower ($model);

		if (isset ($this->renames [$model]))
		{
			return $this->renames [$model];
		}
		
		$prefix = isset ($this->prefixes [$model]) ?
			$this->prefixes [$model] :
			$this->defaultPrefix;

		$table = defined ($model) ? constant ($model) : $model;
			
		return $prefix . $table;
	}
	
	/**
	 * Ключевое поле для модели.
	 * @param string $model
	 * @return string
	 */
	public function keyField ($model)
	{
	    $model = $this->get ($model);
	    
	    if (isset ($this->keyFields [$model]))
	    {
	        return $this->keyFields [$model];
	    }
	    
	    return self::DEFAULT_KEY_FIELD;
	}
	
}
