<?php
/**
 * 
 * @desc Упаковщик Css ресурсов представления 
 * @author Юрий
 * @package IcEngine
 *
 */
Loader::load ('View_Resource_Packer_Abstract');
	
class View_Resource_Packer_Css extends View_Resource_Packer_Abstract
{
	
	/**
	 * @desc Импортируемые стили.
	 * @var array
	 */
	protected $_imports = array (); 
	
	/**
	 * @desc домен второго уровня
	 * @var string
	 */
	protected $_domain = 'localhost';
	
	/**
	 * @desc Шаблоны имен доменов
	 * @var Objective
	 */
	protected $_domains;
	
	/**
	 * @desc Последний использованный домен.
	 * @var integer
	 */
	protected $_last = 0;
	
	/**
	 * @desc Расширения конфига
	 * @var array
	 */
	protected $_configExt = array (
		// Домены 
//		'domains'	=> array (
//			'img1.{$domain}{$url}',
//			'img2.{$domain}{$url}',
//			'img3.{$domain}{$url}',
//			'img4.{$domain}{$url}',
//			'img5.{$domain}{$url}',
//			'img6.{$domain}{$url}',
//			'img7.{$domain}{$url}',
//			'img8.{$domain}{$url}',
//			'img9.{$domain}{$url}'
//		)
	);
	
	/**
	 * @desc Создает и возвращает экземпляра
	 */
	public function __construct ()
	{
		Loader::load ('Helper_Uri');
		$this->_domain = Helper_Uri::mainDomain ();
		
		$this->_config = array_merge (
			$this->_config,
			$this->_configExt
		);
		
		$this->_domains = $this->config ()->domains;
	}
	
	/**
	 * Callback для preg_replace вырезания @import.
	 * @param array $matches
	 * @return string
	 */
	public function _excludeImport (array $matches)
	{
		if (strncmp ($matches [1], '/', 1) == 0)
		{
			$this->_imports [] = $matches [0];
		}
		else
		{
			$this->_imports [] = 
				'@import "' . $this->_currentResource->urlPath . $matches [1] . '";';
		}
		
		return '';
	}
	
	/**
	 * Callback для preg_replace замены путей к изображениям.
	 * @param array $matches
	 * @return string
	 */
	public function _replaceUrl (array $matches)
	{
		if (substr ($matches [1], 0, 1) == '/')
		{
			$url = $matches [1];
		}
		else
		{
			$url = $this->_currentResource->urlPath . $matches [1];
		}
		
		if (substr ($url, 0, 1) == '/')
		{
			$this->_last++;
			
			if ($this->_last >= count ($this->_domains))
			{
				$this->_last = 0;
			}
			
			$url = 'http://' . str_replace (
				array (
					'{$domain}',
					'{$url}'
				),
				array (
					$this->_domain,
					$url
				),
				$this->_domains [$this->_last]
			);			
		}
		
		return 'url("' . $url . '")';
	}	
	
	public function compile (array $packages)
	{
		return
			$this->_compileFilePrefix () .
			implode ("\n", $this->_imports) . "\n" . 
			implode ("\n", $packages);
	}
	
	/**
	 * 
	 * @param string $style
	 * @return string
	 */
	public function packOne (View_Resource $resource)
	{
		$resource->urlPath = dirname ($resource->href) . '/';
		
		if (
			$this->config ()->item_prefix &&
			isset ($resource->filePath)
		)
		{
			$prefix = str_replace (
				'{$source}',
				$resource->filePath,
				$this->config ()->item_prefix
			);
		}
		else
		{
			$prefix = '';
		}
		
		$style = preg_replace_callback (
			'/url\\([\'"]?(.*?)[\'"]?\\)/i',
			array ($this, '_replaceUrl'),
			$resource->content ()
		);
			
		$style = preg_replace_callback (
			'/@import\\s*[\'"]?(.*?)[\'"]?\\s*;/i',
			array ($this, '_excludeImport'),
			$style
		);
		
		$style = preg_replace ('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $style);
		$style = str_replace (array ("\r", "\t", '@CHARSET "UTF-8";'), '', $style);
		
		do {
			$length = strlen ($style);
			$style = str_replace ('  ', ' ', $style); 
		} while (strlen ($style) != $length);
		
		return $prefix . $style . $this->config ()->item_postfix;
	}
}