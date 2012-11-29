<?php

/**
 * Временный контент - специальная модель, предназначенная для
 * хранения дополнительной информации о форме для редактирования.
 *
 * @author Гурус
 * @package IcEngine
 */
class Temp_Content extends Model
{

	/**
	 * Дата
	 *
	 * @var type
	 */
	protected $_data = null;

	/**
	 * Созданные за этот запрос
	 *
	 * @param 0 = name
	 * @param 1 = value
	 * @var array
	 */
	protected static $_created = array ();

	/**
	 * Устанавливаем дату, ключ = значение
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function _setAttr($key, $value)
	{
		$this->_data[$key] = $value;
		$this->update(array(
			'json' => json_encode($this->_data)
		));
	}

	/**
	 * Переопределяем аттрибуты
	 *
	 * @return type
	 */
	public function attr($key, $value = null)
	{
		if (is_null($this->_data)) {
			$this->_data = json_decode($this->json, true);
		}
		$args = func_get_args();
		if (count($args) == 1 && !is_array($args[0])) {
			return isset($this->_data[$args[0]])
					? $this->_data[$args[0]] : null;
		} elseif (is_array($args[0])) {
			foreach ($args[0] as $key => $value) {
				$this->_setAttr($key, $value);
			}
		} elseif (count($args) == 2) {
			$this->_setAttr($args[0], $args[1]);
		}
	}

	/**
	 * Возвращет временный контент по коду
	 *
	 * @param string $utcode
	 * @return Temp_Content
	 */
	public static function byUtcode($utcode)
	{
		return $this->getService('modelManager')->byKey(
			__CLASS__,
			(string) $utcode
		);
	}

	/**
	 * Создает новый временный контент
	 *
	 * @param string|Controller_Abstract $controller Контроллер или название
	 * @param string $table
	 * @param integer $row_id
	 * @return Temp_Content
	 */
	public static function create($controller, $table = '',
		$row_id = 0, $data = null)
	{
		$utcode = self::genUtcode();
		$helperDate = $this->getService('helperDate');
		$request = $this->getService('request');
		$user = $this->getService('user');
		$tc = new Temp_Content(array(
			'time'			=> $helperDate->toUnix(),
			'utcode'		=> $utcode,
			'json'			=> json_encode($data),
			'ip'			=> $request->ip(),
			'controller'	=>
				$controller instanceof Controller_Abstract ?
				$controller->name() :
				$controller,
			'table'			=> $table,
			'rowId'			=> (int) $row_id,
			'day'			=> $helperDate->eraDayNum(),
			'User__id'		=> $user->id()
		));
		return $tc->save();
	}

	/**
	 * Возвращает временный контент для модели на этом запросе
	 *
	 * @param Model $model
	 * @param Controller_Abstract $controller
	 * @return Temp_Content
	 */
	public static function getFor(Model $model,
		Controller_Abstract $controller = null)
	{
		$mname = $model->modelName();
		$mkey = $model->key();

		if (!isset(self::$_create[$mname]))
		{
			self::$_created[$mname] = array();
		}

		if (!isset (self::$_created[$mname][$mkey]))
		{
			self::$_created[$mname][$mkey] = self::create(
				$controller ? $controller->name() : '',
				$model->table(),
				$mkey
			);
		}

		return self::$_created[$mname][$mkey];
	}

	/**
	 * Генерация уникального кода
	 *
	 * @return string
	 */
	public static function genUtcode()
	{
		// ucac7fe407f8e5e1c683005867edd74439452c4.39068717
		$u = uniqid('', true);
		// Вырезаем точку
		return md5(time()) . substr($u, 9, 5) . substr($u, 15);
	}

	public static function idForNew(Temp_Content $tc)
	{
		return $tc->utcode;
	}

	/**
	 *
	 * @param Model $item
	 * @param array $components
	 * @return Temp_Content
	 */
	public function rejoinComponents(Model $item, array $components)
	{
		foreach ($components as $component) {
			$this->component($component)->rejoin($item);
		}
		return $this;
	}
}