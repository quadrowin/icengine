<?php

class Controller_Admin_Action extends Controller_Abstract
{
    public function index($table, $rowId, $context)
    {
        $query = $context->queryBuilder
            ->delete()
            ->from($table);
        if ($rowId) {
            $query->where('rowId', $rowId);
        }
        $context->dds->execute($query);
    }
}