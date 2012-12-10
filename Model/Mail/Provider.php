<?php
/**
 * 
 * @desc Класс-фабрика провайдера сообщений
 * @author Юрий Шведов
 * @package IcEngine
 * @Service("mailProvider")
 */
class Mail_Provider extends Model_Factory
{
	public function byName ($name)
	{
		$model_manager = $this->getService('modelManager');
		$query = $this->getService('query');
		return $model_manager->byQuery (
			'Mail_Provider',
			$query->instance ()
				->where ('name', $name)
		);
	}
}