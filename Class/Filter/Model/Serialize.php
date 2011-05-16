<?php
/**
 * 
 * @desc Фильтр для сериализации модели
 * @author Юрий
 * @package IcEngine
 *
 */
class Filter_Model_Serialize
{
	
	/**
	 * @desc Десириализация модели в строку
	 * @param Model $data
	 * @return string
	 */
	public function filter (Model $data)
	{
		return
			get_class ($data) . ':' . json_encode ($data->asRow ());
	}
	
}