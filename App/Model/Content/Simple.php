<?php

namespace Ice;

Loader::load ('Content_Abstract');

/**
 *
 * @desc Базовый класс контента
 * @author Юрий Шведов
 * @package Ice
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
