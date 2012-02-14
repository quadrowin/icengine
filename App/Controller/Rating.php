<?php

namespace Ice;

/**
 *
 * @desc Контроллер компонента рейтинга
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Controller_Rating extends Controller_Abstract
{
	/**
	 * @desc Голосование
	 * @param string $table
	 * @param integer $row_id
	 * @param integer $value Оценка
	 * @return Rating $rating
	 */
	public function vote ()
	{
		list (
			$table,
			$row_id,
			$value
		) = $this->_input->receive (
			'table',
			'row_id',
			'value'
		);

		Loader::load ('Rating');
		$rating = Rating::voteFor ($table, $row_id, $value);

		$this->_output->send (array (
			'rating'	=> $rating
		));
	}

	/**
	 * @desc Кнопки голосования.
	 * @param Model $model Оцениваемая модель.
	 */
	public function pollBar ()
	{
		$model = $this->_input->receive ('model');
		$rating = $model->component ('Rating');
		$this->_output->send (array (
			'model'		=> $model,
			'rating'	=> $rating
		));
	}

}