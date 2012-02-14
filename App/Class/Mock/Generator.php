<?php

namespace Ice;

/**
 *
 * @desc Генератор заглушек классов
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Mock_Generator
{

	/**
	 * @desc Провайдер для вновь создаваемого мока
	 * @var Mock_Provider
	 */
	protected $_constructProvider = null;

	/**
	 *
	 * @var array of Mock_Provider
	 */
	protected $_mockProviders = array ();

	/**
	 * @param string $class
	 * @param string $mockClass
	 * @return string
	 */
	protected function _generateClassCode ($class, $mockClass)
	{
		$reflection = new \ReflectionClass($class);
		$methods = $reflection->getMethods();

		$code = "class $mockClass extends $class {";

		foreach ($methods as $method)
		{
			if ($method->isStatic ())
			{
				$code .= ' static ';
			}

			$code .= "\n function " . $method->getName () . '(';
			foreach ($method->getParameters () as $i => $param)
			{
				if ($i > 0)
				{
					$code .= ', ';
				}
				if ($param->isArray ())
				{
					$code .= 'array ';
				}
				if ($param->isPassedByReference ())
				{
					$code .= '&';
				}
				$code .= '$' . $param->getName ();
				if ($param->isDefaultValueAvailable ())
				{
					$code .= ' = ';

					$value = $param->getDefaultValue ();

					if (null === $value)
					{
						$code .= 'null';
					}
					elseif (is_array ($value))
					{
						$code .= 'array()';
					}
					else
					{
						$code .= "'" . addslashes ($param->getDefaultValue ()) . "'";
					}
				}
			}

			$code .= '
) {
	return call_user_func_array(
		array(
			\\Ice\\Core::di()->getInstance("Ice\\\\Mock_Generator", $this)->getMockProvider($this),
			"callMethod"
		),
		array($this, __FUNCTION__, func_get_args())
	);
}';
		}

		$code .= "}";

		return $code;
	}

	protected function _pushConstructProvider ($provider)
	{

	}

	/**
	 * @desc
	 * @param string $class
	 * @param string $mockClass [optional]
	 * @param Mock_Provider $provider [optional]
	 * @return Mock object
	 */
	public function generate ($class, $mockClass = '', $provider = null)
	{
		if (!$mockClass)
		{
			$mockClass = 'Mock__' . $class;
		}

		if (!class_exists ($mockClass))
		{
			$code = $this->_generateClassCode ($class, $mockClass);
			eval ($code);
		}

		if (!$provider)
		{
			$provider = new Mock_Provider;
		}

		$this->_constructProvider = $provider;

		$mockClass = '\\' . $mockClass;
		$mock = new $mockClass;

		return $mock;
	}

	/**
	 * @desc
	 * @param object $mock
	 * @return Mock_Provider
	 */
	public function getMockProvider ($mock)
	{
		$hash = spl_object_hash ($mock);
		if (
			!isset ($this->_mockProviders [$hash]) &&
			$this->_constructProvider
		)
		{
			$this->_mockProviders [$hash] = $this->_constructProvider;
			$this->_constructProvider = null;
		}
		return $this->_mockProviders [$hash];
	}

	/**
	 * @desc
	 * @param object $mock
	 * @param Mock_Provider $provider
	 * @return $this
	 */
	public function setMockProvider ($mock, $provider)
	{
		$this->_mockProviders [spl_object_hash ($mock)] = $provider;
		return $this;
	}

}