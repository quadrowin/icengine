<?php

/** 
 * Абстрактный компонент
 * 
 * @author goorus
 */
class Component extends Model
{
	/**
	 * Получение коллекции компонент указанного типа для модели
	 * 
     * @param Model $model Модель
	 * @param string $type Тип данных
	 * @return Component_Collection Связанные данные
	 */
	public function getFor(Model $model, $type)
	{
		$collectionClass = 'Component_' . $type;
        $collectionManager = $this->getService('collectionManager');
		return $collectionManager->create($collectionClass)->getFor($model);
	}
}