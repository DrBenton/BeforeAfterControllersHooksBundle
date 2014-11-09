<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerAware;

abstract class ListenerBase extends ContainerAware
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param mixed $controller
     * @return array
     */
    protected function getControllerAnnotations($controller)
    {
        if (!is_array($controller)) {
            return array();//We only handle OOP Controllers for the moment...
        }

        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
        $object    = new \ReflectionClass($className);
        $method    = $object->getMethod($controller[1]);

        $classAnnotations = $this->annotationReader->getClassAnnotations($object);
        $controllerAnnotations = $this->annotationReader->getMethodAnnotations($method);

        return array_merge(
            $classAnnotations,
            $controllerAnnotations
        );
    }
} 