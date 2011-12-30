<?php

namespace Ice;

/**
 *
 * @desc Коллекция схем рейтинга
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Component_Rating_Scheme_Collection extends Component_Collection
{

	/**
	 * (non-PHPdoc)
	 * @see Component_Collection::getFor()
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;

		$this
		    ->where ('table', $this->_model->table ());

		return $this;
	}

}