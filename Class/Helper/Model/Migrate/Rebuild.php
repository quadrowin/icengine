<?php

/**
 * Хелпер для пересбора аннотации модели конфигурации схемы или источника 
 * данных
 * 
 * @author morph
 * @Service("helperModelMigrateRebuild")
 */
class Helper_Model_Migrate_Rebuild extends Helper_Abstract
{
    /**
     * Получить вывод для поля
     * 
     * @param Model_Field $field
     * @param Config_Array $fieldScheme
     * @param array<Model_Index> $indexes
     * @return string
     */
    public function fieldOutput($field, $fieldScheme, $indexes)
    {
        $comment = $field->getComment() ?: $fieldScheme['Comment'];
        $indexNames = array();
        foreach ($indexes as $index) {
            if (!in_array($field->getName(), $index->getFields())) {
                continue;
            }
            $indexNames[$index->getType()][] = $index->getName();
        }
        $size = $field->getSize();
        $parts = array();
        if ($size) {
            $parts[] = 'Size=' . 
                (is_array($size) ? '{' . implode(',', $size) . '}' : $size);
        }
        if ($field->getNullable()) {
            $parts[] = 'Null';
        } else {
            $parts[] = 'Not_Null';
        }
        if ($field->getAutoIncrement()) {
            $parts[] = 'Auto_Increment';
        }
        $output = "\t/**" . PHP_EOL;
        if ($comment) {
            $output .= "\t * $comment" . PHP_EOL. "\t *" . PHP_EOL;
        }
        $output .= "\t * @Orm\Field\\" . $field->getType() . '(' .
            implode(', ', $parts) . ')';
        if ($indexNames) {
            foreach ($indexNames as $typeName => $indexes) {
                foreach ($indexes as &$index) {
                    $index = '"' . $index . '"';
                }
                $output .= PHP_EOL . "\t * @Orm\Index\\" . $typeName . '(' .
                    implode(', ', $indexes) . ')';
            }
        }
        $output .= PHP_EOL . "\t */" . PHP_EOL . "\tpublic $" . 
            $field->getName() . ';' . PHP_EOL . PHP_EOL;
        return $output;    
    }
    
    /**
     * Преобразует поля полученные из схемы данных в необходимые для схемы
     * маппинга моделей
     * 
     * @param array $fields
     * @return array
     */
    public function fieldsToScheme($fields)
    {
        $schemeFields = array();
        foreach ($fields as $field) {
            $attrs = array();
            if ($field->getSize()) {
                $attrs['Size'] = $field->getSize();
            }
            $default = $field->getDefault();
            if (!is_null($default)) {
                $attrs['Default'] = $default;
            }
            $notNull = !$field->getNullable();
            if ($notNull) {
                $attrs[] = 'Not_Null';
            }
            if ($field->getAutoIncrement()) {
                $attrs[] = 'Auto_Increment';
            }
            if ($field->getUnsigned()) {
                $attrs[] = 'Unsigned';
            }
            $schemeFields[$field->getName()] = array(
                $field->getType(), $attrs
            );
        }
        return $schemeFields;
    }
    
    /**
     * Преобразует индексы полученные из схемы данных в необходимые для схемы
     * маппинга моделей
     * 
     * @param array $fields
     * @return array
     */
    public function indexesToScheme($indexes)
    {
        $schemeIndexes = array();
        foreach ($indexes as $index) {
            $schemeIndexes[$index->getName()] = array(
                $index->getType(), $index->getFields()
            );
        }
        return $schemeIndexes;
    }
    
