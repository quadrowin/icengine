<?php
/**
 * 
 * @desc Модель контента
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Content extends Model
{

	/**
	 * @desc Расширение модели
	 * @return Content_Extending
	 */
	public function extending ()
	{
		return 
			$this->extending ?
				IcEngine::$modelManager->get ($this->extending, $this->id) :
				null;
	}
	
}