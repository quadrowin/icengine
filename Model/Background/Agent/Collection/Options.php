<?php

class Background_Agent_Collection_Options extends Model_Collection_Option
{
    
    public function processExpiration_before (Model_Collection $collection,
        Query $query, array $params)
    {
        $query
            ->where ('Background_Agent_State__id', Background_Agent_State::PROCESS)
            ->where ('UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(lastActiveTime) > ?', (int) $params ['time_limit']);
    }
    
    public function errorExpiration_before (Model_Collection $collection,
        Query $query, array $params)
    {
        $query
            ->where ('Background_Agent_State__id', Background_Agent_State::ERROR)
            ->where ('UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(lastActiveTime) > ?', (int) $params ['time_limit']);
    }
    
}