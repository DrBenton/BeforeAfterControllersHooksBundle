<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @BeforeHook("@testService::beforeHookWithResponseWithArgs", args={"test1", {"key": "value"}})
 */
class FooControllerBeforeAtClassServiceCallWithoutResponseWithArgs
{
    public function testAction()
    {
        return new Response('controllerResponse');
    }
}