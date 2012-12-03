<?php

/**
 * Description of Key
 *
 * @author markov
 */
class Query_Part_Key extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$locator = IcEngine::serviceLocator();
		$modelScheme = $locator->getService('modelScheme');
		$keyField = $this->modelName
			? $modelScheme->keyField($this->modelName) : 'id';
		$field = $this->modelName . '.' . $keyField;
		$this->query->where($field, $this->params['key']);
	}
}

