<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 10:14
 * 公共函数文件
 */

define("MSG_TEXT","<xml>
                   <ToUserName><![CDATA[%s]]></ToUserName>
                   <FromUserName><![CDATA[%s]]></FromUserName>
                   <CreateTime>%s</CreateTime>
                   <MsgType><![CDATA[text]]></MsgType>
                   <Content><![CDATA[%s]]></Content>
                   <FuncFlag>0</FuncFlag>
                   </xml>"
);
define("MSG_IMG","<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[%s]]></MediaId>
</Image>
</xml>");

define("MSG_SINGLE_PIC_TXT" ,
"<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[%s]]></Title> 
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>					
</Articles>
</xml> ");

define("MSG_MULTI_PIC_TXT_COVER",
"<xml>\n<ToUserName><![CDATA[%s]]></ToUserName>\n<FromUserName><![CDATA[%s]]></FromUserName>\n<CreateTime>%s</CreateTime>\n<MsgType><![CDATA[news]]></MsgType>\n<ArticleCount>%s</ArticleCount>\n<Articles>\n%s</Articles>\n</xml>");

define("MSG_MULTI_PIC_TXT_INNER",
"<item>\n<Title><![CDATA[%s]]></Title>\n<Description><![CDATA[%s]]></Description>\n<PicUrl><![CDATA[%s]]></PicUrl>\n<Url><![CDATA[%s]]></Url>\n</item>\n");


define("MSG_URL",
"<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[link]]></MsgType>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<Url><![CDATA[%s]]></Url>
<MsgId>%s</MsgId>
</xml>");

define("MSG_SERVICER", "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>");

define("kf_txt_msg",json_encode(array("touser"=>'%s',"msgtype"=>"text","text"=>array("content"=>"%s"))));
define("kf_txt_image",json_encode(array("touser"=>'%s',"msgtype"=>"image","image"=>array("media_id"=>"%s"))));
define("kf_txt_voice",json_encode(array("touser"=>'%s',"msgtype"=>"voice","voice"=>array("media_id"=>"%s"))));
define("kf_txt_video",json_encode(array("touser"=>'%s',"msgtype"=>"video","video"=>array("media_id"=>"%s","thumb_media_id"=>"%s","title"=>"%s","description"=>"%s"))));
define("kf_txt_news",'
{
    "touser":"%s",
    "msgtype":"news",
    "news":{
        "articles": [
         {
             "title":"%s",
             "description":"%s",
             "url":"%s",
             "picurl":"%s"
		 }]}
}');
define("kf_template_msg", json_encode(array("touser"=>"%s",
           "template_id"=>"%s",
           "url"=>"%s",
           "topcolor"=>"#FF0000",
		   "data"=>array(
		   		"first" => array("value"=>"%s","color"=>"#173177"),
				"orderID" => array("value"=>"%s","color"=>"#173177"),
				"orderMoneySum" => array("value"=>"%s","color"=>"#173177"),
				"backupFieldName" => array("value"=>"%s","color"=>"#173177"),
				"backupFieldData" => array("value"=>"%s","color"=>"#173177"),
				"remark" => array("value"=>"%s","color"=>"#173177"),
			)
)));
//define("tmp_str_qrcode",json_encode(array("expire_seconds"=>'%s',"action_name"=>"QR_STR_SCENE","action_info"=>array("scene"=>array("scene_str"=>"%s")))));
?>
