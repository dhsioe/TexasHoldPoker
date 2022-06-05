<?php

/**
  * 德州牌型基础类
  * @author: hsioe1111@gmail.com
  * @Date: 2022/06/05 
  * @Description: 
*/
namespace TexPocker\Pocker\Cards;


class TexHoldCard
{
    /**
     * 牌类型
     * 
     * @var int
    */
    protected $cardType;

    /**
     * 牌值
     * 
     * @var array
    */
    protected $cardValues;

    public function __construct($cardValues)
    {
        $this->cardValues = $cardValues;
    }

    /**
     * 设置牌的类型值
     * 
     * @param array $cardValues 牌值
    */
    public function setCardValues(array $cardValues)
    {
        $this->cardValues = $cardValues;
    }

    /**
     * 获取牌的类型
     * 
     * @return int 牌类型值
    */
    public function getCardType(): int
    {
        return $this->cardType;
    }

    /**
     * 获取牌型值
     * 
     * @return array
    */
    public function getCardValues(): array
    {
        return $this->cardValues;
    }

    /**
     * 比较牌型大小
     * 
     * @param TexHoldCard $otherCard 比对的牌
     * 
     * @return int 0 - 小
     *             1 - 大
     *             2 - 相等
    */
    public function compare(TexHoldCard $otherCard)
    {
        if($this->cardType < $otherCard->getCardType()) {
            return 0;
        }

        if($this->cardType > $otherCard->getCardType()) {
            return 1;
        }

        foreach($otherCard->getCardValues() as $key => $otherValue) {
            if($otherValue > $this->cardValues[$key]) {
                return 0;
            }

            if($otherValue < $this->cardValues[$key]) {
                return 1;
            }
        }

        return 2;
    }
}
