<?php
/**
 * 
 * @desc Временный контент - специальная модель, предназначенная для
 * хранения дополнительной информации о форме для редактирования.
 * @author Гурус
 * @package IcEngine
 *
 */
class Temp_Content extends Model
{
	
	/**
	 * @desc Созданные за этот запрос
	 * @var array
	 */
	protected static $_created = array ();
	
	/**
	 * @desc Возвращет временный контент по коду
	 * @param string $utcode
	 * @return Temp_Content
	 */
	public static function byUtcode ($utcode)
	{
		return IcEngine::$modelManager->modelBy (
			__CLASS__,
			Query::instance ()
			->where ('utcode', (string) $utcode)
		);
	}
	
	/**
	 * @desc Создает новый временный контент
	 * @param string|Controller_Abstract $controller Контроллер или название
	 * @param string $table 
	 * @param integer $row_id
	 * @return Temp_Content
	 */
	public static function create ($controller, $table = '', $row_id = 0)
	{
		$utcode = self::genUtcode ();
		
		$tc = new Temp_Content (array (
			'time'			=> Helper_Date::toUnix (),
			'utcode'		=> $utcode,
			'ip'			=> Request::ip (),
			'controller'	=> 
				$controller instanceof Controller_Abstract ? 
				$controller->name () : 
				$controller,
			'table'			=> $table,
			'rowId'			=> (int) $row_id,
			'day'			=> Helper_Date::eraDayNum (),
			'User__id'		=> User::id ()
		));
		
		return $tc->save ();
	}
	
	/**
	 * @desc Возвращает временный контент для модели на этом запросе
	 * @param Model $model
	 * @param Controller_Abstract $controller
	 * @return Temp_Content
	 */
	public static function getFor (Model $model, 
		Controller_Abstract $controller = null)
	{
		$mname = $model->modelName ();
		$mkey = $model->key ();
		
		if (!isset (self::$_created [$mname]))
		{
			self::$_created [$mname] = array ();
		}
		
		if (!isset (self::$_created [$mname][$mkey]))
		{
			self::$_created [$mname][$mkey] = self::create (
				$controller ? $controller->name () : '',
				$model->table (),
				$mkey
			);
		}
		
		return self::$_created [$mname][$mkey];
	}
	
	/**
	 * @desc Генерация уникального кода
	 * @return string
	 */
	public static function genUtcode ()
	{
		// ucac7fe407f8e5e1c683005867edd74439452c4.39068717
		$u = uniqid ('', true);
		// Вырезаем точку
		return 'uc' . md5 (time ()) . substr ($u, 9, 5) . substr ($u, 15);
	}
	
	/**
	 * 
	 * @param Model $item
	 * @param array $components
	 * @return Temp_Content
	 */
	public function rejoinComponents (Model $item, array $components)
	{
		foreach ($components as $component)
		{
			$this->component ($component)->rejoin ($item);
		}
		return $this;
	}
	
}