<?php

namespace Ice;

/**
 *
 * @desc Abstract
 * @author Yury Shvedov
 * @package Ice
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
