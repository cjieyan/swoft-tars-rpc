<?php
namespace Swoft\TarsRpc\Server\Packer;
use Swoft\Rpc\Packer\PackerInterface;
use Swoole\Coroutine;
use Tars\client\RequestPacket;
use Swoft\Bean\Annotation\Bean;
use Tars\client\TUPAPIWrapper;
use Tars\Code;
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
    function pack($pack_data)
    {
        $ret_data = $pack_data['ret'];
        $ret_map = new \TARS_Map(\TARS::STRING, \TARS::STRING);
        foreach ($ret_data['data'] as $key => $value) {
            if(is_array($value)){
                $value = json_encode($value);
            }
            $ret_map->pushBack([$key => $value]);
        }
        $__buffer[] = TUPAPIWrapper::putMap('data', 1, $ret_map, $pack_data['iVersion']);
        $__buffer[] = TUPAPIWrapper::putInt32('status', 2, $ret_data['status'], $pack_data['iVersion']);
        $__buffer[] = TUPAPIWrapper::putString('msg', 3, $ret_data['msg'], $pack_data['iVersion']);
        $rspBuf = \TUPAPI::encode($pack_data['iVersion'], $pack_data['iRequestId'], "", "", 0, 0, 2, [], [], $__buffer);
        return $rspBuf;
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