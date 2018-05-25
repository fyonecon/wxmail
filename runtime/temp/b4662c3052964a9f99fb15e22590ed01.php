<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:86:"D:\wamp64\www\wxmail\public/../application/officialnumber\view\letter\show_letter.html";i:1526720725;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=0">
    <title>xxx给你写的信</title>
    <link rel="stylesheet" href="http://wxmail.mukzz.pw/h5/letter/css/common.css" />
    <link rel="stylesheet" href="http://wxmail.mukzz.pw/h5/letter/css/self.css" />
    <script src="http://cdnaliyun.oss-cn-hangzhou.aliyuncs.com/js/jquery1.10.2.min.js"></script>
    <script>
        var web_url = "http://wxmail.mukzz.pw/public/?s=";
        var web_h5 = "http://wxmail.mukzz.pw/h5"
    </script>
</head>
<body>

<!--开始-内容-->
<div class="box">

    <?php if(is_array($users) || $users instanceof \think\Collection || $users instanceof \think\Paginator): $i = 0; $__LIST__ = $users;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <!--头像-->
    <div class="header-div center">
        <img src="<?php echo $users['headimgurl']; ?>" class="user-header" alt="用户头像"/>
    </div>
    <!--信内容-->
    <div class="letter">
        <div class="letter-title">致<span><?php echo $users['nickname']; ?></span></div>
        <div class="letter-content">
            这是信的内容。。
        </div>
        <div class="letter-editor">——你的&nbsp;<span>--</span></div>
    </div>
    <!--按钮-->
    <div class="center">
        <div class="div-btn act">我也来写一封指定人才能看到的信</div>
    </div>
    <?php endforeach; endif; else: echo "" ;endif; ?>

</div>
<!--结束-内容-->

<script>

    $(function () {
        $(".div-btn").click(function () {
            console.log("跳转到写信页面");
            window.location.replace(web_h5+"/letter/write_letter.html");
        });
    });

</script>

</body>
</html>
