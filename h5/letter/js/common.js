


// cookie前缀
var cookie_pre = "le_";


// 判断是否是微信浏览器的函数
// txt: 描述文字
// status: "on"，"off"
// url: 跳转地址
// 参数为空时没有操作
function isWeiXin(txt, status, url){
    //window.navigator.userAgent属性包含了浏览器类型、版本、操作系统类型、浏览器引擎类型等信息，这个属性可以用来判断浏览器类型
    var ua = window.navigator.userAgent.toLowerCase();
    //通过正则表达式匹配ua中是否含有MicroMessenger字符串
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        console.log("是微信浏览器--"+txt);

        return true;
    }else{

        console.log("不是微信浏览器--"+txt);

        if (status == "on"){
            window.location.replace(url);
        }else if (status == "off"){
            console.log("off时不跳转");
        }

        return false;
    }

}


// 设置和更新cookie
function setCookie(c_name,value,expiredays){
    console.log("设置或更新cookie--key="+c_name);
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie=c_name+ "=" +escape(value) + ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
// 读取cookie
function getCookie(c_name){
    console.log("读取cookie--key="+c_name);
    if (document.cookie.length>0) {
        c_start=document.cookie.indexOf(c_name + "=");
        if (c_start!=-1){
            c_start=c_start + c_name.length+1;
            c_end=document.cookie.indexOf(";",c_start);
            if (c_end==-1) c_end=document.cookie.length;
            return unescape(document.cookie.substring(c_start,c_end));
        }
    }
    return ""
}