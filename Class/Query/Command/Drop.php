<?php

/**
 * Часть запроса "drop"
 * 
 * @author morph
 */
class Query_Command_Drop extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::ALTER_TABLE;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $field = $data[0];
        $fieldName = $field->getName();
        $drop = array(
            Query::FIELD => $fieldName
        );
        if ($field instanceof Model_Index) {
            $add[Query::INDEX] = array(
                Query::NAME     => $fieldName,
                Query::TYPE     => $field->getType(),
                Query::FIELD    => $field->getFields()
            );
        }
        $this->data = array(Query::DROP => $drop);
		return $this;
    }
}