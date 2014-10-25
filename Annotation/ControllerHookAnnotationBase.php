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
}
