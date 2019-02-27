<?php

namespace Swoft\TarsRpc\Server\Middleware;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\App;
use Swoft\Bean\Annotation\Bean;
use Swoft\Http\Message\Middleware\MiddlewareInterface;
use Swoft\TarsRpc\Server\Event\RpcServerEvent;
use Swoft\TarsRpc\Server\Packer\TarsPacker;
use Swoft\TarsRpc\Server\Router\HandlerAdapter;
use Swoole\Coroutine;
use Tars\client\TUPAPIWrapper;

/**
 * service packer
 *
 * @Bean()
 * @uses      PackerMiddleware
 * @version   2017年11月26日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class PackerMiddleware implements MiddlewareInterface
{
    /**
     * the server param of service
     */
    const ATTRIBUTE_SERVER = 'serviceRequestServer';

    /**
     * the fd param of service
     */
    const ATTRIBUTE_FD = 'serviceRequestFd';

    /**
     * the fromid param of service
     */
    const ATTRIBUTE_FROMID = 'serviceRequestFromid';

    /**
     * the data param of service
     */
    const ATTRIBUTE_DATA = 'serviceRequestData';

    /**
     * packer middleware
     *
     * @param \Psr\Http\Message\ServerRequestInterface     $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $packer = App::getBean(TarsPacker::class);
        $tmp_data   = $request->getAttribute(self::ATTRIBUTE_DATA);
        $unpack_data   = $packer->unpack($tmp_data, 'tars');
        $pro_map = new \TARS_Map(\TARS::STRING, \TARS::STRING);
        $data = TUPAPIWrapper::getMap('pro', 1,$pro_map, $unpack_data['sBuffer'], $unpack_data['iVersion']);
        $data['interface'] = $unpack_data['sServantName'];
        $data['method']    = $unpack_data['sFuncName'];
        $data['params'] = json_decode($data['params'], true);
        // init data and trigger event
        App::trigger(RpcServerEvent::BEFORE_RECEIVE, null, $data);
        $request = $request->withAttribute(self::ATTRIBUTE_DATA, $data);

        /* @var \Swoft\TarsRpc\Server\Rpc\Response $response */
        $response      = $handler->handle($request);
        $serviceResult = $response->getAttribute(HandlerAdapter::ATTRIBUTE);
        $pack_data['iVersion'] = $unpack_data['iVersion'];
        $pack_data['iRequestId'] = $unpack_data['iRequestId'];
        $pack_data['ret'] = $serviceResult;
        $serviceResult = $packer->pack($pack_data, 'tars');

        return $response->withAttribute(HandlerAdapter::ATTRIBUTE, $serviceResult);
    }
}
