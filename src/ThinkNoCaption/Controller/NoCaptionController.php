<?php

namespace ThinkNoCaption\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use ThinkNoCaptcha\Utils\ValidateCode;
use ThinkNoCaptcha\Utils\NoCaptcha;

use Predis;

class NoCaptionController
{
    private $fonts = '../Resources/fonts/Xingkai.ttc';
    private $admin_codeimg = front_path.'/refreshcode.png';
    private $admin_nocaptcha = front_path.'/nocaptcha.png';

    /*
     * 生成验证码
     */
    public function captchaAction(Request $request)
    {
        $validate = new ValidateCode($this->fonts,$this->admin_codeimg);
        $validate->doimg();

        //设置session，做验证
        $session = $request->getSession();
        if(!$session->isStarted()){
            $session->start();
        }
        $session->set('letters_code',$validate->getCode());
        return new Response('refreshcode.png');
    }

    /*
     * NoCaptcha验证
     */
    public function nocaptchaAction(Request $request)
    {
        $redis = new Predis\Client(array(
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ));

        //绘图 获取标识文字 存储至Session
        $noCaptcha = new NoCaptcha($redis,$this->fonts,$this->admin_nocaptcha);
        $noCaptcha->doimg();

        $session = $request->getSession();
        if(!$session->isStarted()){
            $session->start();
        }
        $session->set('tag',$noCaptcha->getCode());
        $session->set('marignBox',$noCaptcha->getMarginBox());

        $output = array(
            'questiontext'=>'请点击图中的'.$noCaptcha->getCode().'字',
            'imgurl'=>'nocaptcha.png',
            'tag'=>$noCaptcha->getCode()

        );

        return  new JsonResponse($output);
    }

    /*
     * 服务检测-选择用哪种方式验证
     */
    public function analyzeAction(Request $request)
    {
        //获取返回参数
        $ret = $request->get('Ret');
        $opcode = $request->get('Opcode');
        $status = $request->get('Status');
        $constant = $request->get('Constant');

        $session = $request->getSession();
        if(!$session->isStarted()){
            $session->start();
        }
        //获取用户点击次数-超过10次切换到验证码输入模式
        $sessionid = $session->getId();

        $nc_click =0;
        if($session->has($sessionid)){
            $nc_click = $session->get($sessionid);
        }else{
            $session->set($sessionid,$nc_click);
        }

        $ret[$opcode['RETURN_DATA']]=array(
            'code'=>($nc_click<10?100:200),
            'value'=>'default',
            'csessionid'=>$sessionid,
        );

        return new JsonResponse($ret);
    }

    /*
     * 验证成语点击或验证码正确与否
     */
    public function check_codeAction(Request $request)
    {
        //获取返回参数
        $ret = $request->get('Ret');
        $opcode = $request->get('Opcode');
        $status = $request->get('Status');
        $constant = $request->get('Constant');

        //获取客户端提交数据
        $ctype = $request->request->get('ctype');
        $checkcode = $request->request->get('checkcode');
        $appkey = $request->request->get('appkey');
        $token = $request->request->get('token');
        $csessionid = $request->request->get('csessionid');

        //校验APPKEY+TOKEN
        $nc_appkey = $request->get('nc_appkey');
        $nc_token = $request->get('nc_token');

        $session = $request->getSession();
        if(!$session->isStarted()){
            $session->start();
        }

        if($appkey==$nc_appkey && $token==$nc_token){
            switch($ctype){
                case 'noCaptcha':
                    $idiom = json_decode($checkcode,true);
                    //$tag = $session->get('tag');
                    //坐标顺序：左下角[0,1] 右下角[2,3] 右上角[4,5] 左上角[6,7]
                    $marignBox = $session->get('marignBox');
                    $x = $idiom['x'];$y=$idiom['y'];
                    //校验点击区域
                    $captcha = ($x>$marignBox[6] && $x<$marignBox[2]) && ($y>$marignBox[7] && $y<$marignBox[3]);
                    if($captcha==false){
                        $ret[$opcode['RETURN_CODE']]=$status['AJAX_201'];
                        $ret[$opcode['RETURN_MSG']]=$constant['NO_CAPTCHA_ERR'];
                    }
                    break;
                case 'Captcha':
                    $checkcode = json_decode($checkcode,true);
                    $letters_code = $session->get('letters_code');
                    $captcha = ($letters_code == strtolower($checkcode['code']));

                    if($captcha==false){
                        $ret[$opcode['RETURN_CODE']]=$status['AJAX_201'];
                        $ret[$opcode['RETURN_MSG']]=$constant['CAPTCHA_ERR'];
                    }

                    break;
                default:
                    $ret[$opcode['RETURN_CODE']]=$status['AJAX_202'];
                    $ret[$opcode['RETURN_MSG']]=$constant['CAPTCHA_TYPE_ERR'];
            }
        }else{
            //参数不合法
            $ret[$opcode['RETURN_CODE']]=$status['AJAX_203'];
            $ret[$opcode['RETURN_MSG']]=$constant['CAPTCHA_TOKEN_ERR'];
        }

        if($ret[$opcode['RETURN_CODE']]==$status['AJAX_200']){
            $sig = sha1($csessionid);
            $ret[$opcode['RETURN_DATA']]=array(
                'sig'=>$sig,
            );
            //移除计数
            if($session->has($csessionid)){
                $session->remove($csessionid);
            }
            $session->set('sig',$sig);
        }else{
            //更新Session
            if($session->getId()==$csessionid){
                if($session->has($csessionid)){
                    $nc_click = $session->get($csessionid);
                    $session->set($csessionid,$nc_click+1);
                }
            }
        }
        return new JsonResponse($ret);
    }
}