<?php
namespace App\HttpController;
use App\Socket\Logic\Room;
use App\Utility\SysConst;
use App\Utility\SysTools;
use EasySwoole\Core\Http\AbstractInterface\Controller;

/**
 * Class Index
 * @package App\HttpController
 */
class Member extends Controller
{
    /**
     * 用户首页
     */
    function index()
    {
        return $this->response()->write("member index");
    }

    /*
     * 登录
     * */
    function login(){
        $mobile = $this->request()->getRequestParam('mobile');
        if(empty($mobile)){
            return $this->writeJson(400,'','手机号不能为空');
        }
        $password = $this->request()->getRequestParam('password');
        if(empty($password)){
            return $this->writeJson(400,'','密码不能为空');
        }
        $memberModel = new \App\Model\Member();
        $resultMember = $memberModel->findOne(['mobile'=>$mobile,'password'=>$password],'id,name');
        if($resultMember){
            $this->response()->setCookie('token',SysTools::generateToken($resultMember['id']),time()+SysConst::COOKIE_USER_SESSION_TTL);
            $this->response()->setCookie('user_id',$resultMember['id'],time()+SysConst::COOKIE_USER_SESSION_TTL);
            $this->response()->setCookie('name',urlencode($resultMember['name']),time()+SysConst::COOKIE_USER_SESSION_TTL);
            return $this->writeJson(200,'','验证通过');
        }else{
            return $this->writeJson(400,'','手机号或密码错误');
        }
    }

    /*
     * 获取在线用户
     * */
    function online(){
        $room_id = $this->request()->getRequestParam('room_id');
        if(empty($room_id)){
            return $this->writeJson(400,'','房间号不能为空');
        }
        $userList = Room::selectRoomAllUser($room_id);
        return $this->writeJson(200,$userList);
    }
}