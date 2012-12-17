<?php
/**
 * Помощник для работы со связами многие-ко-многим моделей
 *
 * @author goorus, morph
 * @Service("helperLink")
 */
class Helper_Link extends Manager_Abstract
{
    /**
     * Связывает модели
	 *
     * @param string $table1
     * @param integer $key1
     * @param string $table2
     * @param integer $key2
     * @return Link|null
     */
	protected function simpleLink($table1, $key1, $table2, $key2)
	{
		$query =  $this->getService('query')
			->where ('fromTable', $table1)
			->where ('fromRowId', $key1)
			->where ('toTable', $table2)
			->where ('toRowId', $key2);
        $modelManager = $this->getService('modelManager');
		return $modelManager->byQuery('Link', $query);
	}

	/**
     * Связывает модели по схеме
	 *
     * @param string $table1
     * @param integer $key1
     * @param string $table2
     * @param integer $key2
     * @return Link|null
     */
	protected function schemeLink($scheme, $key1, $key2)
	{
		$linkClass = $scheme['link'];
		$query = $this->getService('query')
			->where ($scheme['fromKey'], $key1)
			->where ($scheme['toKey'], $key2);
		if (!empty ($scheme['addict'])) {
			foreach ($scheme['addict'] as $field => $value) {
				$query->where($field, $value);
			}
		}
        $modelManager = $this->getService('modelManager');
		$link = $modelManager->byQuery($linkClass, $query);
		return $link;
	}

