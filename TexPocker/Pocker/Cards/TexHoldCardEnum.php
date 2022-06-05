<?php

/**
  * 牌型常量
  * @author: hsioe1111@gmail.com
  * @Date: 2022/06/05 
  * @Description: 
*/
namespace TexPocker\Pocker\Cards;

class TexHoldCardEnum
{
    /**
     * 高牌
    */
    const HIGHT_CARD = 1;

    /**
     * 对子
    */
    const PAIR = 2;

    /**
     * 两对
    */
    const TWO_PAIR = 3;

    /**
     * 三条
    */
    const THREE_OF_KIND = 4;

    /**
     * 顺子(Stright )
    */
    const STRIGHT = 5;

    /**
     * 同花(Flush)
    */
    const FLUSH = 6;

    /**
     * 葫芦(Full House)
    */
    const FULL_HOUSE = 7;

    /**
     * 四条(FOUR_OF_KIND)
    */
    const FOUR_OF_KIND = 8;

    /**
     * 同花顺(STRIGHT_FLUSH)
    */
    const STRIGHT_FLUSH = 9;

    /**
     * 皇家同花顺(ROYAL_FLUSH)
    */
    const ROYAL_FLUSH = 10;
}
