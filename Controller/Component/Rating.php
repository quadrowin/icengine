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
			'input',
			'row_id',
			'value'
		);
		
		$scheme = IcEngine::$modelManager->modelBy (
			'Component_Rating_Scheme',
			Query::instance ()
			->where ('table', $table)
		);
		
		$rating = $scheme->vote ($table, $row_id, $value);
		
		$this->_output->send (array (
			'rating'	=> $rating
		));
	}
	
}