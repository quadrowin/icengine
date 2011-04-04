<?php
/**
 * 
 * @desc Контролер категорий контента
 * @author ilya
 * @package IcEngine
 * 
 */
class Controller_Content_Category_Abstract extends Controller_Abstract
{	
	
	/**
	 * @desc Возвращает название модели категории.
	 * @return string
	 * @override
	 */
	protected function __categoryModel ()
	{
		return 'Content_Category';
	}

	/**
	 * @desc Возвращает название модели контента.
	 * @return string
	 */
	protected function __contentModel ()
	{
		return 'Content';
	}
	
	/**
	 * @desc Создает и возвращает контроллер
	 */
	public function __construct ()
	{
		Loader::load ('Helper_Link');
		Loader::load ('Helper_Header');
		Loader::load ('Acl_Resource');
	}
	
	/**
	 * @desc Фабрик метод для получения реферера при удалении
	 * @param Model $category
	 * @param string $referer
	 * @return string
	 * @override
	 */
	protected function __deleteReferer (Model $content_category, $referer)
	{
		return rtrim ($referer, '/');
	}

	protected function __makeUniqueLink ($link, $category_id)
	{
		$content_category = IcEngine::$modelManager->collectionBy (
			'Content_Category',
			Query::instance ()
				->where ('url', $link)
		);

		if ($category_id)
		{
			$content_category->where ('id!=?', $category_id);
		}

		if (!$content_category->count())
		{
			$result = $link;
		}
		else
		{
			$unique = (int) $content_category->count();
			if ($linka = preg_split('/_([0-9])$/',$link, -1, PREG_SPLIT_DELIM_CAPTURE))
			{
				$link = $linka [0];
				$unique = (isset ($linka [1]) ? $linka [1] : 0) + 1;
			}
			$link_tmp = $link.'_'.$unique;
			$result = $this->__makeUniqueLink($link_tmp);
		}
		
		return $result;

	}
	
	/**
	 * @desc Фабрик метод для получения разрешения на редактировине
	 * для списка
	 * @param Model $category
	 * @return boolean
	 * @override
	 */
	protected function __rollAcl (Model $category)
	{
		return User::getCurrent ()->isAdmin ();
	}
	
	/**
	 * @desc Вызывается после roll.
	 */
	protected function __rollAfter ()
	{
	}
	
	/**
	 * @desc Фабрик метод для получения реферера для списка
	 * @param Model $category
	 * @param string $url
	 * @return string
	 * @override
	 */
	protected function __rollReferer (Model $category, $url = '')
	{
		return $category->url;
	}
	
	/**
	 * @desc Вызывает после создания категории
	 * @param array $params
	 * @override
	 */
	protected function __saveAfter ($params)
	{
		
	}
	
	/**
	 * @desc Фабрик метод для создания класса контролера
	 * @param array $params
	 * @return string
	 * @override
	 */
	protected function __saveClass ($params)
	{
		Loader::load ('Helper_Translit');
		$title = $params ['title'];
		return Helper_Translit::makeUrlLink ($title, 'en');
	}
	
	/**
	 * @desc Фабрик метод для получения реферера при сохранении
	 * @param array $params
	 * @param Model $category
	 * @param string $url
	 * @return string
	 * @override
	 */
	protected function __saveReferer (array $params, Model $content_category, $url)
	{
		$referer = $params ['referer'];
		return ($content_category->url != $url) ? $url : $referer; 
	}
	
	/**
	 * @desc Факторик метод для создания URL контролера
	 * @param array $params
	 * @return string
	 * @override
	 */
	protected function __saveUrl ($params, $category_id)
	{
		$parent = $params ['parent'];
		Loader::load ('Helper_String');
		$url = 
			Helper_String::end ($parent->url, '.html') ?
				substr ($parent->url, 0, -5) :
				$parent->url;
		$result = rtrim ($url, '/') . '/' . $this->__saveClass ($params);
		$link = $this->__makeUniqueLink($result, $category_id);
		return $link;
	}
	
	/**
	 * @desc Получить список дочерних категорий
	 * @param integer $category_id - id категории
	 * @param string url - url категории
	 * @return Model_Collection
	 * @return string
	 * @return Model
	 * @return boolean
	 */
	public function roll ()
	{
		list (
			$parent_category_id,
			$url
		) = $this->_input->receive (
			'parent_category_id',
			'url'
		);
		
		if ($parent_category_id)
		{
			$parent_category = IcEngine::$modelManager->modelByKey (
				$this->__categoryModel (),
				$parent_category_id
			);
		}
		else
		{
			$parent_category = IcEngine::$modelManager->modelBy (
				$this->__categoryModel (),
				Query::instance ()
					->where ('url', $url ? $url : Request::uri ())	
			);
		}
		
		if (!$parent_category)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}
		
		$category_collection = $parent_category->childs ();
		
		if ($category_collection->count ())
		{
			foreach ($category_collection as $category)
			{
				$category->oneContent ();
			}
		}

		$content_collection = Helper_Link::linkedItems (
			$parent_category,
			$this->__contentModel ()
		);
		
		$this->_output->send (array (
			'categories'		=>	$category_collection,
			'contents'			=>	$content_collection,
			'referer'			=>	$this->__rollReferer ($parent_category, $url),
			'current'			=>	$parent_category,
			'parent'			=>	$parent_category->getParent (),
			'canEdit'			=>	$this->__rollAcl ($parent_category)
		));
		
