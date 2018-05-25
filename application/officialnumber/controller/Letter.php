<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/18
 * Time: 9:15
 */

namespace app\officialnumber\controller;

//领取奖励控制器
use app\officialnumber\controller\BaseAuth;
use app\officialnumber\controller\TaskLetter;
use think\Db;


class Letter extends TaskLetter{


    public function index(){

        echo "0";

    }


    // 渲染模板写新信件
    public function write_letter(){

        //$this->assign('urs',$urs);
        echo $this->fetch('write_letter');

    }



    // 提交新信件
    public function add_letter(){

        //指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        //响应类型
        header('Access-Control-Allow-Methods: GET,POST,PUT');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
        header('Content-Type:application/json; charset=utf-8');

        $data['user_id'] = input('user_id');
        $data['openid'] = input('openid');
        $data['nickname'] = input('nickname');
        $data['letter'] = input('letter');
        $data['others_letter'] = input('others_letter');
        $data['accepter_name'] = input('accepter_name');
        $data['headimgurl'] = input('headimgurl');


        $openid = input('openid');
        $nickname = input('nickname');

        // 将微信头像保存到服务器本地
        $headimgurl = input('headimgurl');
        $img_path = ROOT_PATH."h5/letter/img/"; //文件路径
        $img_name = 'wx_head-'.input('openid').'-'.time().'-'.rand(0,99); //文件名称
        $img_exc = '.jpg'; //文件格式,微信头像默认为jpg
        $file = $img_path.$img_name.$img_exc;   // 文件及文件名保存路径

        // 执行函数之后，会在当前文件的同一目录下生成下载好的图片
        //curlDownFile($headimgurl, $file);
        $this->curlDownFile($headimgurl, $file);

        $files = "h5/letter/img/".$img_name.$img_exc;

        $data['poster_img_url'] = $files;
        $data['create_time'] = time();

        $res = Db::connect('db_wxmail')->name('user_letter')->insert($data);

        if ($res){

            echo $files;

        }else{
            echo json_encode(array("status"=>0,"info"=>"insert error"));
            exit();
        }



    }


