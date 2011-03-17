<?php
/**
 * 
 * @desc Помощник для перевода текста в транслит
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Translit
{
	
	/**
	 * @desc Заменяет символы в строке согласно переданным наборам.
	 * @param string $value Исходая строка.
	 * @param string|array $to Символы, которые будут вставлены на места 
	 * заменяемых.
	 * @param string $from [optional] Символы, которые будут заменены.
	 * Если этот аргумент не передан, в $to ожидается ассоциативный
	 * массив вида "заменяемый символ" => "символ для замены".
	 * @return string Результат замены
	 */
	protected function u_strtr ($value, $to, $from = null)
	{
		if (is_null ($from))
		{
			arsort ($to, SORT_LOCALE_STRING);
			foreach ($to as $c => $r)
			{
				$value = str_replace ($c, $r, $value);
			}
		}
		else
		{
			$len = min (strlen ($to), strlen ($from));
			for ($i = 0; $i < $len; ++$i)
			{
				$value = str_replace (
					mb_substr ($to, $i, 1, 'UTF-8'),
					mb_substr ($from, $i, 1, 'UTF-8'), 
					$value
				);
			}
		}
		return $value;
	}
	
	/**
	 * @desc Перевод строки в транслит
	 * @param string $value Исходна стока
	 * @param string $lang [optional] Направление перевода
	 * 		Если "en" - из русского на транслит,
	 * 		если "ru" - из транслита на русский
	 * @return Результат транслитации.
	 */
	public static function translit ($value, $lang = null)
	{
		$value = trim ($value);
		
		$value = str_replace (
			array ("\r", "\n", "\t", ',', '.', '(', ')', '[', ']', '{', '}'),
			'',
			$value
		
		);
		
		if (!isset ($lang))
		{
			$regexpRus = '/^[а-яА-Я]+/';
			$lang = preg_match ($regexpRus, $value) ? 'ru' : 'en';
		}
		

		if ($lang == 'en')
		{
			// Сначала заменяем "односимвольные" фонемы.
			$value = self::u_strtr ($value, "абвгдеёзийклмнопрстуфхыэ ", "abvgdeeziyklmnoprstufhie_");
			$value = self::u_strtr ($value, "АБВГДЕЁЗИЙКЛМНОПРСТУФХЫЭ ", "ABVGDEEZIYKLMNOPRSTUFHIE_");

			// Затем - "многосимвольные".
			$value = self::u_strtr (
				$value,
				array (
					"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh",
					"щ"=>"shch","ь"=>"", "ъ"=>"", "ю"=>"yu", "я"=>"ya",
					"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH",
					"Щ"=>"SHCH","Ь"=>"", "Ъ"=>"", "Ю"=>"YU", "Я"=>"YA",
					"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye",
					"&nbsp;"=>"_"
				)
			);
		}
		elseif ($lang == 'ru')
		{
			// Сначала заменяем"многосимвольные".
			$value = self::u_strtr (
				$value,
				array (
					"zh"=>"ж", "ts"=>"ц", "ch"=>"ч", "sh"=>"ш",
					"shch"=>"щ", "yu"=>"ю", "ya"=>"я",
					"ZH"=>"Ж", "TS"=>"Ц", "CH"=>"Ч", "SH"=>"Ш",
					"SHCH"=>"Щ", "YU"=>"Ю", "YA"=>"Я",
					"&nbsp;"=>"_"
				)
			);


			//  Затем - "односимвольные" фонемы.
			$value = self::u_strtr ($value, "abvgdeziyklmnoprstufh_", "абвгдезийклмнопрстуфх ");
			$value = self::u_strtr ($value, "ABVGDEZIYKLMNOPRSTUFH_", "АБВГДЕЗИЙКЛМНОПРСТУФХ ");
		}
			
		return $value;
	}
	
	/**
	 * @desc Формирует из названия статьи ссылку.
	 * @param string $value Исходное название
	 * @return string Ссылка
	 */
	public static function makeUrlLink ($value)
	{
		$link = self::translit ($value, 'en');
		$link = preg_replace ('/([^0-9a-zA-Z_])+/', '', $link);
		$link = preg_replace ('/[_]{2,}/', '_', $link);
		$link = preg_replace ('/^_/', '', $link);
		$link = preg_replace ('/_$/', '', $link);

		return $link;
	}
}