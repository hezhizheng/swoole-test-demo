<?php

/**
 * Description:
 * Author: DexterHo <dexter.ho.cn@gmail.com>
 * Date: 2018/11/11
 * Time: 1:19
 * Created by PhpStorm.
 */
class Ws
{
    private $server;

    public function __construct()
    {
        $this->server = new swoole_websocket_server("0.0.0.0", 9501);
    }

    public function run()
    {
        $this->server->on('open', [$this, 'open']);

        $this->server->on('message', [$this, 'message']);

        $this->server->on('close', [$this, 'close']);

        $this->server->start();
    }

    public function open(swoole_websocket_server $server, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function message(swoole_websocket_server $server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

        $count = count($this->getAllConnectFd());

        foreach ($this->getAllConnectFd() as $fd) {
            $this->server->push($fd, $count);
        }
    }

    public function close($ser, $fd)
    {
        echo "client {$fd} closed\n";

        $all_fds = $this->getAllConnectFd();
        foreach ($all_fds as $k => $_fd) {
            if ($fd == $_fd) unset($all_fds[$k]);
        }

        $count = count($all_fds);

        foreach ($all_fds as $fd) {
            $this->server->push($fd, $count);
        }
    }

    /**
     * 获取所有websocket连接用户的fd
     * @return array
     */
    private function getAllConnectFd()
    {
        $fds = [];
        foreach ($this->server->connections as $fd) {
            $fds[] = $fd;
        }
        return $fds;
    }
}


$ws = new Ws();

$ws->run();