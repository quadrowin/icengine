<?php

class View_Helper_Paginator extends View_Helper_Abstract
{
	
	/**
	 * 
	 * @param array $params
	 * 		Параметры, переданные из шаблона.
	 * 		$params['data'] должен быть объектом типа Paginator_Item
	 * @return string
	 */
	public function get (array $params)
	{
	    /**
		 * @var Paginator_Item $paginator
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
				$pages [] = array (
					'href'	    => $href . $i,
					'title'	    => $i,
				    'selected'	=> ($paginator->page == $i)
				);
				$spaced = false;
			}
			elseif (!$spaced)
			{
				$pages [] = array (
					'title'	    => '...',
				    'selected'	=> false
				);
				$spaced = true;
			}
		}
		
		$paginator->pages = $pages;
		
		$this->_view->assign ('paginator', $paginator);
		
		return $this->_view->fetch('View/Helper/Paginator.tpl');
	}
	
}