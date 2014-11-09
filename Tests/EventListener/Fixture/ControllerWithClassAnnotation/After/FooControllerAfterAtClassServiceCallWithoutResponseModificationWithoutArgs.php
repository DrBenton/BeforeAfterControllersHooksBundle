<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\After;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook as AfterHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @AfterHook("@test_service::afterHook")
 */
class FooControllerAfterAtClassServiceCallWithoutResponseModificationWithoutArgs
{
    public function testAction()
    {
        return new Response('controllerResponse');
    }
}