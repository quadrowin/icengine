<?php

/**
 * Упаковщик Js ресурсов представления.
 * 
 * @author goorus, morph
 */
class View_Resource_Packer_Js extends View_Resource_Packer_Abstract
{
	/**
	 * Класс Packer'a
	 * 
     * @var string
	 */
	const PACKER = 'class.JavaScriptPacker.php';
    
    /**
     * Не упаковывать файлы
     * 
     * @var boolean
     */
    protected $noPack = false;

	public function __construct()
	{
		IcEngine::getLoader()->requireOnce(self::PACKER, 'includes');
	}

    /**
     * @inheritdoc
     */
	public function packOne(View_Resource $resource)
	{
        $config = $this->config();
		if ($config->item_prefix && isset($resource->filePath)) {
			$result = strtr($config->item_prefix, array (
                '{$source}' => $resource->filePath,
                '{$src}'	=> $resource->localPath
            ));
		} else {
			$result = '';
		}
		if ($this->currentResource->nopack || $this->noPack) {
			$result .= $resource->content();
		} else {
			$packer = new JavaScriptPacker($resource->content(), 0);
			$result .= $packer->pack();
		}
		$fname = IcEngine::root() . '/cache/js.pack.log';
		file_put_contents($fname, time() . PHP_EOL, FILE_APPEND);
		return $result . $config->item_postfix;
	}
}