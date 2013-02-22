<?php

/**
 * По полю phpSessionId
 *
 * @author markov
 */
class Query_Part_Session extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->where($this->modelName . '.phpSessionId',
			$this->params['id']);
	}
}