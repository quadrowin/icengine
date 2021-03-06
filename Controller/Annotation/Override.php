<?php

/**
 * Контроллер для аннотаций типа "Override"
 * 
 * @author morph
 */
class Controller_Annotation_Override extends Controller_Abstract
{
    /**
     * Обновить аннотации
     * 
     * @Template(null)
     * @Context("helperCodeGenerator")
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context)
    {
        $overrides = array();
        $paths = array_flip(
            $context->configManager->get('Module_Manager')
                ->loaderPaths->__toArray()
        );
        foreach ($data as $className => $annotationData) {
            $annotationData = $annotationData['Override']['data'];
            $type = 'Class';
            if ($annotationData[0]) {
                $type = reset($annotationData[0]);
            }
            $filename = str_replace('_', '/', $className) . '.php';
            $path = 'Ice/' . $paths[$type];
            $pathParts = explode('/', $path);
            $fileParts = explode('/', $filename);
            if ($fileParts[0] == $pathParts[count($pathParts) - 1]) {
                unset($fileParts[0]);
            }
            $overrides[$filename] = implode('/', $pathParts) . '/' .
                implode('/', $fileParts);
        }
        $output = $context->helperCodeGenerator->fromTemplate(
            'override',
            array (
                'overrides'  => $overrides
            )
        );
        $filename = IcEngine::root() . 'Ice/Config/Loader/Override.php';
        file_put_contents($filename, $output);
    }
}