<?php

/**
 * ServerEntry (服务启动).
 *
 * @author: hsioe1111@gmail.com
 * @Date: 2022-05-28
 * @Description:
 */

use TextPocker\Pocker\CardFactory;
use TextPocker\Server;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

require_once __DIR__.'/vendor/autoload.php';

/**
 * ------------------------------------------------------
 * 全局Work进程(进程数为1)
 * 管理单个进程中的所有连接, 一个进程相当一个服务
 * ------------------------------------------------------.
 */
$pockerWorker = new Worker('websocket://0.0.0.0:3300');

// 进程数
$pockerWorker->count = 1;
$pockerWorker->name = 'TexPockerServer';

/**
 * ------------------------------------------------------
 * 全局ROOMS
 * 同一个进程相当于一个服务器
 * 每个ROOMID相当于一个大区.
 *
 * Rooms属性
 *  {Players} 房间里的玩家
 *  {Game} 房间的游戏实力
 *  {RoomInfo} 房间信息
 * ------------------------------------------------------
 */
$rooms = [];

/**
 * ------------------------------------------------------
 * 牌型工厂
 * 集成了德州扑克牌型判断和比牌方法
 * (如需调整，只需按照HsioeGame中提供的牌型工厂接口实现相应接口).
 *
 * 设置牌型
 *  -init(array $cards)
 *
 * 获取牌型
 *  -get():TexPockerCard
 *
 * 牌型比较: 0-小 1-大 2-相等
 *  -compare(TexHoldCard $otherCard): int
 *
 * @var CardFactory
 */
$cardFactory = new CardFactory();

/**
 * --------------------------------------------------------\
 * 全局协议组
 *  服务对客户端 S_C_
 *  客户端对服务端 C_S_
 *  eg:
 * [
 *    'protocol' => [callback, protocolDto]
 * ]
 * --------------------------------------------------------.
 */
$protocols = [
   // 游戏开始协议
   'S_C_ON_GAME_START' => [],
   // 发送手牌协议
   'S_C_ON_HAND_CARDS' => [],
   // 广播协议
   'S_C_ON_BROADCAST' => [],
   // 客户端加入房间协议
   'C_S_ON_JOIN_ROOM' => ['onJoinRoom', JoinRoomProtocol::class],
   // 玩家行动协议
   'C_S_ON_ACTION' => ['onDoAction', PlayerActionProtocol::class],
   // 展示手牌
   'C_S_ON_SHOW_HAND_CARDS' => [],
];

// 消息回调
$pockerWorker->onMessage = function (TcpConnection $connection, $message) {
    Server::onHandle($connection, $message);
};

// 心跳检查
$pockerWorker->onWorkerStart = function ($worker) {
    Server::onTick($worker);
};

// 启动游戏服务
Worker::runAll();
