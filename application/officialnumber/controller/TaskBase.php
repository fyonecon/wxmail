<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 10:12
 */

namespace app\officialnumber\controller;
use think\Controller;
use app\officialnumber\model\OfficialApp;
use app\officialnumber\model\UserRecommend;
use app\officialnumber\model\WatermaskText;//文本水印模型
use app\officialnumber\model\WatermaskPic;//图片水印
use app\officialnumber\model\AutoMsg;//自动回复消息
use app\officialnumber\model\KfMsg;//客服消息
use app\officialnumber\model\TemplateMsg;//模板消息
use think\Db;


class TaskBase extends Controller
{
    protected $token = "";//验证微信服务器的token，也是通过传过来的token来得到appid等信息
    protected $appId = "";
    protected $secretKey = "";
    protected $app = '';//app信息
    protected $accessToken = '';
    protected $app_dir = '';
    protected $newApp = null;//新的未满员app
    protected $user_info;

    //定义构造函数，可以完成一些初始化操作
    public function __construct()
    {
        parent::__construct();

        //$t1 = microtime(true);//采用微妙计时来检测脚本运行时间
        $token = input('token');//接受公众号配置的token参数，这里采用pathinfo方式传参/token/123;
        //先通过$token去app表中拿相应数据
        $official_app = new OfficialApp();//实例化app表模型,该对象就代表这张表
        $app = $official_app->getAppByToken($token);//得到的是一个模型对象，可以用数组访问
        dump($app);
        $this->app = $app;
        $this->appId = $app['appid'];
        $this->secretKey = $app['appsecret'];
        $this->token = $token;
        $this->app_dir = ROOT_PATH.'public/'.config("recommend.dir").'/' . 'app-'.$app['id'];
        $this->createDir($this->app_dir);//创建目录,这个目录是该app合成图片以及头像等图片存放目录，和背景目录是分开的
        $this->accessToken = getAccessToken($app);//换取access_token



        }




    //检查公众号当天人数是否满了
    protected function check_nums($app){
        $time = time();
        $start = strtotime(date('Y-m-d',$time));//获取当天0点0分0时的时间戳
        $end = $start + 86400;//加24小时
        $user_counts = Db::name("user_recommend")->where('app_id',$app['id'])->where('type',$app['type'])->where('cat_id',$app['cat_id'])
        ->where('subscribe_time','>=',$start)->where('subscribe_time','<',$end)->distinct(true)->field('openid')->count("id");//统计该app用户数,去重一下
        if($user_counts >= $app['max_num']){
            //如果大于最大人等于数，更新app状态为0
            if($app['status']) {
                //状态不为0则更新
                //$official_app->updateStatus(0, $app['id']);
                Db::name('official_app')->where('id',$app['id'])->update(['status'=>0]);//更新状态为0
            }
            //拿到一个status为1的同类型同期的未满app
            $new_app = Db::name('official_app')->where('cat_id',$app['cat_id'])
            ->where('type',$app['type'])->where('status',1)->find();//查找行的app
            if(!$new_app){
                //如果为空，说明已经没有可用的群了，终止运行
                $date = date('Y-m-d H:i:s',time());
                file_put_contents(LOG_PATH.'error_log.log',"[{$date}] [notice] [公众号{$app['appname']}人数已满，暂无可用公众号]");
                exit("人数已满，暂时无可用公众号");
            }
            return $new_app;//返回新app
        }
        //人数未满,返回0
        return 0;
    }
    //活动结束
    public function taskEnd($app){
        //关注自动回复活动结束
        $postStr = empty($GLOBALS['HTTP_RAW_POST_DATA']) ? file_get_contents("php://input"): $GLOBALS['HTTP_RAW_POST_DATA'];//这里做个兼容处理
        $accessToken = getAccessToken($app);//换取access_token

        if(!empty($postStr)) {
            //有收到数据的时候。提取数据为对象格式
            $postObj = simplexml_load_string($postStr, "SimpleXMLElement", LIBXML_NOCDATA);//提取为对象
            $fromUsername = $postObj->FromUserName;//用户openid
            //$toUsername = $postObj->ToUserName;//公众号微信号
            $receiveContent = trim($postObj->Content);//这里需要对内容进行一个trim(）处理，去除字符串首尾处的空白字符，否则可能拿不到正确的值
            $msgType = trim($postObj->MsgType);

            $this->user_info = $this->getUserInfo($accessToken, $fromUsername);//拿到用户信息的数组

            switch ($msgType) {
                case 'event':
                    //匹配事件类型
                    $this->dealEndEvent($postObj,$accessToken);//调用事件接收函数处理
                    //$feedback = sprintf(MSG_TEXT, trim($postObj->FromUserName), trim($postObj->ToUserName), time(), "nihaoaaaw111");
                    //echo $feedback;
                    break;

            }
        }
    }
    //活动结束处理关注和扫描事件
    public function dealEndEvent($obj,$accessToken){
        switch(trim($obj->Event)){
            case 'subscribe':
                //匹配订阅事件，活动结束直接回复被动文本消息
                $this->sendText($obj,"本期活动结束，敬请期待下期活动~");

                break;
            case 'SCAN' :
                //活动结束直接回复被动文本消息
                $this->sendText($obj,"本期活动结束，敬请期待下期活动~");
                break;

            case 'unsubscribe' :
                //取消关注推送事件
                //$this->countLostUser($obj);
                break;

            case "CLICK":
                //自定义菜单click事件推送
                $eventKey = trim($obj->EventKey);
                switch($eventKey){
                    case 'end_kf':
                        //回复客服二维码
                        $this->autoSendKf($obj,$accessToken);
                        break;
                }
                break;

        }

    }
    //被动回复客服二维码
    public function autoSendKf($obj,$access_token){
        $kf_dir = ROOT_PATH.'public/'.config("recommend.dir").'/'.'kefu';
        $kf_path = glob($kf_dir."/huifukefu.jpg")[0];
        //file_put_contents(LOG_PATH.'error.log','图片'.$kf_path);
        $kf_img = $this->addTmpStuff($access_token,$kf_path);
        $this->sendImg($obj,$kf_img);//被动回复客服微信二维码

    }

    //活动结束自定义菜单创建
    /*
     * {
                  "type":"view",
                  "name":"快递查询",
                  "url": "http://td.kkfkbgk.pw/tp5/public/index.php?s=/officialnumber/search/index/app_id/{$app_id}"
                  }，
    {
                      "type":"click",
                      "name":"联系客服",
                      "key":"end_kf"

                  }
     *
     */
    public function createEndMenu(){
        $token = input("token");
        $official_app = new OfficialApp();//实例化app表模型,该对象就代表这张表
        $app = $official_app->getAppByToken($token);//得到的是一个模型对象，可以用数组访问
        $access_token = getAccessToken($app);
        $app_id =$this->app['id'];
        //活动结束保留2个菜单，一个收获地址，一个是回复客服二维码
        $url = "http://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
        $menu_json = <<<EOT
            {
                 "button":[
                  {    
                     "name":"福利课程",
                     "sub_button":[
                     {
                        "type":"view",
                        "name":"红楼梦",
                        "url": "http://kc.xabuq.pw/?id=1&e=240"
                     }] 
                  },
                  {
                     "type":"view",
                     "name":"妈妈书院",
                     "url": "https://mp.weixin.qq.com/s?__biz=MzAwNDc4MDA1NA==&tempkey=OTU2XzNGWTRmWjc3TmVLeXNDeUZLQkRxM285WnVkVUtGT0JsbFB6VFo5OVFQcUNCbk1CeEc4TW1Sd3hnTlFWaTdiQ1JRdTJtTXVmbmJiMmFETGJ0OFNnNEVGd3Btbmd1TGZFZEc3ODgzWHItU0hjVV9QWkdDcnpHb3BubWVFNXZ0alNIX0F1bndvY0ptRjJTNVlXaDZOb3ZvZEhSVlJUdzI0MXRxdlktblF%2Bfg%3D%3D&chksm=00ebd4d3379c5dc596d0b3449e779413da9f51ee54276e01a5b1fc4a56c3e9c754c54f45e48f#rd"
                 
                  }
                  ]
             }
EOT;
        $res = json_decode(http_request($url,$menu_json),1);//发起请求，正确时返回{"errcode":0,"errmsg":"ok"}
        if($res['errcode'] == 0){
            //创建菜单成功时
            echo '菜单创建成功';
        }else{
            echo '菜单创建失败，错误号：'.$res['errcode'].'错误信息：'.$res['errmsg'];
        }

    }
    //活动结束推送模板通知
    public function sendEndNotice(){
        $token = input("token");
        $official_app = new OfficialApp();//实例化app表模型,该对象就代表这张表
        $app = $official_app->getAppByToken($token);//得到的是一个模型对象，可以用数组访问
        $acess_token = getAccessToken($this->app);
        $app_id =$app['id'];
        $type_id = $app['type'];
        $cat_id = $app['cat_id'];
        //循环出所有用户
        $users =  Db::name("user_recommend")->where(['app_id'=>$app_id,'type'=>$type_id,'cat_id'=>$cat_id,'is_subscribe'=>1])->field("nickname,openid")->select();
        //dump($users);注意发模板只能发已关注用户，取关不行

        foreach($users as $user){

            $this->EndNotice($user,$acess_token);

        }


        //$user['openid'] = "owTtJ0xA-i4LnY3HeCBHdIRp7mjI";
        //$this->EndNotice($user,$acess_token);
        echo '发送完毕';
    }
    public function EndNotice($user,$acess_token){
        //保存成功则发送通知给推荐人
        //用户保存后给推荐人推送服务通知
        //先指定模板通知参数内容

        $openid = $user['openid'];//获取推荐人openid
        //$openid = "owTtJ0xA-i4LnY3HeCBHdIRp7mjI";
        //$templatId = $this->getTemplateId(1, $this->accessToken);//通过模板短编号拿到模板id,模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
        $template_data['templatId'] = "QLaiLg0NHMACxvMWLvuHkcXV2RpMRcl5AgMZVb0tUuc";
        $template_data['url'] = 'https://mp.weixin.qq.com/s/UJbnTSiknurmxHEDSCb_aQ';//跳转地址，如果指定小程序，则优先跳小程序
        $template_data['first'] = "第二期送书活动圆满结束";//首行语
        $template_data['key1'] ="你读书我送书第二期"; //关键词1，
        $template_data['key2'] = '2018年5月7日-2018年5月11日';//关键词2
        //$template_data['key3'] = $recommend_user['target_num'];//目标人数
        // $template_data['key4'] = $recommend_user['recommended_num'];//已推荐人数
        $text ="本次我们准备的500本书（书单名称：《目送》《追风筝的人》《天才在左疯子在右》），已经全部赠送完毕。
获得领书资格的亲们，请确保已填写正确的收货信息，
没领到书的同学也不要气馁！《第三期送书活动》马上开启，这一次要加快手速哦
如有任何疑问，都可添加客服小新（微信号：anne65669）咨询。
祝大家生活愉快~";

        $template_data['remark'] =$text;
        //底部备注

        $template_res = $this->sendServiceNotice($openid, $acess_token, $template_data);
        if($template_res){
            file_put_contents(LOG_PATH."template_suc.log",'发送成功，用户openid：'.$user['openid'].'用户昵称,'.$user['nickname'].'活动类型'.$this->app['type'].'活动app:'.$this->app['appname'].PHP_EOL,FILE_APPEND);
        }

    }
    
