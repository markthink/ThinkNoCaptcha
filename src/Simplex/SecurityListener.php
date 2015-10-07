<?php

namespace Simplex;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Yaml\Parser;

class SecurityListener implements EventSubscriberInterface
{

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        //构造默认参数开始
        $yaml = new Parser();
        $constants = $yaml->parse(file_get_contents(__DIR__.'/../ThinkNoCaption/Resources/config/constants.yml'));

        $request->attributes->set('Opcode',$constants['Opcode']);
        $request->attributes->set('Status',$constants['Status']);
        $request->attributes->set('Constant',$constants['Constant']);

        //构造返回数据结构
        $ret = array(
            $constants['Opcode']['RETURN_CODE']=>$constants['Status']['AJAX_200'],
            $constants['Opcode']['RETURN_MSG']=>$constants['Constant']['SUCCESS'],
        );
        $request->attributes->set('Ret',$ret);
        //构造默认参数结束

        //注入验证码服务KEY+TOKEN
        $request->attributes->set('nc_appkey','TRJCN');
        $request->attributes->set('nc_token','94153dadbf407ac1f174618c0df174e67d85429b');

//        $session = $request->getSession();
//        if(!$session->isStarted()){
//            $session->start();
//        }
//        //如果已经退出，则不解析客户端Cookie数据
//        if($session->has('userinfo')){
//            $cookie_userid = $request->cookies->get('cookie_uid');
//            if ($cookie_userid){
//                //相关验证处理
//            }else{
//                if($request->attributes->has('login_uid')){
//                    $request->attributes->remove('login_uid');
//                }
//            }
//        }
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => 'onKernelController');
    }
}
