<?php

/**
 * @desc Админка для баз данных 
 * @author Илья Колесников
 * @package IcEngine
 */
class Controller_Admin_Database extends Controller_Abstract
{
	/**
	 * @desc Получить сопряжение полей таблицы на разрешенные
	 * ACL поля для пользователя
	 * @param string $table
	 * @param array $fields
	 * @return array<string>
	 */
	private function __aclFields ($table, $fields)
	{
		$acl_fields = $this->__fields ($table);

		Loader::load ('Helper_Array');
		
		$tmp_fields = Helper_Array::column ($fields, 'Field');
		
		$acl_fields = array_intersect ($acl_fields, $tmp_fields);
		
		return $acl_fields;
	}
	
	/**
	 * @desc Получить сопряжение таблиц на разрешенные
	 * ACL таблицы для пользователя
	 * @param array $fields
	 * @return array<string>
	 */
	private function __aclTables ($tables)
	{
		$acl_tables = $this->__tables ();
		
		Loader::load ('Helper_Array');
		
		$table_names = Helper_Array::column ($tables, 'Name');
		
		return array_intersect ($table_names, $acl_tables);
	}
	
	/**
	 * @desc Получить имя класса по таблице и префиксу
	 * @param string $table
	 * @param string $prefix
	 * @return string
	 */
	private function __className ($table, $prefix)
	{
		$class_name = Model_Scheme::tableToModel ($table);

		$prefix = ucfirst ($prefix);
		
		if (strpos ($class_name, $prefix) === 0)
		{
			$class_name = substr ($class_name, strlen ($prefix));
		}
		
		return $class_name;
	}

	/**
	 * (non-PHPDoc)
	 */
	public function __construct ()
	{
		Loader::load ('Helper_Data_Source');

	}
	
	/**
	 * @desc Получить ACL разрешенные поля таблицы
	 * @param string $table
	 * @return array <string>
	 */
	private function __fields ($table)
	{
		$resources = $this->__resources ();
		
		$result = array ();
		
		$name = 'Table/' . $table . '/';
		
		$len = strlen ($name);
		
		foreach ($resources as $r)
		{
			if (substr ($r, 0, $len) === $name)
			{
				$result [] = substr ($r, $len);
			}
		}
		
		return $result;
	}
	
	/**
	 * @desc Залогоровать операцию
	 * @param string $action
	 * @param string $table
	 * @param array<string> $fields
	 * @return void
	 */
	private function __log ($action, $table, $fields)
	{
		Loader::load ('Admin_Log');
		
		foreach ((array) $fields as $field => $value)
		{
			die ($field);
			$log = new Admin_Log (array (
				'User__id'		=> User::id (),
				'action'		=> $action,
				'table'			=> $table,
				'field'			=> $field,
				'value'			=> $value,
				'createdAt'		=> Helper_Date::toUnix ()
			));
			
			$log->save ();
		}
	}
	
	/**
	 * @desc Получить ресурсы ACl и префиксом Table/
	 * @param null|Acl_Role $role
	 * @return array <string>
	 */
	private function __resources ($role = null)
	{
		$query = Query::instance ()
			->select ('name')
			->from ('Acl_Resource')
			->innerJoin (
				'Link',
				'Link.fromRowId=Acl_Resource.id'
			)
			->where ('Link.fromTable', 'Acl_Resource')
			->where ('Link.toTable', 'Acl_Role')
			->where ('Link.toRowId', 
				is_null ($role) ? 
					$this->__roles ()->column ('id') :
					$role->key ()
			)
			->where ('Acl_Resource.name LIKE "Table/%"');
		
		$resources = DDS::execute ($query)
			->getResult ()
				->asColumn ('name');
		
		return (array) $resources;
	}
	
	/**
	 * @desc Получить все роли пользователя
	 * @return Acl_Role_Collection
	 */
	private function __roles ()
	{
		return Helper_Link::linkedItems (
			User::getCurrent (),
			'Acl_Role'
		);
	}
	
	/**
	 * @desc Получить ACL разрешенные таблицы
	 * @return array <string>
	 */
	private function __tables ()
	{
		$resources = $this->__resources ();
		
		$result = array ();
		
		foreach ($resources as $r)
		{
			$tmp = explode ('/', $r);
			$result [] = $tmp [1];
		}
		
		return $result;
	}
	
