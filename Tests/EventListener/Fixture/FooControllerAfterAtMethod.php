<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use DrBenton\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook as AfterHook;
use Symfony\Component\HttpFoundation\Response;

class FooControllerAfterAtMethod
{
    public $afterHooksResults = array();

    /**
     * @AfterHook("afterHookWithoutResponseModification")
     */
    public function selfContainedAfterHookActionWithoutResponseModificationAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @AfterHook("afterHookWithResponseModification")
     */
    public function selfContainedAfterHookActionWithResponseModificationAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @AfterHook("@testService::afterHook")
     */
    public function serviceAfterHookAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @AfterHook("@testService::afterHookWithArgs", args={"test1", {"key": "value"}})
     */
    public function serviceAfterHookWithArgsAction()
    {
        return new Response('controllerResponse');
    }

    public function afterHookWithoutResponseModification()
    {
        $this->afterHooksResults[] = 'afterHookTriggered';
    }

    public function afterHookWithResponseModification(Response $response)
    {
        $this->afterHooksResults[] = 'afterHookTriggered';
        $response->setContent($response->getContent().' + hookResponse');
    }
}