	/**
	 * Связывает модели
	 *
	 * @param Model $model1
	 * @param Model $model2
	 * @return Link
	 */
	public function link(Model $model1, Model $model2)
	{
	    if (strcmp($model1->table(), $model2->table()) > 0) {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    $scheme = $this->linkScheme($model1->table(), $model2->table());
	    if (!$scheme) {
			$link = $this->simpleLink(
				$model1->table(), $model1->key(),
				$model2->table(), $model2->key()
			);
	    } else {
			$link = $this->schemeLink($scheme, $model1->key(), $model2->key());
	    }
	    if (!$link) {
			$className = 'Link';
			if (!$scheme) {
				$data = array(
					'fromTable'	=> $model1->table(),
					'fromRowId'	=> $model1->key(),
					'toTable'	=> $model2->table(),
					'toRowId'	=> $model2->key()
				);
			} else {
				$className = $scheme['link'];
				$data = array(
					$scheme['fromKey']	=> $model1->key(),
					$scheme['toKey']	=> $model2->key()
				);
				if (!empty($scheme['addict'])) {
					foreach ($scheme['addict'] as $key => $value) {
						$data[$key] = $value;
					}
				}
			}
			$link = $this->getService('modelManager')->create(
                $className, $data
            );
			$link->save();
	    }
	    return $link;
	}

	/**
	 * Возвращает коллекцию связанных с $model моделей типа $linked_model_name
	 *
	 * @param Model $model1
	 * @param string $model2
	 * @return Model_Collection
	 */
	public function linkedItems(Model $model1, $model2)
	{
		$table1 = $model1->table();
		$table2 = $model2;
		if (strcmp($table1, $table2) > 0)
		{
			$tmp = $table1;
			$table1 = $table2;
			$table2 = $tmp;
		}
		$scheme = $this->linkScheme($table1, $table2);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
		$ids = array();
		if (!$scheme) {
			$dir = strcmp($model1->modelName(), $model2) > 0
				? 'from' : 'to';
			$otherDir = $dir == 'to' ? 'from' : 'to';
			$query = $queryBuilder
				->select($dir . 'RowId')
				->from('Link')
				->where($dir . 'Table', $model2)
				->where($otherDir . 'RowId', $model1->key())
				->where($otherDir . 'Table', $model1->table());
			$ids = $dds->execute($query)->getResult()->asColumn();
		} else {
			$link_class = $scheme['link'];
			$query = $queryBuilder->factory('Select');
			$column = null;
			if (strcmp($model1->table(), $model2) > 0) {
				$column = $scheme['fromKey'];
				$query
					->from($link_class)
					->where($scheme['toKey'], $model1->key());
			} else {
				$column = $scheme['toKey'];
				$query
					->from($link_class)
					->where($scheme['fromKey'], $model1->key());
			}
			$query->select($column);
			if (!empty($scheme['addict'])) {
				foreach ($scheme['addict'] as $field => $value) {
					$query->where ($field, $value);
				}
			}
			$ids = $dds->execute($query)->getResult ()->asColumn($column);
		}
        $collectionManager = $this->getService('collectionManager');
		if (!$ids) {
			$result = $collectionManager->create($model2)->reset();
		} else {
			$result = $collectionManager->create($model2)
				->addOptions(array(
					'name'	=> '::Key',
					'key'	=> $ids
				));
		}
	    return $result;
	}

	/**
	 * Возвращает первичные ключи связанных моделей
	 *
	 * @param Model $model1
	 * 		Модель.
	 * @param string $linked_model_name
	 * 		Имя связанной модели.
	 * @return array
	 * 		Массив первичных ключей второй модели.
	 */
	public function linkedKeys(Model $model1, $linked_model_name)
	{
		$collection = $this->linkedItems($model1, $linked_model_name);
        $modelScheme = $this->getService('modelScheme');
		return $collection->column($modelScheme->keyField($linked_model_name));
	}

    /**
	 * Возвращает схему связи
     *
	 * @param string $model1
	 * @param string $model2
	 * @return array
	 */
    public function linkScheme($model1, $model2)
	{
        $modelScheme = $this->getService('modelScheme');
        $links = $modelScheme->links($model1);
        return isset($links[$model2]) ? $links[$model2] : array();
		$model1 = strtolower ($model1);
	}

	/**
	 * Удаляет связь моделей
	 *
	 * @param Model $model1
	 * @param Model $model2
	 */
	public function unlink(Model $model1, Model $model2)
	{
	    if (strcmp($model1->table(), $model2->table()) > 0) {
			$tmp = $model1;
			$model1 = $model2;
			$model2 = $tmp;
	    }
		$scheme = $this->linkScheme($model1->table(), $model2->table());
	    if (!$scheme) {
			$link = $this->simplelink(
				$model1->table(), $model1->key(),
				$model2->table(), $model2->key()
			);
		} else {
			$link = $this->schemeLink($scheme, $model1->key(), $model2->key());
		}
	    if ($link) {
	        return $link->delete();
	    }
	}

	/**
	 * Удаление всех связей модели с моделями указанного типа
	 *
	 * @param Model $model1
	 * @param string $model2
	 */
	public function unlinkWith(Model $model1, $model2)
	{
		$table1 = $model1->table();
		$table2 = $model2;
		if (strcmp($table1, $table2) > 0) {
			$tmp = $table1;
			$table1 = $table2;
			$table2 = $tmp;
		}
		$scheme = $this->linkScheme($table1, $table2);
        $queryBuilder = $this->getService('query');
        $dds = $this->getService('dds');
		if (!$scheme) {
			$query = $queryBuilder
				->delete()
				->from('Link');
			if (strcmp($model1->table(), $model2) > 0) {
				$query
					->where('fromTable', $model2)
					->where('toTable', $model1->table())
					->where('toRowId', $model1->key());
			} else {
				$query
					->where('fromTable', $model1->table())
					->where('fromRowId', $model1->key())
					->where('toTable', $model2);
			}
			$dds->execute($query);
		} else {
			$link_class = $scheme['link'];
			$query = $queryBuilder
				->delete()
				->from($link_class);
			if (strcmp($model1->table(), $model2) > 0) {
				$query->where($scheme['toKey'], $model1->key());
			} else {
				$query->where($scheme['fromKey'], $model1->key());
			}
			if (!empty($scheme['addict'])) {
				foreach ($scheme['addict'] as $field => $value) {
					$query->where($field, $value);
				}
			}
			$dds->execute($query);
		}
	}

    /**
	 * Проверяет, связаны ли модели
	 *
	 * @param Model $model1
	 * @param Model $model2
	 * @return boolean
	 */
	public function wereLinked(Model $model1, Model $model2)
	{
		if (strcmp($model1->table(), $model2->table()) > 0) {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
		$scheme = $this->linkScheme($model1->table(), $model2->table());
		if (!$scheme) {
			$link = $this->simpleLink(
				$model1->table(), $model1->key(),
				$model2->table(), $model2->key()
			);
		} else {
			$link = $this->schemeLink($scheme, $model1->key(), $model2->key());
		}
	    return (bool) $link;
	}
}