<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 10:41
 */

namespace app\officialnumber\extend;
use think\Controller;
use app\officialnumber\model\OfficialApp;
use app\officialnumber\model\UserRecommend;
use think\Db;
//公众号用户统计接口
class UserCounts extends Controller
{
    protected $access_token;
    protected $app;

    public function __construct($app_id)
    {
        parent::__construct();
        $this->app = Db::name('official_app')->where('id',$app_id)->find();//查询这个app数据
        $this->access_token = getAccessToken($this->app);

    }
    /*post请求,json格式
    获取用户增减数据（getusersummary）	7	https://api.weixin.qq.com/datacube/getusersummary?access_token=ACCESS_TOKEN
    获取累计用户数据（getusercumulate）	7	https://api.weixin.qq.com/datacube/getusercumulate?access_token=ACCESS_TOKEN
    access_token	是	调用接口凭证
    begin_date	是	获取数据的起始日期，begin_date和end_date的差值需小于“最大时间跨度”（比如最大时间跨度为1时，begin_date和end_date的差值只能为0，才能小于1），否则会报错
    end_date	是	获取数据的结束日期，end_date允许设置的最大值为昨日
    {
    "begin_date": "2014-12-02",
    "end_date": "2014-12-07"
    }
    */
    public function userCount(){


    }
    //获取用户增减数据
    public function getUserSummary($start,$end){
        $url = "https://api.weixin.qq.com/datacube/getusersummary?access_token={$this->access_token}";
        $post_data = array(
            'begin_date'=>$start,
            'end_date' =>$end
        );
        $json = $this->https_curl($url,json_encode($post_data));//返回json数据
        $arr = json_decode($json,1);
        if(isset($arr['errcode'])){
            die('获取用户增减数据失败，错误号：'.$arr['errcode'].',错误信息：'.$arr['errmsg']);
        }
        return $arr;

    }
    //获取累计用户数据
    public function getUserCumulate(){


    }

    public function https_curl($url,$data='')
    {
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