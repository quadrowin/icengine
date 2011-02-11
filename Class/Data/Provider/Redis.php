<?php

if (!class_exists ('Data_Provider_Abstract'))
{
	include dirname (__FILE__) . '/Abstract.php';
}

class Data_Provider_Redis extends Data_Provider_Abstract
{
	/**
	 * Подключение к редису
	 * @var Redis
	 */
	public $conn = NULL;
	
	/**
	 * Сервера
	 * @var array
	 */
	public $servers = array ();
	
	/**
	 * Максимальное количество выбираемых за раз значений.
	 * Необходимо для обхода бага, когда
	 * в версии Redis под windows стояло жесткое ограничение на 15 значений.
	 * @var integer
	 */
	public $mget_limit = 15; 
	
	public function __construct ($config = array ())
	{
		Loader::requireOnce ('imemcacheclient/Redis.class.php', 'includes');
		$this->conn = new Redis ();
		parent::__construct ($config);
	}
	
	public function add ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('add', $key, $expiration);
		}
		
		if ($expiration < 0)
		{
			$expiration = 0;
		}
		
		return $this->conn->add ($this->prefix . $key, $value, $expiration);
	}
	
	public function addServer ($host, $port = null, $weight = null)
	{
		$this->servers [] = array ($host, $port, $weight);
		return $this->conn->addServer ($host, $port, $weight);
	}
	
	public function addServers ($a)
	{
		foreach ($a as $s)
		{
			$this->addServer ($s[0], $s[1], isset ($s[2]) ? $s[2] : null);
		}
		return true;
	}
	
	public function append ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('append', $key);
		}
		
		return $this->conn->append ($this->prefix . $key, $value);
	}
	
	public function decrement ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('decrement', $key);
		}
		
		return $this->conn->decrement ($this->prefix . $key, $value);
	}
	
	public function delete ($keys, $time = 0, $set_deleted = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('delete', $keys, $time);
		}
		
		if ($time < 0)
		{
			$time = 0;
		}
		
		if (!is_array ($keys))
		{
			if ($set_deleted)
			{
				$this->conn->set ($this->prefix_deleted . $keys, time ());
			}
			if (isset ($this->locks [$keys]))
			{
				unset ($this->locks [$keys]);
			}
			return $this->conn->delete ($this->prefix . $keys, $time);
		}
		
		foreach ($keys as $key)
		{
			$tt = $time;
			if (is_array ($key))
			{
				if (isset ($key [1]))
				{
					$tt = $key [1];
				}
				$key = $key [0];
			}
			
			if (isset ($this->locks [$key]))
			{
				unset ($this->locks [$key]);
			}
			
			if ($set_deleted)
			{
				$this->conn->set ($this->prefix_deleted . $key, time ());
			}
			$this->conn->delete ($this->prefix . $key, $tt);
		}
	}
	
	public function flush ($delay = 0)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('flush', $delay);
		}
		
		return $this->conn->flush ($delay);
	}
	
	public function get ($key, $plain = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('get', $key);
		}
		
		return $this->conn->get ($this->prefix . $key, $plain);
	}
	
	public function getMulti (array $keys, $numeric_index = false)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('getMulti', implode (',', $keys));
		}
		
		if (!empty ($this->prefix))
		{
			foreach ($keys as &$v)
			{
				$v = $this->prefix . $v;
			}
		}
		
		if ($this->mget_limit && count ($keys) > $this->mget_limit)
		{
			// Ограничение на максимальную выборку из кеша. 
			// fix redis.c bug -.-
			// http://code.google.com/p/redis/issues/detail?id=24
			$start = 0;
			$r = array ();
			while ($start < count ($keys))
			{
				$subkeys = array_slice ($keys, $start, $this->mget_limit);
				$r = array_merge (
					$r, 
					$this->conn->getMulti ($subkeys)
				);
				$start += $this->mget_limit;
			}
		}
		else
		{
			$r = $this->conn->getMulti ($keys);
		}
		$l = strlen ($this->prefix);
		
		if ($l == 0 && !$numeric_index)
		{
			return $r;
		}
		
		$result = array ();
		
		if ($numeric_index)
		{
			foreach ($r as $v)
			{
				$result [] = $v;
			}
		}
		else
		{
			foreach ($r as $s => $v)
			{
				$result [substr ($s, $l)] = $v;
			}
		}
		
		return $result;
	}
	
	public function getStats ()
	{
		return $this->conn->getStats ();
	}
	
	public function increment ($key, $value = 1)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('increment', $key);
		}
		
		return $this->conn->increment ($this->prefix . $key, $value);
	}
	
	public function keys ($pattern, $server = NULL)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('keys', $pattern);
		}
		
		$r = $this->conn->keys (
			$this->prefix . $pattern, 
			empty ($server) ? '' : $server
		);
		
		if (empty ($r) || (count ($r) == 1 && empty ($r [0])))
		{
			return array ();
		}
		
		$l = strlen ($this->prefix);
		if ($l > 0 && is_array ($r))
		{
			foreach ($r as &$k)
			{
				$k = substr ($k, $l);
			}
		}
		
		return $r;
	}
	
	public function prepend ($key, $value)
	{
		if ($this->tracer)
		{
			$this->tracer->add ('prepend', $key);
		}
		
		return $this->conn->prepend ($this->prefix . $key, $value);
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		if ($this->tracer)
		{
			$this->tracer->add ('set', $key, $value, $expiration);
		}
		
		if ($expiration < 0)
		{
			$expiration = 0;
		}
		return $this->conn->set ($this->prefix . $key, $value, $expiration, $tags);
	}
	
	public function setOption ($key, $value)
	{
		switch ($key)
		{
			case 'mget_limit':
				$this->mget_limit = $value;
				return;
			case 'servers':
				if ($value instanceof Objective)
				{
					$value = $value->__toArray ();
				}
				
				foreach ($value as $server)
				{
					if ($server instanceof Objective)
					{
						$server = $server->__toArray ();
					}
					
					$this->addServer (
						$server ['host'],
						isset ($server ['port']) ? $server ['port'] : null,
						isset ($server ['weight']) ? $server ['weight'] : null
					);
				}
				return;
		}
		return parent::setOption ($key, $value);
		//return $this->conn->setOption ($key, $value);
	}
	
}