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
     * 
     * @var Smarty
     */
    protected $_smarty;
	
	public static $blankTemplate = array (
	    'id'		=> 0,
		'name'	    => 'empty',
		'parentId'	=> 0,
		'subject'	=> 'subj',
		'body'	    => 'body'
	);
	
	/**
	 * @return Smarty
	 */
	protected function _initSmarty ()
	{
		if (!class_exists ('Smarty'))
		{
			Loader::requireOnce ('smarty/Smarty.class.php', 'includes');
		}
		$smarty = new Smarty ();
		$smarty->template_dir = 'Ice/View/';
		$smarty->compile_dir = 'cache/templates/';
		$smarty->force_compile = true;
		
		$smarty->assign ('siteaddress', $_SERVER ['SERVER_NAME']);
		
		return $smarty;
	}
	
	/**
	 * Возращается шаблон по имени, либо шаблон по умолчанию
	 * @param string $name
	 * @param boolean $blank Вернуть базовый, если шаблон не найден
	 * @return Mail_Template
	 */
	public static function byName ($name, $blank = true)
	{
		$template = IcEngine::$modelManager->modelBy (
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
	    $smarty = $this->smarty ();
	    
	    $smarty->assign ($data);
		
	    $smarty->register_resource (
			$this->resourceKey (),
			array (
				$this,
				"smarty_get_body",
				"smarty_get_body_timestamp",
				"smarty_get_secure",
				"smarty_get_trusted"
			)
		);
		
		$body = $smarty->fetch ($this->resourceKey () . ':body');
		
		$parent = $this->getParent ();
		
		if ($parent)
		{
		    $data ['body'] = $body;
		    $body = $parent->body ($data);
		}
		
		return $body;
	}
	
	/**
	 * @return Smarty
	 */
	public function smarty ()
	{
	    if (!$this->_smarty)
	    {
	        $this->_smarty = $this->_initSmarty ();
	    }
	    return $this->_smarty;
	}
	
	public function smarty_get_body ($tpl_name, &$tpl_source)
	{
		$tpl_source = $this->body;
		return true;
	}
	
	public function smarty_get_body_timestamp ()
	{
		return crc32 ($this->body);
	}
	
	public function smarty_get_subject ($tpl_name, &$tpl_source)
	{
		$tpl_source = $this->subject;
		return true;
	}
	
	public function smarty_get_subject_timestamp ()
	{
		return crc32 ($this->subject);
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
	    $smarty = $this->smarty ();
		$smarty->assign ($data);
		
		$smarty->register_resource (
			$this->resourceKey (),
			array (
				$this,
				"smarty_get_subject",
				"smarty_get_subject_timestamp",
				"smarty_get_secure",
				"smarty_get_trusted"
			)
		);
		
		return $smarty->fetch ($this->resourceKey () . ':subject');
	}
	
}