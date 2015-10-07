<?php

namespace ThinkNoCaptcha\Utils;

/*
 * ref: https://reg.taobao.com/member/reg/fill_mobile.htm
 * 参考淘宝实现的新型验证-noCaptcha源自Google新型验证服务
 * 暂时没有找到合适的开源解决方案，所以此处的实现使用了MySQL成语库，考虑性能问题 所有的成语都进行Redis缓存处理
 */
class NoCaptcha
{
    private $dict;//三条成语数组
    private $tag;//唯一性汉字标识
    private $width = 200;//宽度
    private $height = 200;//高度
    private $img;//图形资源句柄
    private $font;//指定的字体
    private $fontsize = 20;//指定字体大小
    private $fontcolor;//指定字体颜色
    private $marginBox;//标识文字检测盒子边框
    private $output;//输出文件名

    //构造方法初始化
    public function __construct($redis,$font,$output)
    {
        $this->font = $font;//注意字体路径要写对，否则显示不了图片
        $this->output = $output;
        $this->getIdiom($redis);
    }

    /*
     * 获取三条成语+唯一标识汉字
     */
    public function getIdiom($redis)
    {
        //如果成语键不存在，则生成成语数据缓存
        $is_idiom = $redis->exists('idiom');
        if(!$is_idiom){
            new \Exception("没有找到字典键值");
        }
        //每次随机获取三条成语数据
        $keyall = $redis->hkeys('idiom');
        $rand = array_rand( $keyall, 3 );
        $vals= $redis->hmget('idiom',$rand);

        //计算无重复标识汉字 - 分拆汉字
        $tags = array();
        $words = implode('',$vals);
        $words_len = mb_strlen($words);
        while ($words_len){
            $tags[]=mb_substr($words,0,1,'utf-8');
            $words = mb_substr($words,1,$words_len,'utf-8');
            $words_len=mb_strlen($words,'utf-8');
        }
        //过滤逗号
        $tags = array_filter($tags,function($v) {
            if ($v===",") {return false;}
            return true;
        });
        //获取唯一的标识汉字集合
        $tag_rnd = array();
        foreach($tags as $key=>$value){
            if(!in_array($value,$tag_rnd)){
                $tag_rnd[$key]=$value;
            }else{
                $keys = array_keys($tag_rnd,$value);
                $dkey = $keys[0];
                unset($tag_rnd[$dkey]);
            }
        }
        //获取唯一的验证标识汉字
        $tag_key = array_rand($tag_rnd,1);
        $tag = $tag_rnd[$tag_key];

        $this->dict = $vals;
        $this->tag = $tag;
    }

    //生成背景
    private function createBg()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $color = imagecolorallocate($this->img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);

        //雪花
        for ($i = 0; $i < 100; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    //生成文字
    private function createFont()
    {
        foreach($this->dict as $key=>$dict) {
            switch($key){
                case 0:
                    $water = mt_rand(1, 3);
                    break;
                case 1:
                    $water = mt_rand(4, 6);
                    break;
                case 2:
                    $water = mt_rand(7, 9);
                    break;
            }
            $temp = imagettfbbox($this->fontsize, 0, $this->font, $dict);//取得使用 TrueType 字体的文本的范围
            $posXY = $this->getPos($temp, $water);
            //分拆汉字
            $tags = array();
            $words_len = mb_strlen($dict);
            while ($words_len) {
                $tags[] = mb_substr($dict, 0, 1, 'utf-8');
                $dict = mb_substr($dict, 1, $words_len, 'utf-8');
                $words_len = mb_strlen($dict, 'utf-8');
            }
            foreach ($tags as $key=>$value) {
                $this->fontcolor = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));

                $draw_x = $posXY['posX']+$this->fontsize*$key+mt_rand(2,8);
                $draw_y = $posXY['posY']+$this->fontsize+mt_rand(2,8);

                $draw_x = $draw_x>180?180:$draw_x;
                $draw_y = $draw_y>180?180:$draw_y;
                /*
                 * 坐标顺序：左下角[0,1] 右下角[2,3] 右上角[4,5] 左上角[6,7]
                 * 判断点是否在这个矩形区域
                 *  return (x>rect.left && x<rect.right) && (y>rect.top && y<rect.bottom);
                 *  return (x>x6 && x<x2) && (y>x7 && y<x3); 左上角[6,7] 右下角[2,3]
                 */
                $marginBox = imagettftext($this->img, $this->fontsize, mt_rand(-30, 30),$draw_x,$draw_y,$this->fontcolor, $this->font, $value);

                if(strcmp($this->tag,$value)==0){
                    $this->marginBox = $marginBox;
                }
            }
        }
    }

    //返回文字生成位置
    private function getPos($position,$waterPos)
    {
        $w = $position[2] - $position[6];
        $h = $position[3] - $position[7];

        if (($this->width < $w) || ($this->height < $h)) {
            throw new \Exception("需要加水印的图片的长度或宽度比水印文字还小，无法生成水印！");
        }

        switch ($waterPos) {
            case 0://随机
                $posX = rand(0, ($this->width - $w));
                $posY = rand(0, ($this->height - $h));
                break;
            case 1://1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2://2为顶端居中
                $posX = ($this->width - $w) / 2;
                $posY = 0;
                break;
            case 3://3为顶端居右
                $posX = $this->width - $w;
                $posY = 0;
                break;
            case 4://4为中部居左
                $posX = 0;
                $posY = ($this->height - $h) / 2;
                break;
            case 5://5为中部居中
                $posX = ($this->width - $w) / 2;
                $posY = ($this->height - $h) / 2;
                break;
            case 6://6为中部居右
                $posX = $this->width - $w;
                $posY = ($this->height - $h) / 2;
                break;
            case 7://7为底端居左
                $posX = 0;
                $posY = $this->height - $h;
                break;
            case 8://8为底端居中
                $posX = ($this->width - $w) / 2;
                $posY = $this->height - $h;
                break;
            case 9://9为底端居右
                $posX = $this->width - $w;
                $posY = $this->height - $h;
                break;
            default://随机
                $posX = rand(0, ($this->width - $w));
                $posY = rand(0, ($this->height - $h));
                break;
        }
        return array(
            'posX'=>$posX,
            'posY'=>$posY,
        );
    }
    //输出
    private function outPut()
    {
        header('Content-type:image/png');
        imagepng($this->img,$this->output);
        imagedestroy($this->img);
    }

    //对外生成
    public function doimg()
    {
        $this->createBg();
        $this->createFont();
        $this->outPut();
    }

    //获取标识汉字
    public function getCode()
    {
        return strtolower($this->tag);
    }
    //获取验证标识文字图片区域-点击检测
    public function getMarginBox()
    {
        return $this->marginBox;
    }
}