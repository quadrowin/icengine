<?php
/**
 * 
 * @desc Хелпер для работы с яндекс картами.
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
class Helper_YMaps
{
	
	/**
	 * @desc Находит положение по названию (адресу)
	 * @param string $key
	 * @param string $address
	 * @param integer $limit 
	 * @return mixed|null
	 */
	public static function geocodePoint ($key, $address, $limit = 1)
	{
		$params = array (
			'geocode'	=> $address,		// адрес
			'format'	=> 'json',			// формат ответа
			'results'	=> $limit,			// количество выводимых результатов
			'key'		=> $key,			// ваш api key
		);
		$response = json_decode (file_get_contents (
			'http://geocode-maps.yandex.ru/1.x/?' .
			http_build_query ($params)
		));
		
		var_dump ($response);
		
		if (isset ($response->error) && $response->error)
		{
			return null;
		}
		
		if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
		{
			return $response->response->GeoObjectCollection->featureMember[0];
			////->GeoObject->Point->pos;
		}
		
		return null;
	}
	
	/**
	 * @desc Фукнция возвращает расстояние между двумя точками.
	 * Функция взята из YMaps.GeoCoordSystem
	 * @param array $A Координаты первой точки в градусах 
	 * $A [0] широта (longitude), $a долгота (latitude)
	 * @param array $z
	 * $z [0] широта (longitude), $z долгота (latitude)
	 * @return float 
	 */
	public static function distance ($A, $z)
	{
		$AgetX = $A [0];
		$AgetY = $A [1];
		
		$zgetX = $z [0];
		$zgetY = $z [1];
		
		$B = pi () / 180;
		$x = $AgetX * $B;
		$v = $AgetY * $B;
		$w = $zgetX * $B;
		$u = $zgetY * $B;
		$y = 0;
		
		$this_epsilon = 1e-10;
		$this_radius = 6378137;
		$this_e2 = 0.00669437999014;
		
		if (
			!(
				Abs ($u - $v) < $this_epsilon && 
				Abs ($x - $w) < $this_epsilon
			)
		)
		{
			$C = cos (($v + $u) / 2);
			$t = $this_radius * sqrt ((1 - $this_e2) / (1 - $this_e2 * $C * $C));
			$y = 
				$t * acos (
					sin ($v) * sin ($u) + 
					cos ($v) * cos ($u) * cos ($w - $x)
				);
		}
		
		return $y;
	}

	/**
	 * @desc Альтернативный метод расчета расстояния.
	 * Функция найдена на просторах инета, в целом результаты не сильно
	 * отличаются от функции яндекса.
	 * @param float $long1 
	 * @param float $lat1
	 * @param float $long2
	 * @param float $lat2
	 * @return float
	 */
	public static function distanceAlt ($long1, $lat1, $long2, $lat2)
	{
		static $D2R = 0.017453;
		static $R2D = 57.295781;
		static $a  = 6378137.0;
		static $b  = 6356752.314245;
		static $e2 = 0.006739496742337;
		static $f  = 0.003352810664747;
		
		$fdLambda = ($long1 - $long2) * $D2R;
		$fdPhi  = ($lat1 - $lat2) * $D2R;

		$fPhimean = (($lat1 + $lat2) / 2.0) * $D2R;

		$fTemp  = 1 - $e2 * (pow (sin ($fPhimean), 2));
		$fRho  = ($a * (1 - $e2)) / pow ($fTemp, 1.5);
		$fNu  = $a / (
			sqrt (1 - $e2 * 
			pow (sin ($fPhimean), 2))
		);

		$fz   = sqrt (
			pow (sin ($fdPhi / 2.0), 2) + cos ($lat2 * $D2R) * 
			cos ($lat1 * $D2R) * pow (sin ($fdLambda / 2.0), 2)
		);
		$fz   = 2 * asin ($fz);

		$fAlpha  = cos ($lat2 * $D2R) * sin ($fdLambda) * 1 / sin ($fz);
		$fAlpha  = asin ($fAlpha);

		$fR   = 
			($fRho * $fNu) / (
				($fRho * pow (sin ($fAlpha), 2)) +
				($fNu * pow (cos ($fAlpha), 2))
			);

		return $fz * $fR;
	}
	
}
