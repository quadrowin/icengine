<?php

namespace Ice;

Loader::load ('Assert_Collection');

/**
 *
 * @desc Коллекция видео
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Video_Collection extends Asset_Collection
{

	/**
	 * (non-PHPdoc)
	 * @see Asset_Collection::getFor()
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;

		$this
		    ->where ('table', $this->_model->table ())
		    ->where ('rowId', $this->_model->key ())
		    ->query ()
		    	->order ('sort');


		return $this;
	}

}