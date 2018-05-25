<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/26
 * Time: 19:49
 */

namespace app\officialnumber\controller;
use think\Controller;

class Test extends Controller
{

    public function index(){
        //使用方法
        $appid = "wx01d48b0e88d21274";
        $secret = "3ad1dec320823c6ee8a3ab00a305c511";
        $openid= $this->getOpenid($appid, $secret); //接口入口 1/3

        echo $openid;
    }

    // 2/3
    public function getOpenid($appid, $appsecret){
        $SERVER_NAME = $_SERVER['SERVER_NAME'];
        $REQUEST_URI = $_SERVER['REQUEST_URI'];
        $redirect_uri = urlencode('http://' . $SERVER_NAME . $REQUEST_URI); //跳转页面

        $code = input('code'); //每次唯一code。TP5中助手函数input，TP3.2是I

        if (! $code) { //调出授权页面，生成code（小程序则是wx.login(success:function(res){})后才能生成code，但是小程序比较简单。）

            // 网页授权当scope=snsapi_userinfo时才会提示是否授权应用
            $autourl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
            header("location:$autourl");
        } else { //用code、appid、secret换取用信息
            // 获取openid
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code";
            $row = $this->posturl($url);
            return ($row['openid']);
        }
    }

    //3/3
    //解析微信返回的json最终返回用户信息新json
    public function posturl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $jsoninfo = json_decode($output, true);
        return $jsoninfo;
    }

//    public function __construct(){
//
//        parent::__construct();
//
//
//
//    }



}