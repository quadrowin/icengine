<?php
/**
 * 
 * @desc Опция для выбора только родительских разделов
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
class Content_Category_Option_Root extends Model_Option
{
	
	public function before ()
	{
		$this->query->where ('parentId', 0);
	}
	
}
