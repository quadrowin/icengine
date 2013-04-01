<?php

/**
 * Собирает схему модели по аннотациям
 * 
 * @author morph
 */
class Controller_Annotation_Orm extends Controller_Abstract
{
    /**
     * Создать/обновить схему модели 
     * 
     * @Context(
     *      "helperAnnotationOrm", "modelScheme", "helperModelMigrateSync",
     *      "helperModelTable"
     * )
     */
    public function update($data, $context)
    {
        foreach ($data as $className => $subdata) {
            $annotationManager = IcEngine::serviceLocator()->getSource()
                ->getAnnotationManager();
            $annotation = $annotationManager->getAnnotation($className)
                ->getData();
            if (!isset($annotation['class']['Orm\\Entity'])) {
                return;
            }
            $entity = $annotation['class']['Orm\\Entity'][0];
            if (is_array($entity)) {
                $context->helperAnnotationOrm->rewriteModelScheme(
                    $className, $entity
                );
            }
            $scheme = $context->getService('modelScheme')->scheme($className);
            if (!$scheme) {
                $context->helperModelMigrationSync->resync($className);
                $context->helperModelTable->create($className);
            }
        }
    }
}