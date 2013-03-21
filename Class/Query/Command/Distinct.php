<?php

/**
 * Часть запроса "distinct"
 * 
 * @author morph
 */
class Query_Command_Distinct extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStategy = Query::REPLACE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::DISTINCT;
    
    /**
     * @inheritdoc
     */
    public function create($data) 
    {
        $this->data = (bool) reset($data);
        return $this;
    }
}