<?php

class Paginator
{
	
	/**
	 * Текущая страница
	 * @var integer
	 */
	public $page;
		
	/**
	 * Общее количество элементов
	 * @var integer
	 */
	public $fullCount;
	
	/**
	 * Количество элементов на странице
	 * @var integer
	 */
	public $pageLimit = 30;
	
	/**
	 * Сформированные для вывода номера страниц
	 * array (
	 * 		'href'	=> ссылка на страница
	 * 		'title'	=> номер страницы или многоточие
	 * )
	 * @var array
	 */
	public $pages;
	
	/**
	 * 
	 * @param integer $page Текущая страница
	 * @param integer $page_limit Количество элементов на странице
	 * @param integer $full_count Полное количество элементов
	 */
	public function __construct ($page, $page_limit = 30, $full_count = 0)
	{
		$this->page = $page;
		$this->pageLimit = $page_limit;
		$this->fullCount = $full_count;
	}
	
	/**
	 * 
	 * @param string $prefix
	 * @param integer $full_count
	 * @return Paginator
	 */
	public static function fromGet ($full_count = 0, $prefix = '')
	{
		return new self (
			max (Request::get ('page'), 1),
			max (Request::get ('limit', 30), 10),
			$full_count
		);
	}
	
	/**
	 * 
	 * @param Data_Transport $input
	 * 		Входные данные.
	 * @param integer $full_count
	 * 		Общее количество элементов.
	 * @return Paginator
	 */
	public static function fromInput (Data_Transport $input, $full_count = 0)
	{
		return new self (
			max ($input->receive ('page'), 1),
			max ($input->receive ('limit'), 10),
			$full_count
		);
	}
	
	/**
	 * @return integer
	 */
	public function offset ()
	{
		$offset = max (($this->page - 1) * $this->pageLimit, 0);
		return $offset;
	}
	
	/**
	 * @return integer
	 */
	public function pagesCount ()
	{
		if ($this->pageLimit > 0)
		{
			return ceil ($this->fullCount / $this->pageLimit);
		}
		else
		{
			return 1;
		}
	}
	
}