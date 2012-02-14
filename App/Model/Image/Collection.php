<?php

namespace Ice;

Loader::load ('Asset_Collection');

/**
 *
 * @desc Коллекция изображений к сущностям.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Image_Collection extends Asset_Collection
{
	/**
	 * @desc Загрузка списка комментариев для записи
	 * @param Model $model Модель, для которой подгружаются объекты
	 * @return $this Эта коллекция
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