<?php

/**
 * Рендер ошибок
 * 
 * @author morph
 * @Service("errorRender")
 */
class Error_Render extends Manager_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'path'      => 'Error/',
        'layout'    => 'index.tpl'
    );
    
	/**
	 * Рендер
     * 
	 * @var View_Render_Abstract
	 */
	private $render;

	/*
     * Получить текущий рендер
     * 
	 * @return View_Render_Abstract
	 */
	public function getRender()
	{
		return $this->render;
	}
    
    /**
	 * Получить шаблон ошибок
     * 
	 * @param string $code
	 * @return string
	 */
	public function getTemplate($code)
	{
		return $this->config()->path . $code;
	}

	/**
	 * Рендеринг ошибки
     * 
	 * @param Exception $e
	 */
	public function render (Exception $e)
	{
		if ($this->render)
		{
			$msg = '[' . $e->getFile() . '@' .
				$e->getLine() . ':' .
				$e->getCode() . '] ' .
				$e->getMessage () . PHP_EOL;
			error_log($msg . PHP_EOL, E_USER_ERROR, 3);
			echo '<pre>' . $msg . $e->getTraceAsString() . '</pre>';
			return;
		}
		$this->render->assign('e', $e);
        $template = $this->getTemplate($e->getCode());
        $content = $this->render->fetch($template);
        $this->render->assign('content', $content);
        $layout = $this->config()->path . $this->config->layout;
		$this->render->display($layout);
	}

	/**
	 * Изменить текущий рендер
     * 
	 * @param View_Render_Abstract $render
	 */
	public function setRender(View_Render_Abstract $render)
	{
		if ($render instanceof View_Render_Abstract) {
			$this->render = $render;
		}
	}
}