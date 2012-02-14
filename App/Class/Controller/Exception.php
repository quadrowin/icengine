<?php

namespace Ice;

/**
 *
 * @desc Исключения контроллера
 * @package Ice
 * @author Yury Shvedov
 * @tutorial
 * throw Controller_Exception::create('accessDenied');
 * throw Controller_Exception::create()
 *		->setCode(3)
 *		->setTemplate(__METHOD__ . '/error');
 *
 */
class Controller_Exception extends Exception
{

	/**
	 * @desc Флаг, указывающий, что название шаблона будет
	 * формироваться из вызванного экшена и сообщения ошибки.
	 * @var boolean
	 */
	protected $_autoTemplate = true;

	/**
	 * @desc Шаблон
	 * @var string
	 */
	protected $_template;

	/**
	 * @desc
	 * @return boolean
	 */
	public function getAutoTemplate ()
	{
		return $this->_autoTemplate;
	}

	/**
	 * @desc Возвращает шабон
	 * @return string
	 */
	public function getTemplate ()
	{
		return $this->_template;
	}

	/**
	 * @desc Автогенерация названия шаболна
	 * @param boolean $auto Флаг автогенерации
	 * @return $this
	 */
	public function setAutoTemplate ($auto)
	{
		$this->_autoTemplate = $auto;
		return $this;
	}

	/**
	 * @desc Устанавлиает шаблон ошибки
	 * @param string $template
	 * @return $this
	 */
	public function setTemplate ($template)
	{
		$this->_template = $template;
		return $this;
	}

}