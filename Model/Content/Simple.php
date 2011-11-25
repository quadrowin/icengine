<?php
/**
 * 
 * @desc Базовый класс контента
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */

Loader::load ('Content_Abstract');

class Content_Simple extends Content_Abstract
{
    	public function base ()
	{
		return Model_Manager::byKey (
			'Content',
			$this->key ()
		);
	}
	
    /**
     * @see Content_Extending::extending
     */
    public function extending ()
    {
        
    }

	public function modelName ()
	{
		return 'Content';
	}
}