    /*
     * 展示信息内容，匹配了不同人的最新的信件：
     *      1. 匹配查看信件的人是否属于目标人
     * */
    public function show_letter(){

        // 所匹配的符合条件的信件数量
        $len = input('len');

        // 当前浏览页面的用户的信息
        $openid = input('openid');
        $nickname = input('nickname');
        $headimgurl = input('headimgurl');

        // 拿当前页面浏览者的微信昵称，以备与页面所要展示的内容做匹配
        $accepter_name = trim($this->filterEmoji($nickname)); //过滤emoji，如果不过滤，模糊查询很可能失败
        //$accepter_name = "123";
        if (!$accepter_name){
            echo '<meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=0">';
            exit("必要参数不完整");
        }

        // 页面作者的id
        $openid2['openid'] = input('openid');
        $id =  Db::connect('db_wxmail')->name("user")->where($openid2)->column('id');

        // 匹配符合条件信的词条，没有去重
        $who['accepter_name'] = array("like", "%".$accepter_name."%");//封装模糊查询 赋值到数组
        $writor = Db::connect('db_wxmail')->name("user_letter")->where($who)->order('id desc')->select(); // 选取所有模糊查询的结果，并根据openid去重，留下匹配的信内容最新的多用户词条
        //echo json_encode($writor);
        //echo "<hr>";

        // 循环出去重后的数组，只取openid
        $users_info = array();
        for ($j=0;$j<count($writor);$j++){
            $openid = $writor[$j]['openid'];
            $users_info[] = $openid;
        }
        // 去重数组值
        $users_info = array_flip($users_info);
        $users_info = array_keys($users_info);
        //print_r($users_info);
        //echo "<hr>";

        // 将去重后的数组循环查询对应数据词条，并创建目标数组
        $user = array();
        for ($j=0;$j<count($users_info);$j++){
            $openid_only['openid'] = $users_info[$j];

            $writor_only = Db::connect('db_wxmail')->name("user_letter")->where($who)->order('id desc')->where($openid_only)->find();

            //$this->send_you_msg($users_info[$j]); // 可循环给目标用户批量推送客服消息，可能速度很慢

            $user[] = $writor_only;
        }
        //print_r($user);
        //echo "<hr>";
        //echo json_encode($user, JSON_UNESCAPED_UNICODE);


        if(count($writor) == 0){ // 当前用户对应匿名内容
            //exit("没有匹配到的信，将会显示other_letter");

            // 页面作者的微信名
            //$w_openid['openid'] = $writor[0]['openid']; // 优先选第一条查询作为结果输出
            //$w_nickname = $writor[0]['nickname'];

            // 给非目标人展示匿名信
            //$no =  Db::connect('db_wxmail')->name("user")->where($w_openid)->order("id desc")->column("openid");
            //$w_others_letter = $no[0];

            $users = array(

                'id' => $id[0],
                'openid' => $openid,
                'nickname' => $nickname,
                'headimgurl' => $headimgurl,

                'w_nickname' => "匿名人",
                'writor_head' => "http://www.mukzz.pw/tpwx/h5/letter/niming.png",
                'writor_name' => "匿名人X",
                //'writor_letter' => $w_others_letter
                'writor_letter' => "信的作者设置了只有与微信昵称匹配的用户才可以看到信内容哦"

            );

            $this->assign('users',$users);
            echo $this->fetch('others_letter');


        }else{ // 当前用户是目标用户，对应目标信内容


            $users = array(

                'id' => $id[0],
                'openid' => $openid,
                'nickname' => $nickname,
                'headimgurl' => $headimgurl,

            );

            $class_user = array();
            for($i=0;$i < count($user);$i++){

                $class = array(
                    'w_nickname' => $user[$i]['nickname'],
                    'writor_head' => $user[$i]['headimgurl'],
                    'writor_name' => $user[$i]['nickname'],
                    'writor_letter' => $user[$i]['letter']
                );

                $class_user[] = array_merge($users, $class);

            }

            //print_r($class_user);
            //echo "<hr>";

            $this->send_you_msg($users_info[$len]); // 可循环给目标用户批量推送客服消息，可能速度很慢

            $this->assign('users',$class_user[$len]);
            echo $this->fetch('show_letter');



            /*
             *
             * 以下只是匹配了最新匹配信件，不分人头
             *
             * */
//            // 页面作者的微信名
//            $w_openid['openid'] = $writor[0]['openid']; // 优先选第一条查询作为结果输出
//            $w_nickname = $writor[0]['nickname'];
//
//            // 给目标人展示正常信
//            $writor_head =  Db::connect('db_wxmail')->name("user")->where($w_openid)->column('headimgurl');
//            $writor_name =  Db::connect('db_wxmail')->name("user")->where($w_openid)->column('nickname');
//
//            $writor_letter =  Db::connect('db_wxmail')->name("user_letter")->where($w_openid)->order("id desc")->column('letter');
//
//            $users = array(
//
//                'id' => $id[0],
//                'openid' => $openid,
//                'nickname' => $nickname,
//                'headimgurl' => $headimgurl,
//
//                'w_nickname' => $w_nickname,
//                'writor_head' => $writor_head[0],
//                'writor_name' => $writor_name[0],
//                'writor_letter' => $writor_letter[0]
//
//            );
//
//            $this->assign('users',$users);
//            echo $this->fetch('show_letter');
//
//            $this->send_you_msg($writor[0]['openid']);


        }




    }
    /*
     * 展示信息内容，输出匹配不同人的最新的信件数量：
     *      1. 匹配查看信件的人是否属于目标人
     * */
    public function show_letter_len(){

        // 当前浏览页面的用户的信息
        $openid = input('openid');
        $nickname = input('nickname');
        $headimgurl = input('headimgurl');

        // 拿当前页面浏览者的微信昵称，以备与页面所要展示的内容做匹配
        $accepter_name = trim($this->filterEmoji($nickname)); //过滤emoji，如果不过滤，模糊查询很可能失败
        //$accepter_name = "123";
        if (!$accepter_name){
            echo '<meta charset="utf-8" /><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=0">';
            exit("必要参数不完整");
        }

        // 页面作者的id
        $openid2['openid'] = input('openid');
        $id =  Db::connect('db_wxmail')->name("user")->where($openid2)->column('id');

        // 匹配符合条件信的词条，没有去重
        $who['accepter_name'] = array("like", "%".$accepter_name."%");//封装模糊查询 赋值到数组
        $writor = Db::connect('db_wxmail')->name("user_letter")->where($who)->order('id desc')->select(); // 选取所有模糊查询的结果，并根据openid去重，留下匹配的信内容最新的多用户词条
        //echo json_encode($writor);
        //echo "<hr>";

        // 循环出去重后的数组，只取openid
        $users_info = array();
        for ($j=0;$j<count($writor);$j++){
            $openid = $writor[$j]['openid'];
            $users_info[] = $openid;
        }
        // 去重数组值
        $users_info = array_flip($users_info);
        $users_info = array_keys($users_info);
        //print_r($users_info);
        //echo "<hr>";

        // 输出符合条件的信封数量
        $len = count($users_info);
        echo json_encode($len, JSON_UNESCAPED_UNICODE);



    }

