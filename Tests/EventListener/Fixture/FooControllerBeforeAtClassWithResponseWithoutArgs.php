<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @BeforeHook("beforeHookWithReturnedResponse")
 */
class FooControllerBeforeAtClassWithResponseWithoutArgs
{
    public $beforeHooksResults = array();

    public function testAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    public function beforeHookWithReturnedResponse()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';

        return new Response('hookResponse');
    }
}