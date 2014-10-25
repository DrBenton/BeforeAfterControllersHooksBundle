<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\EventListener;

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
        if (is_array($controller)) {
            $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
            $object    = new \ReflectionClass($className);
            $method    = $object->getMethod($controller[1]);

            $controllerAnnotations = $this->annotationReader->getMethodAnnotations($method);
        } else {
            //TODO: handle non OOP controllers?
        }

        return $controllerAnnotations;
    }
} 