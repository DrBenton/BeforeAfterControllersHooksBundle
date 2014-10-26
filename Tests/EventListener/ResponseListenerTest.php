<?php

/*
 * This file is heavily inspired by SensioFrameworkExtraBundle, part of the Symfony package.
 *
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/master/Tests%2FEventListener%2FControllerListenerTest.php
 */

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener;

use Rougemine\Bundle\BeforeAfterControllersHooksBundle\EventListener\ControllerListener;
use Rougemine\Bundle\BeforeAfterControllersHooksBundle\EventListener\ResponseListener;
use Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\FooControllerAfterAtMethod;

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

    public function setUp()
    {
        $this->container = new Container();
        $this->kernel = new HttpKernel(new EventDispatcher(), new ControllerResolver());
        $this->listener = new ResponseListener(new AnnotationReader());
        $this->listener->setContainer($this->container);
        $this->request = new Request();

        // trigger the autoloading of our Controllers hooks annotation
        class_exists('Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook');
    }

    public function tearDown()
    {
        $this->container = null;
        $this->kernel = null;
        $this->listener = null;
        $this->request = null;
    }

    public function testSelfContainedAfterAnnotationAtMethodWithoutResponseModification()
    {
        list($controller, $response) = $this->triggerControllerAction('selfContainedAfterHookActionWithoutResponseModificationAction');

        $this->assertCount(1, $controller->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse', $response->getContent(), 'Controller should have been triggered');
    }

    public function testSelfContainedAfterAnnotationAtMethodWithResponseModification()
    {
        list($controller, $response) = $this->triggerControllerAction('selfContainedAfterHookActionWithResponseModificationAction');

        $this->assertCount(1, $controller->afterHooksResults, 'Controller @AfterHook callback should have been triggered');

        $this->assertEquals('controllerResponse + hookResponse', $response->getContent(), 'Controller response should have been modified');
    }

    public function testServiceCallAnnotationAtMethod()
    {
        $this->container->set('testService', 'Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\TestService');

        list($controller, $response) = $this->triggerControllerAction('serviceAfterHookAction');

        $this->assertEquals('controllerResponse + serviceHookResponse', $response->getContent(), 'Controller response should have been modified by a Service hook');
    }

    public function testAnnotationWithArgsAtMethod()
    {
        $this->container->set('testService', 'Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\TestService');

        list($controller, $response) = $this->triggerControllerAction('serviceAfterHookWithArgsAction');

        $this->assertEquals('controllerResponse + serviceHookResponse; args=["test1",{"key":"value"}]', $response->getContent(), 'Controller response should have been modified by a Service hook with args');
    }

    /**
     * @param string $actionName
     * @return FooControllerAfterAtMethod
     */
    protected function triggerControllerAction($actionName)
    {
        $controller = new FooControllerAfterAtMethod();
        $targetAction = array($controller, $actionName);

        $this->request->attributes->set('_controller', $targetAction);
        $response = $this->kernel->handle($this->request);

        $this->event = $this->getFilterResponseEvent($this->request, $response);
        $this->listener->onKernelResponse($this->event);

        return array($controller, $response);
    }

    protected function getFilterResponseEvent(Request $request, Response $response)
    {
        return new FilterResponseEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);
    }
}