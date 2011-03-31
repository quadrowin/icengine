<?php
/**
 * @desc Контролер контекта 
 * @author ilya
 * @package IcEngine
 */
class Controller_Content_Abstract extends Controller_Abstract
{
	
	/**
	 * @desc После успешного начала создания.
	 * @override
	 */
	protected function _afterCreate ()
	{
		
	}
	
	/**
	 * @desc После успешного сохранения контента
	 * @param Content $content Сохраняемый контент.
	 * @param boolean $is_new true, если это новый контент, иначе false.
	 * @override
	 */
	protected function _afterSave (Content $content, $is_new)
	{
	}
	
	/**
	 * @desc Название модели расширения
	 * @override
	 */
	protected function _extendingModel ()
	{
		return ''; // без расширения
	}
	
	/**
	 * @desc Получить имя контейнера
	 * @return string
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
	 * @desc Имеет ли текущий пользователь права на
	 * добавление в категорию
	 * @param Model $content_category
	 * @return boolean
	 */
	public function __checkAcl ($content_category)
	{
		$user = User::getCurrent ();
		
		if ($user->isAdmin ())
		{
			return true;
		}

		if ($user->id ())
		{
			Loader::load ('Acl_Resource');

			$resource_addContent = Acl_Resource::byNameCheck (
				$this->__categoryModel (),
				$content_category->key (),
				'addContent'
			);

			return (bool) ($resource_addContent 
				&& $resource_addContent->userCan ($user));
		}

		return false;
	}
	
	/**
	 * @desc Фабрик метод для полечение рефера при создании
	 * @param Model $content
	 * @param Model $content_category
	 * @param string $referer
	 * @return string
	 */
	protected function __createReferer (Model $content, 
		Model $contetn_category, $referer)
	{
		return $referer;
	}
	
	/**
	 * @desc Фабрик метод для полечение URL при создании
	 * @param Model $content
	 * @param Model $content_category
	 * @param string $url
	 * @return string
	 */
	protected function __createUrl (Model $content, 
		Model $contetn_category, $url)
	{
		return $url;
	}
	
	/**
	 * @desc Фабрик метод для полечение URL при удалении
	 * @param Model $content
	 * @param string $url
	 * @return string
	 */
	protected function __deleteUrl (Model $content, $url)
	{
		return $url;
	}
	
	/**
	 * @desc Фабрик метод для полечение рефера для списка
	 * @param Model $content_category
	 * @return string
	 */
	protected function __rollReferer (Model $content_category)
	{
		return Request::uri ();
	}
	
	/**
	 * @desc Фабрик метод для полечение реферер при сохранении
	 * @param string $url
	 * @param string $referer
	 * @param string $title
	 * @return string
	 */
	protected function __saveReferer ($url, $referer, $title)
	{
		return ($url != $content->url) ? $url : $referer;
	}
	
	/**
	 * @desc Фабрик метод для полечение URL при сохранении
	 * @param string $url
	 * @param string $referer
	 * @param string $title
	 * @return string
	 */
	protected function __saveUrl ($url, $referer, $title)
	{
		Loader::load ('Helper_Translit');
		return $url = rtrim ($url, '/') . '/' . 
			Helper_Translit::makeUrlLink ($title) . '.html';
	}

	public function __construct ()
	{
		Loader::load ('Helper_Header');
		Loader::load ('Content');
		Loader::load ('Temp_Content');
		Loader::load ('Content_Collection');
	}
	
	/**
	 * @desc Список статей
	 * @param integer $content_category_id - id контейнера
	 * @param string $url - URL контейнера
	 * @return Model_Collection $contents
	 * @return Model $category
	 * @return boolean canEdit
	 * @return Model $parent
	 * @return string $referer
	 * @return string $url
	 */
	public function roll ()
	{
		list (
			$category_id,
			$url
		) = $this->_input->receive (
			'category_id',
			'url'
		);
		
		if ($category_id)
		{
			$category = IcEngine::$modelManager->modelByKey (
				$this->__categoryModel (),
				$category_id
			);	
		}
		else
		{
			$category = IcEngine::$modelManager->modelBy (
				$this->__categoryModel (),
				Query::instance ()
					->where ('url', $url ? $url : Request::uri ())
			);
		}
		
		if (!$category)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}

		$parent = IcEngine::$modelManager->modelByKey (
			$this->__categoryModel (),
			$category->parentKey ()
		);
		
