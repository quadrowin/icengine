<?php

class Component_Image_Option_User extends Model_Option
{

    /**
     * (non-PHPDoc)
     * @see Model_Option
     */
    public function before ()
    {
		$this->query
			->where ('Component_Image.show>0')
			->order('sort DESC');
    }

}