<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/28
 * Time: 11:03
 */

class ResponsePacket
{
    public $_responseBuf;
    public $iVersion;

    public function decode()
    {
        // 接下来解码
        $decodeRet = \TUPAPI::decode($this->_responseBuf, $this->iVersion);
        var_dump("decode:", $decodeRet);
        if ($decodeRet['iRet'] !== 0) {
            $msg = isset($decodeRet['sResultDesc'])?$decodeRet['sResultDesc']:"";
            throw new \Exception($msg, $decodeRet['iRet']);
        }
        $sBuffer = $decodeRet['sBuffer'];
        return $sBuffer;
    }
}
class RequestPacket
{
    public $_encodeBufs = array();
    public $_requestBuf;
    public $_responseBuf;
    public $_sBuffer;

    public $_iVersion;

    public $_servantName;
    public $_funcName;

    public $_iRequestId = 1;

    public $_cPacketType = 0;
    public $_iMessageType = 0;
    public $_tarsTimeout = 2000;
    public $_iTimeout = 2;
    public $_contexts = [];
    public $_statuses = [];

    public function encode()
    {
        if ($this->_iVersion === 0x01) {
            // 需要对数据进行兼容
            $newEncodeBufs = [];
            foreach ($this->_encodeBufs as $buf) {
                $newEncodeBufs[] = $buf;
            }
            $requestBuf = \TUPAPI::encode($this->_iVersion, $this->_iRequestId,
                $this->_servantName, $this->_funcName, $this->_cPacketType,
                $this->_iMessageType, $this->_tarsTimeout, $this->_contexts,
                $this->_statuses, $newEncodeBufs);
        } else {
            $requestBuf = \TUPAPI::encode($this->_iVersion, $this->_iRequestId,
                $this->_servantName, $this->_funcName, $this->_cPacketType,
                $this->_iMessageType, $this->_tarsTimeout, $this->_contexts,
                $this->_statuses, $this->_encodeBufs);
        }

        return $requestBuf;
    }
}

/**
 * 解包，压缩包处理逻辑
 */
class STcp {
    function swooleTcp($sIp, $iPort, $requestBuf, $timeout = 2)
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);

        $client->set(array(
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset' => 0,       //第几个字节开始计算长度
            'package_max_length' => 2000000,  //协议最大长度
        ));

        if (!$client->connect($sIp, $iPort, $timeout)) {
            throw new \Exception("socket tcp 连接失败'", 1);
        }

        if (!$client->send($requestBuf)) {
            $client->close();
            throw new \Exception("socket tcp 发送失败'", 2);
        }
        //读取最多32M的数据
        $tarsResponseBuf = $client->recv();

        if (empty($tarsResponseBuf)) {
            $client->close();
            // 已经断开连接
            throw new \Exception("socket tcp 已经断开连接'", 3);
        }

        return $tarsResponseBuf;
    }
}



