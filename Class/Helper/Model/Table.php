<?php

/**
 * Хелпер по созданию таблиц Mysql
 *
 * @author morph
 * @Service("helperModelTable")
 */
class Helper_Model_Table extends Helper_Abstract
{
    /**
     * Создать таблицу для подели
     *
     * @param string $modelName
     */
    public function create($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $modelMapper = $this->getService('modelMapper');
        $scheme = $modelMapper->scheme($modelName);
		$modelMapperSchemeRenderView = $this->getService(
			'modelMapperSchemeRenderView'
		);
		$dataSource = $modelScheme->dataSource($modelName);
		$view = $modelMapperSchemeRenderView->byName('Mysql');
		$query = $view->render($scheme);
		$dataSource->execute($query);
    }
}