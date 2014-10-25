<?php

/*
 * This file is heavily inspired by SensioFrameworkExtraBundle, part of the Symfony package.
 *
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Tests%2FEventListener%2FControllerListenerTest.php
 */

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener;

use Rougemine\Bundle\BeforeAfterControllersHooksBundle\EventListener\ControllerListener;
use Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\FooControllerBeforeAtMethod;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ControllerListener **/
    protected $listener;
    /** @var Request **/
    protected $request;
    /** @var HttpKernel **/
    protected $kernel;

    public function setUp()
    {
        $this->container = new Container();
        $this->listener = new ControllerListener(new AnnotationReader());
        $this->request = new Request();

        // trigger the autoloading of our Controllers hooks annotation
        class_exists('Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook');
    }

    public function tearDown()
    {
        $this->listener = null;
        $this->request = null;
    }

    public function testSelfContainedBeforeAnnotationAtMethodWithoutReturnedResponse()
    {
        $controller = $this->triggerControllerAction('selfContainedPreHookActionWithoutHookResponseAction');

        $this->assertCount(1, $controller->preHooksResults, 'Controller hook should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
    }

    public function testSelfContainedBeforeAnnotationAtMethodWithReturnedResponse()
    {
        $controller = $this->triggerControllerAction('selfContainedPreHookActionWithHookResponseAction');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse', $response->getContent(), 'Controller should not have been triggered');
    }

    public function testSelfContainedMultipleBeforeAnnotationsAtMethodWithoutReturnedResponse()
    {
        $controller = $this->triggerControllerAction('selfContainedMultiplePreHooksActionWithoutHookResponseAction');

        $this->assertCount(2, $controller->preHooksResults, 'Two Controllers hooks should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should not have been triggered');
    }

    public function testSelfContainedMultipleBeforeAnnotationsAtMethodWithReturnedResponse()
    {
        $controller = $this->triggerControllerAction('selfContainedMultiplePreHooksActionWithHookResponseAction');

        $this->assertCount(2, $controller->preHooksResults, 'Only two Controllers hooks should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse', $response->getContent(), 'Controller should not have been triggered');
    }

    protected function triggerControllerAction($actionName)
    {
        $controller = new FooControllerBeforeAtMethod();
        $targetAction = array($controller, $actionName);

        $this->event = $this->getFilterControllerEvent($targetAction, $this->request);
        $this->listener->onKernelController($this->event);

        return $controller;
    }

    protected function getFilterControllerEvent($controller, Request $request)
    {
        $this->kernel = new HttpKernel(new EventDispatcher(), new ControllerResolver());

        return new FilterControllerEvent($this->kernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    protected function getKernelResponse(/** callable */  $targetAction)
    {
        $this->request->attributes->set('_controller', $targetAction);

        return $this->kernel->handle($this->request);
    }
}