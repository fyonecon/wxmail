1. 环境搭建：
   
   部署代码和数据库；
   
   将Runtime目录设置777权限；
   
   将H5目录设置777权限
   
   在数据official_app中确保字段appid、appsecret、token填写正确；
   
2. 设置微信公众号：
    
    配置白名单ip；
    
    配置token；例如：http://xxxxxxxx/public/?s=/officialnumber/task_letter/index/token/td07
    
3. 其他：
     配置公众号菜单，浏览器直接访问： http://xxxxxx/public/?s=/officialnumber/task_letter/createEndMenu/token/td07
     
     ；
     不能同时发送两个图文消息；
     
 4. 其他2:

// 被动回复文本或者带链接的文字消息
// 获取access_token
             
             $token['token'] = "td07"; // 对应的数据库字段的值
             $app_info = Db::connect('db_wxmail')->name("official_app")->where($token)->find();
             $the_at = getAccessToken($app_info);//换取access_token
             $at ="http://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$the_at}";
             //被动回复文本消息
             $data =sprintf(kf_txt_msg, $openid, "❤记得将专属海报分享到你的朋友圈哦，愿你的意中人也喜欢你❤");
             http_request($at,$data);
             
 ===============
             
 /// 回复图片消息    
 
             $kf_img = $this->addTmpStuff2($the_at, $img_path);
             $img_data = sprintf(kf_txt_image,$openid,$kf_img);
             $url ="http://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$the_at}";
             http_request($url,$img_data);
             
             
             