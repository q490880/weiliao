$(docment).read(function(){
   /*
   * 上报用户信息和访问数据到打点服务器
   * */
    var lock = true;
    if(lock == true){
        $.get("http://www.weiliao.com/count.gif",{
            "time":gettime(),
            "url":geturl(),
            "refer":getrefer(),
            "ua":getuser_agent()
        },function(){
            lock = false;
        })
    }
 });

function getrefer(){
    return document.referrer;
}
function geturl(){
    return window.location.href;
}
function gettime(){
    var nowDate = new Date();
    return nowDate.toLocaleDateString();
}
function getuser_agent(){
    return navigator.userAgent;
}