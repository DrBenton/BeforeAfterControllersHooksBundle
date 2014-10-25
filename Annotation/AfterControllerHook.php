<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Annotation
 */
class AfterControllerHook extends ControllerHookAnnotationBase
{
    public function triggerControllerHook(Response $response)
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

        $hookArgs = $this->targetCallableArgs;
        array_unshift($hookArgs, $response);

        return call_user_func_array($callable, $hookArgs);
    }
}
