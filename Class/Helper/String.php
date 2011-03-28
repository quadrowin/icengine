<?php
/**
 * 
 * @desc Помощник для работы со строками
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_String
{
	
	/**
	 * @desc Возвращает символы после первого вхождения $substr до конца строки.
	 * Если $substr не найден, возвращается вся строка целиком.
	 * 
	 * @param string $str Исходная строка.
	 * @param string $substr Подстрока.
	 * @return string Подстрока, после вхождения $substr.
	 */
	public static function after ($str, $substr)
	{
		$p = strpos ($str, $substr);
		if ($p !== false)
		{
			return substr ($str, $p + strlen ($substr));
		}
		return $str;
	}
	
	/**
	 * @desc Переводи строку из неопределенной кодировки в заданную.
	 * @param string $str Исходная строка.
	 * @param string $output_charset Необходимая кодировка.
	 * @return string Строка в заданной кодировке.
	 */
	public static function autoIconv ($str, $output_charset = 'UTF-8')
	{
		// 'auto' расширяется в 'ASCII, JIS, UTF-8, EUC-JP, SJIS'
		$charset = mb_detect_encoding($str, 'auto');
		
		if ($charset == $output_charset)
		{
			return $str;
		}
	
		if (empty($charset))
		{
			// Неопределно - стопудово windows-1251
			return iconv ('windows-1251', $output_charset, $str);
		}
	
		return iconv ($charset, $output_charset, $str);
	}
	
	/**
	 * @desc Возвращает символы с начала строки до первого вхождения $substr.
	 * Если $substr не найден, возвращается вся строка целиком.
	 * 
	 * @param string $str Исходная строка.
	 * @param string $substr Подстрока.
	 * @return string Подстрока, до вхождения $substr.
	 */
	public static function before ($str, $substr)
	{
		$p = strpos ($str, $substr);
		if ($p !== false)
		{
			return substr ($str, 0, $p);
		}
		return $str;
	}
	
	/**
	 * @desc Возвращает true, если строка $str начинается с $subStr.
	 * @param string $str Строка.
	 * @param string $substr Подстрока.
	 * @return boolean true, если строка $str начинается с $substr.
	 */
	public static function begin ($str, $substr)
	{
		return (strncmp ($str, $substr, strlen ($substr)) == 0);
	}
	
	/**
	 * @desc Возврщает подстроку между $start и $end
	 * @param string $string
	 * @param string $start
	 * @param string $end
	 * @return string
	 */
	public static function between ($string, $start, $end = null)
	{
		$ini = strpos ($string, $start);
		if ($ini === false)
		{
			return '';
		}
		$ini += strlen ($start);
		if ($end === null)
		{
			return substr ($string, $ini);
		}
		$len = strpos ($string, $end, $ini) - $ini;
		return substr ($string, $ini, $len);
	}
	
	/**
	 * Приводит строку к кодировке utf-8.
	 * Для конвертирования используется внешний модуль a.charset.php
	 *
	 * @param string $str
	 * 		Строка в windows-1251, koi-8 or utf-8
	 * @return string
	 * 		Строка в кодировке utf-8
	 */
	public static function charset_x_utf8 ($str)
	{
		Loader::requireOnce ('a.charset.php', 'includes');
	
		return iconv ('windows-1251', 'utf-8', charset_x_win ($str));
	}
	
	/**
	 * Приводит строку к кодировке windows-1251.
	 * Для конвертирования используется внешний модуль a.charset.php
	 *
	 * @param string $str
	 * 		Строка в windows-1251, koi-8 or utf-8
	 * @return string
	 * 		Строка в кодировке windows-1251
	 */
	public static function charset_x_windows1251 ($str)
	{
		Loader::requireOnce ('a.charset.php', 'includes');
	
		return charset_x_win ($str);
	}
	
	/**
	 * @desc Проверяет, что строка заканчивается на $substr
	 * @param unknown_type $str
	 * @param unknown_type $substr
	 * @return boolean
	 */
	public static function end ($str, $substr)
	{
		$len = strlen ($substr);
		
		if (strlen ($str) < $len)
		{
			return false;
		}
		
		return substr ($str, -$len) == $substr;
	}
	
	/**
	 * @desc Возвращает только цифры из строки
	 * @param string $str Исходная строка.
	 * @return string Строка, содержащая только чифры из исходной.
	 */
	public static function extractNums ($str)
	{
		$res = '';
		
		for ($i = 0; $i < strlen ($str); $i++)
		{
			if (is_numeric ($str[$i]))
			{
				$res .= $str[$i];
			}
		}
		
		return $res;
	}
	
	/**
	 * Расшифровка строки "1,2-5" в "1,2,3,4,5"
	 * 
	 * @param string $str
	 * 
	 * @return string
	 */
	public static function idsExtract ($str)
	{
		$ids = array ();
		
		$str = explode (',', $str);
		
		foreach ($str as $s)
		{
			$p = strpos ($s, '-');
			if ($p > 0)
			{
				$x1 = substr ($s, 0, $p);
				$x2 = substr ($s, $p + 1);
				for ($x = $x1; $x <= $x2; $x++)
				{
					$ids[] = (int) $x;
				}
			}
			else
			{
				$ids[] = (int) $s;
			}
		}
		
		return array_unique ($ids);
	}
	
	/**
	 * Ищет числовые значения с прификсами
	 * 
	 * @param string $str
	 * 		Строка вида "a1b222ccc33"
	 * @return array
	 * 		Массив с найденными числами.
	 *      В качестве ключей используются префиксы, соответсвующие им.
	 * 		Если префикс встречается несколько раз, будет установлено последнее
	 * 		для него значение.
	 */
	public static function prefixedInts ($str)
	{
	    $last = strlen ($str) - 1;
	    
	    $pref = '';    // Текущий префикс
	    $int = '';     // Текущее число

	    $result = array ();
	    
	    for ($i = 0; $i <= $last; $i++)
	    {
	        if (ctype_digit ($str [$i]))
	        {
	            $int .= $str [$i];
	        }
	        elseif ($int !== '')
	        {
	            if ($pref !== '')
	            {
    	            $result [$pref] = $int;
	            }
	            $pref = $str [$i];
	            $int = '';
	        }
	        else
	        {
	            $pref .= $str [$i];
	        }
	    }
	    
	    if ($pref !== '' && $int !== '')
	    {
	        $result [$pref] = $int;
	    }
	    
	    return $result;
	}
	
	/**
	 * Получение превью для текста.
	 * @param string $text
	 * @param integer $length
	 * 		Ориентировочно ожидаемая длина превью.
	 * @return string
	 */
	public static function smartPreview ($text, $length = 100)
	{
		$text =  stripslashes ($text) . ' ';
		
		if (!isset ($text {$length}))
		{
			return $text;
		}
		
		$space_pos = strpos ($text, ' ', $length);
		$result = substr ($text, 0, $space_pos);
		return $result;
	}
	
	/**
	 * Перевод строки в число
	 *
	 * @param string $string
	 * 		Исходная строка
	 * @param boolean $concat
	 * 		Склеивать фрагменты числа, если их несколько в строке.
	 * @param integer $def
	 * 		Значение по умолчанию
	 * @return integer
	 * 		Полученное число
	 */
	public static function str2int ($str, $concat = true, $def = 0)
	{
		if (empty ($str))
		{
			return $def;
		}
		else
		{
			$str = (string) $str;
		}
		
		$int = '';
		$concat_flag = true;
		for ($i = 0, $length = strlen ($str); $i < $length; $i++)
		{
			if (is_numeric ($str[$i]) && $concat_flag) 
			{
				$int .= $str[$i];
			}
			elseif (!$concat && strlen ($int) > 0)
			{
				if ($concat_flag)
				{
					$concat_flag = false;
				}
				else
				{
					return (int) $int;
				}
			}
		}
	
		if (is_numeric ($int))
		{
			if ($str[0] == '-')
			{
				return -(int) $int;
			}
			else
			{
				return (int) $int;
			}
		}
		else
		{
			return $def;
		}
	}
	
	/**
	 * Возвращает строку, усеченную до заданной длины с учетом кодировки.
	 * Гарантируется, что в конце строки не останется части мультибайтового символа.
	 * 10x to Drupal
	 * 
	 * @param string $string
	 * 		Исходная строка
	 * @param integer $len
	 * 		Необходимая длина
	 * @param boolean $wordsafe
	 * 		Сохранение цельных слов. Если true, усечение произойдет по пробелу.
	 * @param boolean $dots
	 * 		Вставить многоточие в конец строки, если строка была усечена.
	 * @return string
	 * 		Усеченная строка.
	 */
	public static function truncateUtf8($string, $len, $wordsafe = false, $dots = false)
	{
		$slen = strlen ($string);
		
		if ($slen <= $len)
		{
			return $string;
		}
		
		if ($wordsafe)
		{
			$end = $len;
			while (($string[--$len] != ' ') && ($len > 0)) {};
			if ($len == 0)
			{
				$len = $end;
			}
		}
		//if ((ord($string[$len]) < 0x80) || (ord($string[$len]) >= 0xC0))
		//{
		//	return substr($string, 0, $len) . ($dots ? ' ...' : '');
		//}
		$p = 0;
		while ($len > 0 && $p < strlen ($string))
		{
			if (ord ($string[$p]) >= 0x80 && ord ($string[$p]) < 0xC0)
			{				
				$p++;
			}
			$len--;
			$p++;
		};
		if (
			$p < strlen ($string) && 
			ord ($string[$p]) >= 0x80 && ord ($string[$p]) < 0xC0
		)
		{
			$p++;
		}
		
		return substr ($string, 0, $p) . ($dots ? ' ...' : '');
	}
	
}

/**
 * Для PHP < 5.3
 */
if (!function_exists ('lcfirst'))
{
	function lcfirst ($str)
	{
		if (empty ($str))
		{
			return $str;
		}

		return strtolower (substr ($str, 0, 1)) . substr ($str, 1);
	}
}

/*
// * prefixedInts Test
 * 
$tests = array ('a1bb22c333', '444a4b4c4d4', '1234', 'abcd', '1a1', 'y2010m12d2', 'sgn+2ch');

echo '<pre>';
foreach ($tests as $test)
{
    echo $test . ' =&gt; ';
    $result = Common_String::prefixedInts ($test);
    print_r ($result);
    echo "\r\n\r\n";
}
echo '<pre>';
*/