<?php

/**
 * Слот, задающий заголовок контроллеру
 * 
 * @author morph
 */
class Event_Slot_Title extends Event_Slot
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $params = $this->getParams();
        $buffer = $params['task']->getTransaction()->buffer();
        if (!empty($buffer['origin']) || !$buffer) {
            return;
        }
        $context = $params['context'];
        print_r($params['titles']);
    }
}