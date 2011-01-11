<?php

class Helper_Date
{
    
    const UNIX_DATE_FORMAT = 'Y-m-d';
    
    const UNIX_FORMAT = 'Y-m-d H:i:s';
    
    const UNIX_TIME_FORMAT = 'H:i:s';
	
    public static $daysRu = array (
        1 => array (
            0 => 'воскресенье',
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среда',
            4 => 'четверг',
            5 => 'пятница',
            6 => 'суббота',
            7 => 'воскресенье'
        ),
        'Short' => array (
            0 => 'Вс',
            1 => 'Пн',
            2 => 'Вт',
            3 => 'Ср',
            4 => 'Чт',
            5 => 'Пт',
            6 => 'Сб',
            7 => 'Вс'
        )
    );
    
    public static $monthesRu = array (
		1 => array (
			1 => 'январь',
			2 => 'февраль',
			3 => 'март',
			4 => 'апрель',
			5 => 'май',
			6 => 'июнь',
			7 => 'июль',
			8 => 'август',
			9 => 'сентябрь',
			10 => 'октябрь',
			11 => 'ноябрь',
			12 => 'декабрь'
		),
		2 => array(
			1 => 'января',
			2 => 'февраля',
			3 => 'марта',
			4 => 'апреля',
			5 => 'мая',
			6 => 'июня',
			7 => 'июля',
			8 => 'августа',
			9 => 'сентября',
			10 => 'октября',
			11 => 'ноября',
			12 => 'декабря'
		)
	);
    
    /**
     * Получение даты по номеру недели в году
     * 
     * @param integer $week_number
     * 		Номер недели в году в формате ISO-8601.
     * @param integer $year
     * 		Четырехзначный номер года.
     * 		Если параметр не указан, будет взят номер текущего года.
     * @return integer|false
     * 		unix timestamp
     */
    public static function dateByWeek ($week, $year = null)
    {
        $year = $year ? $year : date ('Y');
        $week = sprintf ('%02d', $week);
        return strtotime ($year . 'W' . $week . '1 00:00:00');
    }
    
	/**
	 * Номер дня от начала эры
	 * @param integer $date
	 * @return integer
	 */
	public static function eraDayNum ($date = false)
	{
		if ($date === false)
		{
			$date = time ();
		}
		
		$d = date ('d', $date);
		$m = date ('m', $date);
		$y = date ('Y', $date);
		
		if ($m > 2)
		{
			$m++;
		}
		else
		{
		    $m += 13;
		    $y--;
		}
		return (int) (365.25 * $y + 30.6 * $m + $d);
	}
	
	/**
	 * Номер недели от начала эры
	 * @param integer $date
	 * @return intger
	 */
	public static function eraWeekNum ($date = false)
	{
		return (int) (self::eraDayNum ($date) / 7);
	}
	
	/**
	 *
	 * Получение времени с точностью до микросекунд
	 * @return float
	 */
	public static function getmicrotime ()
	{
		$usec = $sec = '';
		list ($usec, $sec) = explode (" ", microtime ());
		$usec = substr ($usec, 0, 6);
		return (float) ((float) $usec + $sec);
	}
	
	/**
	 * Сравнение месяцев двух дат
	 * 
	 * @param integer $date1
	 * 		Первая дата
	 * @param integer $date2
	 * 		Вторая дата
	 * @return boolean
	 * 		true, если даты относятся к одному месяцу, иначе false
	 */
	public static function monthEqual ($date1, $date2)
	{
		return (date('m', $date1) == date('m', $date2));
	}
	
	/**
	 * Возвращает название месяца
	 * 
	 * @param integer $month_num
	 * 		Номер месяца (от 1 до 12)
	 * @param integer $form
	 * 		Возвращаемая форма. 1 - именительный патеж, 2 - родительный.
	 * @return string
	 * 		Название месяца
	 */
	public static function monthName ($month_num, $form = 1)
	{	
		return self::$monthesRu [$form][(int) $month_num];
	}
	
