<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Annotation
 */
class AfterControllerHook extends ControllerHookAnnotationBase
{
    public function triggerControllerHook(Response $response)
    {
        $targetCallable = $this->resolveTargetCallable();

        $callableArgs = $this->targetCallableArgs;
        array_unshift($callableArgs, $response);

        return call_user_func_array($targetCallable, $callableArgs);
    }
}
