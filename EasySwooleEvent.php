<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;

use App\Process\Inotify;
use App\Socket\Logic\Room;
use App\Socket\Parser\WebSocket;
use App\Utility\Redis;
use \EasySwoole\Core\AbstractInterface\EventInterface;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Swoole\EventHelper;
use EasySwoole\Core\Swoole\Process\ProcessManager;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;
use Symfony\Component\Console\Logger\ConsoleLogger;
use App\Utility\SysTools;

Class EasySwooleEvent implements EventInterface {

    public static function frameInitialize(): void
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(ServerManager $server,EventRegister $register): void
    {
        // 注册WebSocket解析器
        EventHelper::registerDefaultOnMessage($register, WebSocket::class);
        //注册onClose事件
        $register->add($register::onClose, function (\swoole_server $server, $fd, $reactorId) {
            //清除Redis fd的全部关联
            Room::close($fd);
        });
        // 服务热重启 单独启动一个进程处理
        // ------------------------------------------------------------------------------------------
        ProcessManager::getInstance()->addProcess('autoReload', Inotify::class);
        Di::getInstance()->set('REDIS', new Redis(Config::getInstance()->getConf('REDIS')));
        // 自定义WS握手处理 可以实现在握手的时候 鉴定用户身份
        // @see https://wiki.swoole.com/wiki/page/409.html
        // ------------------------------------------------------------------------------------------
        $register->add($register::onHandShake, function (\swoole_http_request $request, \swoole_http_response $response) {
            if (!isset($request->cookie['token']) || !isset($request->cookie['user_id']) ) {
                var_dump('shake fai1 1');
                $response->end();
                return false;
            }
            if (!SysTools::authToken($request->cookie['user_id'],$request->cookie['token'])) {
                var_dump('shake fai1 2');
                $response->end();
                return false;
            }
            if (!isset($request->header['sec-websocket-key'])) {
                // 需要 Sec-WebSocket-Key 如果没有拒绝握手
                var_dump('shake fai1 3');
                $response->end();
                return false;
            }
            if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $request->header['sec-websocket-key'])
                || 16 !== strlen(base64_decode($request->header['sec-websocket-key']))
            ) {
                //不接受握手
                var_dump('shake fai1 4');
                $response->end();
                return false;
            }
            $key = base64_encode(sha1($request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $headers = array(
                'Upgrade'               => 'websocket',
                'Connection'            => 'Upgrade',
                'Sec-WebSocket-Accept'  => $key,
                'Sec-WebSocket-Version' => '13',
                'KeepAlive'             => 'off',
            );
            foreach ($headers as $key => $val) {
                $response->header($key, $val);
            }
            $response->status(101);
            $response->end();
        });
    }

    public static function onRequest(Request $request,Response $response): void
    {
        //$request->withAttribute('requestTime', microtime(true));
        $except = ['/member/login'];
        if(!in_array($request->getUri()->getPath(),$except)){
            $params = $request->getQueryParams();
            if(!isset($params['token']) || !isset($params['user_id']) || !SysTools::authToken($params['user_id'],$params['token'])){
                $response->withStatus(Status::CODE_METHOD_NOT_ALLOWED);
                $response->end();
            }
        }
    }

    public static function afterAction(Request $request,Response $response): void
    {
//        $start = $request->getAttribute('requestTime');
//        $spend = round(microtime(true) - $start, 3);
//        Logger::getInstance()->console("request :{$request->getUri()->getPath()} take {$spend}");
    }
}