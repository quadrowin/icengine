<?php
/**
 *
 * @desc Базовый класс контента
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Content_Simple extends Content_Abstract
{
	/**
	 * @see Content_Abstract::modelName
	 */
	public function modelName ()
	{
		return 'Content';
	}
}
