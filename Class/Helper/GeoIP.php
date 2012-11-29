<?php
/**
 * @desc Класс для работы с Гео-айпи
 * @author Илья Колесников
 * @package IcEngine
 */

class Helper_GeoIP
{

	public static function ip2int ($ip)
	{
		$ip = explode ('.', $ip);
		return $ip [3] + 256 * ($ip [2] + 256 * ($ip [1] + 256 * $ip [0]));
		//return $ip [3] + 256 * $ip [2] + 65536 * $ip [1] + 16777216 * $ip [0];
	}

	/**
	 * @desc Получить город, которому соответствует
	 * IP адрес текущего пользователя
	 * @return City
	 */
	public static function getCity ($ip = null)
	{
		$ip = self::ip2int ($ip !== null ? $ip : Request::ip ());
		$locator = IcEngine::serviceLocator();
		$dds = $locator->getService('dds');
		$query = $locator->getService('query');
		$city_name = $dds->execute(
			$query->select('net_city.name_ru AS name')
				->from('`net_ru`')
				->from('`net_city`')
				->where('net_ru.begin_ip<=?', $ip)
				->where('net_ru.end_ip>=?', $ip)
				->where('net_ru.city_id=net_city.id')
		)->getResult()->asValue();
		$modelManager = $locator->getService('modelManager');
		return $modelManager->byQuery(
			'City',
			$query->where('name', $city_name)
		);
		$city_id = $dds->execute(
			$query->select('city_id')
				->from('Net_City_Ip')
				->where('begin_ip<=?', $ip)
				->where('end_ip>=?', $ip)
		)->getResult()->asValue();
		return $modelManager->byKey(
			'City',
			$city_id
		);
	}
}