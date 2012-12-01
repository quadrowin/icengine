<?php

/**
 * Сериализация коллекции моделей.
 *
 * @author Юрий, neon
 * @package IcEngine
 */
class Filter_Model_Collection_Serialize
{
	/**
	 * Сериализация коллекции моделей в строку
	 *
	 * @param Model_Collection $data
	 * @return string
	 */
	public function filter($data)
	{
		$addicts = $data->data('addicts');
		$obj_data = $data->data();
		if (isset($obj_data['addicts'])) {
			unset($obj_data['addicts']);
		}
		$pack = array(
			'class'		=> get_class($data),
			'items'		=> $data->items(),
			'data'		=> $obj_data,
			't'			=> $data->data('t')
		);
		for ($i = 0, $icount = sizeof($pack['items']); $i < $icount; ++$i) {
			if ($pack['items'][$i] instanceof Model) {
				$pack['items'][$i] = array(
					'id'		=> is_object($pack['items'][$i])	?
						$pack['items'][$i]->key() :
						$pack['items'][$i],
					'addicts'	=> isset($addicts[$i]) ? $addicts[$i] : null
				);
			}
		}
		return json_encode($pack);
	}
}