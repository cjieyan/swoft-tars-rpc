<?php
namespace Swoft\TarsRpc\Server\Packer;
use Swoft\Rpc\Packer\PackerInterface;
use Tars\client\RequestPacket;
use Swoft\Bean\Annotation\Bean;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13
 * Time: 13:45
 */


/**
 * Class TarsPacker
 * @Bean()
 */
class TarsPacker implements PackerInterface
{
    function pack($data)
    {
        // TODO: Implement pack() method.

        return $data;
    }

    function unpack($data)
    {
        // TODO: Implement unpack() method.
        $ret = \TUPAPI::decodeReqPacket($data);
        var_dump($ret);
        return $ret;
    }

}