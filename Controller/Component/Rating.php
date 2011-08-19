<?php
/**
 * 
 * @desc Контроллер компонента рейтинга
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Component_Rating extends Controller_Abstract
{
	
	/**
	 *
	 * @param type $rs		int R составляющая цвета начала диапозона
	 * @param type $gs		int G составляющая цвета начала диапозона
	 * @param type $bs		int B составляющая цвета начала диапозона
	 * @param type $rf		int R составляющая цвета конца диапозона
	 * @param type $gf		int G составляющая цвета конца диапозона
	 * @param type $bf		int B составляющая цвета конца диапозона
	 * @param type $rateMin минимальное значение рейтинга
	 * @param type $rateMax максисмальное значение рейтинга
	 * @param type $rateValue  значение текущее рейтинга
	 */
	public function colorForRate ($rs, $gs, $bs, $rf, $gf, $bf, $rateMin, $rateMax, $rateValue)
	{
		// преращение цветов
		$dr =$rf-$rs;
		$dg =$gf-$gs;
		$db =$bf-$bs;
		// диапозон изменения рейтинга
		$range = $rateMax - $rateMin;
		
		$outR = ($rateValue-$rateMin)/$range*$dr+$rs;
		$outG = ($rateValue-$rateMin)/$range*$dg+$gs;
		$outB = ($rateValue-$rateMin)/$range*$db+$bs;
		
		return 'rgb(' . round($outR) . ',' . round($outG) . ',' . round($outB) . ')';
		
	}
	/**
	 * @desc Голосование
	 * @param string $table 
	 * @param integer $row_id 
	 * @param integer $value Оценка
	 * @return Component_Rating $rating
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
		
		$scheme = Model_Manager::byQuery (
			'Component_Rating_Scheme',
			Query::instance ()
				->where ('table', $table)
		);
		
		$rating = $scheme->vote ($table, $row_id, $value);
		
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