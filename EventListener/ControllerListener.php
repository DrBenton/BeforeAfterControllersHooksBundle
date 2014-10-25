<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerListener extends ContainerAware implements EventSubscriberInterface
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
            $object    = new \ReflectionClass($className);
            $method    = $object->getMethod($controller[1]);

            $controllerAnnotations = $this->annotationReader->getMethodAnnotations($method);
        } else {
            //TODO: handle non OOP controllers?
        }

        if (empty($controllerAnnotations)) {
            return;
        }

        $controllerAnnotationsResult = $this->handleControllerBeforeHooksAnnotations($controller, $controllerAnnotations);

        if ($controllerAnnotationsResult instanceof Response) {
            $this->changeSymfonyController($event, $controllerAnnotationsResult);
        }
    }

    /**
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController'
        );
    }

    protected function handleControllerBeforeHooksAnnotations($controller, array $controllerAnnotations)
    {
        $preHooks = array();
        foreach ($controllerAnnotations as $annotation) {
            if ($annotation instanceof BeforeHook) {
                $preHooks[] = $annotation;
            }
        }

        /** @var BeforeHook $preHook */
        foreach ($preHooks as $preHook) {
            $preHook->setContainer($this->container);
            $preHook->setController($controller);

            $controllerHookResult = $preHook->triggerControllerHook();

            if ($controllerHookResult instanceof Response) {
                // Hey, seems that this Controller hook wants to bypass its Controller call!
                // --> let's return this HTTP Response!
                return $controllerHookResult;
            }
        }
    }

    protected function changeSymfonyController(FilterControllerEvent $event, Response $hookReturnedResponse)
    {
        $event->setController(function () use ($hookReturnedResponse) {
            return $hookReturnedResponse;
        });
    }
}
