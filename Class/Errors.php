<?php 

class Errors
{
	/**
	 * 
	 * @var View_Render_Abstract
	 */
	private static $_render;
	
	/**
	 * 
	 * @param string $code
	 * @return string
	 */
	private static function _getTemplate ($code)
	{
		Loader::load ('Query');
		$query = Query::instance()
			->select ('template')
			->from ('Errors')
			->where ('code=?', $code);
		$ds = DDS::execute ($query);
		return $ds->getResult ()->asRow ();
	}
	
	/*
	 * @return View_Render_Abstract
	 */
	public static function getRender ()
	{
		return self::$_render;
	}
	
	/**
	 * 
	 * @param Exception $e
	 */
	public static function render (Exception $e)
	{
		if (!is_null (self::$_render))
		{
			self::$_render->assign ('e', $e);
			self::$_render->display (self::_getTemplte ($e->getCode ()));
		}
	}
	
	/**
	 * 
	 * @param View_Render_Abstract $render
	 */
	public static function setRender (View_Render_Abstract $render)
	{
		if ($render instanceof View_Render_Abstract)
		{
			self::$_render = $render;
		}
	}
}