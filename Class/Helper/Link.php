<?php
/**
 * 
 * @desc Помощник для работы со связами многие-ко-многим моделей.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Link
{
	
    /**
     * @desc Связывает модели.
     * @param string $table1
     * @param integer $key1
     * @param string $table2
     * @param integer $key2
     * @return Link|null
     */
	protected static function _link ($table1, $key1, $table2, $key2)
	{        
		return Model_Manager::byQuery (
		    'Link',
		    Query::instance ()
			    ->where ('fromTable', $table1)
			    ->where ('fromRowId', $key1)
			    ->where ('toTable', $table2)
			    ->where ('toRowId', $key2)
		);
	}
	
	/**
	 * @desc Проверяет, связаны ли модели.
	 * @param Model $model1
	 * @param Model $model2
	 * @return boolean
	 */
	public static function wereLinked (Model $model1, Model $model2)
	{
		if (strcmp ($model1->table (), $model2->table ()) > 0)
	    {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    
	    $link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );
	    
	    return (bool) $link;
	}
	
	/**
	 * @desc Связывает модели.
	 * @param Model $model1
	 * @param Model $model2
	 * @return Link
	 */
	public static function link (Model $model1, Model $model2)
	{
		if (strcmp ($model1->table (), $model2->table ()) > 0)
	    {
	        $tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    
	    $link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );
	    
	    if (!$link)
	    {
	    	Loader::load ('Link');
	        $link = new Link (array (
	            'fromTable'	=> $model1->table (),
	            'fromRowId'	=> $model1->key (),
	            'toTable'	=> $model2->table (),
	            'toRowId'	=> $model2->key ()
	        ));
	        $link->save ();
	    }
	    
	    return $link;
	}
	
	/**
	 * @desc Возвращает коллекцию связанных с $model моделей 
	 * типа $linked_model_name.
	 * @param Model $model1
	 * @param string $model2
	 * @return Model_Collection
	 */
	public static function linkedItems (Model $model, $linked_model_name)
	{
	    $collection_class = $linked_model_name . '_Collection';
	    
	    Loader::load ($collection_class);
	    $result = new $collection_class ();
	    $key_field_2 = Model_Scheme::keyField ($linked_model_name);
	    
		if (strcmp ($model->table (), $linked_model_name) > 0)
	    {
	        $result
	        	->query ()
		            ->from ('Link')
		            ->where ('Link.fromTable', $linked_model_name)
		            ->where ("Link.fromRowId=`$linked_model_name`.`$key_field_2`")
		            ->where ('Link.toTable', $model->table ())
		            ->where ('Link.toRowId', $model->key ()); 
	    }
	    else
	    {
	        $result
	        	->query ()
		            ->from ('Link')
		            ->where ('Link.fromTable', $model->table ())
		            ->where ('Link.fromRowId', $model->key ())
		            ->where ('Link.toTable', $linked_model_name)
		            ->where ("Link.toRowId=`$linked_model_name`.`$key_field_2`");
	    }
	    
	    return $result;
	}
	
	/**
	 * @desc Возвращает первичные ключи связанных моделей.
	 * @param Model $model1
	 * 		Модель.
	 * @param string $model2
	 * 		Имя связанной модели.
	 * @return array
	 * 		Массив первичных ключей второй модели.
	 */
	public static function linkedKeys (Model $model1, $model2)
	{
		$query = Query::instance ();
		
		if (strcmp ($model1->table (), $model2) > 0)
	    {
	        $query
	        	->select (array ('Link'	=> 'fromRowId'))
	            ->from ('Link')
	            ->where ('Link.fromTable', $model2)
	            ->where ('Link.toTable', $model1->table ())
	            ->where ('Link.toRowId', $model1->key ()); 
	    }
	    else
	    {
	        $query
	        	->select (array ('Link'	=> 'toRowId'))
	            ->from ('Link')
	            ->where ('Link.fromTable', $model1->table ())
	            ->where ('Link.fromRowId', $model1->key ())
	            ->where ('Link.toTable', $model2);
	    }
	    
	    return DDS::execute ($query)->getResult ()->asColumn ();
	}
	
	/**
	 * @desc Удаляет связь моделей.
	 * @param Model $model1
	 * @param Model $model2
	 */
	public static function unlink (Model $model1, Model $model2)
	{
	    if (strcmp ($model1->table (), $model2->table ()) > 0)
	    {
			$tmp = $model1;
	        $model1 = $model2;
	        $model2 = $tmp;
	    }
	    
	    /*$link = self::_link (
	        $model1->table (), $model1->key (),
	        $model2->table (), $model2->key ()
	    );*/
		
		$link_collection = Model_Collection_Manager::byQuery (
		    'Link',
		    Query::instance ()
			    ->where ('fromTable',	$model1->table ())
			    ->where ('fromRowId',	$model1->key ())
			    ->where ('toTable',		$model2->table ())
			    ->where ('toRowId',		$model2->key ())
		);
	    
	    /*if ($link)
	    {
	        return $link->delete ();
	    }*/
		
		$link_collection->delete ();
	}
	
	/**
	 * Удаление всех связей модели с моделями указанного типа.
	 * @param Model $model1
	 * @param string $model2
	 */
	public static function unlinkWith (Model $model, $linked_model_name)
	{
		$query = 
			Query::instance ()
				->delete ()
				->from ('Link');
			
		if (strcmp ($model->table (), $linked_model_name) > 0)
	    {
	        $query
	            ->where ('fromTable', $linked_model_name)
	            ->where ('toTable', $model->table ())
	            ->where ('toRowId', $model->key ()); 
	    }
	    else
	    {
	        $query
	            ->where ('fromTable', $model->table ())
	            ->where ('fromRowId', $model->key ())
	            ->where ('toTable', $linked_model_name);
	    }
			
		DDS::execute ($query);
	}
	
}