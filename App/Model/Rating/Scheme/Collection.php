<?php

namespace Ice;

/**
 *
 * @desc Коллекция схем рейтинга
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Rating_Scheme_Collection extends Asset_Collection
{

	/**
	 * (non-PHPdoc)
	 * @see Asset_Collection::getFor()
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;

		$this
		    ->where ('table', $this->_model->table ());

		return $this;
	}

}