    //
    public function list_letter(){

        // 当前浏览页面的用户的信息
        $openid = input('openid');
        $nickname = input('nickname');
        $headimgurl = input('headimgurl');

        // 页面作者的id
        $openid2['openid'] = input('openid');
        $id =  Db::connect('db_wxmail')->name("user")->where($openid2)->column('id');

        $users = array(

            'id' => $id[0],
            'openid' => $openid,
            'nickname' => $nickname,
            'headimgurl' => $headimgurl,

        );

        $this->assign('users',$users);
        echo $this->fetch('list_letter');


    }


    public function add_user_img(){

        $user_id = input('user_id');
        $openid = input('openid');


        // base64转图片并保存到本地
        $user_qr = input('user_qr');
        $path = "h5/letter/user_img"; //文件路径
        $file = "/".date('Ymd',time())."/";
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $user_qr, $result)){
            $type = $result[2];

            $new_file = ROOT_PATH.$path.$file;
            if(!file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0755);
            }

            $img = time().".{$type}";

            $new_file = $new_file.$img;

            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $user_qr)))){

                $files = $path.$file.$img;

                //return '/'.$new_file;
            }else{
                return false;
            }
        }else{
            return false;
        }


        $map['openid'] = $openid;
        $data['user_qr'] = $files;

        $max_id = Db::connect('db_wxmail')->name('user_letter')->where($map)->order("id desc")->column('id');
        $max_['id'] = $max_id[0];
        $res = Db::connect('db_wxmail')->name('user_letter')->where($map)->where($max_)->update($data); // 只给该用户提交最新词条更新
        if ($res){
            echo $files;
        }else{
            echo json_encode(array("status"=>0,"info"=>"update poster_img_url error"));
            exit();
        }

        $this->send_only_user_msg($openid, $files);

    }

    //
    public function write_letter_for_newuser(){

        $openid = input('openid');
        $nickname = input('nickname');
        $headimgurl = input('headimgurl');
        $user_id = input('user_id');

        $data['openid'] = $openid;
        $data['nickname'] = $nickname;
        $data['headimgurl'] = $headimgurl;
        $data['user_id'] = $user_id;

        $this->assign('users',$data);
        echo $this->fetch('write_letter_for_newuser');

    }

    // 使用帮助
    public function help(){


        echo $this->fetch('help');
    }


    // 给目标用户发送客服消息
    public function send_you_msg($w_openid){

        $task = new TaskLetter();
        $task->send_to_writor($w_openid);

    }


    // 随机信索引
    public function others_letter_max_num(){

        $len = Db::connect('db_wxmail')->name('others_letter')->select();
        echo json_encode(count($len), JSON_UNESCAPED_UNICODE);
    }
    // 随机向匿名信发送内容
    public function others_letter(){

        $only_num = input('only_num');
        $len = Db::connect('db_wxmail')->name('others_letter')->select();
        if($only_num>count($len)-1){
            echo "数字超过范围了";
            exit();
        }

        //$num['id'] = $only_num;
        $content = Db::connect('db_wxmail')->name('others_letter')->select();
        //print_r($content);
        $res = $content[$only_num];

        echo json_encode($res, JSON_UNESCAPED_UNICODE);

    }



    // 过滤掉emoji表情
    function filterEmoji($str){
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }

    /**
     * @param string $img_url 下载文件地址
     * @param string $filename 下载文件保存名称和路径
     * @return bool
     */
    function curlDownFile($img_url, $filename = '') {
        if (trim($img_url) == '') {
            return false;
        }

        if (trim($filename) == '') {
            $img_ext = strrchr($img_url, '.');
            $img_exts = array('.gif', '.jpg', '.png');
            if (!in_array($img_ext, $img_exts)) {
                return false;
            }
            $filename = time() . $img_ext;
        }

        // curl下载文件
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $img_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $img = curl_exec($ch);
        curl_close($ch);

        // 保存文件到制定路径
        file_put_contents($filename, $img);

        unset($img, $url);
        return true;
    }


}