	/**
	 * @desc Сохраняем права на поля таблиц
	 * @return void
	 */
	public function aclSave ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$role_id = $this->_input->receive ('role_id');
		
		$role = Model_Manager::byKey (
			'Acl_Role',
			$role_id
		);
		
		if (!$role)
		{
			return;
		}
		
		$resources = $this->__resources ($role);
		
		$resource_collection = Model_Collection_Manager::byQuery (
			'Acl_Resource',
			Query::instance ()
				->where ('name', $resources)
		);
			
		foreach ($resource_collection as $resource)
		{
			Helper_Link::unlink ($role, $resource);
		}
		
		$resources = $this->_input->receive ('resources');
		
		Loader::load ('Acl_Resource');
		
		foreach ($resources as $resource_name)
		{
			$resource = Model_Manager::byQuery (
				'Acl_Resource',
				Query::instance ()
					->where ('name', $resource_name)
			);
			
			if (!$resource)
			{
				$resource = new Acl_Resource (array (
					'name'						=> $resource_name,
					'Acl_Resource_Type__id '	=> 1
				));

				$resource->save ();
			}
			
			Helper_Link::link ($role, $resource);
		}
		
		Loader::load ('Helper_Header');
		
		Helper_Header::redirect ('/cp/acl/');
	}
	
	/**
	 * @desc Получить список полей для создания прав
	 * @return void
	 */
	public function aclField ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$role_id = $this->_input->receive ('role_id');
		
		$role = Model_Manager::byKey (
			'Acl_Role',
			$role_id
		);
		
		if (!$role)
		{
			return;
		}
		
		$resources = $this->__resources ($role);
		
		$tables = Helper_Data_Source::tables ();
		
		$result = array ();
		
		foreach ($tables as $table)
		{
			$fields = Helper_Data_Source::fields ('`' . $table ['Name'] . '`');
			 
			$result [$table ['Name']] = array (
				'table'		=> $table,
				'fields'	=> array ()
			);
			
			foreach ($fields as $field)
			{
				$resource_name = 'Table/' . $table ['Name'] . '/' . $field ['Field'];
				
				$result [$table ['Name']]['fields'][] = array (
					'field'		=> $field,
					'resource'	=> $resource_name,
					'on'		=> in_array ($resource_name, $resources)
				);
			}
		}
		
		$this->_output->send (array (
			'tables'	=> $result,
			'role_id'	=> $role->key ()
		));
	}
	
	/**
	 * @desc Получаем список ролей
	 * @return void
	 */
	public function aclRoll ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$role_names = array ('admin', 'conent-manager', 'seo');
		
		$role_collection = Model_Collection_Manager::byQuery (
			'Acl_Role',
			Query::instance ()
				->where ('name', $role_names)
		);
		
		$this->_output->send (array (
			'role_collection'	=> $role_collection
		));
	}
	
	/**
	 * @desc Удаление записи
	 * @return void
	 */
	public function delete ()
	{
		list (
			$table,
			$row_id
		) = $this->_input->receive (
			'table',
			'row_id'
		);
		
		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->__className ($table, $prefix);
		
		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		
		$acl_fields = $this->__aclFields ($table, $fields);
		
		if (!$acl_fields || !User::id())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		/*
		 * @var Model $row
		 */
		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);
				
		if ($row)
		{
			$row->delete ();
			
			$this->__log (
				__METHOD__,
				$table,
				array (null)
			);
		}
		
		Loader::load ('Helper_Header');
		
		Helper_Header::redirect ('/cp/table/' . $table . '/');
	}
	
	/**
	 * @desc Список таблиц
	 * @return void
	 */
	public function index ()
	{
		$tables = Helper_Data_Source::tables ();;
		
		$tmp_tables = $this->__aclTables ($tables);
		
		if (!$tmp_tables || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$result = array ();
		
		Loader::load ('Table_Rate');
		
		foreach ($tables as $table)
		{
			$table ['Rate'] = 0;
			
			if (in_array ($table ['Name'], $tmp_tables))
			{
				$rate = Table_Rate::byTable ($table ['Name']);
				
				$table ['Rate'] = $rate->value; 
				
				$result [] = $table;
			}
		}
		
		Helper_Array::mosort ($result, 'Rate DESC,Comment');
		
		$tmp = array ();
		
		foreach ($result as $i => $r)
		{
			if (!$r ['Comment'])
			{
				$tmp [] = $r;
				unset ($result [$i]);
			}
		}
		
		Helper_Array::mosort ($tmp, 'Name');
		
		$result = array_merge ($result, $tmp);
		
		$this->_output->send (array (
			'tables'	=> $result,
		));
	}
	
	/**
	 * @desc Поля записи
	 * @return void
	 */
	public function row ()
	{
		list (
			$table,
			$row_id
		) = $this->_input->receive (
			'table',
			'row_id'	
		);
		
		Loader::load ('Table_Rate');
		
		$rate = Table_Rate::byTable ($table)->inc ();

		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		
		$acl_fields = $this->__aclFields ($table, $fields);
		
		if (!$acl_fields || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->__className ($table, $prefix);
		
		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);
		
		foreach ($fields as $i => $field)
		{
			// На поле нет разрешения
			if (!in_array ($field ['Field'], $acl_fields))
			{
				unset ($fields [$i]);
			}
			
			// Тип поля - enum
			if (strpos ($field->Type, 'enum(') === 0)
			{
				$values = substr ($field->Type, 6, -1);
				$values = explode (',', $values);
				
				$collection = Model_Collection_Manager::create (
					$class_name
				)
					->reset ();
				
				foreach ($values as $v)
				{
					$v = trim ($v, "' ");
					
					$collection->add (new $class_name (array (
						'id'	=> $v,
						'name'	=> $v
					)));
				}
				
				$field->Values = $collection;
			}
			
			$text_value = Model_Manager::byQuery (
				'Text_Value',
				Query::instance ()
					->where ('tv_field_table', $table)
					->where ('tv_field_name', $field->Field)
			);
			
			// Есть запись для поля таблицы в таблице подстановок
			if ($text_value && $text_value->tv_text_field)
			{
				$query = Query::instance ()
					->select ($text_value->tv_text_table . '.' . 
						$text_value->tv_text_link_field)
				
					->select ($text_value->tv_text_table . '.' .
						$text_value->tv_text_field);
				
				$query
					->from ('`' . $text_value->tv_text_table . '`' );
				
				if ($text_value->tv_text_link_condition)
				{
					$query
						->where ($text_value->tv_text_link_condition);
				}
				
				$result = DDS::execute ($query)
					->getResult ()
						->asTable ();
				
				$collection = Model_Collection_Manager::create (
					$class_name
				)
					->reset ();
				
				foreach ($result as $item)
				{
					$collection->add (new $class_name (array (
						'id'	=> $item [$text_value->tv_text_link_field],
						'name'	=> $item [$text_value->tv_text_field]
					)));
				}
				
				$field->Values = $collection;
			}
			
			// Поле - поле для связи
			if (strpos ($field->Field, '__id') !== false)
			{
				$field->Values = Model_Collection_Manager::create (
					substr ($field->Field, 0, -4)
				);
			}
			
			// Ссылка на родителя
			if ($field->Field == 'parentId')
			{
				$field->Values = Model_Collection_Manager::create (
					$class_name
				);
			}
		}
		
		// Получаем эвенты
		$events  = array ();
		
		$tmp = $this->config ()->events->$class_name;
		
		if ($tmp)
		{
			$events = $tmp->__toArray ();
		}
		
		// Получаем плагины
		$plugins = array ();
		
		$tmp = $this->config ()->plugins->$class_name;
		
		if ($tmp)
		{
			$plugins = $tmp->__toArray ();	
		}
		
		// Получаем список вкладок
		$tabs = array ();
		
		$tmp = $this->config ()->tabs;
		
		if ($tmp)
		{
			$tabs = $tmp->__toArray ();
		}
		
		$this->_output->send (array (
			'row'		=> $row,
			'fields'	=> $fields,
			'table'		=> $table,
			'tabs'		=> $tabs,
			'events'	=> $events,
			'plugins'	=> $plugins,
			'keyField'	=> Model_Scheme::keyField ($class_name)
		));
	}
	
	/**
	 * @desc Список записей
	 * @return void
	 */
	public function table ()
	{
		$tables = Helper_Data_Source::tables ();

		$tmp_tables = $this->__aclTables ($tables->__toArray ());

		$table = $this->_input->receive ('table');
		
		Loader::load ('Table_Rate');
		
		$rate = Table_Rate::byTable ($table)->inc ();
		
		$acl_fields = $this->__fields ($table);

		if (!in_array ($table, $tmp_tables) || !$acl_fields || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->__className ($table, $prefix);

		$collection = Model_Collection_Manager::create ($class_name);
		
		// Получаем фильтры
		$filters = $this->config ()->filters->$class_name;
		
		if ($filters)
		{
			$filters = $filters->__toArray ();
			
			foreach ($filters as $field => $value)
			{
				$collection->where ($field, call_user_func ($value));
			}
		}
		
		// Сортируем коллекцию, если есть конфиг для сортировки
		$sort = $this->config ()->sort->$class_name;
		
		if ($sort)
		{
			$collection->addOptions (array (
				'name'	=> '::Order_Asc',
				'field'	=> $sort
			));
		}
		
		Loader::load ('Paginator');
		
		// Накладываем лимиты
		$limit = $this->config ()->limits->$class_name;
		
		if ($limit)
		{
			$_GET ['limit'] = $limit;
		}
		
		$paginator = Paginator::fromGet ();
		
		$collection->setPaginator ($paginator);
		
		$collection->load ();
		
		$paginator_html = Controller_Manager::html (
			'Paginator/index',
			array (
				'data'	=> $paginator,
				'tpl'	=> 'admin'
			)
		);
		
		$acl_fields = array ();
		
		$class_fields = $this->config ()->fields->$class_name;

		$fields = null;
		
		if ($class_fields)
		{
			$class_fields = $class_fields->__toArray ();
	
			$fields = Helper_Data_Source::fields ('`' . $table . '`');

			$acl_fields = $this->__aclFields ($table, $fields);

			foreach ($fields as $i => $field)
			{
				if (
					!in_array ($field ['Field'], $acl_fields) ||
					!in_array ($field ['Field'], $class_fields)
				)
				{
					unset ($fields [$i]);
				}
			}
		}
		
		$title = $this->config ()->titles->$class_name;
		
		$links = $this->config ()->links->$class_name;

		if ($links)
		{
			$links = $links->__toArray ();
		}
		
		$includes = $this->config ()->includes->$class_name;
		
		if ($includes)
		{
			foreach ($collection as $item)
			{
				foreach ($includes as $field => $model)
				{
					$model = Model_Manager::byKey (
						$model,
						$item->$field
					);
					
					if ($model)
					{
						$item->$field = $model->title ();
					}
				}
			}
		}
		
		$this->_output->send (array (
			'collection'		=> $collection,
			'fields'			=> $fields,
			'class_name'		=> $class_name,
			'table'				=> $table,
			'title'				=> !empty ($title) 
				? $title : $this->config ()->default_title, 
			'links'				=> $links,
			'keyField'			=> Model_Scheme::keyField ($class_name),
			'styles'			=> $this->config ()->styles->$class_name,
			'link_styles'		=> $this->config ()->link_styles->$class_name,
			'paginator_html'	=> $paginator_html
		));
	}
	
	/**
	 * @desc Сохранение записи
	 * @return void
	 */
	public function save ()
	{
		list (
			$table,
			$row_id,
			$column
		) = $this->_input->receive (
			'table',
			'row_id',
			'column'
		);
		
		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->__className ($table, $prefix);
		
		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		 
		$acl_fields = $this->__aclFields ($table, $fields);
		 
		if (!$acl_fields || !User::id ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		/* @var $row Model */
		$row = Model_Manager::get (
			$class_name,
			$row_id
		);
		
		foreach ($column as $field => $value)
		{
			if (!in_array ($field, $acl_fields))
			{
				unset ($column [$field]);
			}
		}
		
		$updated_fields = $column;
		
		if ($row->key ())
		{
			foreach ($column as $field => $value)
			{
				if ($value === $row->field ($field))
				{
					unset ($updated_fields [$field]);
				}
			}
			
			if ($updated_fields)
			{
				$row->update ($updated_fields);
			}
		}
		else
		{
			if ($updated_fields)
			{
				$row->set ($updated_fields);
				$row->save ();
			}
		}
		
		$this->__log (
			__METHOD__,
			$table,
			$updated_fields
		);
		
		Loader::load ('Helper_Header');
		
		Helper_Header::redirect ('/cp/table/' . $table . '/');
	}
}
