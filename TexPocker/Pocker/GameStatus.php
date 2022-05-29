<?php

/**
  * 游戏状态常量
  * @author: hsioe1111@gmail.com
  * @Date: 2022/05/29 
  * @Description: 
*/
namespace TexPocker\Pocker;

class GameStatus
{
    /**
     *  等待开始
    */
    const ON_READY = 0;

    /**
     * 手牌阶段
    */
    const ON_HAND_CARD = 1;

    /**
     * 翻牌阶段
    */
    const ON_FLOP_CARD = 2;

    /**
     * 转牌阶段
    */
    const ON_TURN_CARD = 3;

    /** 
     * 河牌阶段
    */
    const ON_RIVER_CARD = 4;

    /**
     * 比牌阶段
    */
    const ON_COMPARE_CARD = 5;

    /**
     * 结算阶段
    */
    const ON_BALANCE = 6;

    /**
     * 新的一局开始
    */
    const ON_SET_START = 7;
}
