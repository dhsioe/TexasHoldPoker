<?php

/**
 * 玩家动作常量
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/29 
 * @Description: 
 */

namespace TexPocker\Pocker;

class PlayerAction
{
    /**
     * 弃牌
     */
    const FOLD = 1;

    /**
     * 过牌
     */
    const CHECK = 2;

    /**
     * 跟牌
     */
    const CALL = 3;

    /**
     * 加注
     */
    const RAISE = 4;

    /**
     * 全下
     */
    const ALL_IN = 5;
}
