<?php

/**
 * Опшен для ограничения вывода
 *
 * @author markov, neon
 */
class Query_Part_Limit extends Query_Part
{
	/**
	 * @inheritdoc
	 */
	public function query()
	{
		$this->query->limit(
			(int) isset($this->params['perPage']) ?
                $this->params['perPage'] : $this->params['count'],
			isset($this->params['offset']) ? $this->params['offset'] : 0
		);
	}
}