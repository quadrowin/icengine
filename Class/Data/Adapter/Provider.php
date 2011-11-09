<?php

Loader::load ('Data_Adapter_Abstract');

/**
 * @desc Адаптер данных через провайдер.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Data_Adapter_Provider extends Data_Adapter_Abstract
{
    /**
     * @desc Текущий провайдер
     * @var Data_Provider_Abstract
     */
    protected $_provider;

	/**
	 * @see Data_Adapter_Abstract::_queryMethod
	 * @var array
	 */
    protected $_queryMethods = array (
        Query::SELECT    => '_executeSelect',
//        Query::SHOW      => '_executeSelect',
        Query::DELETE    => '_executeDelete',
        Query::UPDATE    => '_executeUpdate',
        Query::INSERT    => '_executeInsert'
    );

	/**
	 * @see Data_Adapter_Abstract::_translatorName
	 * @var string
	 */
	protected $_translatorName = 'KeyValue';

    /**
     * @desc Удаление
     * @param Query $query
     * @param Query_Options $options
     */
	public function _executeDelete (Query $query, Query_Options $options)
	{
		$this->_affectedRows = $this->_fullDeleteByPatterns (
			$this->translator ()->extractTable ($query),
			$this->_query
		);

		return true;
	}

	/**
	 * @see Data_Adapter_Abstract::_executeInsert
	 * @param Query $query
	 * @param Query_Options $options
	 */
    public function _executeInsert (Query $query, Query_Options $options)
    {
        foreach ($this->_query [0] as $key)
        {
        	$this->_provider->set ($key, $this->_query [1]);
        }

		$this->_affectedRows = count ($this->_query [0]);

		return true;
	}

	/**
	 * @see Data_Adapter_Abstract::_executeSelect
	 * @param Query $query
	 * @param Query_Options $options
	 * @return array
	 */
    public function _executeSelect (Query $query, Query_Options $options)
    {
		$translator = $this->translator ();

		$ids = array ();
		$rows = array ();

		// Выбираем ID всех записей, подходящих под условие
		foreach ($this->_query as $pattern)
		{
			$keys =
				(strpos ($pattern, '*') === false) ?
					array ($pattern) :
					$this->_provider->keys ($pattern);

			foreach ($keys as $key)
			{
				$id = $translator->extractId ($key);

				if (!isset ($ids [$id]))
				{
					$ids [$id] = $id;
					$row = $this->_provider->get ($key);
					if ($row)
					{
						$rows [] = $row;
					}
				}
			}
		}

        return $rows;
    }

    /**
     * @desc Выполнить запрос на вставку
     * @param Query $query
     * @param Query_Options $options
     * @return boolean
     */
    public function _executeUpdate (Query $query, Query_Options $options)
    {
    	// Удаление ненужных индексов
		$this->_fullDeleteByPatterns (
			$this->translator ()->extractTable ($query),
			$this->_query [0]
		);

		// Установка новых значений
		foreach ($this->_query [1] as $key)
		{
			$this->_provider->set ($key, $this->_query [2]);
		}

		return true;
	}

	/**
	 * @desc Полный список ключей по маскам.
	 * @param string $table
	 * @param array $patterns
	 * @return integer Количество удаленных первичных ключей.
	 */
	protected function _fullDeleteByPatterns ($table, array $patterns)
	{
		$translator = $this->translator ();

		$ids = array ();

		// Выбираем ID всех записей, подходящих под условие
		foreach ($patterns as $pattern)
		{
			$keys = $this->_provider->keys ($pattern);
			foreach ($keys as $key)
			{
				$id = $translator->extractId ($key);
				$ids [$id] = $id;
			}
		}

		// Для каждого ID выбираем запись,
		// строим ключи согласно индексам и удаляем их.
		foreach ($ids as $id)
		{
			$key =
				$table . $translator->tableIndexDelim .
				'k' . $translator->indexKeyDelim .
				$id;

			$keys = $translator->_compileKeys (
				$table,
				$this->_provider->get ($key)
			);

			$this->_provider->delete ($keys);
		}

		return count ($ids);
	}

	/**
	 * @desc Получить текущий провайдер
	 * @return Data_Provider_Abstract
	 */
	public function getProvider ()
	{
		return $this->_provider;
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption ($key, $value = null)
	{
		switch ($key)
		{
			case 'provider':
				$this->setProvider (Data_Provider_Manager::get ($value));
				return true;
		}
		return false;
	}

	/**
	 * @desc Изменить текущий провайдер
	 * @param Data_Provider_Abstract $provider
	 * @return Data_Mapper_Provider
	 */
	public function setProvider (Data_Provider_Abstract $provider)
	{
		$this->_provider = $provider;
		return $this;
	}

	/**
	 * @desc Получить транслятор запроса
	 * @return Query_Translator_KeyValue
	 */
	public function translator ()
	{
		return Query_Translator::factory ($this->_translatorName);
	}

}