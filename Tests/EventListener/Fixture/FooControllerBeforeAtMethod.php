<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\BeforeControllerHook as BeforeHook;
use Symfony\Component\HttpFoundation\Response;

class FooControllerBeforeAtMethod
{
    public $beforeHooksResults = array();

    /**
     * @BeforeHook("beforeHookWithoutReturnedResponse")
     */
    public function selfContainedPreHookActionWithoutHookResponseAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("beforeHookWithReturnedResponse")
     */
    public function selfContainedPreHookActionWithHookResponseAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    /**
     * @BeforeHook("beforeHookWithoutReturnedResponse")
     * @BeforeHook("beforeHookWithoutReturnedResponse")
     */
    public function selfContainedMultipleBeforeHooksActionWithoutHookResponseAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("beforeHookWithoutReturnedResponseWithArgs", args={"hi": "Hi", "there": "there!"})
     */
    public function selfContainedBeforeHooksActionWithoutHookResponseWithArgsAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("beforeHookWithReturnedResponseWithArgs", args={"hi": "Hi", "there": "there!"})
     */
    public function selfContainedPreHookActionWithHookResponseWithArgsAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    /**
     * @BeforeHook("beforeHookWithoutReturnedResponse")
     * @BeforeHook("beforeHookWithReturnedResponse")
     * @BeforeHook("beforeHookThrowsException")
     */
    public function selfContainedMultipleBeforeHooksActionWithHookResponseAction()
    {
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }

    /**
     * @BeforeHook("@testService::beforeHook")
     */
    public function serviceBeforeHookAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("@testService::beforeHookWithArgs", args={"test1", {"key": "value"}})
     */
    public function serviceBeforeHookWithArgsAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("@testService::beforeHookWithResponse")
     */
    public function serviceBeforeHookWithResponseAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @BeforeHook("@testService::beforeHookWithResponseWithArgs", args={"test1", {"key": "value"}})
     */
    public function serviceBeforeHookWithResponseWithArgsAction()
    {
        return new Response('controllerResponse');
    }

    public function beforeHookWithoutReturnedResponse()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';
    }

    public function beforeHookWithReturnedResponse()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';

        return new Response('hookResponse');
    }

    public function beforeHookWithoutReturnedResponseWithArgs($hi, $there)
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered: '.$hi.' '.$there;
    }

    public function beforeHookWithReturnedResponseWithArgs()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';

        return new Response('hookResponse: '.implode(' ', func_get_args()));
    }

    public function beforeHookThrowsException()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';
        throw new \Exception('The "'.__METHOD__.'" Action should never be called!');
    }
}