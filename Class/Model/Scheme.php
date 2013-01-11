<?php
/**
 * Класс хранящий и предоставляющий информацию о схеме моделей
 *
 * @author gooris, morph
 * @Service("modelScheme")
 */
class Model_Scheme extends Manager_Abstract
{
	/**
	 * Имя ключего поля модели по умолчанию
     *
	 * @var string
	 */
    const DEFAULT_KEY_FIELD = 'id';

    /**
     * Название окружения
     * 
     * @var string
     */
    protected $behavior;
    
	/**
	 * Префикс по умолчанию для всех таблиц
	 *
     * @var string
	 */
	public $default = array(
		'keyGen'	=> null,
		'prefix'	=> 'ice_'
	);

	/**
	 * Модели
     *
	 * @var array
	 */
	public $models = array(
		/**
		 * Класс модели
		 * Обязательно в нижнем регистре.
		 * @var array
		 */
		'abstract'	=> array(
			/**
			 * Ключевое поле.
			 * @var string
			 */
			'key'		=> 'id',
            
            /**
             * Генератор ключей по умолчанию
             * @var string
             */
            'keyGen'    => null,
            
			/**
			 * Префикс таблицы.
			 * @var string
			 */
			'prefix'	=> '',
            
			/**
			 * Источник данных о модели.
			 * @var string
			 */
			'source'	=> 'Abstract'
		),
		/**
		 * Название таблицы, которое не должно изменяться
		 * при построении запроса.
		 * @var string
		 */
		'table'	=> '',
		/**
		 * Альтернативное название модели.
		 * @var string
		 */
		'alt_name'	=> 'abstract'
	);

    /**
	 * Источник данных для модели
     *
	 * @param string $modelName название модели.
	 * @return Data_Source_Abstract
	 */
	public function dataSource($modelName)
	{
		$modelName = strtolower($modelName);
		if (!isset($this->models[$modelName],
            $this->models[$modelName]['source'])) {
            $dds = $this->getService('dds');
			return $dds->getDataSource();
		}
		$name = $this->models[$modelName]['source'];
        $dataSourceManager = $this->getService('dataSourceManager');
		return $dataSourceManager->get($name);
	}

	/**
	 * Генерирует новый ID для модели, если для этого в схеме заданы правила
     *
	 * @param Model $model
	 * @return string
	 */
	public function generateKey(Model $model)
	{
		$name = strtolower($model->table());
		if (!isset($this->models[$name], $this->models[$name]['keyGen'])) {
			if (!isset($this->default['keyGen'])) {
				return null;
			}
			$keygen = $this->default['keyGen'];
		} else {
			$keygen = $this->models[$name]['keyGen'];
		}
        list($className, $method) = explode('::', $keygen);
        $keygen = new $className;
        $callable = array($keygen, $method);
		return call_user_func($callable, $model);
	}

    /**
     * Получить название окружения
     * 
     * @return string
     */
    public function getBehavior()
    {
        return $this->behavior;
    }
    
    /**
     * Получить модели схемы
     * 
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }
    
    /**
	 * Индексы модели
     *
	 * @param string $modelName Название модели.
	 * @return array Массив индексов.
	 */
	public function indexes($modelName)
	{
        $scheme = $this->scheme($modelName);
        $indexes = $scheme->indexes;
        $result = array();
        if ($indexes) {
            foreach ($indexes as $index) {
                $result[] = $index[1];
            }
        }
        return $result;
	}

    /**
	 * Инициализация схемы моделей
     *
	 * @param Config_Array $config
	 */
	public function init(Config_Array $config)
	{
		if ($config->default) {
			$this->default = $config->default;
		}
		if ($config->models) {
			$this->models = $config->models->__toArray();
		}
	}

