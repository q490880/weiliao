# weiliao
本系统是基于easyswoole开发的聊天室系统,实现了登陆、退出、选择聊天室、向某个聊天室内的某个成员发起私聊、和全体成员聊天。<br>
本系统代码简洁，非常适合初学者学习swoole和easyswoole。<br>
开发环境:swoole 2.x easyswoole 2.x redis3.x Mysql5.7 PHP7.1+ Nginx<br>
使用方法:
    1、安装PHP，给php安装swoole2.x扩展<br>
    2、安装Mysql、Redis、Nginx,将根目录下的easyswoole.sql导入到mysql<br>
    3、如果使用了前后端横向拆分，需要单独配置Nginx,前台页面由nginx负责解析,php文件由Nginx指向Swoole<br>
    server {<br>
        client_max_body_size 128M;<br>
        listen       80;<br>
        server_name 这里改成你自己的IP或域名;<br>
        root /var/www/swoole/easy/test/Public;<br>
        index index.html index.htm index.php;<br>
        location / {<br>
            if (!-e $request_filename){
                proxy_pass http://127.0.0.1:9501;<br>
            }<br>
        }<br>
    }<br>
    4、修改根目录下Config.php Redis和Mysql的配置<br>
    5、Public/static/js/common.js下面的baseUrl改为你的域名或IP<br>

如有问题，欢迎大家和我一起交流学习。<br>
开发者:<br>
　许鹏亮 11468804@qq.com<br>