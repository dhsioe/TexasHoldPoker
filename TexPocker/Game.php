<?php

/**
 * 游戏实例
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/28 
 * @Description: 
 */

namespace TexPocker;

class Game
{

    protected $roomId;

    public function __construct(string $roomId)
    {
        $this->roomId = $roomId;
    }

    public function getEmptyChair()
    {
    }

    public function canStart()
    {
    }

    public function onGameStart()
    {
    }

    public function onPlayerJoin($chair, $player)
    {
    }
}
