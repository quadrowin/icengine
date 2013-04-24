<?php

/**
 * Транслятор запроса типа alter table для mysql
 *
 * @author morph, goorus
 */
class Query_Translator_Mysql_Alter_Table extends Query_Translator_Abstract
{
	const SQL_ALTER_TABLE	= 'ALTER TABLE';
	const SQL_ADD			= 'ADD';
	const SQL_CHANGE        = 'CHANGE';
	const SQL_DROP			= 'DROP';

    /**
     * Хелпер транслятора
     *
     * @var Helper_Query_Translator_Mysql
     */
    protected static $helper;

    /**
     * Типы индексов
     *
     * @var array
     */
    protected static $indexTypes = array(
        'key'		=> 'Key',
        'index'		=> 'Index',
        'primary'	=> 'Primary key',
        'unique'	=> 'Unique key'
    );

    /**
	 * Рендеринг части запроса alter table
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	public function doRenderAlterTable(Query_Abstract $query)
	{
		$alterTable = $query->part(Query::ALTER_TABLE);
		$table = strtolower($this->modelScheme->table(
            $alterTable[Query::NAME]
        ));
		$sql = self::SQL_ALTER_TABLE . ' ' .
			$this->helper()->escape($table) . ' ' .
			$this->renderAdd($query) .
			$this->renderChange($query) .
			$this->renderDrop($query);
		return $sql;
	}

    /**
     * Есть ли размер у текущего поля
     *
     * @param array $params
     * @return boolean
     */
    protected function hasSize($params)
    {
        return !empty($params[Model_Field::ATTR_ENUM]) ||
            !empty($params[Model_Field::ATTR_SIZE]);
    }

    /**
     * Получить (инициализировать) хелпер
     *
     * @return Helper_Query_Translator_Mysql
     */
    protected function helper()
    {
        if (is_null(self::$helper)) {
            self::$helper = IcEngine::serviceLocator()->getService(
                'helperQueryTranslatorMysql'
            );
        }
        return self::$helper;
    }

    /**
     * Является ли поле enum
     *
     * @param string $type
     * @return boolean
     */
    protected function isEnum($type)
    {
        $type = strtolower($type);
        return strpos($type, 'enum') !== false;
    }

    /**
     * Является ли тип поля числовым
     *
     * @param array $type
     * @return string
     */
    protected function isNumeric($type)
    {
        $type = strtolower($type);
        return strpos($type, 'int') !== false ||
            strpos($type, 'float') !== false ||
            strpos($type, 'double') !== false ||
            strpos($type, 'real') !== false ||
            strpos($type, 'decimal') !== false;
    }

    /**
     * Проверить необходим ли для типа поля размер
     *
     * @param string $type
     * @return boolean
     */
    protected function isSizeble($type)
    {
        $type = strtolower($type);
        return strpos($type, 'text') === false &&
			strpos($type, 'date') === false &&
			strpos($type, 'time') === false;
    }

    /**
     * Является поле текстовым
     *
     * @param string $type
     * @return boolean
     */
    protected function isText($type)
    {
        $type = strtolower($type);
        return strpos($type, 'text') !== false ||
            strpos($type, 'char') !== false;
    }

	/**
	 * Рендеринг части запроса add
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderAdd(Query_Abstract $query)
	{
		$alterTable = $query->part(Query::ALTER_TABLE);
		if (empty($alterTable[Query::ADD])) {
			return;
		}
		$sql = self::SQL_ADD . ' ';
        $add = $alterTable[Query::ADD];
		$name = $add[Query::FIELD];
		if (!empty($add[Query::ATTR])) {
			$sql .= $this->renderField($name, $add[Query::ATTR]);
		} elseif (!empty($add[Query::INDEX])) {
			$sql .= $this->renderIndex($name, $add[Query::INDEX]);
		}
		return $sql;
	}

	/**
	 * Рендеринг части запроса change
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderChange(Query_Abstract $query)
	{
		$alterTable = $query->part(Query::ALTER_TABLE);
		if (empty($alterTable[Query::CHANGE])) {
			return;
		}
        $change = $alterTable[Query::CHANGE];
		$name = $change[Query::FIELD];
        $helper = $this->helper();
		$sql = self::SQL_CHANGE . ' ' . $helper->escape($name) . ' ';
		$sql .= $this->renderField($change[Query::NAME], $change[Query::ATTR]);
		return $sql;
	}

	/**
	 * Рендеринг части запроса drop
	 *
     * @param Query_Abstract $query
	 * @return string
	 */
	protected function renderDrop(Query_Abstract $query)
	{
		$alterTable = $query->part(Query::ALTER_TABLE);
		if (empty($alterTable[Query::DROP])) {
			return;
		}
        $drop = $alterTable[Query::DROP];
		$name = $drop[Query::FIELD];
		$sql = self::SQL_DROP . ' ';
		if (!empty($drop[Query::INDEX])) {
            $index = $drop[Query::INDEX];
			$indexType = self::$indexTypes[$index[Query::TYPE]];
			$sql .= ' ' . strtoupper($indexType) . ' ';
		}
		$sql .= $this->helper()->escape($name);
		return $sql;
	}

