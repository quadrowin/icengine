<?php

/**
 * Менеджер коллекций
 *
 * @author morph, goorus
 */
class Model_Collection_Manager extends Manager_Abstract
{
	/**
     * @inheritdoc
	 */
	protected $config = array(
		'cache_provider'	=> 'mysqli_cache',
		'delegee'			=> array(
			'Model'				=> 'Simple',
			'Model_Config'		=> 'Simple',
			'Model_Defined'		=> 'Defined',
			'Model_Factory'		=> 'Simple'
		)
	);

	/**
	 * Возвращает коллекцию по запросу.
     *
	 * @author goorus
	 * @param string $modelName Модель коллекции.
	 * @param Query_Abstract $query Запрос.
	 * @return Model_Collection
	 */
	public function byQuery($modelName, Query_Abstract $query)
	{
		$collection = $this->create($modelName);
		$collection->setQuery($query);
		return $collection;
	}

    /**
     * Вызрать делигата коллекции и получить данные
     *
     * @param Model_Collection $collection
     * @param Query_Abstract $query
     * @return array
     */
    public function callDelegee($collection, $query)
    {
        $modelName = $collection->modelName();
        // Делегируемый класс определяем по первому или нулевому
        // предку.
        $parents = class_parents($modelName);
        $first = end($parents);
        $second = prev($parents);
        $config = $this->config();
        $parent = $second && isset($config['delegee'][$second])
            ? $second : $first;
        $delegee = 'Model_Collection_Manager_Delegee_' .
            $config['delegee'][$parent];
        $pack = call_user_func(array($delegee, 'load'), $collection, $query);
        return $pack;
    }

	/**
	 * Создает коллекцию по имени.
     *
	 * @param string $modelName Модель колекции.
	 * @return Model_Collection Коллекция.
	 */
	public function create($modelName)
	{
		$className = $modelName . '_Collection';
		return new $className;
	}
    
    /**
     * Получить тэги по запросу
     * 
     * @param Query_Abstract $query
     * @return array
     */
    protected function getTags($query)
    {
        $tags = array();
        $from = $query->getPart(Query::FROM);
        if ($from) {
            $tables = array();
			$provider = $this->getService('dataProviderManager')->get(
				$this->config()->cache_provider
			);
			if ($provider) {
                $modelScheme = $this->getService('modelScheme');
				foreach ($from as $fromPart) {
					$tables[] = $modelScheme->table($fromPart[Query::TABLE]);
				}
				$tags = $provider->getTags($tables);
			}
        }
        return $tags;
    }

	/**
	 * Получить коллекцию из хранилища по запросу и опшинам
     *
	 * @param Model_Collection
	 * @param Query_Abstract $query
	 */
	public function load(Model_Collection $collection, Query_Abstract $query)
	{
		$modelName = $collection->modelName();
        $keyField = $collection->keyField();
		$collectionTags = array ();
		$tags = $this->getTags($query);
        $addicts = array();
		$isTagsValid = true;
        $resourceManager = $this->getService('resourceManager');
		// Генерируем ключ коллекции
		$key = md5($modelName . $query->translate('Mysql') .
            json_encode($collection->getOptions())
		);
		// Получаем коллецию из менеджера ресурсов
		$data = $resourceManager->get('Model_Collection', $key);
		// Если коллекцию уже использовалась в текущем сценарии,
		// то в менеджере ресурсов она будет уже инициализированная
		if ($data instanceof Model_Collection) {
			$collection->setItems($data->items());
			$collection->data($data->data ());
			return $collection;
		}
        // Из менеджера ресурсов получили свернутую коллекцию
        if (is_array($data)) {
			$collection->data($data['data']);
			$keys = array();
			foreach ($data['items'] as $item) {
				$keys[] = $item[$keyField];
				$addicts[] = $item['addicts'];
			}
			$collectionTags = $data['t'];
            $isTagsValid = $collectionTags && array_diff(
                $tags, $collectionTags
            );
            $data['items']	= $keys;
			$collection->data('addicts', $addicts);
		}
		if (!$data || !$isTagsValid) {
			$data = self::callDelegee($collection, $query);
			$collection->data('t', $tags);
			$addicts = $collection->data('addicts');
		}
		$iterator = $collection->currentIterator();
		if ($iterator) {
			return $iterator->setData($data['items']);
		}
        $modelManager = $this->getService('modelManager');
		// Инициализируем модели коллекции
		foreach ($data['items'] as $i => $item) {
			if (!is_array($item)) {
				$data['items'][$i] = $modelManager->get($modelName, $item);
			} else {
				if (isset($item[$keyField])) {
					$data['items'][$i] = $modelManager->get(
						$modelName, $item[$keyField], $item
					);
				} else {
					unset($data['items'][$i]);
					continue;
				}
			}
			if (!empty($addicts[$i])) {
				$data['items'][$i]->set($addicts[$i]);
			}
		}
		$collection->setItems($data['items']);
		// В менеджере ресурсов сохраняем клона коллеции
		$resourceManager->set('Model_Collection', $key, $collection);
	}
}