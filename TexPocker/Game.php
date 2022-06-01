<?php

/**
 * 游戏实例
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/28 
 * @Description: 
 */

namespace TexPocker;

use RuntimeException;
use TexPocker\Pocker\GameStatus;
use TexPocker\Pocker\PlayerAction;

class Game
{
    /**
     * 游戏房间ID
     * 
     * @var string
     */
    protected $roomId;

    /**
     * 当前房间游戏轮数
     * 
     * @var int
     */
    public $setCount;

    /**
     * 游戏房间桌子
     * 
     * @var Desk
     */
    public $desk;

    /**
     * 游戏房间状态
     * 
     * @var int
     */
    protected $status;

    /**
     * 上一个玩家的动作
     * (参考 PlayerAction) 
     * @var int
     */
    public $lastAcion = 0;

    /**
     * 上一轮投注的最大金额
     * 
     * @var int
     */
    public $lastBetCoin = 0;

    /**
     * 每局游戏的Pool
     * 
     * @var int
     */
    public $coinPoolPerRound = 0;

    /**
     * 游戏的边池子(sidePool)
     * 
     * @var array [$chair => $coinPool]
     */
    protected $sidePoolPerRound = [];

    /**
     * 游戏状态回调
     * 
     * @var array [$status => $callback]
     */
    protected $statusToCallback = [
        GameStatus::ON_HAND_CARD => "onHandCards",
        GameStatus::ON_FLOP_CARD => "onFlop",
        GameStatus::ON_TURN_CARD => "onTurn",
        GameStatus::ON_RIVER_CARD => "onCompareCard",
        GameStatus::ON_BALANCE => "onBalance",
        GameStatus::ON_SET_START => "onSetStart"
    ];

    public function __construct(string $roomId)
    {
        $this->roomId = $roomId;
        $this->setCount = 0;
        $this->status = GameStatus::ON_READY;
        $this->desk = new Desk(2);
    }

    public function setDesk($desk)
    {
        $this->desk = $desk;
    }

    public function getSidePool(): array
    {
        return $this->sidePoolPerRound;
    }

