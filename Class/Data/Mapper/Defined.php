<?php

/**
 * Мэппер для работы с моделями типа "Defined"
 * 
 * @author morph
 */
class Data_Mapper_Defined extends Data_Mapper_Abstract
{
    /**
     * @inheritdoc
     */
    public function _execute(Query_Abstract $query, $options = null) 
    {
        $serviceLocator = IcEngine::serviceLocator();
        $helperArray = $serviceLocator->getService('helperArray');
        $modelName = reset($query->getPart(Query::FROM))[Query::TABLE];
        $rows = $modelName::$rows;
        $where = $query->getPart(Query::WHERE);
        if ($where) {
            $criteria = array();
            foreach ($where as $part) {
                $criteria[$part[Query::WHERE]] = $part[Query::VALUE];
            }
            $rows = $helperArray->filter($rows, $criteria);
        }
        $select = $query->getPart(Query::SELECT);
        if (reset($select) == '*') {
            return $rows;
        }
        return $helperArray->column($rows, array_keys($select));
    }
}