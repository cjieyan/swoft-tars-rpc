<?php
include dirname(__FILE__) . "/lib/tars.php";
$version = 1;
$requestPacket = new RequestPacket();
$requestPacket->_iVersion = $version;
$requestPacket->_funcName = "getUsers";
$requestPacket->_servantName = "App\Lib\DemoInterface";


//封装请求参数
$uid = 1000;
$pro = [
    'version'   => "1.0.1",
    'params'    => json_encode([[$uid]], true),
    'logid'     => uniqid(),
    'spanid'    => "0",
];
$pro_map = new \TARS_Map(\TARS::STRING, \TARS::STRING);
foreach ($pro as $key => $value) {
    $pro_map->pushBack([$key => $value]);
}
$__buffer = TUPAPIWrapper::putMap('pro', 1, $pro_map, $version);
$encodeBufs['pro'] = $__buffer;
$requestPacket->_encodeBufs = $encodeBufs;
$requestBuf = $requestPacket->encode();
$fp = stream_socket_client('tcp://127.0.0.1:9000', $errno, $errstr);
if (!$fp) {
    throw new Exception("stream_socket_client fail errno={$errno} errstr={$errstr}");
}
fwrite($fp, $requestBuf);
$result = fread($fp, 1024);
fclose($fp);

$unpack_data = \TUPAPI::decodeReqPacket($result);
var_dump($unpack_data);
$pro_map = new \TARS_Map(\TARS::STRING, \TARS::STRING);
$data = TUPAPIWrapper::getMap('data', 1,$pro_map, $unpack_data['sBuffer'], $unpack_data['iVersion']);
$status = TUPAPIWrapper::getInt32('status', 2, $unpack_data['sBuffer'], $unpack_data['iVersion']);
$msg = TUPAPIWrapper::getString('msg', 3, $unpack_data['sBuffer'], $unpack_data['iVersion']);

var_dump($data, $status, $msg);






