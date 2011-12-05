<?php

namespace Ice;

Loader::load ('Component_Collection');

/**
 *
 * @desc Коллекция изображений к сущностям.
 * @author Юрий
 * @package Ice
 *
 */
class Component_Image_Collection extends Component_Collection
{
	/**
	 * @desc Загрузка списка комментариев для записи
	 * @param Model $model Модель, для которой подгружаются объекты
	 * @return Component_Collection Экземпляр коллекции
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;

		$this
		    ->where ('table', $this->_model->table ())
		    ->where ('rowId', $this->_model->key ());

		return $this;
	}

}