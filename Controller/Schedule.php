<?php

/**
 * Планировщик
 *
 * @author morph
 */
class Controller_Schedule extends Controller_Abstract
{
    /**
     * Выполнить задания
     * 
     * @Template(null)
     * @Validator("User_Cli")
     */
    public function index($context)
    {
        $schedules = $context->collectionManager->create('Schedule')
            ->addOptions(array(
                'name'  => '::Order_Desc',
                'field' => 'priority'
            ));
        $currentTs = time();
        $helperDate = $this->getService('helperDate');
        foreach ($schedules as $schedule) {
            $scheduleTs = $schedule['lastTs'] + $schedule['deltaSec'];
            if ($scheduleTs > $currentTs) {
                continue;
            }
            $schedule->update(array(
                'lastTs'    => $currentTs,
                'lastDate'  => $helperDate->toUnix()
            ));
            echo $schedule['controllerAction'] . PHP_EOL;
            exec('./ice ' . $schedule['controllerAction']);
        }
    }
}