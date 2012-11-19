<?php

/**
 * Менеджер опций
 *
 * @author morph, goorus
 */
class Model_Collection_Option_Manager
{
    /**
     * Выполняет опции коллекции
     *
     * @param Model_Collection $collection
     * @param Query_Abstract $query
     * @param array $options
     */
	protected static function execute($method, $collection, $options)
    {
        foreach ($options as $option) {
            $data = self::normalize($option);
            if (!$data) {
                continue;
            }
            $option = Model_Option::create($data[0], $collection, $data[1]);
            $option->query = $collection->query();
            call_user_func(array($option, $method));
        }
    }

    /**
     * Выполняет часть опций after
     *
     * @param Model_Collection $collection
     * @param array $options
     */
    public static function executeAfter($collection, $options)
    {
        self::execute('after', $collection, $options);
    }

    /**
     * Выполняет часть опций before
     *
     * @param Model_Collection $collection
     * @param array $options
     */
    public static function executeBefore($collection, $options)
    {
        self::execute('before', $collection, $options);
    }

    /**
     * Приводит опцию к стандартной форме
     *
     * @param mixed $option
     * @return array
     */
    protected static function normalize($option)
    {
        if (is_string($option)) {
            return array($option, null);
        }
        if (is_array($option)) {
            if (isset($option['name'])) {
                $name = $option['name'];
                unset($option['name']);
                return array($name, $option);
            }
            if (isset($option[0]) && is_string($option[0])) {
                return array(
                    $option[0], isset($option[1]) ? $option[1] : null
                );
            }
        }
    }
}