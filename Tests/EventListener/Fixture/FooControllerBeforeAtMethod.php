<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

class FooControllerBeforeAtMethod
{
    public $beforeHooksResults = array();

    /**
     * @BeforeHook("preHookWithoutReturnedResponse")
     */
    public function selfContainedPreHookActionWithoutHookResponseAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("preHookWithReturnedResponse")
     */
    public function selfContainedPreHookActionWithHookResponseAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    /**
     * @BeforeHook("preHookWithoutReturnedResponse")
     * @BeforeHook("preHookWithoutReturnedResponse")
     */
    public function selfContainedMultipleBeforeHooksActionWithoutHookResponseAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("preHookWithoutReturnedResponse")
     * @BeforeHook("preHookWithReturnedResponse")
     * @BeforeHook("preHookThrowsException")
     */
    public function selfContainedMultipleBeforeHooksActionWithHookResponseAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    public function preHookWithoutReturnedResponse()
    {
        $this->beforeHooksResults[] = 'afterHookTriggered';
    }

    public function preHookWithReturnedResponse()
    {
        $this->beforeHooksResults[] = 'afterHookTriggered';

        return new Response('hookResponse');
    }

    public function preHookThrowsException()
    {
        $this->beforeHooksResults[] = 'afterHookTriggered';
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }
}