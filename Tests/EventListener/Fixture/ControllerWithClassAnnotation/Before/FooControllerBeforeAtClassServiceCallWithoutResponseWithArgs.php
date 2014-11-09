<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @BeforeHook("@test_service::beforeHookWithResponseWithArgs", args={"test1", {"key": "value"}})
 */
class FooControllerBeforeAtClassServiceCallWithoutResponseWithArgs
{
    public function testAction()
    {
        return new Response('controllerResponse');
    }
}