<?php

class View_Helper_Js extends View_Helper_Abstract
{

	public function get (array $params)
	{
		$jses = $this->view->resources()->getData (View_Resource_Manager::JS);
		
		$result = '';
		foreach ($jses as $js)
		{
			$result .=
				"\t<script type=\"text/javascript\" src=\"" . $js . "\"></script>\n";
		}
		
		return $result;
	}
	
}