	/**
	 * Рендеринг индекса
	 *
     * @param string $name
	 * @param array $params
	 * @return string
	 */
	protected function renderIndex($name, $params)
	{
        $helper = $this->helper();
        $type = strtolower($params[Query::TYPE]);
		$sql = strtoupper(self::$indexTypes[$type]) .
			' ' . $helper->escape($name) . '(';
		$fields = $params[Query::FIELD];
		foreach ($fields as &$field) {
			$field = $helper->escape($field);
		}
		$sql .= implode(',', $fields) . ')';
		return $sql;
	}

    /**
     * Отрендерить размерность поля
     *
     * @para, string $type
     * @param array $params
     * @return string
     */
    protected function renderFieldSize($type, $params)
    {
        $sql = '(' . implode(',', (array) $params[Model_Field::ATTR_SIZE]) . 
            ')';
        return $sql;
    }

    /**
     * Рендерить null/not null атрибут поля
     *
     * @param array $params
     * @return string
     */
    protected function renderFieldNullable($params)
    {
        if (empty($params[Model_Field::ATTR_NULL])) {
			$sql = Model_Field::ATTR_NOT_NULL;
		} else {
			$sql = Model_Field::ATTR_NULL;
		}
        $sql .= ' ';
        return $sql;
    }

    /**
     * Отрендерить кодировку строки
     *
     * @param array $params
     * @return string
     */
    protected function renderFieldCharset($params)
    {
        $sql = '';
        if (!empty($params[Model_Field::ATTR_CHARSET])) {
            $sql .= ' ' . Model_Field::ATTR_CHARSET . ' ' .
                $params[Model_Field::ATTR_CHARSET];
        }
        if (!empty ($params [Model_Field::ATTR_COLLATE])) {
            $sql .= ' ' . Model_Field::ATTR_COLLATE . ' ' .
                $params[Model_Field::ATTR_COLLATE];
        }
        return $sql;
    }

    /**
     * Отрендерить значение по умолчанию
     *
     * @param string $type
     * @param array $params
     * @return string
     */
    protected function renderFieldDefault($type, $params)
    {
        $default = $params[Model_Field::ATTR_DEFAULT];
        if (!empty($params[Model_Field::ATTR_NULL]) && !$default) {
            $default = 'NULL';
        }
        if ($this->isNumeric($type)) {
            $default = $default == 'NULL' ?: (int) $default;
        }
        if (strpos(strtolower($type), 'text') === false) {
            $helper = $this->helper();
            $default = $default == 'NULL' ?: $helper->quote($default);
            return ' ' . Model_Field::ATTR_DEFAULT . ' ' . $default;
        }
    }

	/**
	 * Рендеринг поля
	 *
     * @param string $name
	 * @param array $params
	 * @return string
	 */
	protected function renderField($name, $params)
	{
		$type = $params[Model_Field::ATTR_TYPE];
        $helper = $this->helper();
		$sql = $helper->escape($name) . ' ' . strtoupper($type);
        if ($this->isSizeble($type) && $this->hasSize($params)) {
            $sql .= $this->renderFieldSize($type, $params);
        }
		if (!empty($params[Model_Field::ATTR_UNSIGNED])) {
			$sql .= ' ' . Model_Field::ATTR_UNSIGNED . ' ';
		}
		if (!empty($params[Model_Field::ATTR_BINARY])) {
			$sql .= ' ' . Model_Field::ATTR_BINARY . ' ';
		}
		$sql .= ' ' . $this->renderFieldNullable($params) . ' ';
        if ($this->isText($type)) {
            $sql .= ' ' . $this->renderFieldCharset($params) . ' ';
        }
        if (!empty($params[Model_Field::ATTR_DEFAULT]) &&
            empty($params[Model_Field::ATTR_AUTO_INCREMENT])) {
            $sql .= ' ' . $this->renderFieldDefault($type, $params) . ' ';
        } elseif (!empty($params[Model_Field::ATTR_AUTO_INCREMENT])) {
            $sql .= ' ' . Model_Field::ATTR_AUTO_INCREMENT . ' ';
        }
		if (!empty($params[Model_Field::ATTR_COMMENT])) {
			$sql .= ' ' . Model_Field::ATTR_COMMENT . ' ' .
				$helper->quote($params[Model_Field::ATTR_COMMENT]);
		}
		return $sql;
	}
}