<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

/**
 * @BeforeHook("beforeHookWithReturnedResponseWithArgs", args={"hello": "Good morning Mr. Phelps."})
 */
class FooControllerBeforeAtClassWithResponseWithArgs
{
    public $beforeHooksResults = array();

    public function testAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    public function beforeHookWithReturnedResponseWithArgs($hello)
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';

        return new Response('hookResponse: '.$hello);
    }
}