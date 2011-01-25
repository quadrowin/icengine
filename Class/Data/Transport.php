<?php

class Data_Transport
{
	
    /**    
     *
     * @var Filter_Collection
     */
	protected $_filters;

    /**
     *
     * @var array <Data_Provider_Abstract>
     */
	protected $_providers = array ();

    /**
     * @var Data_Validator_Collection
     */
	protected $_validators;
	
	/**
	 * Стек начатых транзакций
	 * @var array
	 */
	protected $_transactions = array ();
	
	public function __construct ()
	{
		$this->initFilters ();
		$this->initValidators ();
	}
	
    /**
     * @param Data_Provider_Abstract $provider
     * @return Data_Transport
     */
	public function appendProvider (Data_Provider_Abstract $provider)
	{
		$this->_providers [] = $provider;
		return $this;
	}
	
	/**
	 * Начинает новую транзакцию
	 * @return Data_Transport_Transaction
	 * 		Созданная транзакция
	 */
	public function beginTransaction ()
	{
	    Loader::load ('Data_Transport_Transaction');
	    $transaction = new Data_Transport_Transaction ($this);
	    $this->_transactions [] = $transaction;
	    return $transaction;
	}
	
	/**
	 * @return Data_Transport_Transaction
	 * 		Текущая транзакция
	 */
	public function currentTransaction ()
	{
	    return end ($this->_transactions);
	}
	
	/**
	 * Заканчивает текущую транзакцию
	 * @return Data_Transport_Transaction
	 * 		Законченная транзакция
	 */
	public function endTransaction ()
	{
	    return array_pop ($this->_transactions);
	}
	
    /**
     *
     * @return array
     */
	public function getFilters ()
	{
		return $this->_filters;
	}

	/**
	 * 
	 * @param integer $index
	 * @return Data_Provider_Abstract
	 */
	public function getProvider ($index)
	{
		return $this->_providers [$index];
	}
	
    /**
     *
     * @return array
     */
	public function getProviders ()
	{
		return $this->_providers;
	}

    /**
     *
     * @return array
     */
	public function getValidators ()
	{
		return $this->_validators;
	}

	public function initFilters ()
	{
		Loader::load ('Filter_Collection');
		$this->_filters = new Filter_Collection ();
	}
	
	public function initValidators ()
	{
		Loader::load ('Data_Validator_Collection');
		$this->_validators = new Data_Validator_Collection();
	}

    /**
     *
     * @param mixed $_
     * @return mixed
     */
	public function receive ()
	{	
		$keys = func_get_args ();
		$results = array ();
		
		for ($i = 0, $icount = sizeof ($keys); $i < $icount; $i++)
		{
			$data = null;
			for ($j = 0, $jcount = sizeof ($this->_providers); $j < $jcount; $j++)
			{
			    /**
			     * 
			     * @var $provider Data_Provider_Abstract
			     */
				$provider = $this->_providers [$j];
				$chunk = $provider->get ($keys [$i]);
				if (!is_null ($chunk) && $this->_validators->validate ($chunk))
				{
					$data = $chunk;
				}
			}
			$results [] = $data;
		}
		
		return sizeof ($results) == 1 ? $results [0] : $results;
	}
	
	/**
	 * @return Data_Transport
	 */
	public function reset ()
	{
		$this->_transactions = array ();
		for ($i = 0, $count = sizeof ($this->_providers); $i < $count; $i++)
		{
			$this->_providers [$i]->clear ();
		}
		return $this;
	}

    /**
     *
     * @param string|array $key
     * @param mixed $data
     * @return Data_Transport
     */
	public function send ($key, $data = null)
	{
		if ($this->_transactions)
		{
			$this->currentTransaction ()->send ($key, $data);
		}
		else
		{
			$this->sendForce ($key, $data);
		}
		return $this;
	}
	
	/**
	 * 
	 * @param string|array $key
	 * @param mixed $data
	 * @return Data_Transport
	 */
	public function sendForce ($key, $data = null)
	{
		if (!is_array ($key))
		{
			$key = array ($key => $data);
		}
		
		foreach ($key as $k => $v)
		{
			$this->_filters->apply ($v);
			for ($i = 0, $count = sizeof ($this->_providers); $i < $count; $i++)
			{
				$this->_providers [$i]->set ($k, $v);
			}
		}
		
		return $this;
	}

    /**
     *
     * @param array|Data_Provider_Abstract $providers
     * @return Data_Transport
     */
	public function setProviders ($providers)
	{
		$this->_providers = array_merge ($this->_providers, (array) $providers);
		return $this;
	} 
	
}