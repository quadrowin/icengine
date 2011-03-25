<?php

class Content_Category extends Model
{
	/**
	 * @desc Получить коллекцию дочерних категорий
	 * @return Model_Collection
	 */
	public function childs ()
	{
		return new Model_Collection (DDS::execute (
			Query::instance ()
			->from ('Content_Category')
			->where ('parentId', $this->key ())	
			)
				->asTable ()
		);
	}
}