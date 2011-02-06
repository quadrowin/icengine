<?php

abstract class Widget_Abstract
{
	
	/**
	 * 
	 * @var Data_Transport
	 */
	protected $_input;
	
	/**
	 * 
	 * @var Data_Transport
	 */
	protected $_output;
	
	/**
	 * Шаблон
	 * @var string
	 */
	protected $_template;

	public final function __construct ()
	{
		Loader::load ('Data_Transport');
		
		$this->_input = new Data_Transport ();
		$this->_output = new Data_Transport ();
	}
	
	/**
	 * 
	 * @param string $method
	 * @return string
	 */
	public function template ($method)
	{
		if (!$this->_template)
		{
			$template = str_replace (
				array ('_', '::'),
				'/',
				get_class ($this)
			) . '/' . $method . '.tpl';
		}
		else
		{
			$template = $this->_template;
			$this->_template = null;
		}
		
		return $template;
	}
	
	/**
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_input;
	}
	
	/**
	 * @return Data_Transport
	 */
	public function getOutput ()
	{
		return $this->_output;
	}
	
	/**
	 * 
	 * @param Data_Transport $input
	 * @return Widget_Abstract
	 */
	public function setInput (Data_Transport $input)
	{
		$this->_input = $input;
		return $this;
	}
	
	/**
	 * 
	 * @param Data_Transport $output
	 * @return Widget_Abstract
	 */
	public function setOutput (Data_Transport $output)
	{
		$this->_output = $output;
		return $this;
	}
	
	/**
	 * Получение шаблона вида
	 * "Название/Виджета/метод/$tpl.tpl"
	 * @param string $method __METHOD__
	 * @param string $tpl Шаблон
	 * @return string
	 */
	public function tplFor ($method, $tpl)
	{
		return 
			str_replace (array ('_', '::'), '/', $method) . 
			'/' . $tpl . '.tpl';
	}

	/**
	 * Название виджета (без приставки "Widget_")
	 * @return string
	 */
	public function widgetName ()
	{
		return substr (get_class ($this), 7);
	}
}