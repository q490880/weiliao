<?php
namespace App\Socket\Logic;

use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Swoole\Task\TaskManager;


class Room
{
    /**
     * 获取Redis连接实例
     * @return object Redis
     */
    protected static function getRedis()
    {
        return Di::getInstance()->get('REDIS')->handler();
    }

    /**
     * 进入房间
     * @param  int    $roomId 房间id
     * @param  int    $userId userId
     * @param  int    $fd     连接id
     * @return
     */
    public static function joinRoom(int $roomId, int $fd ,int $userId, string $userName)
    {
        self::getRedis()->hSet("member:{$userId}","name",$userName);
        self::getRedis()->zAdd('rfMap', $roomId, $fd);
        self::getRedis()->hSet("room:{$roomId}", $fd, $userId.','.$userName);
    }

    /**
     * 登录
     * @param  int    $userId 用户id
     * @param  int    $fd     连接id
     * @return bool
     */
    public static function login(int $userId, int $fd)
    {
        self::getRedis()->zAdd('online', $userId, $fd);
    }

    /**
     * 获取用户
     * @param  int    $fd
     * @return array  $user
     */
    public static function getUserName(int $userId)
    {
        return self::getRedis()->hGet("member:{$userId}", "name");
    }

    /**
     * 获取用户fd
     * @param  int    $userId
     * @return array         用户fd集
     */
    public static function getUserFd(int $userId)
    {
        return self::getRedis()->zrangebyscore('online', $userId, $userId);
    }

    /**
     * 获取RoomId
     * @param  int    $fd
     * @return int    RoomId
     */
    public static function getRoomId(int $fd)
    {
        return self::getRedis()->zScore('rfMap', $fd);
    }


    /**
     * 获取room中全部fd
     * @param  int    $roomId roomId
     * @return array         房间中fd
     */
    public static function selectRoomFd(int $roomId)
    {
        return self::getRedis()->hKeys("room:{$roomId}");
    }

    /**
     * 获取room中全部UserId
     * @param  int    $roomId roomId
     * @return array         房间中UserId
     */
    public static function selectRoomAllUser(int $roomId)
    {
        return self::getRedis()->hVals("room:{$roomId}");
    }

    /**
     * 获取room中全部UserId
     * @param  int    $roomId roomId
     * @return array         房间中UserId
     */
    public static function selectRoomOneUser(int $roomId,int $fd)
    {
        $user = self::getRedis()->hGet("room:{$roomId}",$fd);
        if(empty($user)){
            return false;
        }
        return explode(",",$user);
    }
    /**
     * 退出room
     * @param  int    $roomId roomId
     * @param  int    $fd     fd
     * @return
     */
    public static function exitRoom(int $roomId, int $fd)
    {
        $userModel = Room::selectRoomOneUser($roomId,$fd);
        TaskManager::async(function ()use($roomId,$userModel){
            $list = Room::selectRoomFd($roomId);
            foreach ($list as $fd) {
                $message = json_encode([
                    'type'=>'system',
                    'action'=>'exit_room',
                    'userId'=>$userModel[0],
                    'message'=>$userModel[1].'退出房间'
                ]);
                ServerManager::getInstance()->getServer()->push($fd,$message);
            }
        });
        self::getRedis()->hDel("room:{$roomId}", $fd);
        self::getRedis()->zRem('rfMap', $fd);
    }

    /**
     * 关闭连接
     * @param  string $fd 链接id
     */
    public static function close(int $fd)
    {
        $roomId = self::getRoomId($fd);
        self::exitRoom($roomId, $fd);
        self::getRedis()->zRem('online', $fd);
    }
}