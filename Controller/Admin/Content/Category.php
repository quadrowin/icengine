<?php
/**
 * 
 * @desc Контроллер админки разделов контента
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Controller_Admin_Content_Category extends Controller_Abstract
{
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected $_config = array (
		// Роли, имеющие доступ к админке
		'access_roles'	=> array ('admin')
	);
	
	/**
	 * @desc Проверяет, есть ли у текущего пользователя доступ
	 * к экшенам этого контроллера
	 * @return boolean true, если пользователь имеет доступ, иначе false.
	 */
	protected function _checkAccess ()
	{
		$user = User::getCurrent ();
		$roles = $this->config ()->access_roles;
		
		foreach ($roles as $role)
		{
			if ($user->hasRole ($role))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @desc Получение списка дочерних разделов
	 * @param integer $category_id
	 * @param integer $page
	 */
	public function getSubcategories ($category_id, $page)
	{
		$this->_task->setViewRender (View_Render_Manager::byName ('Xslt'));
		
		$categories = Model_Collection_Manager::create ('Content_Category')
			->addOptions (array (
				'name'	=> 'Parent',
				'id'	=> (int) $category_id
			));
		
		Loader::load ('Paginator');
		$paginator = new Paginator ($page);
		$categories->setPaginator ($paginator);
		
		$this->_output->send (array (
			'categories' => $categories,
			'data' => array (
				'full_count' => $paginator->fullCount
			)
		));
	}
	
	/**
	 * @desc Получение списка контента для раздела
	 * @param integer $category_id
	 * @param integer $page
	 */
	public function getSubcontents ($category_id, $page)
	{
		$this->_task->setViewRender (View_Render_Manager::byName ('Xslt'));
		
		$contents = Model_Collection_Manager::create ('Content')
			->addOptions (array (
				'name'	=> 'Category',
				'id'	=> (int) $category_id
			));
		
		Loader::load ('Paginator');
		$paginator = new Paginator ($page);
		$contents->setPaginator ($paginator);
		
		$this->_output->send (array (
			'contents'	=> $contents,
			'data'		=> array (
				'full_count'	=> $paginator->fullCount
			)
		));
	}
	
	/**
	 * @desc Дерево разделов контента
	 */
	public function index ()
	{
		if (!$this->_checkAccess ())
		{
//			return $this->replaceAction ('Error', 'accessDenied');
		}
		$this->_task->setViewRender (View_Render_Manager::byName ('Xslt'));
		
		$categories = Model_Collection_Manager::create ('Content_Category')
			->addOptions ('Root');

		Loader::load ('Paginator');
		$categories->setPaginator (Paginator::fromInput ($this->_input));
		
		$this->_output->send (array (
			'categories'	=> $categories,
			'expand_level'	=> 0
		));
	}
	
}
