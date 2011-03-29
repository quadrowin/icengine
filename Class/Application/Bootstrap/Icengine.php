<?php
/**
 * 
 * @desc Начальный загрузчик тестов
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Application_Bootstrap_Icengine extends Application_Bootstrap_Abstract
{
	
	public function run ()
	{
		$this->initDds ();
		$this->initAttributeManager ();
		$this->initModelScheme ('icengine');
		
		DDS::getDataSource ()->getDataMapper ()->setModelScheme (
			IcEngine::$modelScheme
		);
		
		$this->initModelManager ();
		$this->initWidgetManager ();
		$this->initMessageQueue ();
//		$this->initView ();
//		$this->initUser ();
		$this->initAcl ();
	}
	
}