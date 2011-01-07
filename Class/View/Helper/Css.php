<?php

class View_Helper_Css extends View_Helper_Abstract
{

	public function get (array $params)
	{
		$csses = $this->view->resources()->getData (View_Resource_Manager::CSS);
		
		$result = '';
		foreach ($csses as $css)
		{
			$result .=
				"\t<link href=\"" . $css . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
		}
		
		return $result;
	}
	
}