class TUPAPIWrapper
{
    public static function putBool($paramName, $tag, $bool, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putBool($tag, $bool, $iVersion);
            } else {
                $buffer = \TUPAPI::putBool($paramName, $bool, $iVersion);
            }

            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_BOOL_FAILED), Code::TARS_PUT_BOOL_FAILED);
            }

            return  $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_BOOL_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }
    public static function getBool($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getBool($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getBool($name, $sBuffer, false, $iVersion);
            }

            return $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_BOOL_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putChar($paramName, $tag, $char, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putChar($tag, $char, $iVersion);
            } else {
                $buffer = \TUPAPI::putChar($paramName, $char, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_CHAR_FAILED), Code::TARS_PUT_CHAR_FAILED);
            }

            return  $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_CHAR_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getChar($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getChar($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getChar($name, $sBuffer, false, $iVersion);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_CHAR_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putUInt8($paramName, $tag, $uint8, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putUint8($tag, $uint8, $iVersion);
            } else {
                $buffer = \TUPAPI::putUint8($paramName, $uint8, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_UINT8_FAILED), Code::TARS_PUT_UINT8_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_UINT8_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getUint8($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getUint8($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getUint8($name, $sBuffer, false, $iVersion);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_UINT8_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putShort($paramName, $tag, $short, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putShort($tag, $short, $iVersion);
            } else {
                $buffer = \TUPAPI::putShort($paramName, $short, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_SHORT_FAILED), Code::TARS_PUT_SHORT_FAILED);
            }

            return  $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_SHORT_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getShort($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getShort($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getShort($name, $sBuffer, false, $iVersion);
            }

            return $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_SHORT_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putUInt16($paramName, $tag, $uint16, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putUint16($tag, $uint16, $iVersion);
            } else {
                $buffer = \TUPAPI::putUint16($paramName, $uint16, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_UINT16_FAILED), Code::TARS_PUT_UINT16_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_UINT16_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getUint16($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getUint16($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getUint16($name, $sBuffer, false, $iVersion);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_UINT16_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putInt32($paramName, $tag, $int, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putInt32($tag, $int, $iVersion);
            } else {
                $buffer = \TUPAPI::putInt32($paramName, $int, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_INT32_FAILED), Code::TARS_PUT_INT32_FAILED);
            }

            return  $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_INT32_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getInt32($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getInt32($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getInt32($name, $sBuffer, false, $iVersion);
            }

            return $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_INT32_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putUint32($paramName, $tag, $uint, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putInt32($tag, $uint, $iVersion);
            } else {
                $buffer = \TUPAPI::putInt32($paramName, $uint, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_UINT32_FAILED), Code::TARS_PUT_UINT32_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_UINT32_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getUint32($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getUint32($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getUint32($name, $sBuffer, false, $iVersion);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_UINT32_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putInt64($paramName, $tag, $bigint, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putInt64($tag, $bigint, $iVersion);
            } else {
                $buffer = \TUPAPI::putInt64($paramName, $bigint, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_INT64_FAILED), Code::TARS_PUT_INT64_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_INT64_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getInt64($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getInt64($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getInt64($name, $sBuffer, false, $iVersion);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_INT64_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putDouble($paramName, $tag, $double, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putDouble($tag, $double, $iVersion);
            } else {
                $buffer = \TUPAPI::putDouble($paramName, $double, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_DOUBLE_FAILED), Code::TARS_PUT_DOUBLE_FAILED);
            }

            return  $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_DOUBLE_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getDouble($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getDouble($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getDouble($name, $sBuffer, false, $iVersion);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_DOUBLE_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putFloat($paramName, $tag, $float, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putFloat($tag, $float, $iVersion);
            } else {
                $buffer = \TUPAPI::putFloat($paramName, $float, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_FLOAT_FAILED), Code::TARS_PUT_FLOAT_FAILED);
            }

            return  $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_FLOAT_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getFloat($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getFloat($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getFloat($name, $sBuffer, false, $iVersion);
            }

            return $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_FLOAT_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putString($paramName, $tag, $string, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putString($tag, $string, $iVersion);
            } else {
                $buffer = \TUPAPI::putString($paramName, $string, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_STRING_FAILED), Code::TARS_PUT_STRING_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_STRING_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getString($name, $tag, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getString($tag, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getString($name, $sBuffer, false, $iVersion);
            }
            if ($result < 0) {
                throw new \Exception(Code::getErrMsg(Code::TARS_GET_STRING_FAILED), Code::TARS_GET_STRING_FAILED);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_STRING_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putVector($paramName, $tag, $vec, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putVector($tag, $vec, $iVersion);
            } else {
                $buffer = \TUPAPI::putVector($paramName, $vec, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_VECTOR_FAILED), Code::TARS_PUT_VECTOR_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_VECTOR_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getVector($name, $tag, $vec, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getVector($tag, $vec, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getVector($name, $vec, $sBuffer, false, $iVersion);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_VECTOR_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putMap($paramName, $tag, $map, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putMap($tag, $map, $iVersion);
            } else {
                $buffer = \TUPAPI::putMap($paramName, $map, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_MAP_FAILED), Code::TARS_PUT_MAP_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_MAP_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getMap($name, $tag, $obj, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getMap($tag, $obj, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getMap($name, $obj, $sBuffer, false, $iVersion);
            }
            if (!is_array($result)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_GET_MAP_FAILED), Code::TARS_GET_MAP_FAILED);
            }

            return  $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_MAP_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function putStruct($paramName, $tag, $obj, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $buffer = \TUPAPI::putStruct($tag, $obj, $iVersion);
            } else {
                $buffer = \TUPAPI::putStruct($paramName, $obj, $iVersion);
            }
            if (!is_string($buffer)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_PUT_STRUCT_FAILED), Code::TARS_PUT_STRUCT_FAILED);
            }

            return $buffer;
        } catch (\Exception $e) {
            $code = Code::TARS_PUT_STRUCT_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    public static function getStruct($name, $tag, &$obj, $sBuffer, $iVersion)
    {
        try {
            if ($iVersion === Consts::TARSVERSION) {
                $result = \TUPAPI::getStruct($tag, $obj, $sBuffer, false, $iVersion);
            } else {
                $result = \TUPAPI::getStruct($name, $obj, $sBuffer, false, $iVersion);
            }

            if (!is_array($result)) {
                throw new \Exception(Code::getErrMsg(Code::TARS_GET_STRUCT_FAILED), Code::TARS_GET_STRUCT_FAILED);
            }
            self::fromArray($result, $obj);

            return $result;
        } catch (\Exception $e) {
            $code = Code::TARS_GET_STRUCT_FAILED;
            throw new \Exception(Code::getErrMsg($code), $code);
        }
    }

    // 将数组转换成对象
    public static function fromArray($data, &$structObj)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if ($structObj->$key instanceof \TARS_Struct) {
                    self::fromArray($value, $structObj->$key);
                } else {
                    $structObj->$key = $value;
                }
            }
        }
    }
}




class Code
{
    // 错误码定义（需要从扩展开始规划）
    const TARS_SUCCESS = 0; // taf
    const TARS_FAILED = 1; // taf失败（通用失败）
    const TARS_MALLOC_FAILED = -1; // 内存分配失败

    const ROUTE_FAIL = -100;

    const TARS_SOCKET_SET_NONBLOCK_FAILED = -1002; // socket设置非阻塞失败
    const TARS_SOCKET_SEND_FAILED = -1003; // socket发送失败
    const TARS_SOCKET_RECEIVE_FAILED = -1004; // socket接收失败
    const TARS_SOCKET_SELECT_TIMEOUT = -1005; // socket的select超时，也可以认为是svr超时
    const TARS_SOCKET_TIMEOUT = -1006; // socket超时，一般是svr后台没回包，或者seq错误
    const TARS_SOCKET_CONNECT_FAILED = -1007; // socket tcp 连接失败
    const TARS_SOCKET_CLOSED = -1008; // socket tcp 服务端连接关闭
    const TARS_SOCKET_CREATE_FAILED = -1009;

    const TARS_PUT_STRUCT_FAILED = -10009;
    const TARS_PUT_VECTOR_FAILED = -10010;
    const TARS_PUT_INT64_FAILED = -10011;
    const TARS_PUT_INT32_FAILED = -10012;
    const TARS_PUT_STRING_FAILED = -10013;
    const TARS_PUT_MAP_FAILED = -10014;
    const TARS_PUT_BOOL_FAILED = -10015;
    const TARS_PUT_FLOAT_FAILED = -10016;
    const TARS_PUT_CHAR_FAILED = -10017;
    const TARS_PUT_UINT8_FAILED = -10018;
    const TARS_PUT_SHORT_FAILED = -10019;
    const TARS_PUT_UINT16_FAILED = -10020;
    const TARS_PUT_UINT32_FAILED = -10021;
    const TARS_PUT_DOUBLE_FAILED = -10022;

    const TARS_ENCODE_FAILED = -10025;
    const TARS_DECODE_FAILED = -10026;
    const TARS_GET_INT64_FAILED = -10031;
    const TARS_GET_MAP_FAILED = -10032;
    const TARS_GET_STRUCT_FAILED = -10033;
    const TARS_GET_STRING_FAILED = -10034;
    const TARS_GET_VECTOR_FAILED = -10035;
    const TARS_GET_INT32_FAILED = -10036;
    const TARS_GET_BOOL_FAILED = -10037;
    const TARS_GET_CHAR_FAILED = -10038;
    const TARS_GET_UINT8_FAILED = -10039;
    const TARS_GET_SHORT_FAILED = -10040;
    const TARS_GET_UINT16_FAILED = -10041;
    const TARS_GET_UINT32_FAILED = -10042;
    const TARS_GET_DOUBLE_FAILED = -10043;
    const TARS_GET_FLOAT_FAILED = -10044;

    // tars服务端可能返回的错误码
    const JCESERVERSUCCESS = 0; //服务器端处理成功
    const JCESERVERDECODEERR = -1; //服务器端解码异常
    const JCESERVERENCODEERR = -2; //服务器端编码异常
    const JCESERVERNOFUNCERR = -3; //服务器端没有该函数
    const JCESERVERNOSERVANTERR = -4; //服务器端五该Servant对象
    const JCESERVERRESETGRID = -5; //服务器端灰度状态不一致
    const JCESERVERQUEUETIMEOUT = -6; //服务器队列超过限制
    const JCEASYNCCALLTIMEOUT = -7; //异步调用超时
    const JCEPROXYCONNECTERR = -8; //proxy链接异常
    const JCESERVERUNKNOWNERR = -99; //服务器端未知异常

    public static function getErrMsg($code)
    {
        $errMap = [
            self::JCESERVERSUCCESS => '服务器端处理成功',
            self::JCESERVERDECODEERR => '服务器端解码异常',
            self::JCESERVERENCODEERR => '服务器端编码异常',
            self::JCESERVERNOFUNCERR => '服务器端没有该函数',
            self::JCESERVERNOSERVANTERR => '服务器端无该Servant对象',
            self::JCESERVERRESETGRID => '服务器端灰度状态不一致',
            self::JCESERVERQUEUETIMEOUT => '服务器队列超过限制',
            self::JCEASYNCCALLTIMEOUT => '异步调用超时',
            self::JCEPROXYCONNECTERR => 'proxy链接异常',
            self::JCESERVERUNKNOWNERR => '服务器端未知异常',
            self::ROUTE_FAIL => '路由失败，请检查环境是否匹配，agent是否配置正确',
            self::TARS_PUT_BOOL_FAILED => 'bool类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_STRUCT_FAILED => 'struct类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_VECTOR_FAILED => 'vector类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_INT64_FAILED => 'int64类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_INT32_FAILED => 'int32类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_STRING_FAILED => 'sting类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_MAP_FAILED => 'map类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_FLOAT_FAILED => 'float类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_CHAR_FAILED => 'char类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_UINT8_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_SHORT_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_UINT16_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_UINT32_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_DOUBLE_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',

            self::TARS_ENCODE_FAILED => 'taf编码失败，请检查数据类型，传入字段长度',
            self::TARS_DECODE_FAILED => 'taf解码失败，请检查传入的数据类型，是否从服务端接收到了正确的结果',

            self::TARS_GET_BOOL_FAILED => 'bool类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_STRUCT_FAILED => 'struct类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_VECTOR_FAILED => 'vector类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_INT64_FAILED => 'int64类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_INT32_FAILED => 'int32类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_STRING_FAILED => 'sting类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_MAP_FAILED => 'map类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_FLOAT_FAILED => 'float类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_CHAR_FAILED => 'char类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_UINT8_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_SHORT_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_UINT16_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_UINT32_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_DOUBLE_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',

            self::TARS_SOCKET_SET_NONBLOCK_FAILED => 'socket设置非阻塞失败',
            self::TARS_SOCKET_SEND_FAILED => 'socket发送失败',
            self::TARS_SOCKET_RECEIVE_FAILED => 'socket接收失败',
            self::TARS_SOCKET_SELECT_TIMEOUT => 'socket的select超时，也可以认为是svr超时',
            self::TARS_SOCKET_TIMEOUT => 'socket超时，一般是svr后台没回包，或者seq错误',
            self::TARS_SOCKET_CONNECT_FAILED => 'socket tcp 连接失败',
            self::TARS_SOCKET_CLOSED => 'socket tcp 服务端连接关闭',
            self::TARS_SOCKET_CREATE_FAILED => 'socket 创建失败',
        ];

        return isset($errMap[$code]) ? $errMap[$code] : '未定义异常';
    }
}
class Consts
{
    const SOCKET_MODE_UDP = 1;
    const SOCKET_MODE_TCP = 2;
    const SOCKET_TCP_MAX_PCK_SIZE = 65536; /* 64*1024 */

    const TARSVERSION = 0x01;
    const TUPVERSION = 0x03;
}
