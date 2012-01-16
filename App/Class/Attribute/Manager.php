<?php

namespace Ice;

/**
 *
 * @desc Менеджер атрибутов.
 * Получает и устанавливает значения атрибутов модели.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Attribute_Manager extends Manager_Abstract
{

	/**
	 * @desc разделитель для формирования ключа.
	 * @var string
	 */
	const DELIM = '/';

	/**
	 * @desc Таблица аттрибутов
	 * @var string
	 */
    const TABLE = 'Attribute';

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		// Источник
		'source'		=> null,
		// Провайдер, используемый для кэширования
		'provider'		=> null
	);

	/**
	 * @desc Место хранения аттрибутов
	 * @var Data_Source_Abstract
	 */
	protected $_source;

	/**
	 * @desc Провайдер для кэширования
	 * @var Data_Provider_Abstract
	 */
	protected $_provider;

	/**
	 * @return Data_Provider_Manager
	 */
	protected function _getDataProviderManager ()
	{
		return Core::di ()->getInstance ('Ice\\Data_Provider_Manager', $this);
	}

	/**
	 *
	 * @return Data_Source_Manager
	 */
	protected function _getDataSourceManager ()
	{
		return Core::di ()->getInstance ('Ice\\Data_Source_Manager', $this);
	}

	/**
	 * @desc Инициализация
	 */
	public function init ()
	{
		$config = $this->config ();

		if ($config ['source'])
		{
			$this->_source = $this->_getDataSourceManager ()
				->get ($config ['source']);
		}
		else
		{
			$this->_source = DDS::getDataSource ();
		}

		if ($config ['provider'])
		{
			$this->_provider = $this->_getDataProviderManager ()
				->get ($config ['provider']);
		}
	}

	/**
	 * @desc Удаляет все атрибуты модели.
	 * @param Model $model
	 */
	public function deleteFor (Model $model)
	{
	    $this->_source->execute (
	        Query::instance ()
				->delete ()
				->from (self::TABLE)
				->where ('table', $model->table ())
				->where ('rowId', $model->key ())
	    );

		if ($this->_provider)
		{
			$this->_provider->deleteByPattern (
				$model->table () . '/' .
				$model->key () . '/'
			);
		}
	}

	/**
	 * @desc Получение значения атрибута.
	 * @param Model $model Модель.
	 * @param string $key Название атрибута.
	 * @return mixed Значение атрибута.
	 */
	public function get (Model $model, $key)
	{
		$table = $model->table ();
		$row = $model->key ();

		if ($this->_provider)
		{
			$prov_key = $table . self::DELIM . $row . self::DELIM . $key;

			$v = $this->_provider->get ($prov_key);

			if ($v)
			{
				return $v ['v'];
			}

		}

		$value = $this->_source->execute (
			Query::instance ()
				->select ('value')
				->from (self::TABLE)
				->where ('`table`', $table)
				->where ('`rowId`', $row)
				->where ('`key`', $key)
		)->getResult ()->asValue ();

		if ($this->_provider)
		{
			$value = json_decode ($value, true);

			$this->_provider->set (
				$prov_key,
				array (
					't'	=> $table,
					'r'	=> $row,
					'k'	=> $key,
					'v'	=> $value
				)
			);

			return $value;
		}

		return json_decode ($value, true);
	}

	/**
	 * @desc Задание значения атрибуту.
	 * @param Model $model Модель.
	 * @param string|array $key Название атрибута.
	 * @param mixed $value Значение атрибута.
	 */
	public function set (Model $model, $key, $value)
	{
	    $table = $model->table ();
	    $row = $model->key ();

	    $query = Query::instance ()
			->delete ()
			->from (self::TABLE)
			->where ('`table`', $table)
			->where ('`rowId`', $row);

	    if (!is_array ($key))
	    {
	        $query->where ('key', $key);
	   		$key = array (
	   			$key => $value
	   		);
	    }
	    else
	    {
            $query->where ('key', array_keys ($key));
	    }

	    $this->_source->execute ($query);

		$pref = $table . self::DELIM . $row . self::DELIM;

		foreach ($key as $k => $v)
		{
			$this->_source->execute (
				Query::instance ()
					->insert (self::TABLE)
					->values (array(
						'table'	=> $table,
						'rowId'	=> $row,
						'key'	=> $k,
						'value'	=> json_encode ($v)
					))
			);

			if ($this->_provider)
			{
				$this->_provider->set (
					$pref . $k,
					array (
						't'	=> $table,
						'r'	=> $row,
						'k'	=> $k,
						'v'	=> $v
					)
				);
			}
	    }

	}

}