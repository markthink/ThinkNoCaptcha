<?php

namespace ThinkNoCaption\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends MyTwig
{
    //登陆处理
    public function loginAction(Request $request)
    {
        if($request->isXmlHttpRequest()){
            //获取返回参数
            $ret = $request->get('Ret');
            $opcode = $request->get('Opcode');
            $status = $request->get('Status');
            $constant = $request->get('Constant');
            //验证码校验
            $csig = $request->request->get('nc_sig');
            $nc_sessionid = $request->request->get('nc_sessionid');
            //$nc_ua = $request->request->get('nc_ua');//默认用户账户

            $session = $request->getSession();
            if(!$session->isStarted()){
                $session->start();
            }
            //验证码校验
            $sig = $session->get('sig');
            $sessionid = $session->getId();

            if($csig!=$sig || $nc_sessionid!=$sessionid){
                $ret[$opcode['RETURN_CODE']]=$status['AJAX_201'];
                $ret[$opcode['RETURN_MSG']]=$constant['SECURITY_CODE'];
            }
            return new JsonResponse($ret);
        }

        return new Response($this->twig->render('index.html.twig'));
    }
}