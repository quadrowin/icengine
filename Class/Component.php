<?php

class Component
{
	
	/**
	 * Получение коллекции компонент указанного типа для модели.
	 * 
	 * @param Model $model
	 * 		Модель
	 * @param string $type
	 * 		Тип данных
	 * @return Component_Collection
	 * 		Связанные данные
	 */
	public static function getFor (Model $model, $type)
	{
		$collection_class = 'Component_' . $type . '_Collection';
		
		Loader::load ('Component_Collection');
		Loader::load ($collection_class);
		
		$collection = new $collection_class ();
		/**
		 * @var $collection Component_Collection
		 */
		return $collection->getFor ($model);
	}
	
}