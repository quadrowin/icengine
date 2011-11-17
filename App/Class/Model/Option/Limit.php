<?php
/**
 * 
 * @desc Задание ограничений на выбор.
 * @param integer $count 
 * @param integer $offset
 * @author Goorus
 * @package Ice_Vipgeo
 * 
 */
class Model_Option_Limit extends Model_Option
{

	public function before ()
	{
		$this->query->limit (
			isset ($this->params ['count']) ? $this->params ['count'] : null,
			isset ($this->params ['offset']) ? $this->params ['offset'] : null
		);
	}
	
}
