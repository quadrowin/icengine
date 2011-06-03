<?php
/**
 * 
 * @desc Сериализация коллекции моделей.
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Model_Collection_Serialize
{
	/**
	 * @desc Сериализация коллекции моделей в строку 
	 * @param Model_Collection $data
	 * @return string
	 */
	public function filter ($data)
	{	
		$addicts = $data->data ('addicts');
		
		$data->data ('addicts', null);
		
		$pack = array (
			'class'		=> get_class ($data),
			'items'		=> $data->items (),
			'data'		=> $data->data ()
		);
		
		for ($i = 0, $icount = sizeof ($pack ['items']); $i < $icount; $i++)
		{
			if ($pack ['items'][$i] instanceof Model)
			{
				$pack ['items'][$i] = array (
					'id'		=> $pack ['items'][$i]->key (),
					'addicts'	=> $addicts [$i]
				);
			}
		}
		
		return json_encode ($pack);
	}
	
}