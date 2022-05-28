<?php

/**
 * CardManager(牌管理器)
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/28 
 * @Description: 
 */

namespace TexPocker\Pocker;

class CardManager
{
    /**
     * 牌型
     * 
     * Card: val_suit(牌大小_牌花色)
     * 
     * 牌值:
     *   T(10)    - 10
     *   J(Jack)  - 11

     *   Q(Queen) - 12
     *   K(King)  - 13
     *   A(Ace)   - 14 or 1
     *
     * @var array 
     */
    protected $cards = [];

    protected $cardValues = [
        '2', '3', '4', '5', '6', '7', '8',
        '9', 'T', 'J', 'Q', 'K', 'A'
    ];

    /**
     * 牌花色
     * 
     * @return array
     */
    protected $cardSuits = ['Z', 'X', 'C', 'N'];

    /**
     * 牌张数
     * 
     * @var int
     */
    protected $count = 52;

    public function __construct()
    {
        $this->resetCards();
    }

    /**
     * 重置牌
     */
    public function resetCards()
    {
        $this->cards = [];
        for ($i = 0; $i < count($this->cardValues); $i++) {
            for ($j = 0; $j < count($this->cardSuits); $j++) {
                array_push($this->cards, $this->cardValues[$i], $this->cardSuits[$j]);
            }
        }
    }

    /**
     * 获取牌
     * 
     * @return array ['A_Z', 'A_C' ...] 牌张数组
     */
    public function getCardByCount(int $count)
    {
        $returnCards = [];

        for ($i = 0; $i < $count; $i++) {
            array_push($returnCards, array_shift($this->cards));
        }

        return $returnCards;
    }

    /**
     * 洗牌(将扑克牌打乱)
     */
    public function shuffleCards()
    {
        shuffle($this->cards);
    }

    /**
     * 获取单张牌的牌值和花色
     * {suit}: 花色
     * {value}: 牌值
     * @return [$value, $suit]
     */
    public function getCardValueNSuit(string $card): array
    {
        return explode('_', $card);
    }
}
