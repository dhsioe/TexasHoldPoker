<?php
declare(strict_types=1);

/**
  * 加入房间协议(JoinRoomProtocol)
  * @author: hsioe1111@gmail.com
  * @Date: 2022/05/30 
  * @Description: 
*/
namespace TexPocker\Protocols;

use TexPocker\Common\ProtocolDto;

class JoinRoomProtocol extends ProtocolDto
{
    /**
     * 房间ID
     * @var string
    */
    protected $roomId;


    /**
     * Get the value of roomId
     */ 
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set the value of roomId
     *
     * @return  self
     */ 
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }
}
