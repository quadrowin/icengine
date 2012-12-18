<?php

/**
 * Абстрактный рендер схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * Получить имя рендера
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Model_Mapper_Scheme_Render_'));
	}

	/**
	 * Отрендерить элемент схемы
	 * 
     * @param Model_Mapper_Scheme_State $state
	 * @return string
	 */
	public function render($state)
	{

	}
}