<?php

/**
 * Запрос типа delete
 * 
 * @author goorus, morph
 */
class Query_Delete extends Query_Select
{
    /**
     * @inheritdoc
     */
    protected $type = Query::DELETE;
}