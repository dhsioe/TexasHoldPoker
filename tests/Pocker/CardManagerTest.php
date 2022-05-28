<?php

/**
 * 牌型单元测试
 * @author: hsioe1111@gmail.com
 * @Date: 2022/05/28 
 * @Description: 
 */

namespace PockerTest\Pocker;

use PHPUnit\Framework\TestCase;
use TexPocker\Pocker\CardManager;

class CardManagerTest extends TestCase
{
    /** @var CardManager */
    protected $cardManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->cardManager = new CardManager();
    }

    public function test_can_construct()
    {
        $this->assertNotEmpty(
            $this->cardManager->getCards()
        );
    }

    public function test_can_get_cards()
    {
        $this->assertEquals(1, count($this->cardManager->getCardByCount(1)));
        $this->assertEquals(2, count($this->cardManager->getCardByCount(2)));
        $this->assertEquals(3, count($this->cardManager->getCardByCount(3)));
    }
}
