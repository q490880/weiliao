<?php
namespace App\Socket\Parser;

use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Core\Socket\Common\CommandBean;

use App\Socket\Controller\WebSocket\Index;

class WebSocket implements ParserInterface
{

    public static function decode($raw, $client)
    {
        //检查数据是否为JSON
        $commandLine = json_decode($raw, true);
        if (!is_array($commandLine)) {
            return 'unknown command';
        }

        $CommandBean = new CommandBean();
        $control = isset($commandLine['controller']) ? 'App\\Socket\\Controller\\WebSocket\\'. ucfirst($commandLine['controller']) : '';
        $action = $commandLine['action'] ?? 'none';
        $data = $commandLine['data'] ?? null;
        //找不到类时访问默认Index类
        $CommandBean->setControllerClass(class_exists($control) ? $control : Index::class);
        $CommandBean->setAction(class_exists($control) ? $action : 'controllerNotFound');
        $CommandBean->setArg('data', $data);

        return $CommandBean;
    }

    public static function encode(string $raw, $client): ?string
    {
        // TODO: Implement encode() method.
        return $raw;
    }
}