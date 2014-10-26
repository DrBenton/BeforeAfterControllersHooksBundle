<?php

/*
 * This file is heavily inspired by SensioFrameworkExtraBundle, part of the Symfony package.
 *
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Tests%2FEventListener%2FControllerListenerTest.php
 */

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener\ControllerListener;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\FooControllerBeforeAtMethod;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\TestService;

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
    /** @var Container **/
    protected $container;
    /** @var ControllerListener **/
    protected $listener;
    /** @var Request **/
    protected $request;
    /** @var HttpKernel **/
    protected $kernel;

    public function setUp()
    {
        $this->container = new Container();
        $this->kernel = new HttpKernel(new EventDispatcher(), new ControllerResolver());
        $this->listener = new ControllerListener(new AnnotationReader());
        $this->listener->setContainer($this->container);
        $this->request = new Request();

        // trigger the autoloading of our Controllers hooks annotation
        class_exists('DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook');
    }

    public function tearDown()
    {
        $this->container = null;
        $this->kernel = null;
        $this->listener = null;
        $this->request = null;
    }

    public function testSelfContainedBeforeAnnotationAtMethodWithoutReturnedResponse()
    {
        $controller = $this->triggerControllerAction('selfContainedPreHookActionWithoutHookResponseAction');

        $this->assertCount(1, $controller->beforeHooksResults, 'Controller @BeforeHook callback should have been triggered');

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
        $controller = $this->triggerControllerAction('selfContainedMultipleBeforeHooksActionWithoutHookResponseAction');

        $this->assertCount(2, $controller->beforeHooksResults, 'Two Controllers @BeforeHook callbacks should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been short-circuited by the @BeforeHook');
    }

    public function testSelfContainedMultipleBeforeAnnotationsAtMethodWithReturnedResponse()
    {
        $controller = $this->triggerControllerAction('selfContainedMultipleBeforeHooksActionWithHookResponseAction');

        $this->assertCount(2, $controller->beforeHooksResults, 'Only two Controllers @BeforeHook callbacks should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse', $response->getContent(), 'Controller should not have been triggered');
    }

    public function testServiceCallAnnotationAtMethod()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction('serviceBeforeHookAction');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('serviceBeforeHook', $response->getContent(), 'Service @BeforeHook should have short-circuited the Controller');
    }

    public function testAnnotationWithArgsAtMethod()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction('serviceBeforeHookWithArgsAction');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('serviceBeforeHook; args=["test1",{"key":"value"}]', $response->getContent(), 'Service @BeforeHook should have short-circuited the Controller with args');
    }

    /**
     * @param string $actionName
     * @return FooControllerBeforeAtMethod
     */
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
        return new FilterControllerEvent($this->kernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    protected function getKernelResponse(/** callable */  $targetAction)
    {
        $this->request->attributes->set('_controller', $targetAction);

        return $this->kernel->handle($this->request);
    }

    protected function initTestService()
    {
        $this->container->set('testService', new TestService());
    }
}