    public function getCoinPool(): int
    {
        return $this->coinPool;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getLastBetCoin(): int
    {
        return $this->getLastBetCoin;
    }

    public function getLastAction(): int
    {
        return $this->lastAction;
    }

    /**
     * 每一小局开始之前
     */
    public function beforeSetStart()
    {
        $this->setCount += 1;
        $this->sidePoolPerRound = [];
        $this->coinPoolPerRound = 0;
        $this->status = GameStatus::ON_READY;

        $this->desk->whenGameSetStart();
        $this->desk->setDealer($this->setCount);
        $this->desk->setSb();
        $this->desk->setBb();

        // 发送局开始协议
        Room::broadcast($this->roomId, json_encode([
            'protocol' => 'S_C_ON_SET_START',
            'data' => [
                'setCount' => $this->setCount,
                'dealerChair' => 1,
                'sbChar' => 2,
                'bbChair' => 3,
                'coinPool' => $this->coinPoolPerRound
            ]
        ]));
    }

    /**
     * 每一轮开始之前
     * 德州扑克一局里有多轮的投注
     */
    public function beforeRoundStart()
    {
        $this->status += 1;
        $this->lastBetCoin = 0;
        $this->lastAction = 0;
        // 初始化桌子上轮玩家的投注
        $this->desk->whenSetStart();
    }

    /**
     * 下一局游戏
     * 庄位选择顺序
     *  (setCount=1) 房主做庄
     *  (setCount >1) 房主下一位开始顺时针做庄
     */
    public function onSetStart()
    {
        $this->beforeSetStart();

        $this->nextRound();
    }

    /**
     * 下一回合
     */
    public function nextRound()
    {
        $this->beforeRoundStart();

        if (key_exists($this->status, $this->statusToCallback)) {
            $this->{$this->statusToCallback[$this->status]}();
            return;
        };

        throw new RuntimeException(
            "无效的操作"
        );
    }

    /**
     * 通知下一个行动玩家
     */
    public function nextActionPlayer()
    {
        $player = $this->desk->getNextActionPlayer();
        $player->doSendAllowAction($this->getAllowActions($player), $this->lastBetCoin);
    }

    /**
     * 获取当前可以操作的Action
     * 
     * @param Player $player 当前行动玩家
     * 
     * @return array [$action1, $action2, ...] 可以行动的数组
     */
    public function getAllowActions($player): array
    {
        $defaultActions = [PlayerAction::FOLD, PlayerAction::ALL_IN];

        if ($this->lastAction == 0 || $this->lastAction === PlayerAction::CHECK) {
            // 第一个行动的玩家或者上一个玩家Check
            return array_merge($defaultActions, [
                PlayerAction::CHECK, PlayerAction::RAISE
            ]);
        }

        if ($this->lastAction === PlayerAction::FOLD) {
            // 是否有下注
            if ($this->lastBetCoin === 0) {
                return array_merge($defaultActions, [
                    PlayerAction::CHECK, PlayerAction::RAISE
                ]);
            }
        }

        if ($player->getBalanceCoin() > $this->lastBetCoin) {
            // 剩余金额足够
            return array_merge($defaultActions, [
                PlayerAction::RAISE, PlayerAction::CALL
            ]);
        }

        return $defaultActions;
    }

    /**
     * 更新游戏奖池
     * 
     * @param int $betCoin 投注金额
     */
    public function updateCoinPool(int $betCoin)
    {
        $this->coinPoolPerRound += $betCoin;
    }

    /**
     * 玩家动作
     * 
     * @param Player $player 执行动作的玩家
     * @param int $action 动作
     * @param int $betCoin 投注金额
     */
    public function onPlayerAction(Player $player, int $action, int $betCoin = 0)
    {
        //记录上一个动作
        $this->lastAction = $action;

        if ($betCoin > 0) {
            if ($betCoin > $this->lastBetCoin) {
                // 记录最大投注金额
                $this->lastBetCoin = $betCoin;
            }
            $this->updateCoinPool($betCoin);
            $this->desk->whenChairBet($player, $betCoin);
        }

        if (
            $action === PlayerAction::FOLD &&
            $this->nextIfActionFold($player, $betCoin)
        ) {
            return;
        }

        if (
            in_array($action, [PlayerAction::CHECK, PlayerAction::CALL]) &&
            $this->nextIfActionCheckOrCall($player, $betCoin)
        ) {
            return;
        }

        if (
            $action === PlayerAction::ALL_IN &&
            $this->nextIfActionAllIn($player, $betCoin)
        ) {
            return;
        }

        $this->desk->setLastActionChair($player->chair);
        $this->nextActionPlayer();
    }


    /**
     * 是否可以开始游戏
     * 
     * 默认桌子坐满即可开始游戏
     * 
     * @return bool
     */
    public function canStart(): bool
    {
        return $this->desk->isFull();
    }

    /**
     * 获取一个空的座位
     * 
     * @return int
     */
    public function getEmptyChair(): int
    {
        return $this->desk->getEmptyChair();
    }

    public function onGameStart()
    {
        $this->onSetStart();
    }

    /**
     * 玩家加入游戏
     * 
     * @param Player $player 玩家
     * @param int $chair 玩家加入的位置
     */
    public function onPlayerJoin($player, $chair)
    {
        $this->desk->whenPlayerSeated($player, $chair);
    }

    /**
     * 手牌阶段(Round 1)
     * 
     * 给所有玩家发送手牌
     */
    public function onHandCards()
    {
        $this->desk->dealCards();
        $this->nextActionPlayer();
    }

    /**
     * 翻牌阶段(Flop)
     * 发出三张公共牌并刷新玩家牌型 
     */
    public function onFlop()
    {
        $this->desk->refreshCommuityCards(3);
        $this->nextActionPlayer();
    }

    /**
     * 转牌阶段
     * 发出一张牌并刷新玩家牌型
     */
    public function onTurn()
    {
        $this->desk->refreshCommuityCards(1);
        $this->nextActionPlayer();
    }

    /**
     * 河牌阶段(River)
     * 发出最后一张公共牌刷新玩家牌型
     */
    public function onRiver()
    {
        $this->desk->refreshCommuityCards(1);
        $this->nextActionPlayer();
    }

    /**
     * 比牌阶段(Compare)
     * 河牌下完最后一轮注如果玩家还有多个需要比牌
     */
    public function onCompare()
    {
        $this->nextRound();
    }

    /**
     * 结算阶段
     * 比牌完成或者有玩家胜利后
     */
    public function onBalance()
    {
        $this->nextRound();
    }

    /**
     * 玩家弃牌后是否进入下一轮
     */
    public function nextIfActionFold($player, $betCoin)
    {
        if ($this->desk->isLastActionChair($player->chair)) {
            $this->nextRound();
            return true;
        }

        return false;
    }

    /**
     * 玩家Check or AllIn后是否进入下一轮
     */
    public function nextIfActionCheckOrCall($player, $betCoin)
    {
        if ($this->desk->isLastActionChair($player->chair)) {
            $this->nextRound();
            return true;
        }

        return false;
    }

    /**
     * 玩家AllIn后是否进入下一轮
     * 
     * @return bool
     */
    public function nextIfActionAllIn(Player $player, $betCoin)
    {
        $player->setAllIn(true);

        if ($this->desk->isLastActionChair($player->chair) && $player->betCoin < $this->lastBetCoin) {
            $this->nextRound();
            return true;
        }

        return false;
    }
}
