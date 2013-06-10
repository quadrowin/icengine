<?php

/**
 * Слот для модерации модели
 * 
 * @author morph
 */
class Event_Slot_Model_Moderate extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $params = $this->getParams();
        $model = $params['model'];
        $modelName = $model->modelName();
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $dataDriver = $dataSource->driver();
        $oldMethod = $dataDriver->getQueryMethod(Query::UPDATE);
        $dataDriverNull = new Data_Driver_Null();
        $dataDriver->setQueryMethod(
            Query::UPDATE, array($dataDriverNull, 'executeCommand')
        );
        $scheme = $model->scheme();
        $afterSet = $scheme['afterSet'];
        if (!$afterSet) {
            $afterSet = array();
        }
        $signalName = 'Model_Driver_Back_' . $modelName;
        if (!in_array($signalName, $scheme->__toArray())) {
            array_unshift($afterSet, $signalName);
            $scheme['afterSet'] = $afterSet;
        }
        $signal = $this->getService('eventManager')->getSignal($signalName);
        $signal->setData(array(
            'model'     => $model,
            'oldMethod' => $oldMethod    
        ));
        $signal->bind('Model_Driver_Back');
    }
}