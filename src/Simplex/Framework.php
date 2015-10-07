<?php

namespace Simplex;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

class Framework extends HttpKernel
{
    public function __construct($routes)
    {
        $context = new RequestContext();
        $matcher = new UrlMatcher($routes,$context);
        $resolver = new ControllerResolver();

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher));
        $dispatcher->addSubscriber(new SecurityListener('utf-8'));

        parent::__construct($dispatcher,$resolver);
    }
}