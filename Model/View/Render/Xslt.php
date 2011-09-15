<?php
/**
 * 
 * @desc Рендер с использованием шаблонизатора Smarty.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class View_Render_Xslt extends View_Render_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		
	);
	
	/**
	 * @desc Объект шаблонизатора
	 * @var Smarty
	 */
	protected $_processor;
	
	/**
	 * @desc Инициализация процессора
	 */
	protected function _afterConstruct ()
	{
		$this->_processor = new XSLTProcessor ();
	}
	
	/**
	 * @desc
	 * @param XMLWriter $writer
	 * @param mixed $data
	 */
	protected function _arrayToXml (XMLWriter $writer, $data)
	{
		foreach ($data as $key => $val)
		{
			if (is_numeric ($key))
			{
				$key = 'key' . $key;
			}
			if (is_array ($val))
			{
				$writer->startElement ($key);
				$this->_arrayToXml ($writer, $data);
				$writer->endElement ();
			}
			else
			{
				$writer->writeElement ($key, $val);
			}
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::display()
	 */
	public function display ($tpl)
	{
		ob_start ();
		$this->_processor->importStylesheet ($tpl);
		$this->_processor->transformToURI ($this->xml (), 'php://output');
		echo ob_get_flush ();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::fetch()
	 */
	public function fetch ($tpl)
	{
		ob_start ();
		$this->_processor->importStylesheet ($tpl);
		$this->_processor->transformToURI ($this->xml (), 'php://output');
		return ob_get_flush ();
	}
	
	/**
	 * @desc Возвращает используемый экземпляр шаблонизатора.
	 * @return XSLTProcessor
	 */
	public function processor ()
	{
		return $this->_processor;
	}
	
	/**
	 * @desc Формирует XML документ, содержащий данные для вывода
	 * @return DOMDocument
	 */
	public function xml ()
	{
		$writer = new XMLWriter ();
		$writer->openMemory ();
        $writer->startDocument ('1.0', 'UTF-8');
        $writer->startElement ('Input');
			$this->_arrayToXml ($writer, $data);
        $this->writer->endElement ();
        return $this->writer->outputMemory ();
	}
	
}