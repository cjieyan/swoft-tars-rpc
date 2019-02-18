<?php

namespace Swoft\TarsRpc\Server\Bootstrap;

use Swoft\Bean\Annotation\BootBean;
use Swoft\Core\BootBeanInterface;
use Swoft\TarsRpc\Server\Router\HandlerMapping;
use Swoft\TarsRpc\Server\ServiceDispatcher;

/**
 * The core bean of service
 *
 * @BootBean()
 */
class CoreBean implements BootBeanInterface
{
    /**
     * @return array
     */
    public function beans()
    {
        return [
            'TarsServiceDispatcher' => [
                'class' => ServiceDispatcher::class,
            ],
            'serviceRouter'     => [
                'class' => HandlerMapping::class,
            ],
        ];
    }
}