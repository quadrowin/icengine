<?php

/**
 * Продвайдер $_SESSION данных.
 * 
 * @author goorus, morph
 */
class Data_Provider_Request extends Data_Provider_Buffer
{
    /**
     * @inheritdoc
     */
	public function __construct()
    {
        $this->buffer = &$_SESSION;
    }
}