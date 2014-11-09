<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture\ControllerWithClassAnnotation\After;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook as AfterHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @AfterHook("afterHook")
 */
class FooControllerAfterAtClassWithoutResponseModificationWithoutArgs
{
    public $afterHooksResults = array();

    public function testAction()
    {
        return new Response('controllerResponse');
    }

    public function afterHook()
    {
        $this->afterHooksResults[] = 'afterHookTriggered';
    }
}