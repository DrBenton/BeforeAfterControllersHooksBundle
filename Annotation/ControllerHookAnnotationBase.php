<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation;

use Symfony\Component\DependencyInjection\ContainerAware;

abstract class ControllerHookAnnotationBase extends ContainerAware
{
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
        if (isset($annotationParams['value'])) {
            $this->targetCallable = $annotationParams['value'];
        } elseif (isset($annotationParams['target'])) {
            $this->targetCallable = $annotationParams['target'];
        }

        if (null === $this->targetCallable) {
            throw new \InvalidArgumentException(sprintf(
               'Invalid Pre/Post Annotation usage! Please provide a "value" or "target" parameter. Params received: %s',
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
     * @return mixed the Controller hook result
     */
    public function triggerControllerHook()
    {
        if ('@' == $this->targetCallable[0]) {
            // The target is a "@serviceId::method" string
            list($serviceId, $serviceMethodName) = explode('::', $this->targetCallable);
            $serviceId = substr($serviceId, 1);// leading "@" removal
            $callable = array($this->container->get($serviceId), $serviceMethodName);
        } else {
            // The target is a method name of the Controller itself
            $callable = array($this->controller[0], $this->targetCallable);
        }

        return call_user_func_array($callable, $this->targetCallableArgs);
    }
}
