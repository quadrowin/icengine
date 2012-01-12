<?php

namespace Ice;

Loader::load ('Asset_Collection');

/**
 *
 * @desc Компонент рейтинг для сущностей
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Rating_Collection extends Asset_Collection
{

	/**
	 * @desc Возвращает рейтинг, связанный с сущностью.
	 * Если такого не сущетсвует, создает его.
	 * @return Rating
	 */
	public function single ()
	{
		$rating = $this->first ();
		if (!$rating)
		{
			$rating = Rating::create (array (
				'table'			=> $this->_model->table (),
				'rowId'			=> $this->_model->key (),
				'value'			=> 0,
				'votes'			=> 0,
				'changeTime'	=> Helper_Date::toUnix ()
			))->save ();
			$this->add ($rating);
		}
		return $rating;
	}

	/**
	 * @desc Изменить рейтинг
	 * @param integer $change Изменение рейтинга.
	 * @return Rating_Collection
	 */
	public function increment ($change)
	{
		$this->single ()->increment ($change);
		return $this;
	}

	/**
	 * @desc Возвращает значение рейтинга.
	 * @return integer
	 */
	public function value ()
	{
		$this->single ()->value;
	}

	/**
	 * @desc Возвращает количество голосов.
	 * @return integer
	 */
	public function votes ()
	{
		$this->single ()->votes;
	}

}