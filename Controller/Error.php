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
	
	/**
	 * @desc Доступ запрещен.
	 */
	public function accessDenied ()
	{
		return $this->replaceAction ('Authorization', 'accessDenied');
	}
	
	/**
	 * @desc Страница не найдена
	 */
	public function notFound ()
	{
		
	}
	
	/**
	 * @desc Страница устарела.
	 * В большинстве случаев означает неверный utcode или прикрепление
	 * компонента к несуществующей модели.
	 */
	public function obsolete ()
	{
		$this->_output->send ('error', 'Page obsolete.');
	}
	
}