<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 17:08
 */

namespace app\officialnumber\controller;
use app\officialnumber\model\OfficialApp;
use app\officialnumber\model\UserRecommend;

use think\Controller;

class Auth extends Controller
{
    protected $redirect_uri;//回调地址
    //protected $code;//授权码
   // protected $scope;//授权类型
    protected $openid;
    protected $app_id;
    protected $id;//用户id主键
    protected $app_secret;
    protected $goback;
    public function __construct()
    {
        parent::__construct();
        $id = input("id");

        //$official_app = new OfficialApp();//实例化app表模型,该对象就代表这张表
        //$app = $official_app->getAppById($id);//得到的是一个模型对象，可以用数组访问

        $id['id'] = input('id');
        $app = Db::connect('db_wxmail')->name("official_app")->where($id)->find();

        //dump($app);die;
        $this->id = input('user_id');
        $this->app_id = $app['appid'];
        $this->app_secret = $app['appsecret'];
        $this->goback = input("url");//获取原始网址，也就是最终要调回的地址
        //$this->http = new Http();
        //echo $this->redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        //$this->scope = input("scope");//授权类型

            //$this->redirect_uri = 'http://' . config("wechatauth.host") . '/officialnumber/Auth/get_user_info&url=' . $this->goback;

            $redirect_uri = 'http://' . config("wechatauth.host") . '/officialnumber/Auth/get_openid&id='.$id.'&user_id='.$this->id.'&url=' . $this->goback;
            $this->redirect_uri = urlencode($redirect_uri);//注意要urlencode
    }

    /**
     * @explain
     * 获取code,用于获取openid和access_token
     * @remark
     * code只能使用一次，当获取到之后code失效,再次获取需要重新进入
     * 不会弹出授权页面，适用于关注公众号后自定义菜单跳转等，如果不关注，那么只能获取openid
     * $scope = "snsapi_userinfo",,$scope = "snsapi_base"
     **/
    public function getCode($scope="snsapi_base",$state = 'STATE')
    {

            //不存在则重新授权
            //$str = " http://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->appid . "&redirect_uri=" . $this->index_url . "&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
            $str = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$this->redirect_uri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
            header("Location:" . $str);
            exit();

    }

    //获取用户openid
    public function get_openid()
    {

               // echo $_GET['code'];die;
                if(!isset($_GET['code'])) {
                    die("授权码获取失败");
                }
                dump($this->goback);
                echo PHP_EOL;
                //dump($_GET['code']);die;
                //$access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->appid . "&secret=" . $this->appsecret . "&code=" . $this->code . "&grant_type=authorization_code";
                $token_arr = $this->get_access_token($_GET['code']);//获取access_token和openid
                dump($token_arr);
                if (isset($token_arr['errcode'])) {
                    //如果不存在，重新获取
                    echo $this->app_id,'---';
                    die("获取token失败，错误码：".$token_arr['errcode']."错误信息：".$token_arr['errmsg']);
                    // return redirect($this->redirect_uri);
                }
                /*
                if (!isset($token['access_token']) || !isset($token['openid'])) {
                    return redirect($this->redirect_uri);
                }
                */
                $openid = $token_arr['openid'];
                session('openid_'.$this->id, $openid);
                //file_put_contents(LOG_PATH."error.log", cookie('openid'),FILE_APPEND);
                //dump($this->goback);die;
                header("Location:".$this->goback);




    }

    //获取用户信息
    public function get_user_info()
    {
            //$key = "user_info";

         if(isset($_GET['code'])) {
                //拿到授权码
             $token_arr = $this->get_access_token($_GET['code']);//获取access_token和openid
             if (isset($token_arr['errcode'])) {
                 //如果不存在，重新获取
                 die("错误码：".$token_arr['errcode']."错误信息：".$token_arr['errmsg']);
                // return redirect($this->redirect_uri);
             }
             //$access_token = $access_token_arr['access_token'];
             $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $token_arr['access_token'] . "&openid=" . $token_arr['openid'] . "&lang=zh_CN";
             $userinfo_json = $this->get_curl($userinfo_url);
             $user = json_decode($userinfo_json, TRUE);
             if (isset($user['errcode'])) {
                 die("错误码：".$token_arr['errcode']."错误信息：".$token_arr['errmsg']);
                 //return redirect($this->redirect_uri);
             }
             $openid = $user['openid'];
             $nickname = $user['nickname'];
             $headimgurl =$user['headimgurl'] ;
             //cookie('openid_'.$this->id, $openid, time() + 86400 * 30);
             session('openid_'.$this->id, $openid);
             $arr = [$openid, $nickname, $headimgurl];
             //cookie('user_info_'.$this->id, $arr, time() + 86400 * 30);//cookie设置30天,和access_token有效期一样（网页授权的access_Token)
             session('user_info_'.$this->id, $arr);
             header("Location:".$this->goback);
            // return redirect($this->goback);

             //return $arr;
         }
    }

    /**
     * 获取授权token
     * @param string $code
     * @return bool|mixed
     */
    public function get_access_token($code = '')
    {

        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
        $token_data = $this->get_curl($token_url);

//        echo $token_url;
//        exit();
//        echo $token_data;
//        exit();

        return json_decode($token_data, TRUE);
    }

    /*
     * curl请求
     *
     */
    public function get_curl($url,$data=''){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        if(!empty($data)){
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        $str=curl_exec($ch);
        curl_close($ch);
        return $str;
    }
}