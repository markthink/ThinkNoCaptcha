<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$routes = new RouteCollection();

#验证码生成
$routes->add('captcha', new Route('/captcha', array(
    '_controller' => 'ThinkNoCaption\\Controller\\NoCaptionController::captchaAction',
)));

#nocaptcha-验证成语生成
$routes->add('nocaptcha', new Route('/nocaptcha', array(
    '_controller' => 'ThinkNoCaption\\Controller\\NoCaptionController::nocaptchaAction',
)));

#服务器校验-首次以成语验证，重复次数过多，则改为验证字符验证
$routes->add('analyze', new Route('/analyze', array(
    '_controller' => 'ThinkNoCaption\\Controller\\NoCaptionController::analyzeAction',
)));

#验证码正确性验证
$routes->add('check_code', new Route('/check_code', array(
    '_controller' => 'ThinkNoCaption\\Controller\\NoCaptionController::check_codeAction',
)));


$routes->add('login', new Route('/', array(
    '_controller' => 'ThinkNoCaption\\Controller\\LoginController::loginAction',
)));


return $routes;