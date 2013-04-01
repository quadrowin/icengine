<?php

/**
 * Хелпер для синхронизации схемы моделей и аннотаций модели
 * 
 * @author morph
 * @Service("helperModelMigrateSync")
 */
class Helper_Model_Migrate_Sync extends Helper_Abstract
{
    /**
     * Получить аннотации полей Orm для указаной модели
     * 
     * @param string $modelName
     * @return array
     */
    public function getAnnotations($modelName)
    {
        $annotationManager = $this->getService('controllerManager')
            ->annotationManager();
        $annotations = $annotationManager->getAnnotation($modelName)
            ->getData();
        $annotationProperties = $annotations['properties'];
        $resultAnnotations = array();
        foreach ($annotationProperties as $propertyName => $annotations) {
            if (!$annotations) {
                continue;
            }
            foreach ($annotations as $annotationName => $data) {
                if (strpos($annotationName, 'Orm') === false) {
                    continue;
                }
                $resultAnnotations[$propertyName][$annotationName] = $data;
            }
        }
        return $resultAnnotations;
    }
    
    /**
     * Получить аннотации полей
     * 
     * @param string $modelName
     * @return array
     */
    public function getAnnotationFields($modelName)
    {
        $data = $this->getAnnotations($modelName);
        $resultFields = array();
        $classReflection = new \ReflectionClass($modelName);
        foreach ($data as $propertyName => $annotations) {
            foreach ($annotations as $annotationName => $annotation) {
                $annotation = reset($annotation);
                if (strpos($annotationName, 'Orm\\Field') === false) {
                    if (strpos($annotationName, 'Orm\\State') !== false) {
                        list(,,$state) = explode('\\', $annotationName);
                        $resultFields[$propertyName]->setAttr(
                            $state, $annotation
                        );
                    }
                    continue;
                }
                if (isset($resultFields[$propertyName])) {
                    continue;
                }
                $propertyReflection = $classReflection->getProperty(
                    $propertyName
                );
                $comment = null;
                $doc = $propertyReflection->getDocComment();
                foreach (explode(PHP_EOL, $doc) as $line) {
                    $line = trim($line, "\t *");
                    if (!$line || $line[0] == '/') {
                        continue;
                    } elseif ($line[0] == '@') {
                        break;
                    }
                    $comment .= $line;
                }
                list(,,$type) = explode('\\', $annotationName);
                $field = new Model_Field($propertyName);
                $field
                    ->setType($type)
                    ->setSize(
                        !empty($annotation['Size']) ? $annotation['Size'] : 0
                    )
                    ->setNullable(!isset($annotation['Not_Null']))
                    ->setDefault(isset($annotation['Default']) 
                        ? $annotation['Default'] : null)
                    ->setUnsigned(isset($annotation['Unsigned']))
                    ->setComment($comment);
                $resultFields[$propertyName] = $field;
            }
        }
        return $resultFields;
    }
    
    /**
     * Получить аннотации индексов
     * 
     * @param string $modelName
     * @return array
     */
    public function getAnnotationIndexes($modelName)
    {
        $data = $this->getAnnotations($modelName);
        $preIndexes = array();
        foreach ($data as $properyName => $annotations) {
            foreach ($annotations as $annotationName => $annotation) {
                $annotation = reset($annotation);
                if (strpos($annotationName, 'Orm\\Index') === false) {
                    continue;
                }
                $indexNames = (array) $properyName;
                if ($annotation) {
                    $indexNames = $annotation;
                }
                foreach ($indexNames as $indexName) {
                    if (!isset($preIndexes[$indexName])) {
                        list(,,$type) = explode('\\', $annotationName);
                        $preIndexes[$indexName] = array(
                            $type, array($properyName)
                        );
                    } else {
                        $preIndexes[$indexName][1] = $properyName;
                    }
                }
            }
        }
        $resultIndexes = array();
        foreach ($preIndexes as $indexName => $data) {
            $index = new Model_Index($indexName);
            $index
                ->setType($data[0])
                ->setFields($data[1]);
            $resultIndexes[$indexName] = $index;
        }
        return $resultIndexes;
    }
    
    /**
     * Получить ссылки на 
     * 
     * @param string $modelName
     * @return array
     */
    public function getAnnotationReferences($modelName)
    {
        $data = $this->getAnnotations($modelName);
        $resultReferences = array();
        foreach ($data as $properyName => $annotations) {
            foreach ($annotations as $annotationName => $annotation) {
                $annotation = reset($annotation);
                if (strpos($annotationName, 'Orm\\Reference') === false) {
                    continue;
                }
                list(,,$type) = explode('\\', $annotationName);
                $reference = array(
                    $type, array()
                );
                $reference[1]['Target'] = $annotation['Target'];
                if (isset($annotation['JoinColumn'])) {
                    if (isset($annotation['JoinColumn']['on'])) {
                        $reference[1]['JoinColumn'] = array(
                            0       => reset($annotation['JoinColumn']),
                            'on'    => $annotation['JoinColumn']['on']
                        );
                    } else {
                        $reference[1]['JoinColumn'] = $annotation['JoinColumn'];
                    }
                }
                if (isset($annotation['JoinTable'])) {
                    $reference[1]['JoinTable'] = $annotation['JoinTable'];
                }
                $resultReferences[$properyName] = $reference;
            }
        }
        return $resultReferences;
    }
    
    /**
     * Ресинхронизации схемы и аннотаций
     * 
     * @param string $modelName
     */
    public function resync($modelName)
    {
        $fields = $this->getAnnotationFields($modelName);
        $indexes = $this->getAnnotationIndexes($modelName);
        $references = $this->getAnnotationReferences($modelName);
        $dto = $this->getService('dto')->newInstance('Data_Scheme')
            ->setModelName($modelName)
            ->setFields($fields)
            ->setReferences($references)
            ->setIndexes($indexes);
        $this->getService('helperModelMigrateRebuild')->rewriteScheme(
            $modelName, $dto
        );
    }
}