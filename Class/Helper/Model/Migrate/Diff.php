<?php

/**
 * Хелпер для определения различий между текущей схемой модели и схемой
 * это модели в источнике данных
 * 
 * @author morph
 * @Service("helperModelMigrateDiff")
 */
class Helper_Model_Migrate_Diff extends Helper_Abstract
{
    /**
     * Создать поле
     * 
     * @param string $modelName
     * @param string $fieldName
     * @param array $fieldAttrs
     * @return Query_Abstract
     */
    public function addField($modelName, $fieldName, $fieldAttrs)
    {
        $newField = $this->newField($fieldName, $fieldAttrs);
        $query = $this->getService('query')
            ->alterTable($modelName)
            ->add($newField);
        return $query;
    }
    
    /**
     * Сравнить два поля: из схему и из источника данных
     * 
     * @param array $schemeField
     * @param array $sourceField
     */
    public function compareField($schemeField, $sourceField)
    {
        $schemeAttributes = $this->getAttributes($schemeField);
        $sourceAttributes = $this->getAttributes($sourceField);
        ksort($schemeAttributes);
        ksort($sourceAttributes);
        $schemeAttributeNames = array_keys($schemeAttributes);
        $sourceAttributeNames = array_keys($sourceAttributes);
        if (array_diff($schemeAttributeNames, $sourceAttributeNames)) {
            return true;
        }
        if (array_diff($schemeAttributes, $sourceAttributes)) {
            return true;
        }
    }
    
    /**
     *  Изменить поле
     * 
     * @param string $modelName
     * @param string $fieldName
     * @param array $fieldAttrs
     * @return Query_Abstract
     */
    public function changeField($modelName, $fieldName, $fieldAttrs)
    {
        $newField = $this->newField($fieldName, $fieldAttrs);
        $query = $this->getService('query')
            ->alterTable($modelName)
            ->change($fieldName, $newField);
        return $query;
    }
    
    /**
     * Найти различия текущей схемы и схемы источника
     * 
     * @param string $modelName
     */
    public function diff($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataSchemeDto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName);
        $dataScheme = new Data_Scheme($dataSchemeDto);
        $dataSource->getScheme($dataScheme);
        $this->getService('helperModelMigrateSync')->resync($modelName);
        $currentScheme = $modelScheme->scheme($modelName);
        $currentSchemeFields = $currentScheme->fields->__toArray();
        $resultMigrations = array();
        $setStates = array();
        foreach ($currentSchemeFields as $fieldName => $fieldAttrs) {
            if (isset($fieldAttrs['Rename'])) {
                $setStates[$fieldAttrs['Rename']] = $fieldName;
            }
        }
        foreach ($dataSchemeDto->fields as $fieldName => $fieldAttrs) {
            if (isset($setStates[$fieldName])) {
                $resultMigrations[] = $this->changeField(
                    $modelName, $fieldName, 
                    $currentSchemeFields[$setStates[$fieldName]]
                );
                $dataSchemeDto->fields[$fieldName] = $fieldAttrs;
            } elseif (!isset($currentSchemeFields[$fieldName])) {
                $resultMigrations[] = $this->dropField($modelName, $fieldName);
            }
        }
        foreach ($currentSchemeFields as $fieldName => $fieldAttrs) {
            if (!isset($dataSchemeDto->fields[$fieldName])) {
                $resultMigrations[] = $this->addField(
                    $modelName, $fieldName, $fieldAttrs
                );
            } elseif ($this->compareField(
                $fieldAttrs, $dataSchemeDto->fields[$fieldName])) {
                $resultMigrations[] = $this->changeField(
                    $modelName, $fieldName, $currentSchemeFields[$fieldName]
                );
            }
        }
        return $resultMigrations;
    }
    
    /**
     * Удаление поля
     * 
     * @param string $modelName
     * @param string $fieldName
     * @return Query_Abstract
     */
    public function dropField($modelName, $fieldName)
    {
        $field = new Model_Field($fieldName);
        $query = $this->getService('query')
            ->alterTable($modelName)
            ->drop($field);
        return $query;
    }
    
    /**
     * Получить атрибуты поля
     * 
     * @param array $field
     * @return array
     */
    public function getAttributes($field)
    {
        $result = array();
        if (is_object($field)) {
            $newField = array(
                $field->getType(), array()
            );
            if ($field->getSize()) {
                $newField[1]['Size'] = $field->getSize();
            }
            if (!is_null($field->getDefault())) {
                $newField[1]['Default'] = $field->getDefault();
            }
            if (!$field->getNullable()) {
                $newField[1][] = 'Not_Null';
            } else {
                $newField[1][] = 'Null';
            }
            if ($field->getAutoIncrement()) {
                $newField[1][] = 'Auto_Increment';
            }
            if ($field->getUnsigned()) {
                $newField[1][] = 'Unsigned';
            }
            $field = $newField;
        } 
        $fieldAttributes = array_merge(
            array('Type' => $field[0]), $field[1]
        );
        foreach ($fieldAttributes as $attributeName => $value) {
            if (is_numeric($attributeName)) {
                $attributeName = $value;
                $value = true;
            }
            $result[$attributeName] = $value;
        }
        return $result;
    }
    
    /**
     * Создать новое поле модели
     * 
     * @param string $fieldName
     * @param array $fieldAttrs
     * @return \Model_Field
     */
    public function newField($fieldName, $fieldAttrs)
    {
        if (is_object($fieldAttrs)) {
            return $fieldAttrs;
        }
        $newField = new Model_Field($fieldName);
        $newField->setType($fieldAttrs[0]);
        if (!empty($fieldAttrs[1]['Size'])) {
            $newField->setSize($fieldAttrs[1]['Size']);
        }
        $newField->setNullable(in_array('Not_Null', $fieldAttrs[1]));
        $newField->setAutoIncrement(in_array('Auto_Icrement', $fieldAttrs[1]));
        $newField->setUnsigned(in_array('Unsigned', $fieldAttrs[1]));
        if (!empty($fieldAttrs[1]['Comment'])) {
            $newField->setComment($fieldAttrs[1]['Comment']);
        }
        if (isset($fieldAttrs[1]['Default'])) {
            $newField->setDefault($fieldAttrs[1]['Default']);
        }
        return $newField;
    }
}