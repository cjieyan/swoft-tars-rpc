<?php
namespace Swoft\TarsRpc\Server\Packer;
use Swoft\Rpc\Packer\PackerInterface;
use Swoole\Coroutine;
use Tars\client\RequestPacket;
use Swoft\Bean\Annotation\Bean;
use Tars\core\Request;
use Tars\core\Response;
use Tars\protocol\TARSProtocol;

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
    /**
     * response数据打包
     * @param mixed $data
     * @return mixed
     */
    function pack($data)
    {
        //$ret = $tars_protpcol->packRsp($paramInfo, $unpackResult, $args, $returnVal);
        return $data;
    }

    /**
     * Request 参数解包
     * @param mixed $data
     * @return mixed
     */
    function unpack($data)
    {
        $tars_protpcol = new TARSProtocol();
        $ret = $tars_protpcol->unpackReq($data);
        return $ret;
    }

}