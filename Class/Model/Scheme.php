<?php
/**
 * Класс хранящий и предоставляющий информацию о схеме моделей
 *
 * @author gooris, morph
 */
abstract class Model_Scheme
{
	/**
	 * Имя ключего поля модели по умолчанию
     *
	 * @var string
	 */
    const DEFAULT_KEY_FIELD = 'id';

	/**
	 * Схема линка по умолчанию
	 *
     * @var array
	 */
	public static $defaultLinkScheme = array(

	);

	/**
	 * Префикс по умолчанию для всех таблиц
	 *
     * @var string
	 */
	public static $default = array(
		'keyGen'	=> null,
		'prefix'	=> 'ice_'
	);

	/**
	 * Модели
     *
	 * @var array
	 */
	public static $models = array(
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
			 * Префикс таблицы.
			 * @var string
			 */
			'prefix'	=> '',
			/**
			 * Источник данных о модели.
			 * @var string
			 */
			'source'	=> 'Abstract',
			/**
			 * Существующие индексы (могут использоваться в источнике).
			 * @var array
			 */
			'indexes'	=> array()
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
	public static function dataSource($modelName)
	{
		$modelName = strtolower($modelName);
		if (!isset(self::$models[$modelName],
            self::$models[$modelName]['source'])) {
			return DDS::getDataSource();
		}
		$name = self::$models[$modelName]['source'];
		return Data_Source_Manager::get($name);
	}

	/**
	 * Генерирует новый ID для модели, если для этого в схеме заданы правила
     *
	 * @param Model $model
	 * @return string
	 */
	public static function generateKey(Model $model)
	{
		$name = strtolower($model->table());
		if (!isset(self::$models[$name], self::$models[$name]['keyGen'])) {
			if (!isset(self::$default['keyGen'])) {
				return null;
			}
			$keygen = self::$default['keyGen'];
		} else {
			$keygen = self::$models[$name]['keyGen'];
		}
		return call_user_func($keygen, $model);
	}

    /**
	 * Индексы модели
     *
	 * @param string $modelName Название модели.
	 * @return array Массив индексов.
	 */
	public static function indexes($modelName)
	{
		$modelName = strtolower($modelName);
		if (!isset(self::$models[$modelName],
            self::$models[$modelName]['indexes'])) {
			return array();
		}
		return self::$models[$modelName]['indexes'];
	}

    /**
	 * Инициализация схемы моделей
     *
	 * @param Config_Array $config
	 */
	public static function init(Config_Array $config)
	{
		if ($config->default) {
			self::$default = $config->default;
		}
		if ($config->models) {
			self::$models = $config->models->__toArray();
		}
	}

    /**
	 * Ключевое поле для модели
     *
	 * @param string $modelName Название модели.
	 * @return string Имя ключевого поля.
	 */
	public static function keyField($modelName)
	{
		$modelName = strtolower($modelName);
		if (!isset(self::$models[$modelName],
            self::$models[$modelName]['key'])) {
			return self::DEFAULT_KEY_FIELD;
		}
		return self::$models[$modelName]['key'];
	}

    /**
	 * Получить все ссылки модели
     *
	 * @param string $modelName
	 * @return array
	 */
	public static function links($modelName)
	{
		$modelName = strtolower($modelName);
		return !isset(self::$models[$modelName],
            self::$models[$modelName]['links'])
            ? array() : self::$models[$modelName]['links'];
	}

    /**
	 * Получить опшины схемы для модели
     *
	 * @param string $modelName
	 * @return array|null
	 */
	public static function modelOptions($modelName)
	{
		$modelName = strtolower($modelName);
		return !isset(self::$models[$modelName],
            self::$models[$modelName]['options'])
            ? array() : self::$models[$modelName]['options'];
	}

    /**
     * Получить схему модели
     *
     * @param string $modelName
     * @return array
     */
    public static function scheme($modelName)
    {
        return Config_Manager::get('Model_Mapper_' . $modelName);
    }

	/**
	 * Получение реального имени таблицы в БД
     *
	 * @param string|Model $model Имя модели или экземпляр класса модели.
	 * @return string Действительное имя таблицы.
	 */
	public static function table($model)
	{
	    $modelName = strtolower (is_object($model) ? $model->table() : $model);
        if (strpos($modelName, '`') !== false) {
            return $modelName;
        }
		if (isset(self::$models[$modelName])) {
            $data = self::$models[$modelName];
            if (is_string($data)) {
                return $data;
            } elseif (isset($data['table'])) {
				return $data['table'];
			} elseif (isset($data['prefix'])) {
				return $data['prefix'] . $modelName;
			}
        }
		return self::$default['prefix'] . $modelName;
	}

	/**
	 * Определеяет название модели по названию таблицы
     *
	 * @param string $table
	 * @return string
	 */
	public static function tableToModel($table)
	{
		$prefix = self::$default['prefix'];
		foreach (self::$models as $name => $model) {
			if (!empty($model['table']) && $model['table'] == $table) {
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