		$this->__rollAfter ();
	}
	
	/**
	 * @desc Сохранить изменения или создать категорию контента
	 * @param integer $parent_id - родительская категория, куда будет
	 * добавлена создаваемая категория
	 * @param integer $content_category_id - id редактируемой категорию.
	 * Если категория создается, то здесь будет null
	 * @param string $title - заголовок категории
	 * @param string $url - URL категории
	 * @param string $class - произвольный класс для категории
	 * @param string $sort - порядок сортировки для категории
	 * @param boolean $active - будет ли показываться категория на сайте
	 * @param string $referer - ссылка, куда будет перенаправлен пользователь
	 */
	public function save ()
	{
		list (
			$parent_category_id,
			$category_id,
			$title,
			$class,
			$url,
			$sort,
			$active,
			$referer
		) = $this->_input->receive (
			'parent_category_id',
			'category_id',
			'title',
			'class',
			'url',
			'sort',
			'active',
			'referer'
		);
		
		// Получаем родительскую категорию
		$parent = IcEngine::$modelManager->modelByKey (
			$this->__categoryModel (),
			$parent_category_id
		);
		
		if (!$parent)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}
		
		// Параметры для передачи в фабрик методы
		$params = array (
			'parent'				=> $parent,
			'parent_id'				=> $parent_category_id,
			'content_category_id'	=> $category_id,
			'title'					=> $title,
			'sort'					=> $sort,
			'active'				=> $active,
		);
		
		// Получаем класс
		$class = !$class ?  $this->__saveClass ($params) : $class;
	
		// Получаем URL
		$url = !$url ? $this->__saveUrl ($params, $category_id) : $url;
		$user = User::getCurrent ();
		
		if ($category_id)
		{
			$content_category = IcEngine::$modelManager
				->modelByKey (
					$this->__categoryModel (), 
					$category_id
				);
			
			if (!$content_category->key ())
			{
				return $this->_helperReturn ('Page', 'notFound');
			}

			$resource_edit = Acl_Resource::byNameCheck (
				$this->__categoryModel (),
				$content_category->key (),
				'edit'
			);
			
			if (!$resource_edit || !$resource_edit->userCan ($user))
			{
				return $this->_helperReturn ('Access', 'denied');
			}
			
			/*$referer = $this->__saveReferer (
				$params, 
				$content_category,
				$url
			);*/
			
			$content_category->update (array (
				'title'						=> $title,
				'url'						=> $url,
				'class'						=> $class,
				'sort'						=> $sort,
				'active'					=> (int) !empty ($active),
				'parentId'					=> $parent->key (),
				'controller'				=> $parent->controller
			));
		}
		else
		{
			Loader::load ('Content_Category');
			Loader::load ('Acl_Role_Type_Personal');
			
			$resource_addContent = Acl_Resource::byNameCheck (
				$this->__categoryModel (), 
				$parent->key (), 
				'addContent'	
			);

			$personal_role = $user
				->role (Acl_Role_Type_Personal::ID, true);
				
			if (!$resource_addContent->userCan ($user) || 
				!$personal_role)
			{
				return $this->_helperReturn ('Access', 'denied');
			}
			
			$category_class = $this->__categoryModel ();
			
			$content_category = new $category_class (array (
				'title'			=> $title,
				'name'			=> $this->__categoryModel (),
				'url'			=> $url,
				'class'			=> $class,
				'sort'			=> $sort,
				'active'		=> (int) !empty ($active),
				'parentId'		=> $parent->key (),
				'controller'	=> $parent->controller
			));
			
			$content_category->save ();
			
			list (
				$resource_edit,
				$resource_delete,
				$resource_addContent
			) = Acl_Resource::create (
				array (
					$this->__categoryModel (), 
					$content_category->key ()
				),
				array (
					'edit',
					'delete',
					'addContent'
				)
			);
			
			$personal_role->attachResource (
				$resource_edit, 
				$resource_delete,
				$resource_addContent
			);	
			
			$this->__saveAfter (array (
				$referer,
				$resource_edit,
				$resource_delete,
				$resource_addContent
			));
		}

		Helper_Header::redirect ($referer);
	}
	
	/**
	 * @desc Удаление категории. Зависимые объекты удалит Garbage Collector
	 * @param integer $content_category_id - id категории
	 * @param string $referer - URL, по которому будет направлен 
	 * посетитель
	 */
	public function delete ()
	{
		list (
			$category_id,
			$referer
		) = $this->_input->receive (
			'category_id',
			'referer'
		);

		$category = IcEngine::$modelManager
			->modelByKey (
				$this->__categoryModel (), 
				$category_id
			);

		if (!$category)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}

		$user = User::getCurrent ();

		$resource_delete = Acl_Resource::byNameCheck (array (
			$this->__categoryModel (), 
			$category_id, 
			'delete'
		));
		
		if (!$resource_delete || !$resource_delete->userCan ($user))
		{
			return $this->_helperReturn ('Access', 'denied');
		}
		
		$category->delete ();

		//$referer = $this->__deleteReferer ($category, $referer);
		
		Helper_Header::redirect ($referer);
	}
} 