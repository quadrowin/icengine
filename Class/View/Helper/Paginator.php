<?php
/**
 * 
 * @desc Вывод страниц
 * @author Юрий Шведов
 * @tutorial
 * {Paginator data=$collection->getPaginator() tpl="index"}
 *
 */
class View_Helper_Paginator extends View_Helper_Abstract
{
	
	/**
	 * 
	 * @param array $params
	 * 		Параметры, переданные из шаблона.
	 * 		$params['data'] должен быть объектом типа Paginator
	 * @return string
	 */
	public function get (array $params)
	{
	    /**
		 * @var Paginator $paginator
		 */
		$paginator = $params ['data'];
		
		$pages = array ();
		$pages_count = $paginator->pagesCount ();
		
		if ($pages_count <= 1)
		{
			return '';
		}
		
		$half_page = round ($pages_count / 2);
		$spaced = false;
		
		$href = Request::uri ();
		
		// Удаление из запроса GET параметра page
		$p = 'page';
		$href = preg_replace (
			"/((?:\?|&)$p(?:\=[^&]*)?$)+|((?<=[?&])$p(?:\=[^&]*)?&)+|((?<=[?&])$p(?:\=[^&]*)?(?=&|$))+|(\?$p(?:\=[^&]*)?(?=(&$p(?:\=[^&]*)?)+))+/", 
			'', 
			$href
		);
		
		
		if (strpos ($href, '?') === false)
		{
			$href .= '?page=';
		}
		else
		{
			$href .= '&page=';
		}
		
		for ($i = 1; $i <= $pages_count; $i++)
		{
			if (
				$i <= 3 ||
				($pages_count - $i) < 3 ||
				abs ($half_page - $i) < 3 ||
				abs ($paginator->page - $i) < 3
			)
			{
				$page = array (
					'href'	    => $href . $i,
					'title'	    => $i,
					'next'		=> ($paginator->page == $i - 1),
					'prev'		=> ($paginator->page == $i + 1),
				    'selected'	=> ($paginator->page == $i)
				);
				$pages [] = $page;
				
				if ($page ['prev'])
				{
					$paginator->prev = $page;
				}
				elseif ($page ['next'])
				{
					$paginator->next = $page;
				}
				
				$spaced = false;
			}
			elseif (!$spaced)
			{
				$pages [] = array (
					'title'	    => '...',
					'prev'		=> false,
					'next'		=> false,
				    'selected'	=> false
				);
				$spaced = true;
			}
		}
		
		$paginator->pages = $pages;
		
		$this->_view->assign ('paginator', $paginator);
		
		$template = 'Widget/Paginator/index.tpl';
		
		if (isset ($params ['template']))
		{
			$template = $params ['tempalte'];
		}
		
		if (isset ($params ['tpl']))
		{
			$template = 'Widget/Paginator/' . $params ['tpl'] . '.tpl';
		}
		
		return $this->_view->fetch ($template);
	}
	
}