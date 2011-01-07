<?php

class Data_Transport_Transaction
{
    
    /**
     * 
     * 
     * @var array
     */
    protected $_buffer = array ();
    
    /**
     * 
     * 
     * @var Data_Transport
     */
    protected $_transport;
    
    public function __construct (Data_Transport $transport)
    {
        $this->_transport = $transport;
    }
    
    /**
     * 
     * 
     * @param array|string $key
     * 		Ключ или массив пар (Ключ => Значение)
     * @param mixed $data
     * 		Значение
     */
    public function send ($key, $data = null)
    {
        if (is_array ($key))
        {
            $this->_buffer = array_merge (
                $this->_buffer,
                $key
            );
        }
        else
        {
            $this->_buffer [$key] = $data;
        }
    }
    
    /**
     * @return array
     */
    public function buffer ()
    {
        return $this->_buffer;
    }
    
    /**
     * Коммит транзакции
     */
    public function commit ()
    {
        $this->_transport->sendForce($this->_buffer);
    }
    
}