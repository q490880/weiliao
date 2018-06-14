<?php
namespace App\Socket\Controller\WebSocket;

use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;

class Index extends WebSocketController{
    /**
     * 访问找不到的action
     * @param  ?string $actionName 找不到的name名
     * @return string
     */
    public function actionNotFound(?string $actionName)
    {
        $this->response()->write("action call {$actionName} not found");
    }

    public function index()
    {
        $fd = $this->client()->getFd();
        $this->response()->write("you fd is {$fd}");
    }
}