    /**
     * Пересобрать модель основываясь на схеме данных из источника
     * данных
     * 
     * @param string $modelModel
     */
    public function rebuild($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataSchemeDto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName);
        $dataScheme = new Data_Scheme($dataSchemeDto);
        $dataSource->getScheme($dataScheme);
        $fields = $dataScheme->getDto()->fields;
        $indexes = $dataScheme->getDto()->indexes;
        foreach ($fields as $field) {
            $this->rewriteField($modelName, $field, $indexes);
        }
        $this->rewriteScheme($modelName, $dataScheme->getDto());
    }
    
    /**
     * Переписать поле модели
     * 
     * @param string $modelName
     * @param Model_Field $field
     * @param array $indexes
     */
    public function rewriteField($modelName, $field, $indexes)
    {
        $loader = IcEngine::getLoader();
        $loader->load($modelName);
        $fileKey = str_replace('_', '/', $modelName) . '.php';
        $filename = $loader->getRequired($fileKey, 'Class');
        if (!$filename) {
            return;
        }
        $content = file_get_contents($filename);
        $contentLength = mb_strlen($content, 'UTF-8');
        $varNeedle = 'public $' . $field->getName() . ';';
        $varPos = mb_strpos($content, $varNeedle, 0, 'UTF-8');
        $firstAppendPos = mb_strpos($content, '{', 0, 'UTF-8') + 2;
        if ($varPos !== false) {
            $reversedContent = strrev($content);
            $reversedVarPos = $contentLength - $varPos;
            $reversedBeginCommentPos = mb_strpos(
                $reversedContent, '**/', $reversedVarPos, 'UTF-8'
            );
            $beginFieldPos = $contentLength - $reversedBeginCommentPos - 3;
            $endFieldPos = mb_strpos(
                $content, ';', $beginFieldPos, 'UTF-8') + 3;
        } else {
            $beginFieldPos = $firstAppendPos;
            $endFieldPos = $beginFieldPos;
        }
        $scheme = $this->getService('modelScheme')->scheme($modelName);
        $fieldScheme = $scheme->fields[$field->getName()];
        $contentFirstPart = mb_substr($content, 0, $beginFieldPos, 'UTF-8');
        $contentLastPart = mb_substr(
            $content, $endFieldPos, $contentLength - $endFieldPos, 'UTF-8'
        );
        $output = $contentFirstPart . 
            $this->fieldOutput($field, $fieldScheme, $indexes) .
            $contentLastPart;
        $outputParts = explode(PHP_EOL, $output);
        foreach ($outputParts as &$output) {
            if (strpos($output, "\t/**") === false) {
                continue;
            }
            $output = "\t" . trim($output, " \t");
        }
        $resultOutput = implode(PHP_EOL, $outputParts);
        file_put_contents($filename, $resultOutput);
    }
    
    /**
     * Перезапись схемы модели
     * 
     * @param string $modelName
     */
    public function rewriteScheme($modelName, $dto)
    {
        $scheme = $this->getService('modelScheme')->scheme($modelName);
        $fields = $this->fieldsToScheme($dto->fields);
        $indexes = $this->indexesToScheme($dto->indexes);
        $references = $dto->references;
        $author = null;
        $comment = null;
        $admin = array();
        $languageScheme = array();
        $createScheme = array();
        if ($scheme) {
            $author = $scheme->author;
            $comment = $scheme->comment ?: $dto->info['Comment'];
            $references = $references ?: $scheme->references
                ? $scheme->references->__toArray() : array();
            $admin = $scheme->admin ? $scheme->admin->__toArray() : array();
            $languageScheme = $scheme->languageScheme 
                ? $scheme->languageScheme->__toArray() : array();
            $createScheme = $scheme->createScheme 
                ? $scheme->createScheme->__toArray() : array();
        }
        $filename = IcEngine::root() . 'Ice/Config/Model/Mapper/' .
            str_replace('_', '/', $modelName) . '.php';
        $output = $this->getService('helperCodeGenerator')->fromTemplate(
            'scheme',
            array(
                'author'            => $author,
                'comment'           => $comment,
                'fields'            => $fields,
                'indexes'           => $indexes,
                'references'        => $references,
                'admin'             => $admin,
                'languageScheme'    => $languageScheme,
                'createScheme'      => $createScheme
            )
        );
        file_put_contents($filename, $output);
    }
}