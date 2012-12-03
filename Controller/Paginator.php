<?php

/**
 * Контроллер пагинатор.
 *
 * @author Юрий Шведов, neon
 * @package IcEngine
 */
class Controller_Paginator extends Controller_Abstract
{
	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index()
	{
		list(
			$paginator,
			$template,
			$tpl
		) = $this->input->receive(
			'data',
			'template',
			'tpl'
		);
	    /* @var $paginator Paginator */
		$paginator->buildPages();
		$this->output->send('paginator', $paginator);
		if ($template) {
			$this->task->setTemplate($template);
		}
		if ($tpl) {
			$this->task->setClassTpl(__METHOD__, $tpl);
		}
	}
}