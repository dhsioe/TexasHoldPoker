<?php

/**
 * 房间服务
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/28 
 * @Description: 
 */

namespace TexPocker;

use Workerman\Connection\TcpConnection;

class Room
{

    /**
     * 实例化一个玩家
     * 
     * @param TcpConnection $connection 连接实例
     * 
     * @return Player
     */
    public static function createPlayer(TcpConnection $connection)
    {
        $connection->userId = random_int(1000, 9999);
        return new Player($connection);
    }

    /**
     * 将玩家加入房间
     * 
     * @param string $roomId 房间ID
     * @param Player $player
     */
    public static function addPlayerToRoom(string $roomId, $player)
    {
        global $rooms;
        array_push($rooms[$roomId]['PLAYERS'], $player);
    }

    /**
     * 获取房间游戏实例
     * 
     * @return Game
     */
    public static function getRoomGameInstance(string $roomId)
    {
        global $rooms;
        return $rooms[$roomId]['GAME'];
    }

    /**
     * 获取房间的所有玩家
     * 
     * @param string $roomId
     */
    public static function getRoomPlayers(string $roomId)
    {
        global $rooms;
        return $rooms[$roomId]['PLAYERS'];
    }

    /**
     * 获取房间某个玩家
     * 
     * @param string $roomId
     * @param int $chair 玩家椅子
     */
    public static function getRoomPlayer(string $roomId, int $chair)
    {
        return self::getRoomGameInstance($roomId)
            ->desk->chairToPlayers[$chair];
    }

    /**
     * 监听房间聊天协议
     * 
     * @param string $roomId 房间ID
     * @param string $message 推送消息
     */
    public static function onTalk(string $roomId, string $message)
    {
    }

    /**
     * 加入房间
     * 
     * {$roomId} 房间唯一标识, 寻找进程中分区关键
     * {$connection} 连接实例，当前连接身份
     * 
     * @param string $roomId 加入的房间ID
     * @param TcpConnection $connection 连接实例
     */
    public static function joinRoom(string $roomId, TcpConnection $connection)
    {
        $player = self::createPlayer($connection);
        $gameInstance = self::getRoomGameInstance($roomId);

        // 获取椅子
        $chair = $gameInstance->getEmptyChair();
        if (!$chair) {
            // 已经满了
            $player->connection->send("Room {$roomId} is Full");
            return;
        }

        $player->connection->roomId = $roomId;
        $player->connection->chair = $chair;
        $player->chair = $chair;

        // 通知游戏有玩家加入
        $gameInstance->onPlayerJoin($chair, $player);

        self::addPlayerToRoom($roomId, $player);

        self::broadcast($roomId, "Player:{$player->connection->userId} is Comming!");

        if ($gameInstance->canStart()) {
            // 可以开始游戏了
            $gameInstance->onGameStart();
        }
    }

    /**
     * 生成一个大区房间
     * 
     * @param string $roomId
     */
    public static function create(string $roomId)
    {
        global $rooms;

        $rooms[$roomId]['PLAYERS'] = [];
        $rooms[$roomId]['GAME'] = new Game($roomId);
        $rooms[$roomId]['ROOM_INFO'] = [];
    }

    /**
     * 房间广播
     * 
     * @param string $roomId 房间ID
     */
    public static function broadcast(string $roomId, $protocol)
    {
        foreach (self::getRoomPlayers($roomId) as $player) {
            $player->connection->send($protocol);
        }
    }
}
