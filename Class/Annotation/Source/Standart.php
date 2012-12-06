<?php

/**
 * Стандартный источник аннотаций
 *
 * @author morph, goorus
 */
class Annotation_Source_Standart extends Annotation_Source_Simple
{
	/**
	 * Выполнить регулярное выражение
	 *
	 * @param string $string
	 * @return array
	 */
	protected function parse($string)
	{
		$result = array();
		$parts = $this->extract($string);
		if (!$parts) {
			return;
		}
		foreach ($parts as $param) {
            $param = trim($param);
			$b = strpos($param, '(');
			if ($b !== false) {
				$e = strrpos($param, ')');
				if ($e === false) {
					continue;
				}
				$value = trim(substr($param, $b + 1, $e - $b - 1));
				$param = trim(substr($param, 0, $b));
				if (!$param || !preg_match('#[A-Z]#', $param[0])) {
					continue;
				}
				$r = $this->parsePart($param, $value);
                if ($r) {
					if (!isset($result[$param])) {
						$result[$param] = array();
					}
					$result[$param] = array_merge($result[$param], $r);
				}
			} elseif ($param) {
				$e = strrpos($param, ')');
				if ($e !== false || !preg_match('#[A-Z]#', $param[0])) {
					continue;
				}
				$result[$param] = $param;
			}
		}
		return $result;
	}

	/**
	 * Разбирает часть запроса
	 *
	 * @param string $key
	 * @param string $value
	 * @return mixed
	 */
	protected function parsePart($key, $value)
	{
		$result = array();
		$matches = array();
		$regexp = '#"?([^",\= ]+)"?(?:\s+)?(?:\=(?:\s+)?'.
			'(\d+|"[^"]+"|{[^}]+(?:(}|(?:\s*)?)++)))?#';
		preg_match_all($regexp, $value, $matches);
		foreach ($matches[1] as $i => $key) {
			if ($matches[2][$i] === '') {
				$matches[2][$i] = $key;
			}
			$value = trim($matches[2][$i], '\'" ');
			$key = trim($key, '\'" ');
			if (strpos($value, '{') === 0 &&
				$value[strlen($value) - 1] == '}') {
				$value = substr($value, 1, -1);
				$result[$key] = $this->parsePart($key, $value);
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}
    
    /**
     * Изменить рефлексию класса
     * 
     * @param ReflectionClass $reflection
     */
    public function setReflection($reflection)
    {
        $this->reflection = $reflection;
    }
}
