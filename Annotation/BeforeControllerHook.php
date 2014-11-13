<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation;

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

        if (!is_callable($targetCallable)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid @Before callback %s!', json_encode($this->annotationParams))
            );
        }

        return call_user_func_array($targetCallable, $callableArgs);
    }
}
