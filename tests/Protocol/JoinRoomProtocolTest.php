<?php

/**
  * 加入房间协议单元测试
  * @author: hsioe1111@gmail.com
  * @Date: 2022/05/30 
  * @Description: 
*/
namespace PockerTest\Protocol;

use PHPUnit\Framework\TestCase;
use TexPocker\Protocols\JoinRoomProtocol;

class JoinRoomProtocolTest extends TestCase
{

    public function test_can_get_room_id()
    {
        $protocol = new JoinRoomProtocol([
            'roomId' => 1
        ]);

        $this->assertEquals(1, $protocol->getRoomId());
    }
}
