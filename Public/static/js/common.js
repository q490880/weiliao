baseUrl = 'www.weiliao.com';
/*
* 设置cookie
* */
function getCookie(cookieName){
    if(document.cookie.length > 0){
        c_start = document.cookie.indexOf(cookieName + "=")
        if (c_start != -1){
            c_start = c_start + cookieName.length + 1
            c_end = document.cookie.indexOf(";",c_start)
            if (c_end == -1){
                c_end = document.cookie.length
            }
            return unescape(document.cookie.substring(c_start,c_end))
        }
    }
    return false
}

/*
* 获取cookie
* */
function setCookie(cookieName,value,expiredays)
{
    var exdate = new Date()
    exdate.setDate(exdate.getDate() + expiredays)
    document.cookie = cookieName + "=" +escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString())
}

/*
* 清空cookie
* */
function clearAllCookie() {
    var keys = document.cookie.match(/[^ =;]+(?=\=)/g);
    if(keys) {
        for(var i = keys.length; i--;)
            document.cookie = keys[i] + '=0;expires=' + new Date(0).toUTCString()
    }
}
/*
* 拼装Url
* */
function reformUrl(url){
    return 'http://'+baseUrl+'/' + url;
}
 /**
 * 判断是否是空
 */
function isNull(input){
    if(input == null || input == '' || input == undefined){
        return true;
    }else{
        return false;
    }
}

/*
* 发起Ajax的get请求
* */
function getAjax(url,fromData){
    if(url != "member/login"){
        if(isNull(fromData)){
            var fromData = {};
        }
        fromData['token'] = getCookie('token');
        fromData['user_id'] = getCookie('user_id');
    }
    var result = $.ajax({
        url:reformUrl(url),
        data:fromData,
        async:false,
        dataType:"json",
        success:function(data){
            return data;
        },
        error:function(data){
            if(data.status == 405){
                //没有权限
                window.location.href = 'login.html';
            }else if(data.status == 500){
                return data.responseJSON = {
                    "code":500,
                    'msg':"服务器错误"
                };
            }
        }
    });
    return result.responseJSON;
}

/*
* 获取当前时间
* */
function curentTime()  
{   
	var now = new Date();  
	var year = now.getFullYear();  
	var month = now.getMonth() + 1;
	var day = now.getDate();
	var hh = now.getHours(); 
	var mm = now.getMinutes();
	var ss = now.getSeconds(); 
	var clock = year + "-";  
	if(month < 10)  
		clock += "0";  
	clock += month + "-";  
	if(day < 10)  
		clock += "0";  
	clock += day + " ";  
	if(hh < 10)  
		clock += "0";  
	clock += hh + ":";  
	if (mm < 10) clock += '0';   
	clock += mm + ":";   
	if (ss < 10) clock += '0';   
	clock += ss;   
	return clock;   
} 

/*
* 获取随机数
* */
function RndNum(n){
    var rnd="";
    for(var i=0;i<n;i++)
        rnd+=Math.floor(Math.random()*10);
    return rnd;
}

/*
* 选择发送消息的对象
* */
function selectSendObject(t){
	var name = $(t).html();
    var member_id = $(t).attr('data');
	$('#sendObject').val(name);
    $('#sendObject').attr('member_id',member_id);
}

//获取url中的参数
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)","i");
    var result = window.location.search.substr(1).match(reg);
    if (result != null) {
        return decodeURIComponent(result[2]);
    } else {
        return null;
    }
}

// 转为unicode编码
function encodeUnicode(str) {
    var res = [];
    for ( var i=0; i<str.length; i++ ) {
        res[i] = ( "00" + str.charCodeAt(i).toString(16)).slice(-4);
    }
    return "\\u" + res.join("\\u");
}

//unicode转解码
function decodeUnicode(str) {
    str = str.replace(/\\/g, "%");
    return unescape(str);
}