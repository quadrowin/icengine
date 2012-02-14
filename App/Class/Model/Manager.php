<?php

namespace Ice;

/**
 *
 * @desc Менеджер моделей.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Model_Manager
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		'delegee' => array (
			'Ice\\Model' => 'Simple',
			'Ice\\Model_Config' => 'Config',
			'Ice\\Model_Defined' => 'Defined',
			'Ice\\Model_Factory' => 'Factory'
		)
	);

	/**
	 * @desc Получение условий выборки из запроса
	 * @param Query $query
	 * @return array|null
	 */
	protected static function _prepareSelectQuery (Query $query)
	{
		$where = $query->getPart (Query::WHERE);
		$conditions = array ();
		foreach ($where as $w)
		{
			$condition = $w [Query::WHERE];
			$value = $w [Query::VALUE];

			$p = strpos ($condition, '=?');
			if ($p)
			{
				$condition = substr ($condition, 0, $p);
			}

			$conditions [$condition] = $value;
		}
		return $conditions;
	}

	/**
	 * @desc Получение данных модели из источника данных.
	 * @param Model $object
	 * @return boolean
	 */
	protected function _read (Model $object)
	{
		$key = $object->key ();

		if (!$key)
		{
			return false;
		}

		$query = Query::instance ()
			->select ('*')
			->from ($object->modelName ())
			->where ($object->keyField (), $key);

		$data = Model_Scheme::getInstance ()
			->getDataSource ($object->modelName ())
				->execute ($query)
				->getResult ()->asRow ();

		if ($data)
		{
			// array_merge чтобы не затереть поля, которые были
			// установленны через set
			$object->set (array_merge (
				$data,
				$object->asRow ()
			));
			return true;
		}
		return false;
	}

	/**
	 * @desc Удаление данных модели из источника.
	 * @param Model $object
	 */
	public function _remove (Model $object)
	{
		if (!$object->key ())
		{
			return ;
		}
		Model_Scheme::getInstance ()
			->getDataSource ($object->modelName ())
				->execute (
					Query::instance ()
						->delete ()
						->from ($object->table ())
						->where ($object->keyField (), $object->key ())
				);
	}

	/**
	 * @desc Сохранение модели в источник данных
	 * @param Model $object
	 * @param boolean $hard_insert
	 */
	protected static function _write (Model $object, $hard_insert = false)
	{
		$ms = Model_Scheme::getInstance ();
		$ds = $ms->getDataSource ($object->modelName ());

		$kf = $object->keyField ();
		$id = $object->key ();

		if ($id && !$hard_insert)
		{
			// Обновление данных
			$ds->execute (
				Query::instance ()
					->update ($object->modelName ())
					->values ($object->getFields ())
					->where ($kf, $id)
			);
		}
		else
		{
			// Вставка
			if ($id)
			{
				$ds->execute (
					Query::instance ()
						->insert ($object->modelName ())
						->values ($object->getFields ())
				);
			}
			else
			{
				// Генерация первичного ключа
				$new_id = $ms->generateKey ($object);
				if ($new_id)
				{
					// Ключ указан
					$object->set ($kf, $new_id);
					$ds->execute (
						Query::instance ()
							->insert ($object->modelName ())
							->values ($object->getFields ())
					);
				}
				else
				{
					if (!$id)
					{
						$object->unsetField ($kf);
						$id = $ds->execute (
							Query::instance ()
								->insert ($object->modelName ())
								->values ($object->getFields ())
						)->getResult ()->insertId ();

						$object->set ($kf, $id);
					}
					else
					{
						$ds->execute (
							Query::instance ()
								->insert ($object->modelName ())
								->values ($object->getFields ())
						);
					}
				}
			}
		}
	}

	/**
	 * @desc Получение модели по первичному ключу.
	 * @param string $model Имя класса модели.
	 * @param integer $key Значение первичного ключа.
	 * @return Model|null
	 */
	public function byKey ($model, $key)
	{
		$result = Resource_Manager::get ('Model', $model . '__' . $key);

		if ($result)
		{
			return $result;
		}

		return $this->byQuery (
			$model,
			Query::instance ()
				->where (
					Model_Scheme::getInstance ()->getKeyField ($model),
					$key
				)
		);
	}

	/**
	 * @desc Получение модели по опциям.
	 * @param string $model Название модели.
	 * @param mixed $option Опция
	 * @param mixed $_ [optional]
	 * @return Model|null
	 */
	public function byOptions ($model, $option)
	{
		$c = Model_Collection_Manager::getInstance ()->create ($model)
			->addOptions (array (
				'name'		=> '::Limit',
				'count'		=> 1
			));

		for ($i = 1; $i < func_num_args (); ++$i)
		{
			$c->addOptions (func_get_arg ($i));
		}

		return $c->first ();
	}

	/**
	 * @desc Получение модели по запросу.
	 * @param string $model Название модели.
	 * @param Query $query Запрос.
	 * @return Model|null
	 */
	public function byQuery ($model, Query $query)
	{
		$data = null;

		if (is_null ($data))
		{
			if (!$query->getPart (Query::SELECT))
			{
				$query->select (array ($model => '*'));
			}

			if (!$query->getPart (Query::FROM))
			{
				$query->from ($model, $model);
			}

			$data = Model_Scheme::getInstance ()
				->getDataSource ($model)
					->execute ($query)
					->getResult ()
						->asRow ();
		}

		if (!$data)
		{
			return null;
		}

		return $this->get (
			$model,
			$data [Model_Scheme::getInstance ()->getKeyField ($model)],
			$data
		);
	}

	/**
	 * @desc Конфиги менеджера
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (static::$_config))
		{
			static::$_config = Config_Manager::get (
				get_called_class (),
				static::$_config
			);
		}
		return static::$_config;
	}

	/**
	 * @desc Создать модель из источника
	 * @param string $model_name
	 * @param array $fields источник значений для полей
	 * @return Model
	 */
	public function create ($model_name, $fields)
	{
		$scheme = Model_Scheme::getInstance ()->getScheme ($model_name);
		$scheme_fields = $scheme ['fields'];
		$row = array ();

		foreach ($scheme_fields as $field => $data)
		{
			$value = isset ($fields [$field])
				? $fields [$field]
				: null;
			$row [$field] = $value;
		}
		Loader::load ($model_name);
		return new $model_name ($row);
	}

	/**
	 * @desc Получение данных модели
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model Всегда возвращает модель
	 */
	public function get ($model, $key, $object = null)
	{
		$cached = $object != null;
		$result = null;

		if ($object instanceof Model)
		{
			$cached = true;
			$result = $object;
		}
		else
		{
			$result = Resource_Manager::get ('Model', $model . '__' . $key);

			if ($result instanceof Model)
			{
				$cached = true;
				if (is_array ($object))
				{
					$result->set ($object);
				}
			}
			else
			{
				Loader::load ($model);

				// Делегируемый класс определяем по первому или нулевому
				// предку.
				$parents = class_parents ($model);
				$first = end ($parents);
				$second = prev ($parents);

				$parent =
					$second && isset (self::$_config ['delegee'][$second])
					? $second
					: $first;

				$delegee =
					__NAMESPACE__ . '\\Model_Manager_Delegee_' .
					self::$_config ['delegee'][$parent];

				Loader::load ($delegee);

				$result = call_user_func (
					array ($delegee, 'get'),
					$model, $key, $object
				);

				$result->set ($result->keyField (), $key);

				Resource_Manager::set (
					'Model',
					$model . '__' . $key,
					$result
				);
			}
		}

		$readed = !$cached ? $this->_read ($result) : true;
//		$readed = $this->_read ($result);

		// В случае factory
		$model = get_class ($result);

		if (!$readed)
		{
			return new $model (
				$key
				? array ($result->keyField () => $key)
				: array ()
			);
		}

		$generic = $result->generic ();

		$result = $generic ? $generic : $result;

		$result = new $model (
			$result->getFields (),
			clone $result
		);

//		Model_Scheme::setScheme ($result);

		if (!$result->scheme ()->loaded ())
		{
			$result->scheme ()->setModel ($result);
			$result->scheme ()->load ();
		}
		return $result->key () ? $result : new $model (array ());
	}

	/**
	 * @desc Возвращает используемый экземпляр класса
	 * @return object Model_Manager
	 */
	public static function getInstance ()
	{
		return Core::di ()->getInstance (__CLASS__);
	}

	/**
	 * @desc Удаление модели.
	 * @param Model $object Объект модели.
	 */
	public function remove (Model $object)
	{
		// из хранилища моделей
		Resource_Manager::set ('Model', $object->resourceKey (), null);
		// Из БД (или другого источника данных)
		$this->_remove ($object);
	}

	/**
	 * @desc Сохранение данных модели
	 * @param Model $object Объект модели.
	 * @param boolean $hard_insert Объект будет вставлен в источник данных.
	 */
	public function set (Model $object, $hard_insert = false)
	{
		$this->_write ($object, $hard_insert);

		Resource_Manager::set ('Model', $object->resourceKey (), $object);
	}
}
