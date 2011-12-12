<?php

namespace Ice;

Loader::load ('Data_Adapter_Abstract');

/**
 * @desc Адаптер-заглушка
 * @author Илья Колесников
 * @package Ice
 *
 */
class Data_Adapter_Null extends Data_Adapter_Abstract
{

	/**
	 * @desc Название транслятора
	 * @var string
	 */
	protected $_translatorName = 'Mysql';

}