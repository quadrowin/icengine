<?php

class Query_Result 
{
	private $_result;
	
	public function __construct ($result)
	{
		$this->_result = $result;
	}
	
	/**
	 * @desc
	 * 		Возвращает одну колонку результата.
	 * @param string|null $name [optional]
	 * 		Название колонки.
	 * 		Если не указано, будет возвращен массив, содержащий
	 * 		первую колонку.
	 * @return array
	 */
	public function asColumn ($name = null)
	{
		$result = array ();
		
		if ($name)
		{
			foreach ($this->_result ['result'] as $row)
			{
				$result [] = $row [$name];
			}
		}
		else
		{
			foreach ($this->_result ['result'] as $row)
			{
				$result [] = reset ($row);
			}
		}
		
		return $result;
	}
	
	/**
	 * Результат запроса - единственная запись таблицы
	 * @return array|null
	 */
	public function asRow ()
	{
		if (
			!isset ($this->_result ['result'][0]) ||
			empty ($this->_result ['result'][0])
		)
		{
			return null;
		}
		
		return $this->_result ['result'][0];
	}
	
	/**
	 * Результат запроса - таблица записей
	 * 
	 * @param string $key
	 * 		Поле которое будет использоваться в качестве ключа 
	 * 		при формировании массива строк. Если не указано,
	 * 		будут использованы номера в порядке выбора. 
	 * @return array
	 */
	public function asTable ($key = null)
	{
		if (empty ($this->_result ['result']))
		{
			return array ();
		}
		else
		{
			if ($key)
			{
				$result = array ();
				foreach ($this->_result ['result'] as $row)
				{
					$result [$row [$key]] = $row;
				}
				return $result;
			}
			
			return $this->_result ['result'];
		}
	}
	
	/**
	 * Результат запроса - единственное значение
	 * @return mixed
	 */
	public function asValue ()
	{
		if (
			!isset ($this->_result ['result'][0]) ||
			empty ($this->_result ['result'][0])
		)
		{
			return null;
		}
		
		reset ($this->_result ['result'][0]);
		return current ($this->_result ['result'][0]);
	}
	
	/**
	 * Возвращает общее количество подходящих под условие строк.
	 * @return integer
	 */
	public function foundRows ()
	{
	    return $this->_result ['foundRows'];
	}
	
	/**
	 * Значение последнего добавленого автоинкрементного ключа
	 * @return integer
	 */
	public function insertId ()
	{
		return $this->_result ['insertKey'];
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isNull ()
	{
		return (isset($this->_result ['is_null']) && $this->_result ['is_null']);
	}
	
	/**
	 * Количество затронутых запросом записей.
	 * Количество удаленных, измененных, добавленных или выбранных записей.
	 * @return integer
	 */
	public function touchedRows ()
	{
		return $this->_result ['touchedRows'];
	}
	
	/**
	 * @return DataSource
	 */
	public function getSource ()
	{
		return $this->_result ['source'];
	}
	
	/**
	 * @return mixed
	 */
	public function result ()
	{
		return $this->_result ['result'];
	}
	
}