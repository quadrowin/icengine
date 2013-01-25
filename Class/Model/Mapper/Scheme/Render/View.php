<?php

/**
 * Фабрика представлений рендера схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 * @Service("modelMapperSchemeRenderView")
 */
class Model_Mapper_Scheme_Render_View
{
	/**
	 * Получить представление рендера схемы связей модели по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Render_View_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Render_View_' . $name;
		return new $className;
	}
}