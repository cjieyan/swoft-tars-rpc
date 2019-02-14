<?php

namespace Swoft\TarsRpc\Server\Rpc;

use Swoft\App;
use Swoft\Bean\Collector\SwooleListenerCollector;
use Swoft\Bootstrap\SwooleEvent;
use Swoole\Server;
use Swoft\Bootstrap\Server\AbstractServer;

/**
 * RPC Server
 */
class TarsRpcServer extends AbstractServer
{
    /**
     * Start server
     */
    public function start()
    {
        // add server type
        $this->serverSetting['server_type'] = self::TYPE_RPC;
        $settings = App::getAppProperties()->get('server');
        $tars_settings = $settings['tcp-tars'];
        $this->server = new Server($tars_settings['host'], $tars_settings['port'], $tars_settings['mode'], $this->tcpSetting['type']);

        // Bind event callback
        $listenSetting = $tars_settings;
        unset($listenSetting['host'], $listenSetting['port'], $listenSetting['mode'], $listenSetting['type']);


        $setting = array_merge($this->setting, $listenSetting);
        $this->server->set($setting);
        $this->server->on(SwooleEvent::ON_START, [$this, 'onStart']);
        $this->server->on(SwooleEvent::ON_WORKER_START, [$this, 'onWorkerStart']);
        $this->server->on(SwooleEvent::ON_MANAGER_START, [$this, 'onManagerStart']);
        $this->server->on(SwooleEvent::ON_PIPE_MESSAGE, [$this, 'onPipeMessage']);

        $swooleEvents = $this->getSwooleEvents();
        $this->registerSwooleEvents($this->server, $swooleEvents);

        // before start
        $this->beforeServerStart();
        $this->server->start();
    }

    /**
     * @return array
     */
    private function getSwooleEvents(): array
    {
        $swooleListeners = SwooleListenerCollector::getCollector();
        $portEvents = $swooleListeners[SwooleEvent::TYPE_PORT][0] ?? [];
        $serverEvents = $swooleListeners[SwooleEvent::TYPE_SERVER] ?? [];
        return array_merge($portEvents, $serverEvents);
    }
}
