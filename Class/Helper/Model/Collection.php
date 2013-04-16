<?php

/**
 * Помощник коллекции моделей
 * 
 * @author morph
 * @Service("helperModelCollection")
 */
class Helper_Model_Collection
{
    /**
	 * Переназначение коллекции на модель
	 *
	 * @param Model $model
	 * @param Model_Collection $collection
	 * @return void
	 */
	public function rejoin($model, $collection)
	{
		$collection->update(array(
			'table'	=> $model->modelName(),
			'rowId'	=> $model->key()
		));
	}
    
    /**
     * Восстановить данные коллекции
     *
     * @param Model_Collection $collection
     */
    public function restoreData($collection)
    {
        $keyField = $collection->keyField();
        if (!$collection->data('collectionData')) {
            return;
        }
        $collectionData = $collection->data('collectionData');
        $fieldsFromData = array();
        if ($collection->data('fieldsFromData')) {
            $fieldsFromData = $collection->data('fieldsFromData');
        }
        foreach ($collection as $item) {
            if (!isset($collectionData[$item[$keyField]])) {
                continue;
            }
            $currentData = $collectionData[$item[$keyField]];
            foreach ($currentData as $fieldName => $fieldValue) {
                if (in_array($fieldName, $fieldsFromData)) {
                    $item[$fieldName] = $fieldValue;
                    if (!in_array($fieldName, $collection->rawFields())) {
                        $collection->rawFields()[] = $fieldName;
                    }
                } else {
                    $item['data'] = array_merge((array) $item['data'], array(
                        $fieldName  => $fieldValue
                    ));
                }
            }
        }
    }

	/**
	 * Упорядочивание списка для вывода дерева по полю parentId
	 *
	 * @param Model_Collection $collection
	 * @param boolean $include_unparented Оставить элементы без предка.
	 * Если false, элементы будут исключены из списка.
	 *
	 * @return Model_Collection
	 */
	public function sortByParent($collection, $includeUnparented = false)
	{
		$list = $collection->items();
		if (empty($list)) {
			return $collection;
		}
		$firstIds = $collection->column('id');
		$parents = array();
		$childOf = 0;
		$result = array();
		$i = 0;
        $keyField = $collection->keyField();
		$index = array(0 => 0);
		$fullIndex = array(-1 => '');
		do {
			$finish = true;
			for ($i = 0; $i < count($list); ++$i) {
				if ($list[$i]['parentId'] != $childOf) {
                    continue;
                }
                if (!isset($index[count($parents)])) {
                    $index[count($parents)] = 1;
                } else {
                    $index[count($parents)]++;
                }
                $n = count($result);
                $result[$n] = $list[$i];
                $result[$n]['data']['level'] = count($parents);
                $result[$n]['data']['index'] = $index[count($parents)];
                $parentsCount = count($parents);
                if ($parentsCount > 0) {
                    $fullIndex = $fullIndex[$parentsCount - 1] .
                        $index[count($parents)];
                } else {
                    $fullIndex = (string) $index[count($parents)];
                }
                $result[$n]['data']['fullIndex'] = $fullIndex;
                $result[$n]['data']['brokenParent'] = false;
                $fullIndex[$parentsCount] = $fullIndex . '.';
                array_push($parents, $childOf);
                $childOf = $list[$i][$keyField];
                for ($j = $i; $j < count($list) - 1; $j++) {
                    $list[$j] = $list[$j + 1];
                }
                array_pop($list);
                $finish = false;
                break;
			}
			// Элементы с неверно указанным предком
			if ($finish && count($parents) > 0) {
				$index[count($parents)] = 0;
				$childOf = array_pop($parents);
				$finish = false;
			}
		} while (!$finish);
		/**
		 * чтобы не портить сортировку, если таковая есть у
		 * коллекции, с использованием элементов без родителей
		 *
		 * сортируем по level 0, докидываем дочерних
		 */
		if ($includeUnparented) {
			//out досортированный
			$newResult = array();
			//без родителей, неотсортированные
			$listIds = array();
			//отсортированные родители: level = 0
			$resultIds = array();
			//отсортированные дочерние: level > 0
			$resultSubIds = array();
			for ($i = 0; $i < count($list); $i++) {
				$listIds[$list[$i][$keyField]] = $i;
			}
			for ($i = 0; $i < count($result); $i++) {
				if (!$result[$i]['parentId']) {
					$parentId = $result[$i][$keyField];
					$resultIds[$result[$i][$keyField]] = $i;
				} else {
					$resultSubIds[$parentId][$result[$i][$keyField]] = $i;
				}
			}
			for ($i = 0; $i < count($firstIds); $i++) {
				if (isset($resultIds[$firstIds[$i]])) {
					$newResult[] = $result[$resultIds[$firstIds[$i]]];
					if (isset($resultSubIds[$firstIds[$i]])) {
						foreach ($resultSubIds[$firstIds[$i]] as $index) {
							$newResult[] = $result[$index];
						}
					}
				} elseif (isset($listIds[$firstIds[$i]])) {
					$newResult[] = $list[$listIds[$firstIds[$i]]];
				}
			}
			$result = $newResult;
		}
		$collection->setItems($result);
		return $collection;
	}
}