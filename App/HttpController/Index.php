<?php

namespace App\HttpController;

use App\Model\Room;
use EasySwoole\Core\Http\AbstractInterface\Controller;

/**
 * Class Index
 * @package App\HttpController
 */
class Index extends Controller
{
    /**
     * 首页方法
     * @author : evalor <master@evalor.cn>
     */
    function index()
    {
    }

    /*
     * 获取房间
     * */
    function room(){
        $roomModel = new Room();
        return $this->writeJson(200,$roomModel->findAll());
    }
}