		if (!$parent)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}
		
		$content_collection = Helper_Link::linkedItems (
			$category,
			$this->__contentModel ()
		);

		$parent_url = rtrim($parent->url, '/').'.html';

		$this->_output->send (array (
			'contents'		=> $content_collection,
			'category'		=> $category,
			'canEdit'		=> $this->__checkAcl ($category),
			'parent'		=> $parent,
			'parent_url'	=> $parent_url,
			'referer'		=> $this->__rollReferer ($category)
		));
	}
	
	/**
	 * @desc Вывести контект
	 * @param integer $content_id - id контента
	 * @param string $url - URL контента
	 * @return Model $content,
	 * @return Model $content_category
	 * @return boolean $canEdit
	 */
	public function view ()
	{
		list (
			$content_id,
			$url
		) = $this->_input->receive (
			'content_id',
			'url'
		);
		
		if ($content_id)
		{
			$content = Model_Manager::modelByKey (
				$this->__contentModel (), 
				$content_id
			);
		}
		else
		{
			$content = Model_Manager::modelBy (
				$this->__contentModel (), 
				Query::instance ()
					->where ('url', $url ? $url : Request::uri ())
			);
		}
			
		if (!$content)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}
		
		$content_category = Model_Manager::modelByKey (
			$this->__categoryModel (),
			$content->Content_Category__id
		);
		
		if (!$content_category)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}

		$this->_output->send (array (
			'content'	=> $content,
			'category'	=> $content_category,
			'url'		=> $content_category->url,
			'referer'		=> $this->__rollReferer ($content_category),
			'canEdit'	=> $this->__checkAcl ($content_category)
		));
	}
	
	/**
	 * @desc Создать или редактировать инстанс статьи
	 * @param integer $category_id
	 * @param integer $content_id
	 * @param string $referer
	 * @param string $url,
	 * @param string $back
	 * @return Temp_Content $tc
	 * @return Model $content
	 * @return Model $content_category
	 * @return string $url
	 * @return string $back
	 * @return string $referer
	 */
	public function create ()
	{
		list (
			$category_id,
			$content_id,
			$referer,
			$url,
			$back
		) = $this->_input->receive (
			'category_id',
			'content_id',
			'referer',
			'url',
			'back'
		);

		if (!$category_id)
		{
			if (!$content_id)
			{
				return $this->_helperReturn ('Page', 'notFound');
			}
			else
			{
				$content = Model_Manager::get (
					$this->__contentModel (),
					$content_id
				);

				$category_id = $content->Content_Category->id;
			}
		}
		
		$category = Model_Manager::modelByKey (
			$this->__categoryModel (),
			$category_id
		);
			
		if (!$category)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}
		
		if ($category->controller && $category->controller != $this->name ())
		{
			return $this->replaceAction (
				$category->controller,
				'create'
			);
		}

		$user = User::getCurrent ();

		echo 44444444444;
		if (!User::authorized())
		{
			echo 555555555555;
			return $this->_helperReturn('Access', 'denied');
		}
		
		Loader::load ('Acl_Resource');

		$resource_addContent = Acl_Resource::byNameCheck (
			$this->__categoryModel (),
			$category_id,
			'addContent'
		);
		
		echo 3333333333;
		if (
			!User::getCurrent ()->isAdmin() &&
			(
				!$resource_addContent || 
				!$resource_addContent->userCan (User::getCurrent ())
			)
		)
		{
			echo 1111111111;
			return $this->_helperReturn ('Access', 'denied');
		}
		echo 22222222;
		
		if (!$content)
		{
			$content = Model_Manager::get (
				$this->__contentModel (),
				$content_id
			);
		}
		
		$tc = Temp_Content::create (get_class ($this));
		$tc->attr (array (
			'controller'	=> $this->name (),
			'back'			=> $back,
			'referer'		=> $this->__createReferer ($content, $category, $referer),
			'content_id'	=> $content_id,
			'category_id'	=> $category_id
		));
		
		$this->_output->send (array (
			'tc' 				=> $tc,
			'content'			=> $content,
			'category'			=> $category,
			'url'				=> $this->__createUrl ($content, $category, $url),
			'back'				=> $back,
			'referer'			=> $this->__createReferer ($content, $category, $referer)
		));
		
		$this->_afterCreate ();
	}
	
	/**
	 * @desc Создать/сохранить контент
	 * @param string $title
	 * @param string $short
	 * @param string $text
	 * @param string $utcode
	 * @param integer $content_id
	 * @param integer $content_category_id
	 * @param string $url
	 */
	public function save ()
	{
		list (
			$title, 
			$short,
			$text,
			$utcode,
			$url
		) = $this->_input->receive (
			'title', 
			'short',
			'text',
			'utcode',
			'url'
		);
		
		if (!$utcode)
		{
			return $this->_helperReturn ('Page', 'obsolete');
		}
		
		$tc = Temp_Content::byUtcode ($utcode);
		
		if ($tc->attr ('controller') != $this->name ())
		{
			return $this->replaceAction (
				$tc->attr ('controller'),
				'save'
			);
		}
		
		$category_id = $tc->attr ('category_id');
		
		$user = User::getCurrent ();
		
		$resource_addContent = Acl_Resource::byNameCheck (
			$this->__categoryModel (),
			$category_id,
			'addContent'
		);
		
		if (
			!User::getCurrent ()->isAdmin () &&
			(
				!$resource_addContent || 
				!$resource_addContent->userCan ($user)
			)
		)
		{
			return $this->_helperReturn ('Access', 'denied');
		}
		
		$back = $tc->attr ('back');
		$referer = $tc->attr ('referer');
		$content_id = $tc->attr ('content_id');
		$url = $this->__saveUrl ($url, $referer, $title);
		
		if ($content_id)
		{			
			$content = Model_Manager::modelByKey ($this->__contentModel (), $content_id);

			$referer = $this->__saveReferer ($url, $referer, $title);

			$content->update (array (
				'title'			=> $title,
				'short'			=> $short,
				'content'		=> $text,
				'url'			=> $url
			));
		}
		else
		{
			$content = new Content (array (
				'title'					=> $title,
				'short'					=> $short,
				'content'				=> $text,
				'createdAt'				=> Helper_Date::toUnix (),
				'url'					=> $url,
				'Content_Category__id'	=> $category_id,
				'extending'				=> $this->_extendingModel ()
			));
			
			$content->save ();
			
			// Если это контент с расширением, создаем расширение
			$content->extending ();
			
			Loader::load ('Helper_Link');
			
			$content_category = IcEngine::$modelManager
				->modelByKey (
					$this->__categoryModel (),
					$category_id
				);
			
			if (!$content_category)
			{
				return $this->_helperReturn ('Page', 'notFound');
			}
			
			Helper_Link::link (
				$content,
				$content_category
			);

			if ($back)
			{
				$referer = $url;
			}
		}

		$tc->component ('Image')->rejoin ($content);
		
		$is_new = !$content_id;
		
		$this->_afterSave ($content, $is_new);
		
		return Helper_Header::redirect ($referer);
	}
	
	/**
	 * @desc Удалить контент
	 * @param integer $content_id
	 * @param string $url
	 */
	public function delete () 
	{
		list (
			$content_id,
			$url
		) = $this->_input->receive (
			'content_id', 
			'url'
		);
		
		$content = IcEngine::$modelManager->modelByKey (
			$this->__contentModel (), 
			$content_id
		);
		
		if (!$content)
		{
			return $this->_helperReturn ('Page', 'notFound');
		}
		
		Loader::load ('Helper_Link');
		
		$category_collection = Helper_Link::linkedItems (
			$content,
			'Content_Category'
		);
		
		if (!$category_collection->count ())
		{
			return $this->_helperReturn ('Access', 'denied');
		}
		
		$category = $category_collection->first ();
		
		$user = User::getCurrent ();

		$resource_addContent = Acl_Resource::byNameCheck (
			$this->__categoryModel (),
			$category->key (),
			'addContent'
		);
		  
		if (
			!User::getCurrent ()->isAdmin () &&
			(
				!$resource_addContent || 
				!$resource_addContent->userCan ($user)
			)
		)
		{
			return $this->_helperReturn ('Access', 'denied');
		}

		$content->delete ();

		$url = $this->__deleteUrl ($content, $url);
		
		return Helper_Header::redirect ($url);
	}

	public function uploadImage ()
	{
	    if (!User::authorized ())
		{
			return $this->_helperReturn ('Access', 'denied');
		}

		Loader::load ('Temp_Content');
	    $utcode = $this->_input->receive('utcode');
	    $tc = Temp_Content::byUtcode ($utcode);
		
	    Loader::load ('Helper_Image');
	    $image = Helper_Image::uploadSimple (
		    $tc->modelName(),
		    $tc->key (),
		    'content_image'
	    );

		$this->_output->send ('image', $image);
	}

	public function removeImage ()
	{
		$image_id = (int) $this->_input->receive ('image_id');
		$image = Model_Manager::modelBy (
			'Component_Image',
			Query::instance ()
				->where ('id', $image_id)
				->where ('User__id', User::id ())
		);

		if (!$image)
		{
			$this->_sendError (
				'not_found',
				__METHOD__,
				'/not_found'
			);
			return;
		}

		$image->delete ();
		
		$this->_output->send (
			'data', array ('image_id' => $image_id)
		);
	}

	public function check ()
	{
		list (
			$title, 
			$content_category_id
		) = $this->_input->receive (
			'title',
			'content_category_id'
		);

		$content = Model_Manager::modelBy (
			'Content',
			Query::instance ()
				->where ('Content_Category__id', $content_category_id)
				->where ('title', $title)
		);

		$this->_output->send(
			'content', $content
		);
	}
} 