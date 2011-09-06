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
		if (!$this->extending)
		{
			return null;
		}
		
		$extending = Model_Manager::byKey ($this->extending, $this->id);
				
		if (!$extending && $this->extending && $this->id)
		{
			// Расширение не создано
			$extending = Model_Manager::get (
				$this->extending,
				$this->id
			)->firstSave ();
		}
		
		return $extending;
	}
	
	public function title ()
	{
		return $this->title . ' ' . $this->url;
	}
	
}