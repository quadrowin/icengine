<?php
/**
 * 
 * @desc Контроллер пагинатор.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Paginator extends Controller_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		list (
			$paginator,
			$template,
			$tpl
		) = $this->_input->receive (
			'data',
			'template',
			'tpl'
		);
		
	    /**
		 * @var Paginator $paginator
		 */
		
		$paginator->buildPages ();
		
		$this->_output->send ('paginator', $paginator);
		
		if ($template)
		{
			$this->_dispatcherIteration->setTemplate ($template);
		}
		
		if ($tpl)
		{
			$this->_dispatcherIteration->setClassTpl (__METHOD__, $tpl);
		}
	}
	
}