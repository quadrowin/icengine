<?php
/**
 *
 * @desc Контроллер управления доступом
 * @author Илья Колесников, Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Admin_Acl extends Controller_Abstract
{

	/**
	 * @desc Config
	 * @var array|Objective
	 */
	protected $_config = array (
		// Роли, имеющие доступ к админке
		'access_roles'	=> array ('admin'),
		// Типы доступа
		'access_types'	=> array ('create', 'edit'),
		// Контроллируемые роли
		'control_roles'	=> array ('admin')
	);

	/**
	 * @desc Получить ресурсы Aсl и префиксом Table/
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
	 * @desc Получить список полей для создания прав
	 */
	public function field()
	{
		if (!$this->_checkAccess()) {
			return $this->replaceAction('Error', 'accessDenied');
		}
		$role_id = $this->_input->receive('role_id');
		$modelManager = $this->getService('modelManager');
		$role = $modelManager->byKey(
			'Acl_Role',
			$role_id
		);
		if (!$role) {
			return;
		}
		$resources = $this->__resources($role);
		$helperDataSource = $this->getservice('helperDataSource');
		$tables = $helperDataSource->tables();
		$result = array();
		foreach ($tables as $table) {
			$fields = $helperDataSource->fields('`' . $table ['Name'] . '`');
			$result[$table['Name']] = array(
				'table'		=> $table,
				'fields'	=> array()
			);
			foreach ($this->config()->access_types as $type) {
				foreach ($fields as $field) {
					if (!isset ($result [$table ['Name']]['fields'][$field ['Field']]))
					{
						$result [$table ['Name']]['fields'][$field ['Field']] = array (
							'field'			=> $field,
							'resources'		=> array ()
						);
					}

					$resource_name = 'Table/' . $table ['Name'] . '/' . $field ['Field'] . '/' . $type . '/';

					$result [$table ['Name']]['fields'][$field ['Field']]['resources'][$type] = array (
						'resource'	=> $resource_name,
						'type'		=> $type,
						'on'		=> in_array ($resource_name, $resources)
					);
				}
			}
		}

		$this->_output->send (array (
			'tables'	=> $result,
			'role'		=> $role
		));
	}

	/**
	 * @desc Получаем список ролей
	 */
	public function roll()
	{
		if (!$this->_checkAccess()) {
			return $this->replaceAction('Error', 'accessDenied');
		}
		$role_names = $this->config()->control_roles->asArray();
		$collectionManager = $this->getService('collectionManager');
		$query = $this->getService('query');
		$role_collection = $collectionManager->byQuery(
			'Acl_Role',
			$query->where('name', $role_names)
		);
		$this->_output->send(array(
			'role_collection'	=> $role_collection
		));
	}

	/**
	 * @desc Сохраняем права на поля таблиц
	 */
	public function save ()
	{
		set_time_limit (0);

		if (!$this->_checkAccess ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$role_id = $this->_input->receive ('role_id');
		$modelManager = $this->getService('modelManager');
		$role = $modelManager->byKey (
			'Acl_Role',
			$role_id
		);
		if (!$role) {
			return;
		}
		$resources = $this->__resources($role);
		$collectionManager = $this->getService('collectionManager');
		$query = $this->getService('query');
		$resource_collection = $collectionManager->byQuery(
			'Acl_Resource',
			$query->where('name', $resources)
		);
		$helperLink = $this->getService('helperLink');
		foreach ($resource_collection as $resource) {
			$helperLink->unlink($role, $resource);
		}
		$resources = $this->input->receive('resources');
		foreach ($resources as $resource_name) {
			$resource = Model_Manager::byQuery (
				'Acl_Resource',
				$query->where('name', $resource_name)
			);
			if (!$resource) {
				$resource = new Acl_Resource(array(
					'name'	=> $resource_name
				));
				$resource->save();
			}
			$helperLink->link($role, $resource);
		}
		$helperHeader = $this->getService('helperHeader');
		$helperHeader->redirect('/cp/acl/');
	}

	/**
	 * Сохраняем права на один определенный ресурс
	 */
	public function saveOneResource($resource_name, $checked, $role_id)
	{
		$this->_task->setTemplate(null);
		set_time_limit (0);
		if (!$this->_checkAccess()) {
			return $this->replaceAction('Error', 'accessDenied');
		}
		$role_id = $this->_input->receive('role_id');
		$modelManager = $this->getService('modelManager');
		$role = $modelManager->byKey(
			'Acl_Role',
			$role_id
		);
		if (!$role) {
			return;
		}
		$query = $this->getService('query');
		$resource = $modelManager->byQuery(
			'Acl_Resource',
			$query->where('name', $resource_name)
		);
		if (!$resource) {
			$resource = new Acl_Resource(array(
				'name'	=> $resource_name
			));
			$resource->save();
		}
		$helperLink = $this->getService('helperLink');
		if ($checked) {
			$helperLink->link ($role, $resource);
		} else {
			$helperLink->unlink ($role, $resource);
		}
	}
}