    /**
	 * Ключевое поле для модели
     *
	 * @param string $modelName Название модели.
	 * @return string Имя ключевого поля.
	 */
	public function keyField($modelName)
	{
		$modelName = strtolower($modelName);
		if (!isset($this->models[$modelName],
            $this->models[$modelName]['key'])) {
			return self::DEFAULT_KEY_FIELD;
		}
		return $this->models[$modelName]['key'];
	}
    
    /**
	 * Получить опшины схемы для модели
     *
	 * @param string $modelName
	 * @return array|null
	 */
	public function modelOptions($modelName)
	{
		$modelName = strtolower($modelName);
		return !isset($this->models[$modelName],
            $this->models[$modelName]['options'])
            ? array() : $this->models[$modelName]['options'];
	}

    /**
     * Получить схему модели
     *
     * @param string $modelName
     * @return array
     */
    public function scheme($modelName)
    {
        $configManager = $this->getService('configManager');
        return $configManager->get('Model_Mapper_' . $modelName);
    }
    
    /**
     * Изменить название окружения
     * 
     * @param string $behavior
     */
    public function setBehavior($behavior)
    {
        $this->behavior = $behavior;
    }

    /**
     * Изменить источник данных модели
     * 
     * @param string $modelName
     * @param string $dataSource
     */
    public function setDataSource($modelName, $dataSource)
    {
        $this->models[strtolower($modelName)]['source'] = $dataSource;
    }
    
    /**
     * Изменить значение первичного ключа
     * 
     * @param string $modelName
     * @param string $keyField
     */
    public function setKeyField($modelName, $keyField)
    {
        $this->models[strtolower($modelName)]['key'] = $keyField;
    }
    
    /**
     * Изменить название генератора ключей для модели
     * 
     * @param string $modelName
     * @param string $keyGenerator
     */
    public function setKeyGenerator($modelName, $keyGenerator)
    {
        $this->models[strtolower($modelName)]['keyGen'] = $keyGenerator;
    }
    
    /**
     * Изменить опции по умолчанию для модели
     * 
     * @param string $modelName
     * @param string $options
     */
    public function setOptions($modelName, $options)
    {
        $this->models[strtolower($modelName)]['options'] = $options;
    }
    
    /**
     * Изменить префикс таблицы модели
     * 
     * @param string $modelName
     * @param string $prefix
     */
    public function setPrefix($modelName, $prefix)
    {
        $this->models[strtolower($modelName)]['prefix'] = $prefix;
    }
    
    /**
     * Изменить название таблицы модели
     * 
     * @param string $modelName
     * @param string $table
     */
    public function setTable($modelName, $table)
    {
        $this->models[strtolower($modelName)]['table'] = $table;
    }

	/**
	 * Получение реального имени таблицы в БД
     *
	 * @param string|Model $model Имя модели или экземпляр класса модели.
	 * @return string Действительное имя таблицы.
	 */
	public function table($model)
	{
	    $modelName = strtolower(is_object($model) ? $model->table() : $model);
        if (strpos($modelName, '`') !== false) {
            return $modelName;
        }
		if (isset($this->models[$modelName])) {
            $data = $this->models[$modelName];
            if (is_string($data)) {
                return $data;
            } elseif (isset($data['table'])) {
				return $data['table'];
			} elseif (isset($data['prefix'])) {
				return $data['prefix'] . $modelName;
			}
        }
		return $this->default['prefix'] . $modelName;
	}

	/**
	 * Определеяет название модели по названию таблицы
     *
	 * @param string $table
	 * @return string
	 */
	public function tableToModel($table)
	{
		$prefix = $this->default['prefix'];
		foreach ($this->models as $name => $model) {
			if (isset($model['table']) && $model['table'] == $table) {
				$parts = explode('_', $name);
                $mappedParts = array_map('ucfirst', $parts);
				return implode('_', $mappedParts);
			}
		}
		$parts = explode('_', $table);
		if ($parts[0] . '_' == $prefix) {
			array_shift($parts);
		}
		$mappedParts = array_map('ucfirst', $parts);
		return implode('_', $mappedParts);
	}
}