<?php

/**
 * Менеджер моделей
 *
 * @author goorus, morph, neon
 */
class Model_Manager extends Manager_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected static $_config = array(
		'delegee'	=> array(
			'Model'             => 'Simple',
			'Component'         => 'Simple',
			'Model_Config'		=> 'Config',
			'Model_Defined'		=> 'Defined',
			'Model_Factory'		=> 'Factory'
		)
	);

	/**
	 * Получение модели по первичному ключу
     *
	 * @param string $model Имя класса модели.
	 * @param integer $key Значение первичного ключа.
     * @param boolean $lazy Добавить ли загрузку модели в очередь отложенных
     * загрузок
	 * @return Model|null
	 */
	public static function byKey($modelName, $key, $lazy = false)
	{
        $result = Resource_Manager::get('Model', $modelName . '__' . $key);
        if ($result) {
            return $result;
        }
        $keyField = Model_Scheme::keyField($modelName);
        if (!$lazy) {
            $query = Query::instance()
                ->where($keyField, $key);
            return self::byQuery($modelName, $query);
        }
        $model = self::create($modelName, array());
        $model->set($keyField, $key);
        $query = Query::instance()
            ->select('*')
            ->where($keyField, $key);
        Unit_Of_Work::push($query, $model, 'Simple');
        $model->setLazy(true);
        return $model;
	}

	/**
	 * Получение модели по опциям
     *
	 * @param string $modelName Название модели.
	 * @param mixed $_ [optional]
	 * @return Model|null
	 */
	public static function byOptions($modelName)
	{
        $collection = Model_Collection_Manager::create($modelName)
            ->addOptions(array(
                'name'  => '::Limit',
                'count' => 1
            ));
        $args = func_get_args();
        $count = count($args);
        if ($count > 1) {
            for ($i = 1; $i < $count; $i++) {
                $collection->addOptions($args[$i]);
            }
        }
        return $collection->first();
	}

	/**
	 * Получение модели по запросу
     *
	 * @param string $modelName Название модели.
	 * @param Query_Abstract $query Запрос.
     * @param boolean $lazy Добавить ли запрос на загрузку о очередь
     * отложенных запросов
	 * @return Model|null
	 */
	public static function byQuery($modelName, Query_Abstract $query,
        $lazy = false)
	{
        $config = self::config();
        $parentClass = self::getParentClass($modelName, $config['delegee']);
        static $heavyDelegees = array('Model_Factory', 'Model_Defined');
        if (!$query->getPart(Query::SELECT)) {
            $query->select(array($modelName => '*'));
        }
        if (!$query->getPart(Query::FROM)) {
            $query->from($modelName);
        }
		if ($lazy && !in_array($parentClass, $heavyDelegees)) {
            return self::lazyLoad($modelName, $query);
        }
        $dataSource = Model_Scheme::dataSource($modelName);
        $data = $dataSource->execute($query)->getResult()->asRow();
        $keyField = Model_Scheme::keyField($modelName);
        if (!$data || !isset($data[$keyField])) {
            return null;
        }
        $key = $data[$keyField];
        $model = self::get($modelName, $key, $data);
        if ($lazy) {
            $model->setLazy(true);
        }
        return $model;
	}

	/**
	 * Создать модель из источника
     *
	 * @param string $modelName
	 * @param array $fields источник значений для полей
	 * @return Model
	 */
	public static function create($modelName, $fields)
	{
		$scheme = Model_Scheme::scheme($modelName);
		$schemeFields = $scheme->fields;
		$row = array();
		foreach ($schemeFields as $field => $data) {
            $default = isset($data['Default']) ? $data['Default'] : null;
            $value = isset($fields[$field]) ? $fields[$field] : $default;
            $row[$field] = $value;
        }
		$config = self::config();
        $parent = self::getParentClass($modelName, $config['delegee']);
		$delegee = 'Model_Manager_Delegee_' . $config['delegee'][$parent];
		$model = call_user_func_array(
            array($delegee, 'get'),
            array($modelName, 0, $row)
		);
		$model->set($row);
		return $model;
	}

	/**
	 * Получение данных модели
     *
	 * @param string $modelName Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $source Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get($modelName, $key, $source = null)
	{
		if ($source instanceof Model) {
            return $source;
        }
        $resourceKey = $modelName . '__' . $key;
        $model = Resource_Manager::get('Model', $resourceKey);
        if ($model instanceof Model) {
            if (is_array($source)) {
                $model->set($source);
            }
            return $model;
        }
        $config = self::config();
        $parent = self::getParentClass($modelName, $config['delegee']);
        $delegee = 'Model_Manager_Delegee_' .
            $config['delegee'][$parent];
        $newModel = call_user_func_array(
            array($delegee, 'get'),
            array($modelName, $key, $source)
        );
        $keyField = $newModel->keyField();
        $newModel->set($keyField, $key);
        if ($key) {
            self::read($newModel);
        }
        Resource_Manager::set('Model', $resourceKey, $newModel);
		return $newModel;
	}

    /**
     * Получить имя родительского класса
     *
     * @param string $modelName
     * @param array|Objective $config
     * @return string
     */
    public static function getParentClass($modelName, $config)
    {
        $parents = class_parents($modelName);
		$first = end($parents);
		$second = prev($parents);
		return isset($config[$second]) ? $second : $first;
    }

    /**
     * Отложенная загрузка модели по запросу
     *
     * @param string $modelName
     * @param Query_Abstract $query
     * @return Model
     */
    public static function lazyLoad($modelName, $query)
    {
		$where = $query->part(QUERY::WHERE);
		$wheres = array();
		$whereFields = array();
		foreach ($where as $value) {
			$wheres[] = $value[QUERY::WHERE] . '=' . $value[QUERY::VALUE];
			$whereFields[$value[QUERY::WHERE]] = $value[QUERY::VALUE];
		}
		$keyHash = implode(':', $wheres);
		$result = Resource_Manager::get('Model', $modelName . '__' . $keyHash);
		if ($result) {
			return $result;
		}
        static $filters = array(
            '?'	=> '',
            '<'	=> '',
            '>'	=> ''
        );
		$whereFieldsPrepared = array();
		foreach($whereFields as $key => $whereField) {
			$fieldName = trim(strtr($key, $filters));
			$whereFieldsPrepared[$fieldName] = $whereField;
		}
		$model = self::create($modelName);
        $model->set($whereFieldsPrepared);
		Unit_Of_Work::push($query, $model, 'Simple');
		return $model;
    }

    /**
	 * Получение данных модели из источника данных.
	 *
     * @param Model $model
	 * @return boolean
	 */
	protected static function read(Model $model)
	{
		$key = $model->key();
		if (!$key) {
			return false;
		}
        $modelName = $model->table();
		$query = Query::instance ()
			->select ('*')
			->from($modelName)
			->where($model->keyField(), $key);
        $dataSource = Model_Scheme::dataSource($modelName);
        $data = $dataSource->execute($query)->getResult()->asRow();
        if ($data) {
            $data = array_merge($data, $model->asRow());
            $model->set($data);
            return true;
        }
        return false;
	}

	/**
	 * Удаление данных модели из источника
     *
	 * @param Model $model
	 */
	public static function remove(Model $model)
	{
        $key = $model->key();
        if (!$key) {
            return;
        }
        Resource_Manager::set('Model', $model->resourceKey(), null);
        $modelName = $model->table();
        $dataSource = Model_Scheme::dataSource($modelName);
        $query = Query::instance()
            ->delete()
            ->from($modelName)
            ->where($model->keyField(), $key);
        $dataSource->execute($query);
	}

	/**
	 * Сохранение данных модели
     *
	 * @param Model $model Объект модели.
	 * @param boolean $hardInsert Объект будет вставлен в источник данных.
	 */
	public static function set(Model $model, $hardInsert = false)
	{
        $resourceKey = $model->resourceKey();
        $updatedFields = $model->getUpdatedFields();
        if ($updatedFields || $hardInsert || $model->key()) {
            self::write($model, $hardInsert);
        }
		Resource_Manager::set('Model', $resourceKey, $model);
		Resource_Manager::setUpdated('Model', $resourceKey, $updatedFields);
	}

    /**
	 * Сохранение модели в источник данных
     *
	 * @param Model $model
	 * @param boolean $hardInsert
	 */
	protected static function write(Model $model, $hardInsert = false)
	{
        $modelName = $model->table();
        $key = $model->key();
        $keyField = $model->keyField();
        $dataSource = Model_Scheme::dataSource($modelName);
        if ($key && !$hardInsert) {
            $query = Query::instance()
                ->update($modelName)
                ->values($model->getFields())
                ->where($keyField, $key);
            $dataSource->execute($query);
        } else {
            if (!$key) {
                $key = Model_Scheme::generateKey($model);
            }
            if ($key) {
                $model->set($keyField, $key);
            } else {
                $model->unsetField($keyField);
            }
            $query = Query::instance()
                ->insert($modelName)
                ->values($model->getFields());
            $result = $dataSource->execute($query)->getResult();
            if (!$key) {
                $key = $result->insertId();
                $model->set($keyField, $key);
            }
        }
	}
}