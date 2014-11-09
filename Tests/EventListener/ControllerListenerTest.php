<?php

/*
 * This file is heavily inspired by SensioFrameworkExtraBundle, part of the Symfony package.
 *
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Tests%2FEventListener%2FControllerListenerTest.php
 */

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener\ControllerListener;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\FooControllerBeforeAtMethod;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before\FooControllerBeforeAtClassWithoutResponseWithoutArgs;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before\FooControllerBeforeAtClassWithResponseWithoutArgs;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before\FooControllerBeforeAtClassWithResponseWithArgs;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before\FooControllerBeforeAtClassServiceCallWithoutResponseWithoutArgs;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before\FooControllerBeforeAtClassServiceCallWithoutResponseWithArgs;
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
    /** @var FilterControllerEvent **/
    protected $event;

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
        $this->event = null;
    }

    /**********************************
     * Methods Annotations tests
     *********************************/

    /**
     * Controller short-circuiting: N - Args : N - Service call : N
     */
    public function testSelfContainedBeforeAnnotationAtMethodWithoutReturnedResponse()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'selfContainedPreHookActionWithoutHookResponseAction'
        );

        $this->assertCount(1, $controller->beforeHooksResults, 'Controller @BeforeHook callback should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
    }

    /**
     * Controller short-circuiting: Y - Args : N - Service call : N
     */
    public function testSelfContainedBeforeAnnotationAtMethodWithReturnedResponse()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'selfContainedPreHookActionWithHookResponseAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse', $response->getContent(), 'Controller should not have been triggered');
    }

    /**
     * Controller short-circuiting: N - Args : Y - Service call : N
     */
    public function testSelfContainedBeforeAnnotationAtMethodWithoutReturnedResponseWithArgs()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'selfContainedBeforeHooksActionWithoutHookResponseWithArgsAction'
        );

        $this->assertCount(1, $controller->beforeHooksResults, 'Controller @BeforeHook callback should have been triggered');
        $this->assertEquals('beforeHookTriggered: Hi there!', $controller->beforeHooksResults[0], 'Controller @BeforeHook callback should have received args');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
    }

    /**
     * Controller short-circuiting: Y - Args : Y - Service call : N
     */
    public function testSelfContainedBeforeAnnotationAtMethodWithReturnedResponseWithArgs()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'selfContainedPreHookActionWithHookResponseWithArgsAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse: Hi there!', $response->getContent(), 'Controller should not have been triggered, args should have been received');
    }

    /**
     * Controller short-circuiting: N - Args : N - Service call : Y
     */
    public function testServiceCallAnnotationAtMethod()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'serviceBeforeHookAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
        $this->assertCount(1, $this->container->get('test_service')->beforeHooksResults, 'Service should have been used');
    }

    /**
     * Controller short-circuiting: N - Args : Y - Service call : Y
     */
    public function testServiceCallAnnotationWithArgsAtMethod()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'serviceBeforeHookWithArgsAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
        $this->assertCount(1, $this->container->get('test_service')->beforeHooksResults, 'Service should have been used');
        $this->assertEquals('beforeHookTriggered: args=["test1",{"key":"value"}]', $this->container->get('test_service')->beforeHooksResults[0], 'Service @BeforeHook should have been used with args');
    }

    /**
     * Controller short-circuiting: Y - Args : N - Service call : Y
     */
    public function testServiceCallAnnotationWithResponseAtMethod()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'serviceBeforeHookWithResponseAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('serviceBeforeHook', $response->getContent(), 'Service @BeforeHook should have short-circuited the Controller');
    }

    /**
     * Controller short-circuiting: Y - Args : Y - Service call : Y
     */
    public function testServiceCallAnnotationWithResponseWithArgsAtMethod()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'serviceBeforeHookWithResponseWithArgsAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('serviceBeforeHook; args=["test1",{"key":"value"}]', $response->getContent(), 'Service @BeforeHook should have short-circuited the Controller with args');
    }

    /**
     * Controller short-circuiting: N - Args : N - Service call : N - Multiple Annotations
     */
    public function testSelfContainedMultipleBeforeAnnotationsAtMethodWithoutReturnedResponse()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'selfContainedMultipleBeforeHooksActionWithoutHookResponseAction'
        );

        $this->assertCount(2, $controller->beforeHooksResults, 'Two Controllers @BeforeHook callbacks should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
    }

    /**
     * Controller short-circuiting: Y - Args : N - Service call : N - Multiple Annotations
     */
    public function testSelfContainedMultipleBeforeAnnotationsAtMethodWithReturnedResponse()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtMethod(),
            'selfContainedMultipleBeforeHooksActionWithHookResponseAction'
        );

        $this->assertCount(2, $controller->beforeHooksResults, 'Only two Controllers @BeforeHook callbacks should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse', $response->getContent(), 'Controller should not have been triggered');
    }

    /**********************************
     * Class Annotations tests
     *********************************/

    /**
     * Controller short-circuiting: N - Args : N - Service call : N
     */
    public function testSelfContainedBeforeAnnotationAtClassWithoutReturnedResponseWithoutArgs()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtClassWithoutResponseWithoutArgs(),
            'testAction'
        );

        $this->assertCount(1, $controller->beforeHooksResults, 'Controller @BeforeHook callback should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
    }

    /**
     * Controller short-circuiting: Y - Args : N - Service call : N
     */
    public function testSelfContainedBeforeAnnotationAtClassWithReturnedResponseWithoutArgs()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtClassWithResponseWithoutArgs(),
            'testAction'
        );

        $this->assertCount(1, $controller->beforeHooksResults, 'Controller @BeforeHook callback should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse', $response->getContent(), 'Controller should not have been triggered');
    }

    /**
     * Controller short-circuiting: Y - Args : Y - Service call : N
     */
    public function testSelfContainedBeforeAnnotationAtClassWithReturnedResponseWithArgs()
    {
        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtClassWithResponseWithArgs(),
            'testAction'
        );

        $this->assertCount(1, $controller->beforeHooksResults, 'Controller @BeforeHook callback should have been triggered');

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('hookResponse: Good morning Mr. Phelps.', $response->getContent(), 'Controller should not have been triggered');
    }

    /**
     * Controller short-circuiting: Y - Args : N - Service call : Y
     */
    public function testServiceCallAnnotationAtClass()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtClassServiceCallWithoutResponseWithoutArgs(),
            'testAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('serviceBeforeHook', $response->getContent(), 'Service @BeforeHook should have short-circuited the Controller');
    }

    /**
     * Controller short-circuiting: Y - Args : Y - Service call : Y
     */
    public function testServiceCallAnnotationWithArgsAtClass()
    {
        $this->initTestService();

        $controller = $this->triggerControllerAction(
            new FooControllerBeforeAtClassServiceCallWithoutResponseWithArgs(),
            'testAction'
        );

        $response = $this->getKernelResponse($this->event->getController());
        $this->assertEquals('serviceBeforeHook; args=["test1",{"key":"value"}]', $response->getContent(), 'Service @BeforeHook should have short-circuited the Controller with args');
    }

    /**
     * @param object $controllerInstance
     * @param string $actionName
     * @return FooControllerBeforeAtMethod
     */
    protected function triggerControllerAction($controllerInstance, $actionName)
    {
        $targetAction = array($controllerInstance, $actionName);

        $this->event = $this->getFilterControllerEvent($targetAction, $this->request);
        $this->listener->onKernelController($this->event);

        return $controllerInstance;
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
        $this->container->set('test_service', new TestService());
    }
}