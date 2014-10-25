<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

class FooControllerBeforeAtMethod
{
    public $preHooksResults = array();

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
    public function selfContainedMultiplePreHooksActionWithoutHookResponseAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("preHookWithoutReturnedResponse")
     * @BeforeHook("preHookWithReturnedResponse")
     * @BeforeHook("preHookThrowsException")
     */
    public function selfContainedMultiplePreHooksActionWithHookResponseAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    public function preHookWithoutReturnedResponse()
    {
        $this->preHooksResults[] = 'preHookTriggered';
    }

    public function preHookWithReturnedResponse()
    {
        $this->preHooksResults[] = 'preHookTriggered';

        return new Response('hookResponse');
    }

    public function preHookThrowsException()
    {
        $this->preHooksResults[] = 'preHookTriggered';
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }
}