<?php

/**
 * Генератор кода
 *
 * @author ..., neon
 */
class Helper_Code_Generator
{
	/**
	 * Генерирует код из шаблона
	 *
	 * @param string $tpl Название шаблона без .tpl
	 * @param array $data Данные для замены
	 * @return string
	 */
	public static function fromTemplate($tpl, $data = array())
	{
		$locator = IcEngine::serviceLocator();
		$viewRenderManager = $locator->getService('viewRenderManager');
		$render = $viewRenderManager->byName('Smarty');
		if ($data) {
			$render->assign($data);
		}
		$content = $render->fetch('Helper/Code/Generator/' . $tpl);
		unset($render);
		return $content;
	}
}