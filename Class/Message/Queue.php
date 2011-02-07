<?php

include dirname (__FILE__) . '/Type.php';
include dirname (__FILE__) . '/Abstract.php';

class Message_Queue
{
	
	/**
	 * Все сообщения.
	 * @var array <Message_Queue_Abstract>
	 */
	protected $_items;
	
	/**
	 * Сообщения по типам.
	 * @var array <array <Message_Queue_Abstract>>
	 */
	protected $_byType = array ();
	
	/**
	 * Обработчки событий
	 * @var array <callback>
	 */
	protected $_handlers = array ();
	
	/**
	 * 
	 * @param integer $type
	 * @return array <Message_Queue_Abstract>
	 */
	public function byType ($type)
	{
		if (!isset ($this->_byType [$type]))
		{
			return array ();
		}
		
		return $this->_byType [$type];
	}
	
	/**
	 * 
	 * @param string $type
	 * 		Тип
	 * @param array $data
	 * 		Данные
	 * @return Message_Abstract
	 * 		
	 */
	public function push ($type, array $data)
	{
		$class = 'Message_' . $type;
		if (!Loader::load ($class))
		{
		    $class = 'Message_Abstract';
		}

		$n = count ($this->_items);
		$data ['index'] = $n;
		
		$message = new $class ($data, $type);
		
		$this->_items [$n] = $message;
		$this->_byType [$type][] = $message;
		
		if (isset ($this->_handlers [$type]))
		{
			foreach ($this->_handlers [$type] as $function)
			{
			    $message->notify ($function);
			}
		}
		
		return $message;
	}
	
	/**
	 * 
	 * @param Model $model
	 * @param array $extends
	 * @return Message_After_Load_Content
	 */
	public function pushAfterLoadContent (Model $model, array $extends = array ())
	{
		return $this->push (
			'After_Load_Content', 
			array_merge (
				$extends,
				array (
					'model'    => $model
				)
			)
		);
	}
	
	/**
	 * 
	 * @param string $type
	 * 		
	 * @param integer $offset
	 * 		Отступ с конца списка
	 * @return Message_Abstract
	 * 		Найденное сообщение. Если не найдено - null.
	 */
	public function last ($type, $offset = null)
	{
		if (is_null ($offset))
		{
			$offset = count ($this->_items) - 1;
		}
		
		for ($i = $offset; $i >= 0; $i--)
		{
			if ($this->_items [$i]->type () == $type)
			{
				return $this->_items [$i];
			}
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param integer $offset
	 * @return Message_After_Load_Content
	 */
	public function lastAfterLoadContent ($offset = null)
	{
		return $this->last ('After_Load_Content', $offset);
	}
	
	/**
	 * 
	 * @param integer $type
	 * @param callback $function
	 * @param string|null $name
	 * @param boolean $call_for_old
	 */
	public function setCallback ($type, $function, $name = null, 
		$call_for_old = false)
	{
		if (!isset ($this->_handlers [$type]))
		{
			$this->_handlers [$type] = array ();
		}
		
		if (!$name)
		{
			$this->_handlers [$type][] = $function;
		}
		else
		{
			$this->_handlers [$type][$name] = $function;
		}
		
		if ($call_for_old)
		{
			$olds = $this->byType ($type);
			foreach ($olds as $message)
			{
			    $message->notify ($function);
			}
		}
	}
	
}