<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook as AfterHook;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener extends ListenerBase implements EventSubscriberInterface
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $controller = $event->getRequest()->attributes->get('_controller');
        $controllerAnnotations = $this->getControllerAnnotations($controller);

        if (empty($controllerAnnotations)) {
            return;
        }

        $response = $event->getResponse();
        $this->handleControllerAfterHooksAnnotations($controller, $controllerAnnotations, $response);
    }

    /**
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse'
        );
    }

    protected function handleControllerAfterHooksAnnotations($controller, array $controllerAnnotations, Response $response)
    {
        $afterHooks = array();
        foreach ($controllerAnnotations as $annotation) {
            if ($annotation instanceof AfterHook) {
                $afterHooks[] = $annotation;
            }
        }

        /** @var AfterHook $afterHook */
        foreach ($afterHooks as $afterHook) {
            $afterHook->setContainer($this->container);
            $afterHook->setController($controller);

            $afterHook->triggerControllerHook($response);
        }
    }
}
