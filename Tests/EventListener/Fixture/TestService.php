<?php

namespace Rougemine\Bundle\BeforeAfterControllersHooksBundle\Tests\EventListener\Fixture;

use Symfony\Component\HttpFoundation\Response;

class TestService
{
    public function beforeHook()
    {
        return new Response('serviceBeforeHook');
    }

    public function beforeHookWithArgs()
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