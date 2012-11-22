<?php

/**
 * Состояние сервиса
 *
 * @author morph
 */
class Service_State
{
    /**
     * Имя класса услуги
     *
     * @var string
     */
    protected $className;

    /**
     * Рефлексия класса услуги
     *
     * @var ReflectionClass
     */
    protected $classReflection;

    /**
     * Обработчик нового вызова сервиса
     *
     * @var array
     */
    protected $instanceCallback;

    /**
     * Рефлексии методов класса
     *
     * @var array
     */
    protected $methodReflections;

    /**
     * Экземпляр объекта
     *
     * @var mixed
     */
    protected $object;

    /**
     * Конструктор
     *
     * @param mixed $object
     */
    public function __construct($object, $className, $instanceCallback)
    {
        $this->object = $object;
        $this->className = $className;
        $this->instanceCallback = $instanceCallback;
        $this->classReflection = new ReflectionClass($className);
    }

    /**
     * Вызвать метод
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if ($this->instanceCallback) {
            $result = call_user_func_array(
                $this->instanceCallback[0], $this->instanceCallback[1]
            );
            if ($this->object) {
                $this->object = $result;
            }
        }
        if (!$this->methodReflections) {
            $this->methodReflections = $this->classReflection->getMethods();
        }
        $isStatic = false;
        if (isset($this->methodReflections[$method])) {
            $reflectionMethod = $this->methodReflections[$method];
            $isStatic = $reflectionMethod->isStatic();
        }
        if ($this->object) {
            if (!$this->classReflection->isAbstract() && !$isStatic) {
                return call_user_func_array(
                    array($this->object, $method), $args
                );
            }
        }
        return call_user_func_array(array($this->className, $method), $args);
    }
}