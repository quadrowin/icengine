<?php

class Filter_Escape implements Filter_Interface
{
	/**
	 * 
	 * @param string $string
	 * @return string
	 */
	public function apply ($string)
	{
		return DDS::escape ($string);
	}
}