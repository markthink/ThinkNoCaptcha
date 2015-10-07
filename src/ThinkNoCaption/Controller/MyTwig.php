<?php

namespace ThinkNoCaption\Controller;

class MyTwig
{
    public $twig;
    public function __construct()
    {
        //载入模板系统
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../Resources/views/');
        $twig = new \Twig_Environment($loader,array(
            'cache'=>front_path.'/compilation_cache',
        ));

        $this->twig = $twig;
    }
}

