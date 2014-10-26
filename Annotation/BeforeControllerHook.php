<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation;

/**
 * @Annotation
 */
class BeforeControllerHook extends ControllerHookAnnotationBase
{
    /**
     * @return mixed the Controller "before hook" result; if it's a Symfony Response, the target Controller will be short-circuited
     */
    public function triggerControllerHook()
    {
        $targetCallable = $this->resolveTargetCallable();

        $callableArgs = $this->targetCallableArgs;

        return call_user_func_array($targetCallable, $callableArgs);
    }
}
