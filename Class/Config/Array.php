<?phpif (!class_exists ('Objective')){
	require_once dirname (__FILE__) . '/../Objective.php';}
class Config_Array extends Objective
{	/**	 * 	 * @var array	 */
	protected $_errors;	/**	 * 	 * @param array $data	 */
	public function __construct (array $data)
	{
		parent::__construct ($data);
	}		/**	 * @return array	 */
	public function errors ()
	{		return $this->_errors;
	}		/**	 * 	 * @param array $base_config	 * @return array	 */	public function mergeConfig (array $base_config)	{		return self::_arrayMergeReplaceRecursive (			$base_config, 			$this->__toArray ()		);	}		/**	 * Merges any number of arrays of any dimensions, the later overwriting	 * previous keys, unless the key is numeric, in whitch case, duplicated	 * values will not be added.	 *	 * The arrays to be merged are passed as arguments to the function.	 *	 * @access public	 * @return array Resulting array, once all have been merged	 */	protected static function _arrayMergeReplaceRecursive ()	{		// Holds all the arrays passed		$params = func_get_args ();	   		// First array is used as the base, everything else overwrites on it		$return = array_shift ($params);	   		// Merge all arrays on the first array		foreach ($params as $array)		{			foreach ($array as $key => $value)			{				// Numeric keyed values are added (unless already there)				if (is_numeric ($key) && (!in_array ($value, $return)))				{					if (is_array ($value))					{						$return [] = self::_arrayMergeReplaceRecursive ($return [$key], $value);					}					else					{						$return [] = $value;					}				   				// String keyed values are replaced				}				else				{					if (isset ($return [$key]) && is_array ($value) && is_array ($return [$key]))					{						$return [$key] = self::_arrayMergeReplaceRecursive ($return [$key], $value);					}					else					{						$return [$key] = $value;					}				}			}		}	   		return $return;	}
}