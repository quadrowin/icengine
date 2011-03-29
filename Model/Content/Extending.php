<?php
/**
 * 
 * @desc Базовая модель для расширений контента.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Content_Extending extends Model
{
	
	/**
	 * @desc Возвращает контент, к которому привязано.
	 * @return Content
	 */
	public function content ()
	{
		return Model_Manager::modelByKey ('Content', $this->id);
	}
	
}