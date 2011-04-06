<?php
/**
 * 
 * @desc Транзакция данных.
 * Используется для отложенного направления данных в(из) транспорт.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Transport_Transaction
{
    
    /**
     * @desc Буффер транзакции.
     * Данные, которые были направлены в транспорт.
     * @var array
     */
    protected $_buffer = array ();
    
    /**
     * @desc Транспорт, для которого создана транзакция.
     * @var Data_Transport
     */
    protected $_transport;
    
    public function __construct (Data_Transport $transport)
    {
        $this->_transport = $transport;
    }
    
    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function receive ($key)
    {
        return isset ($this->_buffer [$key]) ? $this->_buffer [$key] : null;
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
     * @desc Коммит транзакции.
     * Направляет в транспорт данные, накопленные в транзакции.
     */
    public function commit ()
    {
        $this->_transport->sendForce ($this->_buffer);
    }
    
}