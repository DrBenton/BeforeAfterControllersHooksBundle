<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use Rougemine\Bundle\BeforeAfterControllersHooksBundle\Annotation\AfterControllerHook as AfterHook;
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