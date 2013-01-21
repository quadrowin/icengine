<?php

/**
 * Description of Converter
 *
 * @author markov, neon
 * @Service("helperConverter")
 */
class Helper_Converter
{
	/**
	 * создает текстовое представление массива по синтаксису php
	 */
	public function arrayToString($data, $offset = 0)
	{
		$locator = IcEngine::serviceLocator();
		$viewRenderManager = $locator->getService('viewRenderManager');
		$render = $viewRenderManager->byName('Smarty');

		$padding = null;
		$padding2 = 0;
		for ($i = 0; $i < $offset; $i++) {
			if ($i == $offset - 1) {
				$padding2 = $padding;
			}
			$padding .= '	';
		}
		$render->assign('data', $data);
		$render->assign('offset', $offset);
		$render->assign('padding', $padding);
		$render->assign('padding2', $padding2);
		$content = $render->fetch('Helper/Converter/arrayToString');
		unset($render);
		return $content;
	}
}
