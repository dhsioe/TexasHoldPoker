<?php
declare(strict_types=1);
/**
  * ProtocolDto 模型
  * @author: hsioe1111@gmail.com
  * @Date: 2022/05/30 
  * @Description: 
*/
namespace TexPocker\Common;

use JsonSerializable;


class ProtocolDto implements JsonSerializable
{
    use JsonTrait;

    /**
     * 初始化数据
    */
    public function __construct($attrs)
    {
        if(!empty($attrs) && (is_array($attrs) || is_object($attrs)) ) {
            foreach($attrs as $key => $val) {
                if(!$val) {
                    continue;
                }

                call_user_func([$this, 'set'.$this->_findKey($key)], $this->_findValue($key, $val));
            }
        }
    }

    /**
     * 获取key
    */
    public function _findKey(string $key): string
    {
        return implode("", array_map(function ($key){
            return ucfirst($key);
        },explode('_', $key)));
    }

    /**
     * 获取values
     * 
     * @return mixed
    */
    public function _findValue(string $key, $value)
    {
        return $value;
    }

    /**
     * 默认调用
    */
    public function __call($name, $argument)
    {
        // 防止方法不存在
    }
}
