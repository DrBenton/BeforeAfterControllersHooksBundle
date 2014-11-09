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
     * @AfterHook("afterHookWithResponseModification", args={"%response%"})
     */
    public function selfContainedAfterHookActionWithResponseModificationAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @AfterHook("@test_service::afterHook")
     */
    public function serviceAfterHookWithoutResponseModificationAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @AfterHook("@test_service::afterHookWithArgs", args={"test1", {"key": "value"}})
     */
    public function serviceAfterHookWithoutResponseModificationWithArgsAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @AfterHook("@test_service::afterHookWithResponseModification", args={"%response%"})
     */
    public function serviceAfterHookWithResponseModificationAction()
    {
        return new Response('controllerResponse');
    }

    /**
     * @AfterHook("@test_service::afterHookWithResponseModificationWithArgs", args={"%response%", "test1", {"key": "value"}})
     */
    public function serviceAfterHookWithResponseModificationWithArgsAction()
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