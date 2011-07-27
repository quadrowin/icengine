<?php
/**
 * 
 * @desc Менеджер моделей.
 * @author Юрий
 * @package IcEngine
 *
 */
class Model_Manager extends Manager_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		'delegee'	=> array (
			'Model'				=> 'Simple',
			'Model_Config'		=> 'Config',
			'Model_Defined'		=> 'Defined',
			'Model_Factory'		=> 'Factory'
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
	
	/**0
	 * @desc Получение данных модели из источника данных.
	 * @param Model $object
	 */
	protected static function _read (Model $object)
	{
		$key = $object->key ();
		
		if (!$key)
		{
			return;
		}
		
		$query = Query::instance ()
			->select ('*')
			->from ($object->modelName ())
			->where ($object->keyField (), $key);
		
		$data = Model_Scheme::dataSource ($object->modelName ())
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
		}
	}
	
	/**
	 * @desc Удаление данных модели из источника.
	 * @param Model $object
	 */
	public static function _remove (Model $object)
	{
		if (!$object->key ())
		{
			return ;
		}
		Model_Scheme::dataSource ($object->modelName ())
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
		$ds = Model_Scheme::dataSource ($object->modelName ());

		$kf = $object->keyField ();
		$id = $object->key ();
		
		if ($id && !$hard_insert)
		{
			// Обновление данных
			$ds->execute (
				Query::instance ()
					->update ($object->modelName ())
					->values ($object->asRow ())
					->where ($kf, $id)
			);
		}
		else
		{
			// Вставка
			$new_id = Model_Scheme::generateKey ($object);
			if ($new_id)
			{
				// Ключ указан
				$object->set ($kf, $new_id);
				$ds->execute (
					Query::instance ()
						->insert ($object->modelName ())
						->values ($object->asRow ())
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
							->values ($object->asRow ())
					)->getResult ()->insertId ();
					
					$object->set ($kf, $id);
				}
				else
				{
					$ds->execute (
						Query::instance ()
							->insert ($object->modelName ())
							->values ($object->asRow ())
					);
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
	public static function byKey ($model, $key)
	{
		$result = Resource_Manager::get ('Model', $model . '__' . $key);
		
		if ($result)
		{
			return $result;
		}
		
		return self::byQuery (
			$model,
			Query::instance ()
				->where (Model_Scheme::keyField ($model), $key)
		);
	}

	/**
	 * @desc Получение модели по опциям.
	 * @param string $model Название модели.
	 * @param mixed $option Опция
	 * @param mixed $_ [optional]
	 * @return Model|null
	 */
	public static function byOptions ($model, $option)
	{
		$c = Model_Collection_Manager::create ($model)
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
	public static function byQuery ($model, Query $query)
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
			
			$data = 
				Model_Scheme::dataSource ($model)
					->execute ($query)
					->getResult ()
						->asRow ();
		}
		
		if (!$data)
		{
			return null;
		}
		
		return self::get (
			$model,
			$data [Model_Scheme::keyField ($model)],
			$data
		);
	}
	
	/**
	 * @desc Получение данных модели
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get ($model, $key, $object = null)
	{
		$result = null;
		
		if ($object instanceof Model)
		{
			$result = $object;
		}
		else
		{
			$result = Resource_Manager::get ('Model', $model . '__' . $key);
			
			if ($result instanceof Model)
			{
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
				$second = next ($parents);
				
				$parent = 
					$second && isset (self::$_config ['delegee'][$second]) ?
					$second :
					$first;
				
				$delegee = 
					'Model_Manager_Delegee_' .
					self::$_config ['delegee'][$parent];

				Loader::load ($delegee);
					
				$result = call_user_func (
					array ($delegee, 'get'),
					$model, $key, $object
				);
				
				$result->set ($result->keyField (), $key);
				Resource_Manager::set ('Model', $model . '__' . $key, $result);
			}
		}
		
		self::_read ($result);
		
		// В случае factory
		$model = get_class ($result);
		
		$generic = $result->generic ();
		
		$result = !is_null ($generic) ? $generic : $result;
		
		return new $model (
			array (),
			$result
		);
	}
	
	/**
	 * @desc Удаление модели.
	 * @param Model $object Объект модели.
	 */
	public static function remove (Model $object)
	{
		// из хранилища моделей
		Resource_Manager::set ('Model', $object->resourceKey (), null);
		// Из БД (или другого источника данных)
		self::_remove ($object);
	}
	
	/**
	 * @desc Сохранение данных модели
	 * @param Model $object Объект модели.
	 * @param boolean $hard_insert Объект будет вставлен в источник данных.
	 */
	public static function set (Model $object, $hard_insert = false)
	{
		self::_write ($object, $hard_insert);
		
		Resource_Manager::set ('Model', $object->resourceKey (), $object);
	}
}