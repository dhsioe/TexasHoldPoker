<?php
declare(strict_types=1);
/**
  * JsonTrait
  * @author: hsioe1111@gmail.com
  * @Date: 2022/05/30 
  * @Description: 
*/
namespace TexPocker\Common;

trait JsonTrait
{

    public function jsonSerialize()
    {
        $data = [];

        foreach($this as $key => $val) {
            if(!$val) {
                continue;
            }

            $data[$key] = $val;
        }

        return $data;
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this), true);
    }

    /**
     * 序列化
    */
    public function serializa()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * 反序列化
     * 
     * @param string $data
    */
    public function unserialize($data)
    {
        $values = unserialize($data);

        foreach($values as $key => $val) {
            $this->$key = $val;
        }
    }
}
