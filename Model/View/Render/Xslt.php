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
		/**
		 * @desc Директория для скопилированных шаблонов Smarty
		 * @var string
		 */
		'compile_path'		=> 'cache/templates',
		/**
		 * @desc Пути до шаблонов
		 * @var array
		 */
		'templates_path'	=> array (),
		/**
		 * @desc Пути до плагинов
		 * @var array
		 */
		'plugins_path'		=> array (),
		/**
		 * @desc Фильры
		 * @var array
		 */
		'filters'			=> array (
			'Dblbracer'
		)
	);
	
	/**
	 * @desc Объект шаблонизатора
	 * @var Smarty
	 */
	protected $_processor;
	
	protected function _afterConstruct ()
	{
		$config = $this->config ();
		
		$this->_processor = new XSLTProcessor ();
		
		$this->_templatesPathes = array_reverse (
			$config ['templates_path']->__toArray ()
		);
		
		// Фильтры
		foreach ($config ['filters'] as $filter)
		{
			$filter = 'Helper_Smarty_Filter_' . $filter;
			Loader::load ($filter);
			$filter::register ($this->_smarty);
		}
	}
	
	/**
	 * @desc Получает идентификатор компилятор для шаблона.
	 * Необходимо, т.к. шаблон зависит от путей шаблонизатора.
	 * @param string $tpl
	 * @return string
	 */
	protected function _compileId ($tpl)
	{
		return crc32 (json_encode ($this->_smarty->template_dir));
	}
	
	/**
	 * @desc Добавление пути до директории с плагинами Smarty
	 * @param string|array $path Директории с плагинами
	 */
	public function addPluginsPath ($path)
	{
		$this->_smarty->plugins_dir = array_merge (
			(array) $this->_smarty->plugins_dir,
			(array) $path
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addTemplatesPath()
	 */
	public function addTemplatesPath ($path)
	{
		$this->_smarty->template_dir = array_merge (
			array_reverse ((array) $path),
			(array) $this->_smarty->template_dir
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::addHelper()
	 */
	public function addHelper ($helper, $method)
	{
	}
	
	/**
	 * (non-PHPdoc)
	 * @see View_Render_Abstract::assign()
	 */
	public function assign ($key, $value = null)
	{
		if (is_array ($key))
		{
			$this->_smarty->assign ($key);
		}
		else
		{
			$this->_smarty->assign ($key, $value);
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
	 * @return Smarty
	 */
	public function processor ()
	{
		return $this->_processor;
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