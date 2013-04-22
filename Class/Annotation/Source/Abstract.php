<?php

/**
 * Абстрактный источник аннотаций
 *
 * @author morph, goorus
 */
abstract class Annotation_Source_Abstract
{
    /**
     * Имя класса текущей аннотации
     * 
     * @var string
     */
    protected $className;
    
	/**
	 * Получить набор аннотаций класса
	 *
	 * @param \StdClass $class
	 * @return \Ruon\Annotation\AnnotationSet
	 */
	final public function get($class)
	{
        $this->className = is_object($class) ? get_class($class) : $class;
		$classAnnotation = $this->getClass($class);
		$methodAnnotations = $this->getMethods($class);
		$propertyAnnotations = $this->getProperties($class);
		$annotationSet = new Annotation_Set(
			$classAnnotation, $methodAnnotations, $propertyAnnotations
		);
		return $annotationSet;
	}

	/**
	 * Распарсить аннотацию класса
	 *
	 * @param \StdClass $class
	 * @return array
	 */
	abstract protected function getClass($class);

	/**
	 * Распарсить аннотации методов класса
	 *
	 * @param \StdClass $class
	 * @return array
	 */
	abstract protected function getMethods($class);

	/**
	 * Распарсить аннотации полей класса
	 *
	 * @param \StdClass $class
	 * @return array
	 */
	abstract protected function getProperties($class);
}