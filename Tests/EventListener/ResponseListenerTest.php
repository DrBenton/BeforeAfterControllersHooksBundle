<?php

/*
 * This file is heavily inspired by SensioFrameworkExtraBundle, part of the Symfony package.
 *
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Tests%2FEventListener%2FControllerListenerTest.php
 */

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\EventListener\ResponseListener;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\FooControllerAfterAtMethod;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\After\FooControllerAfterAtClassWithoutResponseModificationWithoutArgs;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\After\FooControllerAfterAtClassWithResponseModificationWithoutArgs;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\After\FooControllerAfterAtClassServiceCallWithoutResponseModificationWithoutArgs;
use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\TestService;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Container **/
    protected $container;
    /** @var ResponseListener **/
    protected $listener;
    /** @var Request **/
    protected $request;
    /** @var HttpKernel **/
    protected $kernel;
    /** @var FilterResponseEvent **/
    protected $event;

    public function setUp()
    {
        $this->container = new Container();
        $this->kernel = new HttpKernel(new EventDispatcher(), new ControllerResolver());
        $this->listener = new ResponseListener(new AnnotationReader());
        $this->listener->setContainer($this->container);
        $this->request = new Request();

        // trigger the autoloading of our Controllers hooks annotation
        class_exists('DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook');
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
     * Response modification: N - Args : N - Service call : N
     */
    public function testSelfContainedAfterAnnotationAtMethodWithoutResponseModification()
    {
        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtMethod(),
            'selfContainedAfterHookActionWithoutResponseModificationAction'
        );

        $this->assertCount(1, $controller->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered, without response modification');
    }

    /**
     * Response modification: Y - Args : N - Service call : N
     */
    public function testSelfContainedAfterAnnotationAtMethodWithResponseModification()
    {
        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtMethod(),
            'selfContainedAfterHookActionWithResponseModificationAction'
        );

        $this->assertCount(1, $controller->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse + hookResponse', $response->getContent(), 'Controller response should have been modified');
    }

    /**
     * Response modification: Y - Args : N - Service call : Y
     */
    public function testSelfContainedAfterAnnotationAtMethodWithoutResponseModificationWithArgs()
    {
        $this->initTestService();

        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtMethod(),
            'serviceAfterHookWithoutResponseModificationWithArgsAction'
        );

        $this->assertCount(1, $this->container->get('test_service')->afterHooksResults, 'Service hook should have been triggered');

        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered, without response modification');
        $this->assertEquals('afterHookTriggered: args=["test1",{"key":"value"}]', $this->container->get('test_service')->afterHooksResults[0], 'Service hook should have been triggered with args');
    }

    /**
     * Response modification: N - Args : N - Service call : Y
     */
    public function testServiceCallAnnotationAtMethodWithoutResponseModification()
    {
        $this->initTestService();

        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtMethod(),
            'serviceAfterHookWithoutResponseModificationAction'
        );

        $this->assertCount(1, $this->container->get('test_service')->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered, without response modification');
    }

    /**
     * Response modification: Y - Args : N - Service call : Y
     */
    public function testServiceCallAnnotationAtMethodWithResponseModification()
    {
        $this->initTestService();

        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtMethod(),
            'serviceAfterHookWithResponseModificationAction'
        );

        $this->assertEquals('controllerResponse + serviceHookResponse', $response->getContent(), 'Controller response should have been modified by a Service hook');
    }

    /**
     * Response modification: N - Args : Y - Service call : Y
     */
    public function testServiceCallAnnotationAtMethodWithoutResponseModificationWithArgs()
    {
        $this->initTestService();

        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtMethod(),
            'serviceAfterHookWithoutResponseModificationWithArgsAction'
        );

        $this->assertCount(1, $this->container->get('test_service')->afterHooksResults, 'Controller @AfterHook callback should have been triggered with args');

        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered, without response modification');
        $this->assertEquals('afterHookTriggered: args=["test1",{"key":"value"}]', $this->container->get('test_service')->afterHooksResults[0], 'Service hook should have been triggered with args');
    }

    /**
     * Response modification: Y - Args : Y - Service call : Y
     */
    public function testServiceCallWithResponseAnnotationAtMethodWithArgs()
    {
        $this->initTestService();

        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtMethod(),
            'serviceAfterHookWithResponseModificationWithArgsAction'
        );

        $this->assertEquals('controllerResponse + serviceHookResponse; args=["test1",{"key":"value"}]', $response->getContent(), 'Controller response should have been modified by a Service hook with args');
    }

    /**********************************
     * Class Annotations tests
     *********************************/

    /**
     * Response modification: N - Args : N - Service call : N
     */
    public function testSelfContainedAfterAnnotationAtClassWithoutResponseModification()
    {
        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtClassWithoutResponseModificationWithoutArgs(),
            'testAction'
        );

        $this->assertCount(1, $controller->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
    }

    /**
     * Response modification: Y - Args : N - Service call : N
     */
    public function testSelfContainedAfterAnnotationAtClassWithResponseModification()
    {
        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtClassWithResponseModificationWithoutArgs(),
            'testAction'
        );

        $this->assertCount(1, $controller->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse + hookResponse', $response->getContent(), 'Controller response should have been modified');
    }

    /**
     * Response modification: N - Args : N - Service call : Y
     */
    public function testServiceCallAnnotationAtClassWithoutResponseModification()
    {
        $this->initTestService();

        list($controller, $response) = $this->triggerControllerAction(
            new FooControllerAfterAtClassServiceCallWithoutResponseModificationWithoutArgs(),
            'testAction'
        );

        $this->assertCount(1, $this->container->get('test_service')->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered, without response modification');
    }

    /**
     * @param object $controllerInstance
     * @param string $actionName
     * @return [object, Response]
     */
    protected function triggerControllerAction($controllerInstance, $actionName)
    {
        $targetAction = array($controllerInstance, $actionName);

        $this->request->attributes->set('_controller', $targetAction);
        $response = $this->kernel->handle($this->request);

        $this->event = $this->getFilterResponseEvent($this->request, $response);
        $this->listener->onKernelResponse($this->event);

        return array($controllerInstance, $response);
    }

    protected function getFilterResponseEvent(Request $request, Response $response)
    {
        return new FilterResponseEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);
    }

    protected function initTestService()
    {
        $this->container->set('test_service', new TestService());
    }
}