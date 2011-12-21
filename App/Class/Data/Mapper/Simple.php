<?php

namespace Ice;

/**
 *
 * @desc Simple
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Data_Mapper_Simple extends Data_Mapper_Abstract {

	/**
	 * @desc
	 * @param string $model
	 * @return string
	 */
	public function getTable ($model)
	{
		return Model_Scheme::getInstance ()->table ($model);
	}

	/**
	 * @desc
	 * @param string $table
	 * @return string
	 */
	public function getModel ($table)
	{
		return Model_Scheme::getInstance ()->tableToModel ($table);
	}

}
