<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/18
 * Time: 13:47
 */
namespace App\Boot;

use Swoft\App;
use Swoft\Bean\Annotation\ServerListener;
use Swoft\Bootstrap\Listeners\Interfaces\BeforeStartInterface;
use Swoft\Bootstrap\Server\AbstractServer;
use Swoft\Bootstrap\SwooleEvent;
use Swoole\Server;

/**
 * Class TarsServerListener
 * @package App\Boot\Listener
 * @ServerListener(event=SwooleEvent::ON_BEFORE_START)
 */
class TarsServerListener implements BeforeStartInterface
{
    /**
     * 监听服务器对象
     * @var null
     */
    private $_port = null;
    /**
     * 主服务器出发 before事件,创建自定自定义服务器
     * @var Server $server
     * */
    public function onBeforeStart(AbstractServer $server)
    {
        $this->create($server->getServer());
    }
    /**
     * 创建监听服务器
     * @param Server $serv
     */
    public function create(Server $serv)
    {
        //获取tcp的配置信息
        $settings = App::getAppProperties()->get('server');
        if (!isset($settings['tcp-tars'])) {
            throw new \InvalidArgumentException('Tcp startup parameter is not configured，settings=' . \json_encode($settings));
        }
        $tcpSettings = $settings['tcp-tars'];
        $this->_port = $serv->listen($tcpSettings['host'], $tcpSettings['port'], $tcpSettings['type']);
        $this->setPortSettings($tcpSettings);
        $this->addPortListeners();
        $this->writeServerInfo($settings);
    }
    /**
     * 显示信息
     * @param $settings
     */
    public function writeServerInfo(array $settings) {
        $tips =  "                         Tars TCP Information                       \n";
        $tips .= "********************************************************************\n";
        $tips .= "* TCP  | host: <note>{$settings['tcp-tars']['host']}</note>, port: <note>{$settings['tcp-tars']['port']}</note>, type: <note>{$settings['tcp-tars']['type']}</note>, worker: <note>{$settings['setting']['worker_num']}</note> (<note>Enabled</note>)\n";
        $tips .= "********************************************************************\n";
        echo \style()->t($tips);
    }
    /**
     * 设置服务器配置参数， 和主服务器一致
     * @param array $tcpSettings
     */
    public function setPortSettings(array $tcpSettings)
    {
        unset($tcpSettings['host'], $tcpSettings['port'], $tcpSettings['type']);
        //PS:问题, 这里一定要触发一下设置swoole方法, 要不然不会触发receive事件, 注意设置参数, 有些设置参数也会导致不触发receive,例如:open_eof_split
        $this->_port->set($tcpSettings);
    }
    /**
     * 添加监听事件
     */
    public function addPortListeners()
    {
        $this->_port->on('connect', array($this, 'onConnect'));
        $this->_port->on('Receive', array($this, 'onReceive'));
        $this->_port->on('Close', array($this, 'onClose'));
    }
    //tcp连接回调
    public function onConnect(Server $serv, $fd) {
        App::info("onConnect-------------");
    }
    //TCP的消息处理逻辑
    public function onReceive($server, $fd, $fromId, $data) {
        $dispatcher = App::getBean('TarsServiceDispatcher');
        $dispatcher->dispatch($server, $fd, $fromId, $data);
    }
    //服务器关闭回调
    public function onClose(Server $serv, $fd) {
        App::info("onClose-------------");
    }
}