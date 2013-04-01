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
		$modelMapperSchemeRenderView = $this->getService(
			'modelMapperSchemeRenderView'
		);
		$dataSource = $modelScheme->dataSource($modelName);
		$scheme = $modelScheme->scheme($modelName);
		$view = $modelMapperSchemeRenderView->byName('Mysql');
		$query = $view->render($scheme);
		$dataSource->execute($query);
    }
}