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
    protected $cardManager;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_construct()
    {
        $this->cardManager = new CardManager();

        $this->assertNotEmpty(
            $this->cardManager->getCards()
        );
    }
}
