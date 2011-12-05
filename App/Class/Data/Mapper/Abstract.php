<?php
/**
 * 
 * @desc Abstract
 * @author Shvedov_U
 * @package IcEngine
 * 
 */
abstract class Data_Mapper_Abstract
{
	
	/**
	 * @desc
	 * @param string $model 
	 * @return string
	 */
	abstract public function getTable ($model);
	
	/**
	 * @desc 
	 * @param string $table 
	 * @return string
	 */
	abstract public function getModel ($table);
	
}
