<?php

/**
 * 牌型工厂(CardFactory)
 * @author: hsioe1111@gmail.com
 * @Date: 2022/06/08 
 * @Description: 
 */

namespace TexPocker\Pocker;

use TexPocker\Pocker\Cards\Flush;
use TexPocker\Pocker\Cards\FourOfKind;
use TexPocker\Pocker\Cards\FullHouse;
use TexPocker\Pocker\Cards\HightCard;
use TexPocker\Pocker\Cards\Pair;
use TexPocker\Pocker\Cards\RoyalFlush;
use TexPocker\Pocker\Cards\Stright;
use TexPocker\Pocker\Cards\StrightFlush;
use TexPocker\Pocker\Cards\TexHoldCard;
use TexPocker\Pocker\Cards\ThreeOfKind;
use TexPocker\Pocker\Cards\TwoPair;

class CardFactory
{
    /**
     *  大于10的牌型映射(MAP)
     * 
     * A(14): Ace
     * K(13): King
     * Q(12): Queen
     * J(11): Jack
     * T(10): Ten
     * 
     * @var array <string, int>
     */
    const SPECIAL_VALUE_MAPS = [
        'A' => 14,
        'K' => 13,
        'Q' => 12,
        'J' => 11,
        'T' => 10,
    ];

    protected $originCards = [];

    /**
     * 牌型值的存储
     * 
     * @var array <int, int>
     */
    protected $cardValueMaps = [];

    /**
     * 牌花色存储
     * 
     * @var array <string, int>
     */
    protected $cardSuitMaps = [];

    /**
     * 出现四次的牌
     * 
     * @var array
     */
    protected $fourValues = [];

    /**
     * 出现3次的牌
     * 
     * @var array
     */
    protected $threeValues = [];

    /**
     * 出现2次的牌
     * 
     * @var array
     */
    protected $twiceValues = [];

    /**
     * 单牌
     * 
     * @var array
     */
    protected $singleValues = [];

    /**
     * 顺子成牌
     * 
     * @var array
     */
    protected $strightValues = [];

    /**
     * 同花成牌
     * 
     * @var array
     */
    protected $suitValues = [];

    /**
     * 初始话花色和牌
     * 花色初始化:
     * ["suits" => [val1, val2, ...]]
     * 
     * 牌初始化
     * [$cardValue => $count(张数)]
     * [val1 => count1, val2 => count2]
     * 
     * @param array $cards ['A_Z', 'A_C', ...]
     */
    public function init(array $cards)
    {
        $this->fourValues = [];
        $this->twiceValues = [];
        $this->threeValues = [];
        $this->singleValues = [];
        $this->strightValues = [];
        $this->cardValueMaps = [];
        $this->cardSuitMaps = [];

        foreach ($cards as $card) {

            list($cardVal, $cardSuit) = explode('_', $card);

            if (key_exists($cardVal, self::SPECIAL_VALUE_MAPS)) {
                $val = (int) self::SPECIAL_VALUE_MAPS[$cardVal];
            }

            isset($this->cardValueMaps[$val]) ?
                $this->cardValueMaps[$val] += 1 :
                $this->cardValueMaps[$val] = 1;

            $this->cardSuitMaps[$cardSuit][] = $cardVal;
        }

        //排序
        ksort($this->cardValueMaps);
        return $this;
    }

    public function setCardValueMaps(array $cardValueMaps)
    {
        $this->cardValueMaps = $cardValueMaps;
        ksort($this->cardValueMaps);
        return $this;
    }

    public function setCardSuitMaps(array $cardSuitMaps)
    {
        $this->cardSuitMaps = $cardSuitMaps;
        return $this;
    }

    public function getCardValueMaps(): array
    {
        return $this->cardValueMaps;
    }

    public function getCardSuitMaps(): array
    {
        return $this->cardSuitMaps;
    }

    public function doExtra()
    {
        $this->extractSuitValues();
        $this->extractRepeatValues();
        $this->extractStrightValues();

        return $this;
    }

    /**
     * 按牌出现次数提取
     * 
     * {$singleValues}: 存储只出现一次的
     * {$twiceValues}: 存储出现两次的
     * {$threeValues}: 存储出现三次的
     * {$fourValues}: 存储出现四次的
     */
    public function extractRepeatValues()
    {
        foreach ($this->cardValueMaps as $cardVal => $count) {
            switch ($count) {
                case 1:
                    array_push($this->singleValues, $cardVal);
                    break;
                case 2:
                    count($this->twiceValues) < 2 && array_push($this->twiceValues, $cardVal);
                    break;
                case 3:
                    count($this->threeValues) < 1 && array_push($this->threeValues, $cardVal);
                    break;
                case 4:
                    array_push($this->fourValues, $cardVal);
                    break;
            }
        }
    }

    /**
     * 按花色提取牌
     * 初始化归类
     * eg:
     * {$cardSuitMaps}: ["X" => [1,2,3], "Z"=>[4], ...]
     */
    public function extractSuitValues()
    {
        foreach ($this->cardSuitMaps as $suitVals) {
            if (count($suitVals) >= 5) {
                $this->suitValues = $suitVals;
                ksort($this->suitValues);
            }
        }
    }

    /**
     * 提取顺子
     * (初始化已经做了去重)
     * 
     * 顺子判断: 头部和尾部之差是否为4，则可以得出是否是顺子
     * {$fast}: 尾部位置
     * {$slow}: 头部位置
     * {$singleValues}: 只出现一次的牌 [8, 7, 6, 5, 4, 3, 2]
     * eg:
     *  slow: 0
     *  fast: 4
     *  singleValues: [7,6,5,4,3,2,1] . 7-3 =4
     */
    public function extractStrightValues()
    {
        $singleValues = $this->singleValues;
        if (in_array(14, $singleValues)) {
            array_push($singleValues, 1);
        }

        $singleValCount = count($singleValues);

        $fast = 4;
        $slow = 0;

        while ($fast < $singleValCount) {
            if (($singleValues[$slow] - $singleValues[$fast]) === 4) {
                $this->strightValues = array_slice($singleValues, $slow, $fast);
                break;
            }

            $fast += 1;
            $slow += 1;
        }
    }

    /**
     * 获取牌型
     * 
     * @return TexHoldCard
     */
    public function get(): TexHoldCard
    {
        if ($this->suitValues && $this->strightValues) {
            // 即是同花又是顺子
            return $this->strightValues[0] === 14 ?
                new RoyalFlush($this->strightValues) :
                new StrightFlush($this->strightValues);
        }

        if ($this->fourValues) {
            return new FourOfKind(
                array_merge($this->fourValues, $this->singleValues[0])
            );
        }

        if ($this->threeValues && $this->twiceValues) {
            return new FullHouse([
                $this->threeValues[0], $this->twiceValues[0]
            ]);
        }

        if ($this->suitValues) {
            return new Flush($this->suitValues);
        }

        if ($this->strightValues) {
            return new Stright($this->strightValues);
        }

        if ($this->threeValues) {
            return new ThreeOfKind(
                array_merge($this->threeValues[0], array_slice($this->singleValues, 0, 2))
            );
        }

        if ($this->twiceValues) {
            return count($this->twiceValues) > 1 ?
                new TwoPair(
                    array_merge(array_slice($this->twiceValues, 2), $this->singleValues[0])
                ) :
                new Pair(
                    array_merge($this->twiceValues[0], array_slice($this->singleValues, 0, 3))
                );
        }

        return new HightCard(
            array_slice($this->singleValues, 0, 5)
        );
    }
}
