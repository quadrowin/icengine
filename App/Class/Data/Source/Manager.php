<?php

namespace Ice;

/**
 *
 * @desc Менеджер источников данных.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Data_Source_Manager
{

	/**
	 * @desc Загруженные источники.
	 * @var array of Data_Source
	 */
	protected $_sources = array ();

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array (
		/**
		 * @desc Массив источников
		 * @var array
		 */
		'sources'	=> array (
			'default'	=> array (
				'adapter'			=> 'Null',
				'adapter_options'	=> array (),
				'mapper'			=> 'Simple'
			)
		)
	);

	/**
	 * @desc Загружает и возвращает конфиг
	 * @return Objective
	 */
	public function config ()
	{
		if (is_array ($this->_config))
		{
			$this->_config = Config_Manager::get (__CLASS__, $this->_config);
		}
		return $this->_config;
	}

	/**
	 * @desc Получение данных провайдера.
	 * @param string $name
	 * @return Data_Source
	 */
	public function get ($name)
	{
		$config = $this->config ();

		if (!isset ($this->_sources [$name]))
		{
			$source_config = $config ['sources'][$name];

			if (!$source_config)
			{
				foreach ($config ['sources'] as $key => $value)
				{
					if (fnmatch ($key, $name))
					{
						return $this->_sources [$name] = $this->get ($key);
					}
				}
			}

			if (is_string ($source_config))
			{
				return $this->_sources [$name] = $this->get ($source_config);
			}

			Loader::load ('Data_Source');
			$this->_sources [$name] = new Data_Source ();

			// Адаптер источника отличается от указанного в конфигах
			$adapter_class = 'Data_Adapter_' . $source_config ['adapter'];
			Loader::load ($adapter_class);
			$adapter_class = __NAMESPACE__ . '\\' . $adapter_class;
			$adapter = new $adapter_class;
			$this->_sources [$name]->setAdapter ($adapter);

			if ($source_config ['adapter_options'])
			{
				foreach ($source_config ['adapter_options'] as $key => $value)
				{
					$this->_sources [$name]->getAdapter ()
						->setOption ($key, $value);
				}
			}

			// маппер
			Loader::load ('Data_Mapper_Manager');
			$mapper = Data_Mapper_Manager::get (
				$source_config ['mapper']
				? $source_config ['mapper']
				: 'Simple'
			);
			$this->_sources [$name]->setDataMapper ($mapper);

		}

		return $this->_sources [$name];
	}

	/**
	 * @desc
	 * @param string|object $context Запрашивающий класс или объект
	 * @return Data_Source_Manager
	 */
	public static function getInstance ($context = null)
	{
		return Core::di ()->getInstance (__CLASS__, $context);
	}

	/**
	 * @desc Находит конфиг по названию.
	 * @param string $name Название источника.
	 * @return Objective|null Конфиг.
	 */
	public function getSourceConfig ($name)
	{
		$config = $this->config ();

		$src_cfg = $config ['sources'][$name];

		if (!$src_cfg)
		{
			foreach ($config ['sources'] as $key => $value)
			{
				if (fnmatch ($key, $name))
				{
					return $this->getSourceConfig ($key);
				}
			}
		}

		return is_string ($src_cfg)
			? $this->getSourceConfig ($src_cfg)
			: $src_cfg;
	}

	/**
	 * @desc
	 * @param string $name
	 * @param Data_Source $source
	 * @return $this
	 */
	public function set ($name, Data_Source $source)
	{
		$this->_sources [$name] = $source;
		return $this;
	}

}