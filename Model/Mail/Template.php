<?php
/**
 * 
 * @desc Шаблоны сообщений.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Template extends Model_Child
{
	
	/**
	 * @desc Данные для пустого шаблона
	 * @var array
	 */
	public static $blankTemplate = array (
		'id'		=> 0,
		'name'	    => 'empty',
		'parentId'	=> 0,
		'subject'	=> 'subj',
		'body'	    => 'body'
	);
	
	/**
	 * Возращается шаблон по имени, либо шаблон по умолчанию
	 * @param string $name
	 * @param boolean $blank Вернуть базовый, если шаблон не найден
	 * @return Mail_Template
	 */
	public static function byName ($name, $blank = true)
	{
		$template = Model_Manager::byQuery (
		    'Mail_Template',
		    Query::instance ()
		   		->where ('name', $name)
		);
		
		if (!$template && $blank)
		{
			$template = new Mail_Template (self::$blankTemplate);
		}
		
		return $template;
	}
	
	/**
	 * @desc Получение тела по шаблону.
	 * @param array $data Переменные шаблона.
	 * @return string
	 */
	public function body (array $data = array ())
	{
		if (!$this->body)
		{
			// пустое тело
			return '';
		}
		
//	    $smarty = $this->smarty ();
//		View_Render_Broker::render (array (
//			array (
//				'template'	=> 'fuck.tpl',
//				'data'		=> $data,
//				'assign'	=> 'content'
//			),
//			array (
//				'template'	=> 'you.tpl',
//				'data'		=> $data
//			)
//		));
		$smarty = View_Render_Broker::pushViewByName ('Smarty')->smarty ();
	    
	    $smarty->assign ($data);
		
	    $smarty->register_resource (
			$this->resourceKey () . 'b',
			array (
				$this,
				"smarty_get_body",
				"smarty_get_body_timestamp",
				"smarty_get_secure",
				"smarty_get_trusted"
			)
		);
		
		$body = $smarty->fetch ($this->resourceKey () . 'b:body');
		
		View_Render_Broker::popView ();
		
		$parent = $this->getParent ();
		
		if ($parent)
		{
		    $data ['body'] = $body;
		    $body = $parent->body ($data);
		}
		
		return $body;
	}
	
	public function smarty_get_body ($tpl_name, &$tpl_source)
	{
		$tpl_source = $this->body;
		return true;
	}
	
	public function smarty_get_body_timestamp ()
	{
		return time ();
	}
	
	public function smarty_get_subject ($tpl_name, &$tpl_source)
	{
		$tpl_source = $this->subject;
		return true;
	}
	
	public function smarty_get_subject_timestamp ()
	{
		return time ();
	}
	
	public function smarty_get_secure ()
	{
		return true;
	}
	
	public function smarty_get_trusted ()
	{
		
	}
	
	/**
	 * @desc Получение заголовка по шаблону.
	 * @param array $data
	 * @return string
	 */
	public function subject (array $data = array ())
	{
		if (!$this->subject)
		{
			// пустая тема
			return '';
		}
		
		$smarty = View_Render_Broker::pushViewByName ('Smarty')->smarty ();
		
		$smarty->assign ($data);
		
		$smarty->register_resource (
			$this->resourceKey () . 's',
			array (
				$this,
				"smarty_get_subject",
				"smarty_get_subject_timestamp",
				"smarty_get_secure",
				"smarty_get_trusted"
			)
		);
		
		$result = $smarty->fetch ($this->resourceKey () . 's:subject');
		View_Render_Broker::popView ();
		return $result;
	}
	
}