	/**
	 * Возвращает следующее время согласно периоду
	 *
	 * @param integer $time
	 * 		Исходная метка времени (unix timestamp)
	 * @param string $period
	 * 		Период.
	 * 		Задается либо в секундах, либо строкой.
	 * @return integer
	 * 		Следующая метка времени (unix timestamp).
	 * 		Если период указан неверно, возвращается false
	 */
	public static function nextTime($time, $period)
	{
		if (is_numeric ($period))
		{
			return $time + $period;
		}
		else
		{
			switch ($period)
			{
				case 'second';
				case '1 second':
					return $time + 1;
				break;
				case 'minute';
				case '1 minute':
					return $time + 60;
				break;
				case 'hour';
				case '1 hour':
					return $time + 60 * 60;
				break;
				case '4 hour':
					return $time + 60 * 60 * 4;
				break;
				case 'day':
					return $time + 60 * 60 * 24;
				break;
				case 'week':
					return $time + 60 * 60 * 24 * 7;
				break;
				case 'month':
					return strtotime ('+1 month', $time);
				break;
			}
		}
		
		return false;
	}
	
	/**
	 * Получение даты и времени из строки.
	 * В качестве исходной строки может выступать запись
	 * даты и времени практически в любом формате, не зависимо от разделителя
	 * и порядка данных.
	 *
	 * @param string|integer $str
	 * 		Строка с датой или unix timestamp
	 * @param integer $default
	 * 		Возвращаемое значение по умолчанию
	 * @return integer
	 * 		Опередленная дата или $def, если дату определить не удалось.
	 */
	public static function strToTimestamp($str, $default = 0)
	{
		if (is_numeric ($str))
		{
			return (int) $str;
		}
		if (strlen ($str) < 8)
		{
			return $default;
		}
		$n = 0;
		
		$arr = array (
			'', '', '',
			'', '', ''
		);
		
		for ($i = 0; $i < strlen ($str); $i++)
		{
			if (strpos ('-0123456789', $str[$i]) == 0)
			{
				if (strlen ($arr[$n]) > 0)
				{
					$arr[$n] = (int) $arr[$n];
					$n++;
				}
			}
			else
			{
				$arr[$n] .= $str[$i];
			}
		}
	
		for ($i = $n; $i <= 5; $i++)
		{
			$arr[$i] = (int) $arr[$i];
		}
	
		if (strlen ($arr[0]) == 4)
		{
			// Y-m-d H:i:s
			return mktime ($arr[3], $arr[4], $arr[5], $arr[1], $arr[2], min(2040, $arr[0]));
		}
		elseif (strlen ($arr[2]) == 4)
		{
			// d.m.Y H:i:s
			return mktime ($arr[3], $arr[4], $arr[5], $arr[1], $arr[0], min(2040, $arr[2]));
		}
		elseif (strlen ($arr[3]) == 4)
		{
			// H:i:s Y-m-d
			return mktime ($arr[0], $arr[1], $arr[2], $arr[4], $arr[5], min(2040, $arr[3]));
		}
		elseif (strlen ($arr[5]) == 4)
		{
			// H:i:s d.m.Y
			return mktime ($arr[0], $arr[1], $arr[2], $arr[4], $arr[3], min(2040, $arr[5]));
		}
		
		return $default;
	} # function str_to_timestamp
	
	/**
	 * Перевод строки в unix timestamp
	 * 
	 * @param string $str
	 * 		Исходная строка
	 * @param integer $def
	 * 		Возвращаемое значение по умолчанию
	 * @return integer
	 * 		Время в unix timestamp.
	 * 		Если не удалось определить время, возвращается $def
	 */
	public static function strToTimeDef($str, $def = 0)
	{
		if (is_numeric ($str))
		{
			return $str;
		}
		if (strlen($str) < 3)
		{
			return $def;
		}
	
		$n = 0;
	
		$arr = array('', '', '');
		for ($i = 0; $i < strlen($str); $i++)
		{
			if (strpos('-0123456789', $str[$i]) == 0)
			{
				if (strlen($arr[$n]) > 0)
				{
					$n++;
				}
			}
			else 
			{
				$arr[$n] .= $str[$i];
			}
		}
	
		return mktime((int) $arr[0], (int) $arr[1], (int) $arr[2]);
	}
	
}