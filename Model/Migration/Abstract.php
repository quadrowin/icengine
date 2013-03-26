<?php

/**
 * Абстрактный класс миграции
 * 
 * @author morph
 */
class Migration_Abstract extends Model
{
	/**
	 * Миграция поднята
	 * 
     * @var interger
	 */
	const ST_UP = 1;

	/**
	 * Миграция откачена
	 * 
     * @var interger
	 */
	const ST_DOWN = 0;

	/**
	 * Параметры, переданные в миграцию контроллером,
	 * который ее запустил
	 * 
     * @var array
	 */
	protected $params;

	/**
	 * Откат миграции
	 */
	public function down()
	{
        return true;
	}

	/**
	 * Вернуть данные о последнем выполнение данной миграции
	 * 
     * @return array
	 */
	public function getLast()
	{
		$lastData = $this->getService('helperMigration')->getLastLog();
		if (!$lastData) {
			return;
		}
		$name = $this->getName();
		foreach ($lastData as $data) {
			if ($data['name'] == $name) {
				return $data;
			}
		}
		return null;
	}

	/**
	 * Получить имя миграции
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Migration_'));
	}

	/**
	 * Вернуть параметры миграции
	 * 
     * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Узнать состояние (поднята/откачена) миграции
	 * 
     * @return integer
	 */
	public function getState()
	{
        $helperMigration = $this->getService('helperMigration');
		$queue = $helperMigration->getQueue();
		$lastData = $helperMigration->getLastData();
		if (!$lastData) {
			return self::ST_DOWN;
		}
		$lastName = $lastData['name'];
		$name = $this->getName();
		foreach ($queue as $migrationName => $params) {
			if (!is_array($params))
			{
				$migrationName = $params;
			}
			if ($lastName == $migrationName) {
				return self::ST_DOWN;
			}
			if ($name == $migrationName)
			{
				return self::ST_UP;
			}
		}
		return self::ST_DOWN;
	}

	/**
	 * Залогировать выполнение миграции
	 * 
     * @param string $action
	 */
	public function log($action)
	{
        print $action;
        print "\n";
        $this->getService('helperMigration')->log($this->getName(), $action);
	}

	/**
	 * Востановить данные, которые были до миграции
	 * 
     * @desc array $data
	 */
	public function restore($data)
	{

	}

	/**
	 * Изменить параметры миграции
	 * 
     * @param array $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Сохранить данные, которые были до миграции
	 */
	public function store()
	{

	}

	/**
	 * Поднятие миграции
	 */
	public function up()
	{
        return true;
	}
}