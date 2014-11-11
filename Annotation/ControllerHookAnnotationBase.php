<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation;

use Symfony\Component\DependencyInjection\ContainerAware;

abstract class ControllerHookAnnotationBase extends ContainerAware
{
    /**
     * @var array
     */
    protected $annotationParams;
    /**
     * @var object an instance of of Symfony Controller class
     */
    protected $controller;
    /**
     * @var string
     */
    protected $targetCallable;
    /**
     * @var array
     */
    protected $targetCallableArgs = array();

    /**
     * @param  array                     $annotationParams
     * @throws \InvalidArgumentException
     */
    public function __construct(array $annotationParams)
    {
        $this->annotationParams = $annotationParams;

        if (isset($annotationParams['value'])) {
            $this->targetCallable = $annotationParams['value'];
        } elseif (isset($annotationParams['target'])) {
            $this->targetCallable = $annotationParams['target'];
        }

        if (null === $this->targetCallable) {
            throw new \InvalidArgumentException(sprintf(
               'Invalid Before/After Annotation usage! Please provide a "value" or "target" parameter. Params received: %s',
                json_encode($annotationParams)
            ));
        }

        if (isset($annotationParams['args'])) {
            $this->targetCallableArgs = $annotationParams['args'];
        }
    }

    public function setController($controller)
    {
        $this->controller= $controller;
    }

    /**
     * @return callable
     * @throws \InvalidArgumentException
     */
    protected function resolveTargetCallable()
    {
        if ('@' == $this->targetCallable[0]) {
            // The target is a "@serviceId::method" string
            list($serviceId, $serviceMethodName) = explode('::', $this->targetCallable);
            $serviceId = substr($serviceId, 1);// leading "@" removal

            if (!$this->container->has($serviceId)) {
                throw new \InvalidArgumentException(sprintf(
                    'No Symfony service found with id "%s" in "%s" Controller Hook Annotation',
                    $serviceId, json_encode($this->annotationParams)
                ));
            }

            $callable = array($this->container->get($serviceId), $serviceMethodName);
        } else {
            // The target is a method name of the Controller itself
            $callable = array($this->controller[0], $this->targetCallable);
        }

        return $callable;
    }
}
