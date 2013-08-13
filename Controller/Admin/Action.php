<?php

/**
 * Главный помошник программиста
 */
class Controller_Admin_Action extends Controller_Abstract
{
    /**
     * @Template(null)
     */
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