    //创建一个自定义菜单,浏览器提交即可
    public function buildMenu(){

        $this->createMenu($this->accessToken);
    }
    //微信消息服务器地址
    public function index(){

        if($this->app) {

            //保存到对象属性中，供后面使用
            //先创建该app的专属图片目录

            //先判断是否有接受到get方式传递的echostr参数，有的话说明是设置消息服务器验证
            if(input('echostr')){
                //如果存在，调用valid函数验证
                //echo input('echostr');
                $this->valid();
            }else{
                //不存在说明是post数据，也就是消息的推送
                //file_put_contents(LOG_PATH."error.log",'进入response:123'.PHP_EOL,FILE_APPEND);

                $this->responseMsg();

            }



            //$t2 = microtime(true);//返回当前 Unix 时间戳和微秒数,true代表返回一个带微妙的浮点数，用来给脚本运行计时
            //file_put_contents(ROOT_PATH . "runtime_check/public_timeout.log", '脚本耗时' . round($t2 - $t1, 6) . '秒' . PHP_EOL . 'Now memory_get_usage: ' . memory_get_usage(), FILE_APPEND);
        }
    }
    protected function valid(){
        $echoStr = input('echostr');
        if($this->checkSignature()){
            //验证签名成功则返回echoStr
            echo $echoStr;
            exit;
        }
    }
    //签名验证
    protected function checkSignature()
    {
        $signature = input("signature");
        $timestamp = input("timestamp");
        $nonce = input("nonce");

        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        } else {
            return false;
        }
    }
    //消息回复处理
    /*
     * 只要用户点击微信公众号的模板消息、层叠菜单按钮，都会触发【服务器地址(URL)】地址函数，进而触发其他函数如responseMsg()

     * */
    public function responseMsg(){

        //file_put_contents(LOG_PATH."error.log",'进入response:123'.PHP_EOL,FILE_APPEND);
        //首先接收微信服务器发送过来的post原文数据，一般是xml类型，不能用$_POST方式来接收
        $postStr = empty($GLOBALS['HTTP_RAW_POST_DATA']) ? file_get_contents("php://input"): $GLOBALS['HTTP_RAW_POST_DATA'];//这里做个兼容处理
        $this->accessToken = getAccessToken($this->app);//换取access_token

        if(!empty($postStr)){
            //有收到数据的时候。提取数据为对象格式
            $postObj = simplexml_load_string($postStr,"SimpleXMLElement",LIBXML_NOCDATA);//提取为对象
            $fromUsername = $postObj->FromUserName;//用户openid
            $toUsername = $postObj->ToUserName;//公众号微信号
            $receiveContent = trim($postObj->Content);//这里需要对内容进行一个trim(）处理，去除字符串首尾处的空白字符，否则可能拿不到正确的值
            $msgType = trim($postObj->MsgType);

            $this->user_info = $this->getUserInfo($this->accessToken,$fromUsername);//拿到用户信息的数组
            file_put_contents(ROOT_PATH . "runtime/custom_log/public_out.log", 'post数据：' .json_encode($postObj) . PHP_EOL, FILE_APPEND); //写入日志

            //先判断公众号是否活动结束
//            switch($this->app['is_end']) {
//                case 1:
//                    $this->taskEnd($this->app);
//                    die;
//                    break;
//                default:
//                    //如果存在公众号
//                    if ($new_app = $this->check_nums($this->app)) {
//                        //如果满员就会得到一个新app
//                        $this->newApp = $new_app;
//                    }
//                    $this->matchMsg($postObj);
//            }



        }

    }
    //匹配消息类型
    public function  matchMsg($obj){
        $msgType = trim($obj->MsgType);
        switch($msgType){
            case 'event':
                //匹配事件类型
                $this->receiveEvent($obj);//调用事件接收函数处理
                //$feedback = sprintf(MSG_TEXT, trim($postObj->FromUserName), trim($postObj->ToUserName), time(), "nihaoaaaw111");
                //echo $feedback;
                break;
            case 'text':
                $this->dealText($obj);
                break;
        }

    }
    //被动回复统一匹配
    public function matchAutoMsg($obj,$response){
        $type = $response['msg_type'];//要回复的消息类型
        $access_token = $this->accessToken;
        switch($type){
            case 'text':
                $this->sendText($obj,$response);
                break;
            case 'image':
                $media_id = $this->addTmpStuff($access_token,$response['media_path']);
                $this->sendImg($obj,$media_id);
                break;
            case 'news':
               //回复图文消息
                $this->sendNews($obj,$response['title'],$response['description'],$response['picurl'],$response['url']);
                break;

        }

    }
    //客服消息统一匹配
    public function matchCustomMsg($openid,$response){
        $type = $response['msg_type'];//要回复的消息类型
        $access_token = $this->accessToken;
        switch($type){
            case 'text':
                $data = sprintf(kf_txt_msg,$openid,$response['content']);
                $this->sendKf($data);
                break;
            case 'image':

                $media_id = $this->addTmpStuff($access_token,$response['media_path']);
                $data = sprintf(kf_txt_image,$openid,$media_id);
                $this->sendKf($data);
                break;
            case 'news':
                //回复客服图文消息
                //$this->sendNews($obj,$response['title'],$response['description'],$response['picurl'],$response['link_url']);
                $data = sprintf(kf_txt_news,$openid,$response['title'],$response['description'],$response['url'],$response['picurl']);
                $this->sendKf($data);
                break;

        }

    }

    //回复活动详情的图文消息
    public function sendDetail($obj,$response){
        $this->matchAutoMsg($obj,$response);
        /*
        $title = "点击了解活动详情>>>";
        $description = "戳此查看领书教程！！！";
        $pic_url ="http://tfbfi.pw/tp5/public/recommend/news/detail.jpg";
        $link_url = "http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/test/detail";
        $this->sendNews($obj,$title,$description,$pic_url,$link_url);
        */
    }
    /*
    //处理接受的文本
    回复消息[1]，获了解活动详情
    回复消息[2]，获取专属海报
    回复消息[3]，查询任务进度
    回复消息[4]，获取客服微信
    */
    public function dealText($obj){
        $receiveContent = trim($obj->Content);
        $openid = $obj->FromUserName;
        $accessToken = $this->accessToken;
        //数据库查找对应文本信息的回复内容
        //$sendContent = $this->getTextContent($receiveContent,$this->app['type'],$this->app['cat_id']);
        switch($receiveContent){
            case 1 :
                //回复活动详情
                //$this->sendDetail($obj,$sendContent);
                $flag = "auto_detail";
                $sendContent = $this->getAutoContent($flag);
                $this->sendDetail($obj,$sendContent);
                break;
            case 2 :
                $flag = "auto_poster";
                $sendContent = $this->getAutoContent($flag);
                $this->buildPoster($obj,$sendContent);
                break;
            case 3 :
                //任务进度
                $this->dealProgress($obj);

                break;
            case 4 :
                //获取客服微信
                $this->sendCustomQr($obj);
                break;

        }

    }
    //拿对应类型和期数的模板消息内容，通过flag直接匹配，不同接收的内容
    public function getTemplateContent($flag){
        $type = $this->app['type'];
        $cat_id = $this->app['cat_id'];
        $template_msg = new TemplateMsg();//自动回复消息表
        $msg = $template_msg->getOne(['type'=>$type,'cat_id'=>$cat_id,'flag'=>$flag]);//拿对应类型对应期数的回复内容
        return $msg;
    }
    //拿对应类型期数和flag的回复内容，通过flag直接匹配，不同接收的内容
    public function getAutoContent($flag){
        $type = $this->app['type'];
        $cat_id = $this->app['cat_id'];
        $auto_msg = new AutoMsg();//自动回复消息表
        $msg = $auto_msg->getOne(['type'=>$type,'cat_id'=>$cat_id,'flag'=>$flag]);//拿对应类型对应期数的回复内容
        return $msg;
    }
    //
    public function sendCustomQr($obj){
        $flag ="auto_custom_qrcode";//自动回复客服二维码
        $response = $this->getAutoContent($flag);
        $kf_dir = ROOT_PATH.'public/'.config("recommend.dir").'/'.'kefu';
        $kf_path = $kf_dir.'/'.$response['media_path'];

        $this->matchAutoMsg($obj,$response);
        //$kf_path = glob($kf_dir."/huifukefu.jpg")[0];
        //file_put_contents(LOG_PATH.'error.log','图片'.$kf_path);
        //$kf_img = $this->addTmpStuff($accessToken,$kf_path);
        //$this->sendImg($obj,$kf_img);//被动回复客服微信二维码

    }


    //接收事件函数
    public function receiveEvent($obj){
        //file_put_contents(LOG_PATH."error.log",'进入事件:'. $obj.PHP_EOL,FILE_APPEND);
        switch(trim($obj->Event)){
            case 'subscribe':
                //匹配订阅事件，又可以分为直接订阅和经过场景值二维码进入并且未订阅用户
                $this->checkSubscribe($obj);//检查是否取关过
                if(!empty(trim($obj->EventKey))){
                    //存在该参数说明是通过场景值二维码进来的新关注的用户
                    $eventKey =  explode("_",$obj->EventKey)[1];//场景值带前缀qrscene_后面的参数值
                    $this->dealEvent($obj,$eventKey);

                }else{
                    //无场景值的时候或者场景值为空，给该用户
                    $this->dealEvent($obj);
                }

                break;
            case 'SCAN' :
                //通过二维码扫描的事件，并且用户已经订阅过
                $eventKey = trim($obj->EventKey);
                $this->dealEvent($obj,$eventKey);
                break;

            case 'unsubscribe' :
                //取消关注推送事件
                $this->countLostUser($obj);
                break;

            case "CLICK":
                //自定义菜单click事件推送
                $eventKey = trim($obj->EventKey);
                switch($eventKey){
                    case 'customer':
                        $this->dealCustomer($obj);
                        break;
                    case 'task_progress':
                        //如果匹配，则回复一条图文消息，显示任务完成进度
                        $this->dealMenuEvent($obj);
                        break;
                    case 'unique_poster':
                        $this->buildPoster($obj);//调用函数生成专属海报
                        break;
                    case 'receive_welfare':
                        $this->receiveWelfare($obj);//领取福利，也就是跳到授权的index页面去
                        break;
                    case 'interest_game':
                        //记录一下点击次数
                        $count = file_get_contents(LOG_PATH."game_click_counts.log");
                        if(!$count){
                            //如果没有该文件或者文件内容为0，空
                            $count = 1;
                        }else {
                            $count += 1;
                        }
                        file_put_contents(LOG_PATH."game_click_counts.log",$count);
                        $this->receiveGame($obj,$eventKey);//专属游戏
                        break;

                }
                break;


        }
    }
    //检查关注状态
    public function checkSubscribe($obj){
        $openid = $obj->FromUserName;
        $app_id = $this->app['id'];
        $user_recommend = new UserRecommend();
        $user = $user_recommend->getUser($openid,$app_id);
        if($user){
            //只处理存在用户的情况
            if($user['is_subscribe'] == 0){
                //更新为关注
                $user_recommend->addOrUp(['id'=>$user['id']],['is_subscribe'=>1]);//更新并返回更新数据
            }
        }
    }
    //记录取消关注用户人数,这里主要是记录用户关注状态，如果用户取关，则把用户的is_subscribe变成0，在关注的时候需要判断下用户是否曾经关注过
    public function countLostUser($obj){
        $openid = $obj->FromUserName;
        $app_id = $this->app['id'];
        $user = new UserRecommend();
        $rs =$user->addOrUp(['openid'=>$openid,'app_id'=>$app_id],['is_subscribe'=>0,'unsubscribe_time'=>time()]);//更新并返回更新数据


    }


    //回复客服二维码
    public function dealCustomer($obj){
        $kf_dir = ROOT_PATH.'public/'.config("recommend.dir").'/'.'kefu';
        $kf_path = glob($kf_dir."/*.*")[0];
        $kf_img = $this->addTmpStuff($this->accessToken,$kf_path);//素材id
        $this->sendImg($obj,$kf_img);
    }

    //回复游戏类的图文消息
    public function receiveGame($obj,$type){
        $openid = trim($obj->FromUserName);
        $smallGame = new SmallGame();//实例化app表模型,该对象就代表这张表
        $games = $smallGame->getGameInfo($type);//拿到该类型下所有游戏信息

        $news_counts = count($games);//图文数量
        $items = $this->bindItems($games,$obj);//拼接多个图文item
        $this->sendMultiNews($obj,$news_counts,$items);



    }
    //拼接图文
    public function bindItems($items_arr,$obj){
        $openid = trim($obj->FromUserName);
        $user_recommend = new UserRecommend();//实例化app表模型,该对象就代表这张表
        $user = $user_recommend->getUser($openid,$this->app['id']);//先查该用户数据
        $news = '';
        foreach ($items_arr as $item){
           // http://www.djfans.net/tp5/public/index.php?s=/officialnumber/index/index/user_id/{$user['id']}/app_id/" . $user['app_id'];

            $item['game_url'] = $item['game_url']."/user_id/{$user['id']}/app_id/" . $user['app_id'];
            $news .= sprintf(MSG_MULTI_PIC_TXT_INNER,$item['game_name'],$item['description'],$item['pic_url'],$item['game_url']);
        }
        return $news;

    }
    //领取福利
    public function receiveWelfare($obj){
        $openid = trim($obj->FromUserName);
        //$time = time();
        //先查用户表得到用户数据
        $user_recommend = new UserRecommend();//实例化app表模型,该对象就代表这张表
        $user = $user_recommend->getUser($openid,$this->app['id']);//先查该用户数据
        if(!$user) {
            exit();
        }
        if ($user['recommended_num'] >= $user['target_num']) {
            //如果用户已经达成目标，则可以直接领取福利
            $success_url = "http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/reward_task/collectinfo/app_id/{$user['app_id']}/type/{$user['type']}";

            $pic_url = "http://tfbfi.pw/tp5/public/recommend/news/fuli.jpg";
            //$pic_url ="http://pic35.nipic.com/20131109/13533589_110928675314_2.jpg";
            $title = "专属福利领取";
            $description = $user['nickname'].",任务完成，现在可以领取书籍喽~戳此领取>>>\n活动发货及后续问题，可以咨询客服，微信号：anne65669";

            $this->sendNews($obj,$title,$description,$pic_url, $success_url);

            //exit();
        }else{
            //未达标回复的文本消息
            //$kf_data = sprintf(kf_txt_msg, $openid, $user['nickname'] . "，您还没有完成目标，不能");
            //$this->sendKf($kf_data);
            $content = $user['nickname'] . "，您还没有完成目标，不能领取专属福利，具体任务步骤请点击任务查看~谢谢亲的支持~";
            $this->sendText($obj,$content);
        }



    }
    //处理自定义菜单事件推送---任务进度
    public function dealProgress($obj){
        $openid = trim($obj->FromUserName);
        $access_token = $this->accessToken;
        //$time = time();
        //先查用户表得到用户数据
        //$user_recommend = new UserRecommend();//实例化app表模型,该对象就代表这张表
        //$user = $user_recommend->getUser($openid,$this->app['id']);//先查该用户数据
        $user = $this->checkUser($openid);
        $cur_num = $user['recommended_num'];//已推荐人数
        $target = $user['target_num'];//目标人数
        //被动回复图文消息
        $rest_book = $this->getRestBook();//剩余书籍数
        /*
        $link_url = ""; //图文跳转地址，为空不跳转
        $pic_url = "http://tfbfi.pw/tp5/public/recommend/news/renwu.jpg"; //图文图片地址，网络资源，非本地
        $title ="任务进度跟踪！";
        $description ="任务进度：[{$cur_num}/{$target}];".PHP_EOL."还剩最后{$rest_book}本就要被抢完啦！么么哒~";
        //$this->sendNews($obj,$title,$description,$pic_url,$link_url);
        */
        //被动回复图文
        $flag = 'auto_progress';//任务进度
        $sendContent = $this->getAutoContent($flag);//获取该flag内容
        $sendContent['description'] = "任务进度：[{$cur_num}/{$target}];".PHP_EOL."还剩最后{$rest_book}本就要被抢完啦！么么哒~";
        $this->matchAutoMsg($obj,$sendContent);//自动匹配消息发送，不一定要是图文。

        $data = array(
            'update_time'=>date("Y-m-d H:i:s",time())
        );
        Db::name('user_recommend')->where('openid',$openid)->where('app_id',$this->app['id'])->where('type',$this->app['type'])->update($data);


    }
    //任务完成被动回复的消息
    public function  sendSucAutoMsg($obj){
        $flag = 'auto_success';//任务进度
        $sendContent = $this->getAutoContent($flag);//获取该flag内容
        $this->matchAutoMsg($obj,$sendContent);//自动匹配消息发送，不一定要是图文。
    }
    //全新用户生成海报并保存
    public function buildNewPoster($obj){

        $cat_id = $this->app['cat_id'];
        $type = $this->app['type'];//活动类型，可以区分同一个公众号不同活动
        $openid = trim($obj->FromUserName);//用户openid
        $user_info = $this->getUserInfo($this->accessToken,$openid);//拿到用户信息的数组
        $app_id = $this->app['id'];

        $backgroud_path = $this->getBackground();//背景图统一放在background目录
        $head_url = $this->getHeadImg($openid,$app_id,$backgroud_path);//拿到头像路径
        if($head_url){
            $arr_picinfo[] = $head_url;
        }
        $nickname =$user_info['nickname'];//用户昵称
        $nickname_url = $this->getWaterText($nickname,$backgroud_path);//用户昵称水印信息
        if($nickname_url){
            $arr_textinfo[] = $nickname_url;
        }
        if(!empty($eventKey)) {
            //场景值不为空则查推荐人
            //$recommend_user = $user_recommend->change_recommended_num($eventKey, $this->app['id']);//更新推荐人数并返回推荐人id
            //$user_info['recommend_user_id'] =  $recommend_user['id'];//推荐人id
            $kf_data = sprintf(kf_txt_msg, $openid, "您通过 " .$recommend_user['nickname'] ." 的二维码成为我们的好朋友~么么哒");
            $this->sendKf($kf_data);
        }
        $this->sendLogo($openid);//回复logo欢迎语
        //如果不存在该用户，说明该用户是新用户，可以先输出海报，然后计数，通过场景值给推荐人计数加1，最后保存该用户的信息，包括场景值和推荐人id
        $uniq_scene =  md5(date("YmdHis").uniqid(rand()));    //这里采用字符串类型的scene_id,随机生成一个字符串作为用户专属场景值
        $qrcode_url = $this->getWaterQrcode($this->accessToken,$uniq_scene,$backgroud_path);//默认app未满员，获取二维码水印信息

        //判断是否满足热切换的条件，也就是查看该app的max_num是否达标，如果达标则进行切换
        if(isset($this->newApp['id'])){
            //如果存在新app，说明当前app已经满员了，需要切换公众号二维码
            $new_access_token = getAccessToken($this->newApp);
            //$user_qrcode_img = $this->getTmpStrQrcode($new_access_token,$uniq_scene);//换取二维码，返回二维码路径
            $qrcode_url = $this->getWaterQrcode($new_access_token,$uniq_scene,$backgroud_path);//换取二维码，返回二维码路径
        }
        if($qrcode_url){
            $arr_picinfo[] = $qrcode_url;
        }

        $media_id = $this->watermark($backgroud_path,$arr_picinfo,$arr_textinfo);
        unlink($head_url['path']);//删除头像图片
        unlink($qrcode_url['path']);//删除二维码图片
        //客服消息回复
        $kf_data = sprintf(kf_txt_msg,$openid,$user_info['nickname']."，您的专属海报正在生成中...");
        $this->sendKf($kf_data);
        //回复图片消息
        if(!empty($media_id)) {
            $this->sendImg($obj, $media_id);
            //$kf_data = sprintf(kf_txt_msg,$openid,"回复消息[1]，获取客服微信二维码");
            //$this->sendKf($kf_data);
            //通过二维码场景值查到推荐人，并且更新推荐人的推荐人数，返回推荐人id

            //保存用户

            if(!empty($eventKey)) {
                $user_recommend = new UserRecommend();
                //场景值不为空则查推荐人
                $recommend_user = $user_recommend->change_recommended_num($eventKey, $this->app['id']);//更新推荐人数并返回推荐人id

                $recommend_user_id=  $recommend_user['id'];//推荐人id
                $save_rs = $this->saveNewUser($media_id,$uniq_scene,$recommend_user_id);
            }else{
                $save_rs = $this->saveNewUser($media_id,$uniq_scene);
            }


            //推荐成功消息回复

            //有flag说名当前推荐人数是target_num+1，所以不需要推消息，没有的话就推，需要判断是否刚好完成
            if(!isset($recommend_user['flag'] ) && !empty($recommend_user['id']) && $save_rs ) {
                //file_put_contents(LOG_PATH.'error.log',json_encode($recommend_user).PHP_EOL,FILE_APPEND);
                //file_put_contents(LOG_PATH."error.log",'推荐人信息'.json_encode($recommend_user).'用户保存结果'.$save_rs.PHP_EOL,FILE_APPEND);
                switch($this->app['is_template']) {
                    case 0:
                        $this->sendSucKf($recommend_user, $user_info);//推送客服消息
                        break;
                    case 1:
                        $this->sendSucTemplate($recommend_user, $user_info);
                        break;
                }

            }
        }

    }


    //生成已存在用户的专属海报
    public function buildPoster($obj,$user){
        $openid =  $obj->FromUserName;
        $user_info = $this->user_info;
        $app_id = $this->app['id'];
        $backgroud_path = $this->getBackground();//背景图统一放在background目录
        /*
        $type = $this->app['type'];
        $openid = $obj->FromUserName;
        $user_recommend = new UserRecommend();//实例化app表模型,该对象就代表这张表
        //$user = $user_recommend->getUser($openid,$this->app['id']);//先查该用户是否在用户表中存在，存在的话说明不是新加的用户
        $user = $this->checkUser($openid,$this->app,$this->user_info);//检查用户是否存在
        */


            if($user['recommended_num'] >= $user['target_num']){
                //如果已推荐人数大于等于目标人数，可以选择回复模板消息或者图文消息
                //$success_url = "http://www.djfans.net/tp5/public/index.php?s=/officialnumber/index/index/user_id/{$user['id']}/app_id/".$user['app_id'];
                $this->sendSucAutoMsg($obj);//任务完成回复消息，这里是自动回复
                /*
                $success_url = "http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/reward_task/collectinfo/app_id/{$user['app_id']}/type/{$user['type']}";
                $pic_url = "http://tfbfi.pw/tp5/public/recommend/news/success.jpg";
                //file_put_contents(LOG_PATH."error.log",'success_url:'. $success_url.PHP_EOL,FILE_APPEND);

                //$pic_url ="http://pic35.nipic.com/20131109/13533589_110928675314_2.jpg";
                $title ="恭喜您,任务完成";
                $description ="恭喜您，任务完成，现在可以领取书籍喽~戳此领取>>>\n活动发货及后续问题，可以咨询客服，微信号：anne65669";
                $this->sendNews($obj,$title,$description,$pic_url, $success_url);
                //$r_data = sprintf(kf_txt_msg,$openid,"如有疑问，请添加客服微信或者扫描二维码咨询，\n微信号：anne65669，填写收货地址\n活动发货及后续问题，也可以咨询客服哦~");
                //$this->sendKf($r_data);
                //$this->sendKeFuQrcode($openid);//回复客服微信二维码

                //$news_data = sprintf(kf_txt_news,$openid,$title,$description,$success_url,$pic_url);
               // $this->sendKf($news_data);//调用客服接口发送图文消息
                */
            }else {
                //未达标回复的客服消息
                /*
                $flag = "old_poster_making";
                $sendContent = $this->getAutoContent($flag);//获取该flag内容
                $this->matchCustomMsg($obj,$sendContent);//自动匹配消息发送，不一定要是图文。
                */
                $kf_data = sprintf(kf_txt_msg, $openid, $user_info['nickname'] . "，目前为您助力的小伙伴人数为:" . $user['recommended_num'] . '人;' . "您的专属邀请函正在制作中，赶紧邀请小伙伴加入吧0_0~");
                $this->sendKf($kf_data);

                //如果存在用户，判断是否存在media_id,并且有效，直接根据参数组合专属海报回复给用户
                if ($user['media_timestamp'] > time()) {
                    //有效则直接返回media_id
                    $media_id = $user['media_id'];
                    //回复图片消息
                    $this->sendImg($obj, $media_id);
                } else {
                    $head_url = $this->getHeadImg($openid,$app_id,$backgroud_path);//拿到头像路径
                    if($head_url){
                        $arr_picinfo[] = $head_url;
                    }
                    $nickname =$user_info['nickname'];//用户昵称
                    $nickname_url = $this->getWaterText($nickname,$backgroud_path);//用户昵称水印信息
                    if($nickname_url){
                        $arr_textinfo[] = $nickname_url;
                    }
                    //无效，也就是过期了就重新制作一张，并更新到数据库
                    $uniq_scene = $user['uniq_scene'];//拿到自己的唯一场景值
                    //$user_qrcode_img = $this->getTmpStrQrcode($this->accessToken, $uniq_scene);//换取二维码，返回二维码路径
                    $qrcode_url = $this->getWaterQrcode($this->accessToken,$uniq_scene,$backgroud_path);//获取二维码位置信息
                    if($qrcode_url){
                        $arr_picinfo[] = $qrcode_url;
                    }
                    $media_id = $this->watermark($backgroud_path,$arr_picinfo,$arr_textinfo);
                    unlink($head_url['path']);//删除头像图片
                    unlink($qrcode_url['path']);//删除二维码图片
                    //回复图片消息

                    if (!empty($media_id)) {
                        $this->sendImg($obj, $media_id);
                        //回复完后更新该用户的media_id到数据库
                        $expire_time = time() + 3600 * 24 * 2.5;//设置有效期为2.5天
                        Db::name("user_recommend")->where('id',$user['id'])->update(["media_id"=>$media_id,"media_timestamp"=>$expire_time]);
                        //$user_recommend->updateMediaId($user['id'], $media_id, $expire_time);
                    }
                }

            }
            $data = array(
                'update_time'=>date("Y-m-d H:i:s",time())
            );
            Db::name('user_recommend')->where('openid',$openid)->where('app_id',$app_id)->update($data);//这里只需要openid和app_id，因为类型和期数在开始就检查过，只要进来了就会更新成该app的当前type和cat_id



            /*
            $water_img = http_request($user['headimgurl']);//读取网络图片内容
            $img_dir = $this->app_dir . DS . config('recommend.headimgurl');//目录
            $img_url = $img_dir .DS.$openid.uniqid().".png";
            //$img_url ="/web/wxvote/tp5/public".DS.config("recommend.dir").DS . 'app-'.$this->app['id'] . DS . config('recommend.headimgurl').DS.date("ymdhis").uniqid(rand()).".png";
            file_put_contents($img_url, $water_img);//将一个二进制字符串写入图片
            $img_url = getCircleAvatar($img_url,$img_url);//获取圆形头像，png格式
            $nickname =$user['nickname'];//用户昵称
            if ($user['recommended_num'] >= $user['target_num']) {
                //如果已推荐人数大于等于目标人数，可以选择回复模板消息或者图文消息

                $success_url = "http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/reward_task/collectinfo/app_id/{$user['app_id']}/type/{$user['type']}";

                $pic_url = "http://tfbfi.pw/tp5/public/recommend/news/success.jpg";

                //$pic_url ="http://pic35.nipic.com/20131109/13533589_110928675314_2.jpg";
                $title = "恭喜您任务达标";
                $description = "任务完成，现在可以领取书籍喽~戳此领取>>>\n活动发货及后续问题，可以咨询客服，微信号：anne65669";

                $this->sendNews($obj,$title,$description,$pic_url, $success_url);



            } else {
                //未达标回复的客服消息
                $kf_data = sprintf(kf_txt_msg, $openid, $user['nickname'] . "，目前为您助力的小伙伴人数为:" . $user['recommended_num'] . '人;' . "您的专属邀请函正在制作中，赶紧邀请小伙伴加入吧0_0~");
                $this->sendKf($kf_data);

                //如果存在用户，判断是否存在media_id,并且有效，直接根据参数组合专属海报回复给用户
                if ($user['media_timestamp'] > time()) {
                    //有效则直接返回media_id
                    $media_id = $user['media_id'];
                    //回复图片消息
                    $this->sendImg($obj, $media_id);
                } else {

                    //无效，也就是过期了就重新制作一张，并更新到数据库
                    $uniq_scene = $user['uniq_scene'];//拿到自己的唯一场景值
                    $user_qrcode_img = $this->getTmpStrQrcode($this->accessToken, $uniq_scene);//换取二维码，返回二维码路径
                    $media_id = $this->watermark($nickname, $img_url, $user_qrcode_img);
                    //回复图片消息
                    if (!empty($media_id)) {
                        $this->sendImg($obj, $media_id);
                        //回复完后更新该用户的media_id到数据库
                        $expire_time = time() + 3600 * 24 * 2.5;//设置有效期为2.5天
                        $user_recommend->updateMediaId($user['id'], $media_id, $expire_time);
                    }

                }
            }
            $data = array(
                'update_time'=>date("Y-m-d H:i:s",time())
            );
            Db::name('user_recommend')->where('openid',$openid)->where('app_id',$this->app['id'])->where('type',$type)->update($data);
            */
        }



    //检查用户，并更新新一期的老用户，返回老用户数据,$type:当前活动类型
    public function checkUser($openid){
        $user_info =$this->user_info;
        $app = $this->app;
        $app_id = $app['id'];
        $type = $app['type'];
        $target_num = $app['target_num'];
        $time = date("Y-m-d H:i:s",time());
        $user_recommend = new UserRecommend();//实例化app表模型,该对象就代表这张表
        $user = $user_recommend->getUser($openid,$app_id);//检查用户是否存在
        if(!$user){
            //如果没有该用户，直接返回
            return 0;
        }
        if($user['type'] != $type){
            //类型不符合，说明是往期活动，更新该用户数据，并返回用户数据
            //id,openid,nickname,headimgurl,uniq_scene,recommended_num,media_id,
            //media_timestamp,app_id,target_num,type,recommend_user_id
            $uniq_scene =  md5(date("YmdHis").uniqid(rand()));
            $where = ['openid'=>$openid,'app_id'=>$app_id] ;
            $data = [
                'nickname'=>$user_info['nickname'],
                'headimgurl'=>$user_info['headimgurl'],
                'recommended_num'=>0,
                'recommend_user_id'=>0,
                'media_id'=>'',
                'media_timestamp'=>0,
                'target_num'=>$target_num,
                'type'=>$type,
                'uniq_scene'=>$uniq_scene,
               // 'update_time'=>$time,
            ];
             if($new_data = $user_recommend->updateUser($where,$data)){
                 return $new_data;//更新并返回数据
             }
        }else{
            return $user;//如果是本期用户直接返回
        }
    }
    //关注回复欢迎语
    public function sendLogo($openid){
        $user_info =$this->user_info;
        //调用客服消息回复文本
        /*
        //<a href='https://www.djfans.net/tp5/public/index.php?s=/officialnumber/test/index'>戳此查看活动详情</a>
        $content = "Hi~ {$user_info['nickname']}，终于等到你~你有一份五月阅读书单待领取\n↓↓↓
<a href='http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/test/detail'>戳此查看领书详情</a>，送完截止！
\n回复消息[1]，了解领书详情
回复消息[2]，获取专属海报
回复消息[3]，查询任务进度
回复消息[4]，获取客服微信";
        $kf_data = sprintf(kf_txt_msg,$openid,$content);
        $this->sendKf($kf_data);
        */
        $flag ="kf_welcom_logo";
        $sendContent = $this->getCustomContent($flag); //获取客服消息表的内容
        $sendContent['content'] = "Hi~ {$user_info['nickname']},".$sendContent['content'];
        $this->matchCustomMsg($openid,$sendContent);//发送客服消息
    }
    //获取客服消息表的内容
    public function getCustomContent($flag){
        $cat_id = $this->app['cat_id'];
        $type = $this->app['type'];
        $custom = new KfMsg();
        $custom_data = $custom->getOne(['type'=>$type,'cat_id'=>$cat_id,'flag'=>$flag]);//拿到要回复的客服消息内容
        return $custom_data;
    }
    //处理相应的订阅事件,订阅和扫描事件的处理过程
    public function dealEvent($obj,$eventKey=''){

        //file_put_contents(ROOT_PATH . "runtime_check/public_timeout.log", '---event数据：' .json_encode($obj) . PHP_EOL, FILE_APPEND);

        //说明是subscribe进来的带场景值的方式，先到用户表中查是否存在该用户，因为可能有那种用户关注又取消，再关注的情况
        $openid = trim($obj->FromUserName);//用户openid
        $user = $this->checkUser($openid);//拿到用户数据
        file_put_contents(LOG_PATH."error.log",'用户新数据:'. json_encode($user,JSON_UNESCAPED_UNICODE).PHP_EOL,FILE_APPEND);

        //拿背景图

        /*
        //带场景值的二维码生成
        $uniq_scene =  md5(date("YmdHis").uniqid(rand()));//获取一个唯一的场景值
        $this->getWaterQrcode($uniq_scene,$backgroud_path);//获取二维码位置信息
         */
        //首先都回复组装好的图片给用户，然后再做对应的数据库操作
        if($user){
            $this->sendLogo($openid);//回复logo欢迎语
            $this->buildPoster($obj,$user);//已存在用户生成海报

        }else {
            //该用户为全新的新用户,无场景值说明是直接搜索公众号关注的
            $this->buildNewPoster($obj);

        }



    }
    //保存新用户
    public function saveNewUser($media_id,$uniq_scene,$recommend_user_id=''){

        $user_recommend = new UserRecommend();
        //$this->sendImg($obj, $media_id);//回复图片
        //保存用户
        if($recommend_user_id){
            $user_info['recommend_user_id'] =  $recommend_user_id;//推荐人id
        }
        $user_info['uniq_scene'] = $uniq_scene; //当前用户专属场景值
        if(!empty($this->newApp)){
            //满员了的话，该用户需要保存到新的app下面
            $user_info['app_id'] = $this->newApp['id'];
            $user_info['type'] = $this->newApp['type'];
            $user_info['cat_id'] = $this->newApp['cat_id'];
            $user_info['target_num'] = $this->newApp['target_num'];
        }else {
            $user_info['app_id'] = $this->app['id'];
            $user_info['type'] = $this->app['type'];
            $user_info['cat_id'] = $this->app['cat_id'];
            $user_info['target_num'] = $this->app['target_num'];
        }
        $user_info['media_id'] = $media_id;
        $user_info['media_timestamp'] = time() + 86400 * 2.5;//设置有效期为2.5天;
        //$user_info['nickname'] = base64_encode($user_info['nickname']);
        $user_info['nickname'] = $this->filterEmoji($user_info['nickname']);//过滤emoji表情
        //$user_info['target_num'] = $this->app['target_num'];
        $save_rs = $user_recommend->save_user($user_info);//保存用户
        if(!$save_rs){
            $save_rs = 0;
        }
        return $save_rs;
    }
    //推送模板通知
    public function sendSucTemplate($recommend_user,$user_info){
        //保存成功则发送通知给推荐人
        //用户保存后给推荐人推送服务通知
        //先指定模板通知参数内容
        $flag = "template_recommend_notice";
        $template_data = $this->getTemplateContent($flag);//拿要发送的模板内容
        $rest_book = $this->getRestBook();//剩余领取的书籍数量
        $recommend_openid = $recommend_user['openid'];//获取推荐人openid
        $template_data['templatId'] = $this->app['template_id'];
        $template_data['first'] = "您的朋友{$user_info['nickname']}扫描了您的邀请卡，加入您的助力团~";//首行语
        $template_data['key1'] = $recommend_user['nickname'];//关键词1，这里是推荐人
        $template_data['key2'] = $user_info['nickname'];//关键词2，这里是被推荐人
        $distance = $recommend_user['target_num']-$recommend_user['recommended_num'];//差距人数
        if(!$template_data['remark']) {
            //如果备注为空，说明是固定的内容，不通过数据库控制
            $template_data['remark'] = "[任务进度]:{$recommend_user['recommended_num']}/{$recommend_user['target_num']};\n
还差{$distance}位好友你就可以获得领取资格了;\n
还剩最后{$rest_book}本就要被抢完啦！加油么么哒!";
        }
        $template_data['url'] = $template_data['url']."/app_id/{$recommend_user['app_id']}";

        if($distance == 0){
            $this->changeRestBook();//修改剩余书数量
            //file_put_contents(LOG_PATH.'error.log','distances:'.$distance.PHP_EOL,FILE_APPEND);
            //如果达成目标，推送一个带url的模板通知
            $template_data['first'] = '恭喜您，任务完成，现在可以领取书籍喽~！';//首行语
            //$template_data['url'] = "http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/reward_task/collectinfo/app_id/{$recommend_user['app_id']}";
            //$template_data['url'] = "http://www.djfans.net/tp5/public/index.php?s=/officialnumber/index/index/app_id/".$recommend_user['app_id'];//跳转地址，如果指定小程序，则优先跳小程序
            $template_data['remark'] = "戳此领取>>>\n活动发货及后续问题，可以咨询客服，微信号：anne65669";//底部备注
        }

        $template_res = $this->sendServiceNotice($recommend_openid, $this->accessToken, $template_data);

        /*
        $rest_book = $this->getRestBook();//剩余领取的书籍数量
        $recommend_openid = $recommend_user['openid'];//获取推荐人openid
        //$templatId = $this->getTemplateId(1, $this->accessToken);//通过模板短编号拿到模板id,模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
        $template_data['templatId'] = $this->app['template_id'];
        $template_data['url'] = '';//跳转地址，如果指定小程序，则优先跳小程序
        $template_data['first'] = "您的朋友{$user_info['nickname']}扫描了您的邀请卡，加入您的助力团~";//首行语
        $template_data['key1'] = $recommend_user['nickname'];//关键词1，这里是推荐人
        $template_data['key2'] = $user_info['nickname'];//关键词2，这里是被推荐人
        //$template_data['key3'] = $recommend_user['target_num'];//目标人数
        // $template_data['key4'] = $recommend_user['recommended_num'];//已推荐人数
        $distance = $recommend_user['target_num']-$recommend_user['recommended_num'];//差距人数
        $template_data['remark'] ="[任务进度]:{$recommend_user['recommended_num']}/{$recommend_user['target_num']};\n
还差{$distance}位好友你就可以获得领取资格了;\n
还剩最后{$rest_book}本就要被抢完啦！加油么么哒!";

 //底部备注
        $template_data['url'] = "http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/reward_task/collectinfo/app_id/{$recommend_user['app_id']}/type/{$recommend_user['type']}";


        if($distance == 0){
            $this->changeRestBook();//修改剩余书数量
            //file_put_contents(LOG_PATH.'error.log','distances:'.$distance.PHP_EOL,FILE_APPEND);
            //如果达成目标，推送一个带url的模板通知
            $template_data['first'] = '恭喜您，任务完成，现在可以领取书籍喽~！';//首行语
            $template_data['url'] = "http://tfbfi.pw/tp5/public/index.php?s=/officialnumber/reward_task/collectinfo/app_id/{$recommend_user['app_id']}/type/{$recommend_user['type']}";
            //$template_data['url'] = "http://www.djfans.net/tp5/public/index.php?s=/officialnumber/index/index/app_id/".$recommend_user['app_id'];//跳转地址，如果指定小程序，则优先跳小程序
            $template_data['remark'] = "戳此领取>>>\n活动发货及后续问题，可以咨询客服，微信号：anne65669";//底部备注
        }

        $template_res = $this->sendServiceNotice($recommend_openid, $this->accessToken, $template_data);
        */
    }
    //推荐成功推送客服消息
    public function sendSucKf($recommend_user,$user_info){
        $cur_num = $recommend_user['recommended_num'];
        $target_num =$recommend_user['target_num'];
        $distance_num = $target_num -  $cur_num;
        $rest_book = $this->getRestBook();//剩余领取的书籍数量
        $rest_logo = "还差{$distance_num}位好友你就可以获得领取资格了，还剩最后{$rest_book}本就要被抢完啦！加油么么哒(づ￣ 3￣)づ";
        if(!$rest_book){
            $rest_logo = "很抱歉，书籍已被抢完啦，欢迎参与下次活动！";
        }
        if($distance_num == 0){
            $this->changeRestBook();//修改剩余书数量
            $notice_data = "恭喜您，任务完成，现在可以领取书籍喽~
请扫描下方二维码添加客服微信，咨询活动发货及后续问题
或添加客服微信号：anne65669
祝你生活愉快";
            $r_data = sprintf(kf_txt_msg,$recommend_user['openid'],$notice_data);
            $this->sendKf($r_data);
            $this->sendKeFuQrcode($recommend_user['openid']);
        }else {
            $notice_data = "您的朋友{$user_info['nickname']}扫描了您的邀请卡，加入您的助力团~\n[任务进度]：{$cur_num}/{$target_num}\n{$rest_logo}";
            $r_data = sprintf(kf_txt_msg,$recommend_user['openid'],$notice_data);
            $this->sendKf($r_data);
        }

    }
    //用户完成任务，更新剩余书籍数量
    public function changeRestBook(){
        $app_id = $this->app['id'];
        $rest_book = $this->app['rest_book'];
        $book_step = $this->app['book_step'];
        $rest = $rest_book -$book_step;
        if($rest < 0){
            $rest = $rest_book;//小于10本那么返回实际本数
        }
        Db::name('official_app')->where('id',$app_id)->update(['rest_book'=>$rest]);

    }

    //获取剩余领取的书籍数量
    public function getRestBook(){
        $app_id = $this->app['id'];
        $rest = $this->app['rest_book'];
        /*
       $user = new UserRecommend();
       $app_target = $this->app['target_num'];//获取公众号设置的目标人数
       $user_counts = $user->where('recommended_num','>=',$app_target)->where('app_id',$app_id)->count('id');//获取达成目标的人数
       $t_book = $this->app['total_counts'];
       $rest = $t_book-$user_counts;//剩余书数量
       //$rest = $t_book- 10;

       if($rest > 0){
           Db::name('official_app')->where('id',$app_id)->update(['rest_book'=>$rest]);
           return $rest;
       }
       */
        return $rest;

    }
    //回复客服微信二维码
    public function sendKeFuQrcode($openid){

        $kf_dir = ROOT_PATH.'public/'.config("recommend.dir").'/'.'kefu';
        $kf_path = glob($kf_dir."/*.*")[0];
        $kf_img = $this->addTmpStuff($this->accessToken,$kf_path);
        $this->sendKfImg($openid,$kf_img);
    }
    //回复客服图片消息
    public function sendKfImg($openid,$media_id){
        //define("kf_txt_image",json_encode(array("touser"=>'%s',"msgtype"=>"image","image"=>array("media_id"=>"%s"))));
        $img_data = sprintf(kf_txt_image,$openid,$media_id);
        $this->sendKf($img_data);
    }
    //过滤微信昵称中的emoji表情
    public function filterEmoji($str){
        //用户preg_replace_callback函数匹配字符串中的unicode字符，并且通过回调函数来处理匹配到的字符，也就是判断字节是否大于等于4，因为utf8最大字节是3
        //二emoji表情是4个字节以上的，该函数最终会返回替换后的目标字符串（或字符串数组），对于每个模式用于每个 subject 字符串的最大可替换次数。 默认是-1（无限制）。
        return preg_replace_callback("/./u",function($match){
                return strlen($match[0]) >= 4 ? '' : $match[0];
        },$str);

    }
    //获取用户基本信息
    public function getUserInfo($access_token,$openid){
        $url = "http://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $arr = json_decode(http_request($url),1);//返回json字符串
        if(isset($arr['errcode'])){
            //file_put_contents("publicNumberMsg.log",'错误号：'.$arr['errcode'].'错误信息：'.$arr['errmsg'].'---'.date("YmdHis").PHP_EOL,FILE_APPEND);
            return false;
        }else {
            return $arr;//返回用户信息数组
        }


    }
    //被动回复文本消息
    public function sendText($obj,$content){
        $news_data = sprintf(MSG_TEXT,$obj->FromUserName, $obj->ToUserName,time(),$content);
        echo $news_data;


    }
    //被动回复多图文
    public function sendMultiNews($obj,$news_counts,$news_items){
        //接收图文数量参数$news_counts和多个图文内容拼接好的xml
        $news_data = sprintf(MSG_MULTI_PIC_TXT_COVER,$obj->FromUserName, $obj->ToUserName,time(),$news_counts,$news_items);//多图文外部结构，内部可以加入多个item（每个item是一个图文内容）
        echo $news_data;
    }
    //被动回复图文消息
    public function sendNews($obj,$title,$description,$pic_url,$link_url){
        $news_data = sprintf(MSG_SINGLE_PIC_TXT,$obj->FromUserName, $obj->ToUserName,time(),$title,$description,$pic_url,$link_url);
        echo $news_data;

    }
    //被动回复图片消息
    public function sendImg($obj,$media_id){

        //图片回复
        //$feedback = sprintf(kf_txt_image,$fromUsername,$media_id);
        //$this->sendKf($access_token,$feedback);
        $feedback = sprintf(MSG_IMG, $obj->FromUserName, $obj->ToUserName, time(), $media_id);
        echo $feedback;
    }
    //

    //合并图像，返回media_id
    public function watermark($back_path,$arr_picinfo,$arr_textinfo){

        $file_dir = $this->app_dir . "/" . config('recommend.final');
        $this->createDir($file_dir);//创建目录

        $filepath = $file_dir.'/'.md5(date("YmdHis")).'.jpg';
        $result_path =createpic($back_path,$arr_picinfo,$arr_textinfo,$filepath);//获取合成的图片路径
        file_put_contents(LOG_PATH."error_log.log",'背景图路径：'.$back_path.'---合成路径：'.$filepath.'---结果路径：'. $result_path.date("YmdHis").PHP_EOL,FILE_APPEND);

        $media_id = $this->addTmpStuff($this->accessToken,$result_path);//拿到meida_id
        unlink($filepath);//删除合成图
        if($media_id){
            return $media_id;
        }else{
            return 0;
        }

    }
    //生成临时字符串场景值二维码
    public function getTmpStrQrcode($accessToken,$scene_str){
        /*
        if(empty($scene_str)) {
            $scene_str = microtime(true);
        }
        */

        $expire_time = 3600*24*7;
        //$scene_str= date("Ymdhis").uniqid();
        $url = "http://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$accessToken}";
        //echo sprintf(tmp_str_qrcode,3600*24*7,date("ymdhis"));
        //array("expire_seconds"=>'%s',"action_name"=>"QR_STR_SCENE","action_info"=>array("scene"=>array("scene_str"=>'%s')));
        $postStr =<<<EOT
{"expire_seconds": $expire_time,
 "action_name": "QR_STR_SCENE", 
 "action_info": {"scene": {"scene_str": "$scene_str"}}};
EOT;
        $rs_arr = json_decode(http_request($url,$postStr),1);//解析成数组
        if(isset($rs_arr['errcode'])){
            file_put_contents( LOG_PATH.'error.log',$rs_arr['errcode'].':'.$rs_arr['errmsg'].PHP_EOL,FILE_APPEND);//写入路径
        }
        //file_put_contents( LOG_PATH.'error.log','带参数二维码url:'.$rs_arr['url']);//写入路径
        //$user_img = MD5(date("ymdhis"));
        $user_dir = $this->app_dir . "/" . config('recommend.qrcode');
        $this->createDir($user_dir);//如果没哟目录就创建目录
        /*
        if(!file_exists($user_dir)){
            mkdir($user_dir,0777);
        }
        */
        $user_img = MD5(date("ymdhis"));
        $user_qrcode =  $user_dir . "/{$user_img}.jpg";//指定用户生成的专属二维码本地路径
        $img = $this->getQrcodeByTicket($rs_arr['ticket'],$user_qrcode);//换取二维码
       // $qrcode_str = http_request($user_qrcode,$rs_arr['url']);//读取网络资源。
        //file_put_contents( $user_qrcode,$qrcode_str);//写入路径
        // echo '<img src="'."http://www.djfans.net/tp5/public/recommend/app-".$this->app['id']."/user_qrcode/{$user_img}.jpg".'"/>';
        return $img;
        /*
         * 返回值json格式
         * ticket	获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
        expire_seconds	该二维码有效时间，以秒为单位。 最大不超过2592000（即30天）。
        url	二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
         */


    }
    //ticket换取二维码
    public function getQrcodeByTicket($ticket,$img){
        $url = "http://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
        //echo '<img src="'.http_request($url).'"/>';die;
        file_put_contents($img,http_request($url));//返回图片
        return $img;
    }
    //发送客服消息
    public function sendKf($data){
        $url ="http://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$this->accessToken}";
        http_request($url,$data);
    }
    //新增对应类型临时素材
    public function addStuff($access_token,$file_path,$type)
    {
        $url = "http://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=$type";
        if (class_exists("CURLFile")) {
            $data=array(
                'media'=>new \CURLFile($file_path)
            );

        }else{
            $data=array(
                'media'=>'@'.$file_path
            );
        }
        $res=json_decode(http_request($url,$data),1);
        //file_put_contents('mem.txt',$mem->get('access_token'));
        //这里需要把media_id保存到数据库，保存到用户对应的生日数字中，这里还返回了一个时间戳可以用来设置过期时间
        if(isset($res['errcode'])){
            //file_put_contents("publicNumberMsg.log",'上传素材失败：'.$res['errmsg'],FILE_APPEND);
            exit;
        }
        file_put_contents(LOG_PATH.'voice_media_id.log','上传的素材类型:'.$type.'---media_id:'.$res['media_id'].PHP_EOL,FILE_APPEND);
        return  $res['media_id'];

    }

    //新增临时图片素材
    public function addTmpStuff($access_token,$file_path){
        /*
         * http请求方式：POST/FORM，使用http
        http://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE
        调用示例（使用curl命令，用FORM表单方式上传一个多媒体文件）：
        curl -F media=@test.jpg "http://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE"
         */
        $url = "http://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
        //curl传输文件的2种方法，@新版本curl弃用
        if (class_exists("CURLFile")) {
            $data=array(
                'media'=>new \CURLFile($file_path)
            );

        }else{
            $data=array(
                'media'=>'@'.$file_path
            );
        }
        $res=json_decode(http_request($url,$data),1);
        //file_put_contents('mem.txt',$mem->get('access_token'));
        //这里需要把media_id保存到数据库，保存到用户对应的生日数字中，这里还返回了一个时间戳可以用来设置过期时间
        if(isset($res['errcode'])){
            //file_put_contents("publicNumberMsg.log",'上传素材失败：'.$res['errmsg'],FILE_APPEND);
            exit;
        }
        return  $res['media_id'];

    }
    /*
    //发送模板服务通知
     * http请求方式: POST
     * 请求url:http://api.weixin.qq.com/cgi-bin/message/template/send?access_token=ACCESS_TOKEN
     * 参数说明
        touser	是	接收者openid
        template_id	是	模板ID
        url	否	模板跳转链接
        miniprogram	否	跳小程序所需数据，不需跳小程序可不用传该数据
        appid	是	所需跳转到的小程序appid（该小程序appid必须与发模板消息的公众号是绑定关联关系）
        pagepath	是	所需跳转到小程序的具体页面路径，支持带参数,（示例index?foo=bar）
        data	是	模板数据
        color	否	模板内容字体颜色，不填默认为黑色
    注：url和miniprogram都是非必填字段，若都不传则模板无跳转
     * 若都传，会优先跳转至小程序。开发者可根据实际需要选择其中一种跳转方式即可。
     * 当用户的微信客户端版本不支持跳小程序时，将会跳转至url。
    *正常时的返回JSON数据包：
      {
           "errcode":0,
           "errmsg":"ok",
           "msgid":200228332
       }
    */
    public function  sendServiceNotice($openid,$access_token,$data){
        $post_arr = array("touser"=>$openid,
            "template_id"=>$data['templatId'],
            "url"=>$data['url'],
            "topcolor"=>"#FF0000",
            "data"=>array(
                "first" => array("value"=>$data['first'],"color"=>"#FF0000"),
                "keyword1" => array("value"=>$data['key1'],"color"=>"#173177"),
                "keyword2" => array("value"=>$data['key2'],"color"=>"#173177"),
                "remark" => array("value"=>$data['remark'],"color"=>"#000"),
            )
        );

        $url ="http://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";//请求url
        $postData = json_encode($post_arr);//json编码
        $res_arr = json_decode(http_request($url,$postData),1);//返回json数据，解析成数组
        if(!$res_arr['errcode']){
            //没有错误时候errcode为0
            //file_put_contents(LOG_PATH."error.log",'信息'.$res_arr['msgid'].PHP_EOL,FILE_APPEND);
          return $res_arr['msgid'];//返回消息id


        }else{
            file_put_contents(LOG_PATH."error.log",'错误号：'.$res_arr['errcode'].'错误信息'.$res_arr['errmsg'].PHP_EOL,FILE_APPEND);
            //return false;
        }
    }
    /*
     * 获取模板id
     * http请求方式: POST
       http://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=ACCESS_TOKEN
     *access_token	是	接口调用凭证
      template_id_short	是	模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     */
    public function getTemplateId($tp_id,$access_token){
        $url = "http://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$access_token}";
        $postData = <<<EOT
        {
           "template_id_short":"$tp_id"
       }
EOT;
        $res_arr = json_decode(http_request($url,$postData),1);
        if($res_arr['errcode']== 0){
            //成功时返回模板id
            return $res_arr['template_id'];
        }else{
            return false;
        }
    }
    /*
     * 创建自定义菜单
     *http请求方式：POST（请使用http协议） http://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN
     * 绑定小程序可以使用{
                             "type":"miniprogram",
                             "name":"聆听微课",
                             "url":"http://www.djfans.net/index.php/WebLessonController/index",
                             "appid":"wxd39a70bfffe094cb",
                             "pagepath":"pages/homePage/homePage"
                         },
     */
    //删除指定app的自定义菜单，通过token来区分是哪个app
    public function delMenu(){
        $access_token = $this->accessToken;
        //http请求方式：GET
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$access_token}";
        $res = json_decode(http_request($url),1);//发起请求，正确时返回{"errcode":0,"errmsg":"ok"}
        if($res['errcode'] == 0){
            //创建菜单成功时
            echo '菜单删除成功';
        }else{
            echo '菜单删除失败，错误号：'.$res['errcode'].'错误信息：'.$res['errmsg'];
        }

    }
    //创建菜单
    public function createMenu($access_token){
        $url = "http://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
        $menu_json = <<<EOT
            {
                 "button":[
                 {    
                     "name":"任务查看",
                      "sub_button":[
                      {  
                          "type":"view",
                          "name":"任务详情",
                          "url":"https://www.djfans.net/tp5/public/index.php?s=/officialnumber/test/index"
                          },
                      {
                           "type":"click",
                           "name":"任务进度",
                           "key":"task_progress"
                      },{
                           "type":"click",
                           "name":"专属海报生成",
                           "key":"unique_poster"
                      }]
                  },
                  {
                      "type":"click",
                      "name":"联系客服",
                      "key":"customer"    
                       
                  },
                   {
                       "name":"福利中心",
                       "sub_button":[
                       {    
                           "type":"view",
                           "name":"毒故事",
                           "url":"http://kjlne.pw/WxGameJumpController/index/3"
                        },
                       {    
                           "type":"view",
                           "name":"今日星座",
                           "url":"http://pbuos.pw/WxGameJumpController/index/1"
                        },
                        {    
                           "type":"view",
                           "name":"欲望分布",
                           "url":"http://td.odwco.pw/WxGameJumpController/index/7"
                        },
                        {    
                           "type":"click",
                           "name":"领取福利",
                           "key":"receive_welfare"
                        },
                        ]
                  }]
             }
EOT;
        $res = json_decode(http_request($url,$menu_json),1);//发起请求，正确时返回{"errcode":0,"errmsg":"ok"}
        if($res['errcode'] == 0){
            //创建菜单成功时
            echo '菜单创建成功';
        }else{
            echo '菜单创建失败，错误号：'.$res['errcode'].'错误信息：'.$res['errmsg'];
        }

    }
    //创建目录
    protected function createDir($dir_path){
        if(!file_exists($dir_path)){
            if(!mkdir($dir_path,0777)){
                $date = date('Y-m-d H:i:s', time());
                file_put_contents(LOG_PATH.'error_log.log',"[{$date}] [mkdir_fail] 目录创建失败，目录路径：".$dir_path.PHP_EOL,FILE_APPEND);
            }
        }
    }
    //获取背景图
    public function getBackground(){
        $cat_id = $this->app['cat_id'];
        $type= $this->app['type'];
        $backgroud_dir = ROOT_PATH.'public/'. config("recommend.dir").'/' . config('recommend.back')."/cat-{$cat_id}/type-{$type}";
        $backgroud_path = glob("$backgroud_dir/background.*")[0];
        return $backgroud_path;
    }
    //获取用户头像
    public function getHeadImg($openid,$app_id,$background=''){
        $user_info= $this->user_info ;
        $cat_id = $this->app['cat_id'];
        $type = $this->app['type'];
        $flag = "headimgurl";
        //从数据库拿位置信息
        $where = ['cat_id'=>$cat_id,'type_id'=>$type,'flag'=>$flag];
        $head_info = Db::name("watermask_pic")->where($where)->find();
        if(!$head_info){
            //如果没记录
            return 0;
        }
        $water_img = http_request($user_info['headimgurl']);//读取网络图片内容
        $img_dir = $this->app_dir . DS . config('recommend.headimgurl');//目录
        $this->createDir($img_dir);//创建目录
        $img_url = $img_dir .DS.$app_id.'_'.$openid.".png";
        //$img_url ="/web/wxvote/tp5/public".DS.config("recommend.dir").DS . 'app-'.$this->app['id'] . DS . config('recommend.headimgurl').DS.date("ymdhis").uniqid(rand()).".png";
        file_put_contents($img_url, $water_img);//将一个二进制字符串写入图片
        $img_url = getCircleAvatar($img_url,$img_url);//获取圆形头像，png格式
        $head_info['path'] = $img_url;//用户头像本地路径
        //判断头像是否需要居中
        if($head_info['is_level_center']){
            //如果该字段不为0，也就是为1，那么就对该文本进行水平居中
            $head_info['posx'] = $this->imgCenter($background,$head_info['width']);
        }
        return $head_info;//返回头像位置信息
    }
    //图片水平居中
    protected function imgCenter($back_img,$cur_width){
        $width = getimagesize($back_img)[0];//背景图宽度
        //直接把数据库保存的最终调整的尺寸传过来即可
        //$cur_width = getimagesize($cur_img)[0];//当前图片宽度
        return $posx = ceil(($width - $cur_width) / 2);


    }
    //获取二维码水印
    protected function getWaterQrcode($access_token,$uniq_scene,$background=''){
        $flag = "qrcode";
        $cat_id = $this->app['cat_id'];
        $type = $this->app['type'];
        $where = ['cat_id'=>$cat_id,'type_id'=>$type,'flag'=>$flag];
        $qrcode_info = Db::name("watermask_pic")->where($where)->find();
        if(!$qrcode_info){
            //如果没记录
            return 0;
        }
        $user_qrcode_img = $this->getTmpStrQrcode($access_token, $uniq_scene);//换取二维码，返回二维码路径
        $qrcode_info['path'] = $user_qrcode_img;
        //判断二维码是否需要居中
        if($qrcode_info['is_level_center']){
            //如果该字段不为0，也就是为1，那么就对该文本进行水平居中
            $qrcode_info['posx'] = $this->imgCenter($background,$qrcode_info['width']);
        }
        return $qrcode_info;//返回头像位置信息


    }

    //通用获取文本水印信息
    protected function getWaterText($name,$background=''){
        $cat_id = $this->app['cat_id'];
        $type_id = $this->app['type'];
        //$user_info= $this->user_info ;
        //从数据库拿位置信息
        $flag = "nickname";
        $where = ['cat_id'=>$cat_id,'type_id'=>$type_id,'flag'=>$flag];
        $nickname = Db::name("watermask_text")->where($where)->find();//获取昵称的水印位置信息

        if(empty($nickname)){
            //状态为0 直接返回
            return 0;
        }
        $nickname['rgb'] = explode(',',$nickname['rgb']);//得到颜色的数组
        $nickname['text'] = $name;
        //这里还可以判断文字是否需要居中,需要用到背景图宽高。
        if($nickname['is_level_center']){
            //如果该字段不为0，也就是为1，那么就对该文本进行水平居中
            $nickname['posx'] = $this->textCenter($background,$name,$nickname['font'],$nickname['size']);
        }
        if($nickname['target_width']){
            $nickname['posx'] = $this->getTruePosx($nickname['target_width'],$name,$nickname['font'],$nickname['size'],$nickname['posx']);
        }
        $font_dir = ROOT_PATH.'public/'.config("recommend.dir").'/font';
        //$font_path = glob("$font_dir/*.*")[1];//字体路径
        $nickname['font'] = $font_dir.'/'.$nickname['font'];//数据库里只需要填写字体名称
        return $nickname;
    }
    protected function textCenter($back_img,$text,$font,$fontSize){
        //$fontSize = 18;//像素字体
        $width = getimagesize($back_img)[0];//背景图宽度
        //imagettfbbox() 返回一个含有 8 个单元的数组表示了文本外框的四个角：0左上角x,y坐标，1左下角坐标，以此逆时针类推，坐标是相对文本本身的的。
        $fontBox = imagettfbbox($fontSize, 0, $font, $text);//文字水平居中实质,取得使用 TrueType 字体的文本的范围
        return $posx = ceil(($width - $fontBox[2]) / 2);
    }
    //获取文字的实际x坐标,imagettfbbox,size参数单位是像素，二imagettftext的size在gd2.0版本是对应磅，1磅为4/3像素
    public function getTruePosx($target_width,$text,$font,$fontSize,$posx,$angle=0){

        $fontBox = imagettfbbox($fontSize, $angle, $font, $text);//文字水平居中实质,取得使用 TrueType 字体的文本的范围,八个角的坐标，做下角开始逆时针方向

        file_put_contents("publicNumberMsg.log", '目标宽度---' .$target_width .'名字实际宽度：'.$fontBox[2]. PHP_EOL, FILE_APPEND);
        $inter = ceil(1.5 *($target_width - $fontBox[2]) / 2);
        return  $posx + $inter;
    }




}