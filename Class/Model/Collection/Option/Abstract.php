<?php

Loader::load ('Model_Collection_Option');
abstract class Model_Collection_Option_Abstract extends Model_Collection_Option
{
    final public function __construct ()
    {
    	
    }
	
    /**
     * Вызывается после выполения запроса
     * @param Model_Collection $collection
     * @param Query $query
     * @param array $params
     */
    public function after (Model_Collection $collection, 
        Query $query, array $params)
    {
        
    }
        
    /**
     * Вызывается перед выполнением запроса
     * @param Model_Collection $collection
     * @param Query $query
     * @param array $params
     */
    public function before (Model_Collection $collection, 
        Query $query, array $params)
    {
        
    }
    
}