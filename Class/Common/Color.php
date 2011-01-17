<?php

class Common_Color
{
	
	/**
	 * Возвращает массив значений R, G, B для цвета
	 * @param string $css
	 * 		Цвет CSS
	 * @return array
	 * 		Массив [0..2] в порядке R, G, B
	 */
	public static function cssToRgb($css)
	{
		$hexcolor = '000000';
		$p = 0;
		
		for ($i = 0; $i < strlen($css) && $p < strlen ($hexcolor); $i++)
		{
			if (strpos('-01234567890abcdefABCDEF', $css[$i]) > 0)
			{
				$hexcolor[$p] = $css[$i];
				$p++;
			}
		}
		
		return array(
			0 => hexdec (substr ($hexcolor, 0, 2)),
			1 => hexdec (substr ($hexcolor, 2, 2)),
			2 => hexdec (substr ($hexcolor, 4, 2))
		);
	}
	
}