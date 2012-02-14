<?php

namespace Ice;

class Link_Collection_Option_From extends Model_Collection_Option_Abstract
{

    public function after (Model_Collection $collection, Query $query,
        array $params)
    {

    }

    /**
     *
     * @param Model_Collection $collection
     * @param Query $query
     * @param array $params
     * 		$params['table']
     * 		$params['rowId']
     */
    public function before (Model_Collection $collection, Query $query,
        array $params)
    {
        $query
            ->where ('Link.fromTable=?', $params ['table'])
            ->where ('Link.fromRowId=?', $params ['rowId']);
    }

}