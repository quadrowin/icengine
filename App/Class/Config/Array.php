<?phpnamespace Ice;if (!class_exists (__NAMESPACE__ . '\\Objective')){	require __DIR__ . '/../Objective.php';}/** * * @desc Конфиг из массива * @author Юрий * @package Ice * */class Config_Array extends Objective{	/**	 *	 * @param array $data	 */	public function __construct (array $data)	{		parent::__construct ($data);	}	/**	 *	 * @param string $path	 */	public function includeFile ($path)	{		$config = &$this->_data;		include $path;	}}