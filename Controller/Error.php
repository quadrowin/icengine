<?php

class Controller_Error extends Controller_Abstract
{

    public function e403 ()
    {
        Loader::load ('Header');
        Header::setStatus (Header::E403);
    }

	public function e404 ()
	{
		Loader::load ('Header');
		Header::setStatus(Header::E404);
	}
	
	public function notFound ()
	{
		
	}
	
	public function obsolete ()
	{
		$this->_output->send ('error', 'Page obsolete.');
	}
	
}