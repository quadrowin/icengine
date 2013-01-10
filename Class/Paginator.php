<?php

/**
 * Объект для хранения списка страниц.
 *
 * @author Юрий Шведов, neon
 * @package IcEngine
 * @Service("paginator")
 */
class Paginator
{
	/**
	 * Флаг означает, что ссылки нам нужны без всяких ?&page
	 * @var bool
	 */
	public $notGet = false;

	/**
	 * Общее количество элементов
	 * @var integer
	 */
	public $total;

	/**
	 * Ссылка на страницу.
	 * Если на задана, будешь использован адрес из запроса.
	 * @var string
	 */
	public $href;

	/**
	 * Текущая страница
	 * @var integer
	 */
	public $page;

	/**
	 * Количество элементов на странице
	 * @var integer
	 */
	public $perPage = 30;

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
	 * Предыдущая страница
	 * @var array
	 */
	public $prev;

	/**
	 * Следующая страница
	 * @var array
	 */
	public $next;

	/**
	 *
	 * @param integer $page Текущая страница
	 * @param integer $page_limit Количество элементов на странице
	 * @param integer $full_count Полное количество элементов
	 * @param boolean $notGet ЧПУ стиль
	 */
	public function __construct($page, $perPage = 30, 
        $total = 0, $notGet = false)
	{
		$this->page = $page;
		$this->perPage = $perPage;
		$this->total = $total;
		$this->notGet = $notGet;
	}

	/**
	 * Заполнение массива страниц со ссылками.
	 */
	public function buildPages()
	{
		$locator = IcEngine::serviceLocator();
		$request = $locator->getService('request');
		$this->pages = array();
		$pages_count = $this->pagesCount();
		//Debug::logVar($pages_count);
		if ($pages_count <= 1) {
			return ;
		}
		$half_page = round($pages_count / 2);
		$spaced = false;
		$href = isset($this->href) ? $this->href : $request->uri(false);
		// Удаление из запроса GET параметра page
		$p = 'page';
		$href = preg_replace(
			"/((?:\?|&)$p(?:\=[^&]*)?$)+|((?<=[?&])$p(?:\=[^&]*)?&)+|((?<=[?&])$p(?:\=[^&]*)?(?=&|$))+|(\?$p(?:\=[^&]*)?(?=(&$p(?:\=[^&]*)?)+))+/",
			'',
			$href
		);
		/**
		 * Для ссылок вида $page/, тоже учтём
		 */
		if (!$this->notGet) {
			if (strpos ($href, '?') === false) {
				$href .= '?page=';
			} else {
				$href .= '&page=';
			}
		} else {
			if ($this->page > 1) {
				$href = substr(
					$href,
					0,
					(int) (strlen((string) $this->page) + 1) * -1
				);
			}
		}
		for ($i = 1; $i <= $pages_count; $i++) {
			if (
				$i <= 3 ||							// первые 3 страницы
				($pages_count - $i) < 3 ||			// последние 3 страницы
				abs($half_page - $i) < 3 ||			// середина
				abs($this->page - $i) < 3			// возле текущей
			) {
				$pageHref = $href . ($i > 1 ?
					$i . ($this->notGet ? '/' : '') : '');
				// Ссылка с номером страницы
				$page = array(
					'href'	    => $pageHref,
					'title'	    => $i,
					'next'		=> ($this->page == $i - 1),
					'prev'		=> ($this->page == $i + 1),
					'selected'	=> ($this->page == $i)
				);
				$this->pages[] = $page;
				if ($page['prev']) {
					$this->prev = $page;
				} elseif ($page['next']) {
					$this->next = $page;
				}
				$spaced = false;
				continue ;
			}
			if (!$spaced) {
				$this->pages[] = array(
					'href'		=> '',
					'title'		=> '...',
					'prev'		=> false,
					'next'		=> false,
					'selected'	=> false
				);
				$spaced = true;
			}
		}
	}

	/**
	 * @param integer $full_count
	 * @param string $prefix
	 * @return Paginator
	 */
	public static function fromGet($full_count = 0, $prefix = '')
	{
		$locator = IcEngine::serviceLocator();
		$request = $locator->getService('request');
		return new self(
			max($request->get('page'), 1),
			max($request->get('limit', 30), 10),
			$full_count,
			false
		);
	}

	/**
	 *
	 * @param Data_Transport $input Входные данные.
	 * @param integer $total Общее количество элементов.
	 * @return Paginator
	 */
	public function fromInput(Data_Transport $input,
		$total = 0, $notGet = false)
	{
		return new self(
			max($input->receive('page'), 1),
			max($input->receive('limit'), 10),
			$total,
			$notGet
		);
	}

	/**
	 * Возвращает индекс первой записи на текущей страницы
	 * (индекс первой записи - 0).
	 *
	 * @return integer Индекс первой записи или 0.
	 */
	public function offset()
	{
		$offset = max(($this->page - 1) * $this->perPage, 0);
		return $offset;
	}

	/**
	 * @return integer
	 */
	public function pagesCount()
	{
		if ($this->perPage > 0) {
			if ($this->total) {
				return ceil($this->total / $this->perPage);
			} elseif (isset($this->fullCount)) {
				return ceil($this->fullCount / $this->perPage);
			} else {
				return 1;
			}
		} else {
			return 1;
		}
	}

	/**
	 * Установить число элементов на страницу
	 *
	 * @param int $value
	 */
	public function setPerPage($value)
	{
		$this->perPage = $value;
	}
}