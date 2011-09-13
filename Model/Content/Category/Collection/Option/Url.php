<?php
/**
 * 
 * @desc Опшн для выбора раздела по адресу.
 * @author Юрий Шведов
 * 
 */
class Content_Category_Collection_Option_Url extends Model_Collection_Option_Abstract
{
	
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		$query->where ('url', $params ['url']);
	}
	
}
