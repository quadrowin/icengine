<?php
/**
 * @Service("mailTemplate")
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
	public function byName ($name, $blank = true)
	{
		$model_manager = $this->getService('modelManager');
		$query = $this->getService('query');
		$template = $model_manager->byQuery (
		    'Mail_Template',
		    $query->instance ()
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
		$view_render_manager = $this->getService('viewRenderManager');
		$smarty = $view_render_manager->pushViewByName ('Smarty')->smarty ();
		$smarty->assign ($data);

		$tpl_name = 'Mail/Template/' . $this->name . '.tpl';
		if($smarty->templateExists($tpl_name))
		{
			$body = $smarty->fetch ($tpl_name);
		}
		else
		{
			$body = $smarty->fetch ('string:' . $this->body);
		}

		$view_render_manager->popView ();

		$parent = $this->getParent ();

		if ($parent)
		{
		    $data ['body'] = $body;
		    $body = $parent->body ($data);
		}

		return $body;
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
		
		$view_render_manager = $this->getService('viewRenderManager');
		$smarty = $view_render_manager->pushViewByName ('Smarty')->smarty ();

		$smarty->assign ($data);

		$result = $smarty->fetch ('string:' . $this->subject);
		$view_render_manager->popView ();
		return $result;
	}

}