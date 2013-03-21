<?php

/**
 * Часть запроса "order"
 * 
 * @author morph
 */
class Query_Command_Order extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $part = Query::ORDER;
    
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $sort = reset($data);
        if (!is_array($sort)) {
			$sort = func_get_args();
		}
		foreach ($sort as $field => $direction) {
			if (is_numeric($field)) {
				$field = $direction;
				$direction = Query::ASC;
			}
			$this->data[] = array($field, $direction);
		}
		return $this;
    }
}