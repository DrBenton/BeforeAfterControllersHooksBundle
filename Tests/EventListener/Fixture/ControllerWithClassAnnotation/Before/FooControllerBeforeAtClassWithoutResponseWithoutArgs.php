<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @BeforeHook("beforeHookWithoutReturnedResponse")
 */
class FooControllerBeforeAtClassWithoutResponseWithoutArgs
{
    public $beforeHooksResults = array();

    public function testAction()
    {
        return new Response('controllerResponse');
    }

    public function beforeHookWithoutReturnedResponse()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';
    }
}