<?php

/**
 * Генератор уникальных ключей
 *
 * @author Yury Shveodv, Ilya Kolesnikov
 * @package IcEngine
 */
class Key_Generator extends Manager_Abstract
{

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		// Провайдер
		'provider'		=> null,
		// Минимальное значение
		'min_value'		=> 1,
		// Максимальное значение
		'max_value'		=> 100000000
	);

	/**
	 * Провайдер для хранения текущего значения
	 * 
     * @var Data_Provider_Abstract
	 */
	protected $provider;

	/**
	 * Генерирует новый ключ
     * 
	 * @param string|Model $model
	 * @return integer
	 */
	public function get($model = 'def')
	{
		if (is_object($model)) {
			$model = $model->modelName();
		}
		$provider = $this->provider();
		$value = $provider->increment($model);
		$newValue = $value;
        if ($value < $this->config()->min_value) {
			if (!$provider->lock($model, 1, 5, 100)) {
				throw new Exception ('Failed to lock key value');
			}
			$value = $this->load($model, $this->config()->min_value);
			$provider->set($model, $value);
			$provider->unlock($model);
			$newValue = $provider->increment($model);
		}
		$this->save($model, $newValue);
		return $newValue;
	}

	/**
	 * Возвращает название файла с бэкапом ключей.
	 * 
     * @return string
	 */
	public function lastFile($model)
	{
		$helperSiteLocation = $this->getService('helperSiteLocation');
		$dir = IcEngine::root() . 'Ice/Var/Key/Generator/' .
			urlencode ($helperSiteLocation->getLocation());
		if (!is_dir($dir)) {
			mkdir($dir, 0666);
			chmod($dir, 0666);
		}
		return $dir . '/' . urlencode($model) . '.txt';
	}

	/**
	 * Загрузка значения из надежного хранилища
	 * 
     * @param string $model
	 * @param integer $min
	 */
	public function load($model, $min)
	{
		$file = $this->lastFile($model);
		if (file_exists($file)) {
			$value = file_get_contents ($file);
		} else {
            $modelScheme = $this->getService('modelScheme');
			$dataSource = $modelScheme->dataSource($model);
			$keyField = $modelScheme->keyField($model);
            $queryBuilder = $this->getService('query');
            $query = $queryBuilder
                ->select($keyField)
                ->from($model)
                ->where("$keyField < ?", $this->config()->max_value)
                ->order(array($keyField => Query::DESC))
                ->limit(1);
			$value = $dataSource->execute($query)->getResult()->asValue();
		}
		return max($value, $min) + 7; // magic 7
	}

	/**
	 * Провайдер
	 * 
     * @return Data_Provider_Abstract
	 */
	public function provider()
	{
		if (!$this->provider) {
            $dataProviderManager = $this->getService('dataProviderManager');
            $providerName = $this->config()->provider;
			$this->provider = $dataProviderManager->get($providerName);
		}
		return $this->provider;
	}

	/**
	 * Дублирование значения в файл
	 * 
     * @param string $model
	 * @param integer $value
	 */
	public function save($model, $value)
	{
		$file = $this->lastFile($model);
		file_put_contents($file, $value);
	}
}