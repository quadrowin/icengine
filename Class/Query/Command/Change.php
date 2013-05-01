<?php

/**
 * Часть запроса "change"
 * 
 * @author morph
 */
class Query_Command_Change extends Query_Command_Abstract
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
        $change = array(
            Query::FIELD    => $data[0],
            Query::ATTR     => array_merge(
                array(Query::NAME => $data[1]->getName()),
                $this->query->getAttrs($data[1])
            )
        );
        $this->data = array(Query::CHANGE => $change);
        return $this;
    }
}