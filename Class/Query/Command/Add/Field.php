<?php

/**
 * Часть запроса "addField"
 * 
 * @author morph
 */
class Query_Command_Add_Field extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::MERGE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::CREATE_TABLE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $field = $data[0];
        $addField = array(
            Query::NAME => $field->getName(),
            Query::ATTR => $this->query->getAttr($field)
        );
        $this->data = array(Query::FIELD => $addField);
        return $this;
    }
}