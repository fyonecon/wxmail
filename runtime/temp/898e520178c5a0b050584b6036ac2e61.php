<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:87:"D:\wamp64\www\wxmail\public/../application/officialnumber\view\letter\write_letter.html";i:1526976500;}*/ ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=0">
		<title>编写新信</title>
		<link rel="stylesheet" href="http://www.mukzz.pw/tpwx/h5/letter/css/common.css" />
		<link rel="stylesheet" href="http://www.mukzz.pw/tpwx/h5/letter/css/self.css" />
        <script src="http://cdnaliyun.oss-cn-hangzhou.aliyuncs.com/js/jquery1.10.2.min.js"></script>
		<script src="http://www.mukzz.pw/tpwx/h5/letter/js/common.js"></script>
        <script src="http://www.mukzz.pw/tpwx/h5/letter/js/qrcode.js"></script>
        <script src="http://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
		<script>
            var web_url = "http://www.mukzz.pw/tpwx/public/?s=";
            var head_url = "http://www.mukzz.pw/tpwx/";
            isWeiXin("write-page", "off", "");
		</script>
	</head>
	<body>
		
		<div class="box">
			
			<div class="padding-div">
				
				<div class="write">
					<div class="write-title">传信神器</div>
					<div class="write-title-sumary">
						写一份情书晒到朋友圈，只有特定的人打开，才能看到特使的文字，快来给你喜欢的人写一份假匿名信吧。
                        （点击<a href="#">查看使用说明</a>）
					</div>
				</div>
				
				<div  style="margin-top: 20px;">
                    <div class="write">
                        <div class="write-title-sumary font-weight-600">你喜欢的人能看到的话</div>
                    </div>
					<textarea class="write-textarea1" placeholder="这里输入你喜欢的人能看到的话"></textarea>

					<div>
                        <div class="write">
                            <div class="write-title-sumary font-weight-600">你喜欢的人的微信昵称</div>
                        </div>
						<input class="write-input" placeholder="请输入你喜欢的人的微信昵称，可不全" />
					</div>
				</div>
				
				<div>
                    <div class="write">
                        <div class="write-title-sumary font-weight-600">其他人能看到的话</div>
                    </div>
					<textarea class="write-textarea2" placeholder="这里输入其他人能看到的话"></textarea>
				</div>
				
				<div class="center">
					<div class="post-btn act">提交</div>
				</div>
				 <script>

                     var openid = getCookie(cookie_pre+"openid");
                     var id = getCookie(cookie_pre+"id");
                     var nickname = getCookie(cookie_pre+"nickname");
                     var headimgurl = getCookie(cookie_pre+"headimgurl");

                         //console.log(openid+"="+id+"="+nickname);

					 $(document).ready(function(){
						$(".post-btn").click(function(){

                            var write_txt1 = $(".write-textarea1").val().trim();
                            var write_txt2 = $(".write-textarea2").val().trim();
                            var write_input = $(".write-input").val().trim();

                            if (!write_txt1 || !write_txt2 || !write_input){
                                alert("请填写完整信内容");
                                return;
                            }
                            if (!openid || !id || !nickname){
                                alert("用户信息不完整，不能提交！原因：可能是用户cookie未启用或为空。");
                                return;
                            }

						
							//向接口提交数据，并返回后端提示
							$.ajax({
								 url: web_url+"/officialnumber/letter/add_letter",
								 type: "POST",
								 //dataType: "json", // 已经默认json
								 //async: true, // 已经默认true
								 data:{
									user_id: id,
									openid: openid,
									nickname: nickname,
                                    headimgurl: headimgurl,
									letter: write_txt1,
									others_letter: write_txt2,
									accepter_name: write_input
								 },
								 success: function(data, status){
								 	console.log("提交数据：" + data+"status："+status);
								 	headimgurl = head_url+data; // 重新赋值
								 	console.log(headimgurl);
                                     //alert("提交成功");
                                     alert_txt("提交成功", 1000);
                                     setTimeout("make_img()", 1000);

                                     //window.location.reload(); // 清除数据，防止重复提交
								 },
								 error: function (xhr) {
								 	console.log(xhr);
								 	console.log(xhr.responseText);
								 	alert('提交失败');
								 }
						
							});
					
						});
					 });
				 </script>
				
			</div>
			
			
			
		</div>
		<div class="footer-txt">一定要记得关注支持我们哦</div>

        <!--canvas画图，生成用户二维码图-->
        <div class="layer-bg hide"></div>
        <img src="" class="canvas-div hide mypicture" id="mypicture" alt="结果图"/>
        <div class="alert-txt hide">-</div>
        <script>

            function make_img() {

                $(".canvas-div").removeClass("hide");

                var bg = head_url+"h5/letter/bg.jpg?"+Math.floor(Math.random()*9999);
                var qr = head_url+"h5/letter/qr.jpg?"+Math.floor(Math.random()*9999);
                //headimgurl = "http://www.mukzz.pw/tpwx/h5/letter/img/wx_head-ohFHb0f1cvPx8R-uNFleqL_nzTmU-1526969175-5.jpg";

                //绘制目标画图区域
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');

                var bg_w = 0;
                var bg_h = 0;

                var top = 0; // 结果图的 top

                new Promise(function(resolve, reject) {  // 背景图

                    var img = new Image();
                    img.src = bg;
                    img.crossOrigin = "anonymous";
                    img.onload = function () {

                        //初始化模板图，初始化图片尺寸
                        canvas.width = img.width;
                        canvas.height = img.height;

                        bg_w = img.width;
                        bg_h = img.height;

                        top = Math.abs(window.innerHeight - img.height/img.width*window.innerWidth)/2;
                        document.getElementById("mypicture").style.top = top+"px";


                        ctx.drawImage(img, 0, 0, img.width, img.height);
                        console.log("图片初始化完成");
                        resolve()

                    }
                })

                    .then(function(){ // 二维码图

                        return new Promise(function(resolve, reject){
                            var img_qr = new Image();
                            img_qr.crossOrigin = "anonymous";
                            img_qr.src = qr;
                            console.log("二维码地址："+qr);
                            img_qr.onload = function(){

                                var w = 210;
                                var h = 210;

                                var x = (bg_w - w)/2;
                                var y = (bg_h - h)/2+180;

                                ctx.drawImage(img_qr, x, y, w, h);

                                console.log("二维码图片加入背景图完毕！");
                                resolve()
                            }
                        })

                    })
                    .then(function(){ // 微信头像图

                        return new Promise(function(resolve, reject){
                            var img_qr = new Image();
                            img_qr.crossOrigin = "anonymous";
                            console.log("头像地址："+headimgurl);
                            img_qr.src = headimgurl;
                            img_qr.onload = function(){


                                var w = 140;
                                var h = 140;

                                var x = (bg_w - w)/2;
                                var y = (bg_h - h)/2 - 350;

                                var r = w/2;

                                // 将图片裁成圆形
                                ctx.beginPath();
                                ctx.arc(x+w/2, y+h/2, r, 0, 2*Math.PI); //画裁剪区域，此处以圆为例
                                ctx.clip(); //次方法下面的部分为待剪切区域，上面的部分为剪切区域

                                ctx.drawImage(img_qr, x, y, w, h);
                                console.log("头像加入背景图完毕！");
                                resolve()
                            }
                        })

                    })
                    .then(function () {

                        //展示结果图 3/3
                        var bg_base64 = canvas.toDataURL('image/jpeg'); //最终base64码
                        $(".canvas-div").removeClass("hide");
                        $(".layer-bg").removeClass("hide");
                        $('.mypicture').attr('src', bg_base64);
                        alert_txt("长按可以保存图片哦", 2500)
                        console.log("展示结果图！");
                        //console.log(bg_base64);

                    })

            }
            //make_img();
            
            $(function () {
                $(".layer-bg").click(function () {
                    $(".canvas-div").addClass("hide");
                    $(".layer-bg").addClass("hide");
                    window.location.reload();
                });
            });

            //alert-txt
            function alert_txt(txt, time) {
                if (!time){
                    time = 2000;
                }
                $(".alert-txt").html(txt).fadeIn(500);
                setTimeout(function () {
                    $(".alert-txt").html("-").fadeOut(500);
                    console.log("alert-txt 隐藏");
                }, time);
            }
            
        </script>
        <style>

            .alert-txt{
                display: inline-block;
                padding: 8px 30px;
                margin-left: auto;
                margin-right: auto;
                position: fixed;
                z-index: 9999999;
                left: 0;
                right: 0;
                width: 60%;
                text-align: center;
                border-radius: 50px;
                top: 45%;
                font-size: 13px;
                color: white;
                background: rgba(0,0,0,0.7);
            }

			.hide{
				display: none;
			}
            .myhead{
                width: 80px;
                border-radius: 50%;
                margin-left: auto;
                margin-right: auto;
                display: block;
                margin-top: 10%;
            }
            .myqr{
                width: 130px;
                border-radius: 5px;
                margin-left: auto;
                margin-right: auto;
                display: block;
                margin-top: 55%;
            }
            .mypicture{
                /*margin-top: 30px;*/
                width: 100%;
            }
            .layer-bg{
                position: fixed;
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                z-index: 99980;
                background: rgba(0,0,0,0.6);
            }
            .canvas-div{
                position: fixed;
                width: 100%;
                min-height: 200px;
                left: 0;
                right: 0;
                margin: auto;
                z-index: 99988;
                /*background-image: url("http://www.mukzz.pw/tpwx/h5/letter/bg.jpg");*/
                /*background-repeat: no-repeat;*/
                /*background-size: 100% auto;*/

            }
        </style>
<script>

</script>

	</body>
</html>
