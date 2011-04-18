<?php
/**
 * 
 * @desc Контроллер для яндекс карт
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Yandex_Map extends Controller_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array (
		// Ключ по умолчанию
		'default_key'		=> 'AN61kk0BAAAAmfUFHQIAviXJjepM1vwUHiRbviRvdAPQ1NMAAAAAAAAAAADerIzEKE8_wbCnBoKFcC4TrqVRCg==',
		// Ключи для различных доменов
		'domains'			=> array (
			// $pattern	=> $key
			'/.*forguest\\.grs/'	=> 'AK6GpU0BAAAA0p1BFwIAcBCubCS1dft85kAI-THTx4i475oAAAAAAAAAAABYpvprBqy52GepuLwQlMVPg7xDpg=='
		),
		
		// Скрывать ошибки яндекс карт (будут выводиться в консоль)
		'hide_errors'		=> true,
		
		// loadByRequire в запросе скрипта
		'load_by_require'	=> false
	);
	
	/**
	 * @desc Возврщает ключ для домена 
	 * @param string $domain Домен. Если null, будет взят из $_SERVER ['HTTP_HOST']
	 */
	protected function _getKey ($domain = null)
	{
		if (!$domain)
		{
			$domain = $_SERVER ['HTTP_HOST'];
		}
		
		$config = $this->config ();
		
		if ($config ['domains'])
		{
			foreach ($config ['domains'] as $pattern => $key)
			{
				if (preg_match ($pattern, $domain))
				{
					return $key;
				}
			}
		}
		
		return $config ['default_key'];
	}
	
	/**
	 * @desc Добавляет строку подключения скрипта яндекс карт.
	 */
	public function includeScript ()
	{
		list (
			$domain,
			$hide_errors,
			$load_by_require,
			$wizard
		) = $this->_input->receive (
			'domain',
			'hide_errors',
			'load_by_require',
			'wizard'
		);
		
		$key = $this->_getKey ($domain);
		
		$config = $this->config ();
		
		$this->_output->send (array (
			'hide_errors'		=> 
				is_null ($hide_errors) ?
					$config ['hide_errors'] :
					$hide_errors,
			'key'				=> $key,
			'load_by_require'	=> 
				is_null ($load_by_require) ?
					$config ['load_by_require'] :
					$load_by_require,
			'wizard'			=> $wizard
		));
	}
	
}