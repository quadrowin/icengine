<?php
/**
 * 
 * @desc Абстрактный рендер сообщений
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Mail_Render_Abstract 
{
    
	/**
	 * @desc Рендер сообщения
	 * @param string $template Шаблон.
	 * @param array $data Данные для шаблона
	 * @return string Готовое сообщение.
	 */
	public function render ($template, array $data)
	{
		
	}
	
}