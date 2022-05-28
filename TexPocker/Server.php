<?php

/**
 * 游戏服务(GameServer).
 *
 * @author: hsioe1111@gmail.com
 * @Date: 2022-05-28
 * @Description:
 */

namespace TexPocker;

use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Worker;

class Server
{
    /**
     * onHandle(处理消息).
     */
    public static function onHandle(TcpConnection $connection, $message)
    {
        global $protocols;

        $message = json_decode($message, true);
        // 记录消息时间戳
        $connection->lastMessageTime = time();

        if (!key_exists($message['protocol'], $protocols)) {
            // 协议不存在
            return;
        }

        list($callback, $protocol) = $protocols[$message['protocol']];

        self::{$callback}($connection, new $protocol($message['data']));
    }

    /**
     * 心跳检测(Heaert Beat Detected).
     */
    public static function onTick(Worker $worker)
    {
        Timer::add(10, function () use ($worker) {
            $timeNow = time();

            foreach ($worker->connections as $connection) {
                if (empty($connection->lastMessageTime)) {
                    $connection->lastMessageTime = $timeNow;
                    continue;
                }

                if ($timeNow - $connection->lastMessageTime > self::closeWhenTimeLong()) {
                    $connection->close();
                }
            }
        });
    }

    public static function closeWhenTimeLong(): int
    {
        return 120;
    }
}
