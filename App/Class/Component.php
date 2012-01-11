<?php

namespace Ice;

/**
 *
 * @desc
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Component
{

	/**
	 * @desc Получение коллекции компонент указанного типа для модели.
	 * @param Model $model Модель
	 * @param string $type Тип данных
	 * @return Component_Collection Связанные данные
	 */
	public static function getFor (Model $model, $type)
	{
		$collection_class = 'Component_' . $type;

		Loader::load ('Component_Collection');

		return Model_Collection_Manager::getInstance ()
			->create ($collection_class)
				->getFor ($model);
	}

}