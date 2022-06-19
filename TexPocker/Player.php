<?php

/**
 * 玩家实例
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/28 
 * @Description: 
 */

namespace TexPocker;

use Workerman\Connection\TcpConnection;

class Player
{
    /**
     * 连接句柄
     */
    public $connection;

    /**
     * 玩家椅子
     * 
     * @var int
     */
    public $chair;

    /**
     * 是否庄位
     * 
     * @var bool
     */
    public $isDealer = false;

    /**
     * 是否小盲(sb)
     * 
     * @var bool
     */
    public $isSb = false;

    /**
     * 是否大盲(bb)
     * 
     * @var bool
     */
    public $isBb = false;

    /**
     * 是否弃牌(fold)
     * 
     * @var bool
     */
    public $isFold = false;

    /**
     * 是否AllIn
     * 
     * @var bool
     */
    public $isAllIn = false;

    /**
     * 玩家手牌(HandCards)
     */
    protected $handCards = [];

    /**
     *  玩家余额
     * 
     * @var int
     */
    protected $balanceCoin;

    /**
     * 本轮下注金额
     * 
     * @var int
     */
    protected $betCoin = 0;


    public function __construct(TcpConnection $connection)
    {
        $this->connection = $connection;
        $this->balanceCoin = random_int(1000, 9999);
    }

    /**
     * 配置位置
     * 
     * @param int $chair
     */
    public function setChair(int $chair)
    {
        $this->chair = $chair;
    }

    /**
     * 设置手牌
     * 
     * @param array $handCards ['A_Z', 'A_C']
     */
    public function setHandCards(array $handCards)
    {
        $this->handCards = $handCards;
        return $this;
    }

    public function setBetCoin(int $coin)
    {
        $this->betCoin = $coin;
        return $this;
    }

    public function setAllIn(bool $allIn)
    {
        $this->isAllIn = $allIn;
        return $this;
    }

    public function getHandCards(): array
    {
        return $this->handCards;
    }

    public function getBalanceCoin(): int
    {
        return $this->balanceCoin;
    }

    /**
     * 组合公共牌获取最大牌型
     * 
     * @return int
     */
    public function getCardTypeWithCommuityCards(array $commuityCards): int
    {
        return 1;
    }

    /**
     * 投注
     * 
     * @param int $betCoin 投注金额
     */
    public function doBet(int $betCoin)
    {
        if ($this->balanceCoin >= $betCoin) {
            $this->balanceCoin -= $betCoin;
            $this->betCoin += $betCoin;
        }
    }

    /**
     * 发送玩家可以操作协议
     * 
     * @param array $action [PlayerAciont::ALL_IN, ...]
     * @param int $lastBetCoin 可以下注的最小金额
     */
    public function doSendAllowAction(array $actions, int $lastBetCoin)
    {
        $protocol = [
            'protocol' => 'S_C_ALLOW_ACTIONS',
            'data' => [
                'actions' => $actions,
                'minBet'  => $lastBetCoin
            ]
        ];

        $this->connection->send(json_encode($protocol));
    }

    /**
     * 发送手牌协议
     */
    public function doSendHandCards(array $cards)
    {
        $protocol = [
            'protocol' => 'S_C_HAND_CARD',
            'data' => [
                'handCards' => $this->getHandCards(),
                'cardType' => $this->getCardTypeWithCommuityCards([])
            ]
        ];

        $this->setHandCards($cards)
            ->connection
            ->send(json_encode($protocol));
    }

    /**
     * 每轮游戏开始初始化投注金额
     */
    public function initBetCoin()
    {
        $this->setBetCoin(0);
    }
}
