<?php

namespace DrBenton\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use Symfony\Component\HttpFoundation\Response;

class TestService
{
    public $beforeHooksResults = array();

    public function beforeHook()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered';
    }

    public function beforeHookWithArgs()
    {
        $this->beforeHooksResults[] = 'beforeHookTriggered: args=' . json_encode(func_get_args());
    }

    public function beforeHookWithResponse()
    {
        return new Response('serviceBeforeHook');
    }

    public function beforeHookWithResponseWithArgs()
    {
        return new Response('serviceBeforeHook; args=' . json_encode(func_get_args()));
    }

    public function afterHook(Response $response)
    {
        $response->setContent($response->getContent().' + serviceHookResponse');
    }

    public function afterHookWithArgs(Response $response)
    {
        $args = array_slice(func_get_args(), 1);
        $response->setContent($response->getContent().' + serviceHookResponse; args=' . json_encode($args));
    }
}