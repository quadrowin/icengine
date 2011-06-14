<?php
/**
 * @desc Класс для работы с Гео-айпи
 * @author Илья Колесников
 * @package IcEngine
 */

class Helper_GeoIP 
{
	/**
	 * @desc Получить город, которому соответствует
	 * IP адрес текущего пользователя
	 * @return City
	 */
	public function getCity ()
	{
		$ip = ip2long (Request::ip ());

		$query = Query::instance ()
			->select ('city_id')
			->from ('Net_City_Ip')
			->where ('begin_ip<=?', $ip)
			->where ('end_ip>=?', $ip);
		
		$city_id = DDS::execute ($query)
			->getResult ()
				->asValue ();
		
		return Model_Manager::byKey (
			'City',
			$city_id
		);
	}
}