<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerListener extends ListenerBase implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $controllerAnnotations = $this->getControllerAnnotations($controller);

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
        $beforeHooks = array();
        foreach ($controllerAnnotations as $annotation) {
            if ($annotation instanceof BeforeHook) {
                $beforeHooks[] = $annotation;
            }
        }

        /** @var BeforeHook $beforeHook */
        foreach ($beforeHooks as $beforeHook) {
            $beforeHook->setContainer($this->container);
            $beforeHook->setController($controller);

            $controllerHookResult = $beforeHook->triggerControllerHook();

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
