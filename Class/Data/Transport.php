<?php

/**
 * Транспорт данных
 * 
 * @author goorus, morph
 */
class Data_Transport
{
    /**
     * Поставщики данных.
     * 
     * @var array <Data_Provider_Abstract>
     */
	protected $providers = array();

    /**
     * Валидаторы выхода.
     * 
     * @var Data_Validator_Collection
     */
	protected $validators;

	/**
	 * Стек начатых транзакций
	 * 
     * @var array
	 */
	protected $transactions = array();

    /**
     * Добавить провайдер 
     * 
     * @param Data_Provider_Abstract $provider
     * @return Data_Transport
     */
	public function appendProvider(Data_Provider_Abstract $provider)
	{
		$this->providers[] = $provider;
		return $this;
	}

	/**
	 * Начинает новую транзакцию
     * 
	 * @return Data_Transport_Transaction
	 * 		Созданная транзакция
	 */
	public function beginTransaction()
	{
	    $transaction = new Data_Transport_Transaction($this);
	    $this->transactions[] = $transaction;
	    return $transaction;
	}

	/**
     * Получить текущую транзакцию
     * 
	 * @return Data_Transport_Transaction
	 * 		Текущая транзакция
	 */
	public function currentTransaction()
	{
	    return end($this->transactions);
	}

	/**
	 * Заканчивает текущую транзакцию
     * 
	 * @return Data_Transport_Transaction
	 * 		Законченная транзакция
	 */
	public function endTransaction()
	{
	    return array_pop($this->transactions);
	}

	/**
	 * Получить провайдер по индексу
     * 
	 * @param integer $index
	 * @return Data_Provider_Abstract
	 */
	public function getProvider($index)
	{
		return isset($this->providers[$index]) ? $this->providers[$index] : null;
	}

    /**
     * Получить весь пул провайдеров
     * 
     * @return array
     */
	public function getProviders()
	{
		return $this->providers;
	}
    /**
     * Получение данных.
     * 
     * @param mixed $_
     * @return mixed
     */
	public function receive()
	{
		$keys = func_get_args();
		$results = array ();
		if ($this->transactions) {
			$buffer = end($this->transactions)->buffer();
			foreach ($keys as $key) {
				$data = null;
				$chunk = isset($buffer[$key]) ? $buffer[$key] : null;
				$results[] = $chunk;
			}
		} else {
            $jcount = count($this->providers);
			for ($i = 0, $icount = count($keys); $i < $icount; $i++) {
				$data = null;
				for ($j = 0; $j < $jcount; ++$j) {
					$provider = $this->providers[$j];
					$chunk = $provider->get($keys[$i]);
                    if (!is_null($chunk)) {
                        $data = $chunk;
                    }
				}
				$results[] = $data;
			}
		}
		return count($results) == 1 ? $results[0] : $results;
	}

	/**
	 * Получает все значения из всех провайдеров.
	 * Не рекомендуется использовать.
     * 
	 * @return array Массив пар (ключ => значение)
	 */
	public function receiveAll ()
	{
		if ($this->transactions) {
			return end($this->transactions)->buffer();
		}
		$result = array ();
		foreach ($this->providers as $provider){
			$result = array_merge(
				$result,
				$provider->getAll()
			);
		}
		return $result;
	}

	/**
	 * Очистка данных всех провайдеров и сброс транзаций.
	 * 
     * @return Data_Transport
	 */
	public function reset ()
	{
		$this->transactions = array ();
		for ($i = 0, $count = sizeof($this->providers); $i < $count; ++$i) {
			$this->providers[$i]->clear();
		}
		return $this;
	}

    /**
     * Отправить данные в транспорт или в буффер транзации, если она начата
     * 
     * @param string|array $key
     * @param mixed $data
     * @return Data_Transport
     */
	public function send($key, $data = null)
	{
		if ($this->transactions) {
			$this->currentTransaction()->send ($key, $data);
		} else {
			$this->sendForce($key, $data);
		}
		return $this;
	}

	/**
	 * Отправить данные в транспорт
     * 
	 * @param string|array $key
	 * @param mixed $data
	 * @return Data_Transport
	 */
	public function sendForce($key, $data = null)
	{
		if (!is_array($key)) {
			$key = array($key => $data);
		}
        $count = $count = sizeof($this->providers);
		foreach ($key as $k => $v) {
            if($v){
                for ($i = 0; $i < $count; $i++){
                    $this->providers[$i]->set($k, $v);
                }
            }
		}
		return $this;
	}

    /**
     * Изменить пул провайдеров
     * 
     * @param array|Data_Provider_Abstract $providers
     * @return Data_Transport
     */
	public function setProviders($providers)
	{
		$this->providers = $providers;
		return $this;
	}
}