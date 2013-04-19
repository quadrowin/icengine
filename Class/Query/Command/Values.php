<?php

/**
 * Часть запроса "values"
 * 
 * @author morph
 */
class Query_Command_Values extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::MERGE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::VALUES;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = reset($data);
        if (!empty($data[1])) {
            $this->mergeStrategy = Query::PUSH;
            if ($this->query instanceof Query_Insert) {
                $this->query->setMultiple(true);
            }
        }
        return $this;
    }
}