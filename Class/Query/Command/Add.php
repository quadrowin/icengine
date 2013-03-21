<?php

/**
 * Часть запроса "add"
 * 
 * @author morph
 */
class Query_Command_Add extends Query_Command_Abstract
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
        $field = reset($data);
        $fieldName = $field->getName();
        $add = array(
            Query::FIELD => $fieldName
        );
        if ($field instanceof Model_Field) {
            $add[Query::ATTR] = $this->query->getAttr($field);
        } elseif ($field instanceof Model_Index) {
            $add[Query::INDEX] = array(
                Query::NAME     => $fieldName,
                Query::TYPE     => $field->getType(),
                Query::FIELD    => $field->getFields()
            );
        }
        $this->data = array(Query::ADD => $add);
        return $this;
    }
}