<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\Before;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @BeforeHook("@test_service::beforeHookWithResponse")
 */
class FooControllerBeforeAtClassServiceCallWithoutResponseWithoutArgs
{
    public function testAction()
    {
        return new Response('controllerResponse');
    }
}