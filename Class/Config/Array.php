<?phpif (!class_exists ('Objective')){
	require_once dirname (__FILE__) . '/../Objective.php';}
class Config_Array extends Objective
{	/**	 * 	 * @var array	 */
	protected $_errors;	/**	 * 	 * @param array $data	 */
	public function __construct (array $data)
	{
		parent::__construct ($data);
	}		/**	 * Merges any number of arrays of any dimensions, the later overwriting	 * previous keys, unless the key is numeric, in whitch case, duplicated	 * values will not be added.	 *	 * The arrays to be merged are passed as arguments to the function.	 *	 * @access public	 * @return array Resulting array, once all have been merged	 */	protected static function _mergeReplaceRecursive ()	{		// Holds all the arrays passed		$params = func_get_args ();				// First array is used as the base, everything else overwrites on it		$return = array_shift ($params);				// Merge all arrays on the first array		foreach ($params as $array)		{			foreach ($array as $key => $value)			{				// Numeric keyed values are added (unless already there)				if (is_numeric ($key) && (!in_array ($value, $return)))				{					if (is_array ($value) || $value instanceof Objective)					{						$return [] = self::_mergeReplaceRecursive ($return [$key], $value);					}					else					{						$return [] = $value;					}								// String keyed values are replaced				}				else				{					if (						isset ($return [$key]) && 						(is_array ($value) || $value instanceof Objective) && 						(is_array ($return [$key]) || $return [$key] instanceof Objective)					)					{						$return [$key] = self::_mergeReplaceRecursive ($return [$key], $value);					}					else					{						$return [$key] = $value;					}				}			}		}	   		return $return;	}		/**	 * @return array	 */
	public function errors ()
	{		return $this->_errors;
	}		/**	 * @desc Получение конфига на основе базового и изменений.	 * @param array $base_config	 * @return Objective	 */	public function merge (array $base_config)	{		return self::_mergeReplaceRecursive (			new self ($base_config),			$this		);	}		/**	 * @desc Получение конфига на основе базового и изменений.	 * @param array $base_config	 * @return array	 */	public function mergeConfig (array $base_config)	{		return self::_mergeReplaceRecursive (			$base_config, 			$this->__toArray ()		);	}
}