<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @BeforeHook("@testService::beforeHookWithResponse")
 */
class FooControllerBeforeAtClassServiceCallWithoutResponseWithoutArgs
{
    public function testAction()
    {
        return new Response('controllerResponse');
    }
}