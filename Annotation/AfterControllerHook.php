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
        // "%response%" args are replaced with our Symfony Response
        foreach ($callableArgs as $index => $arg) {
            if ('%response%' === $arg) {
                $callableArgs[$index] = $response;
            }
        }

        if (!is_callable($targetCallable)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid @After callback %s!', json_encode($this->annotationParams))
            );
        }

        return call_user_func_array($targetCallable, $callableArgs);
    }
}
