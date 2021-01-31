<?php

/*
 * The MIT License
 *
 * Copyright (c) 2021 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Demo;

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version3X;

class RPC
{
    protected $url = null;
    protected $socket = null;
    protected $connected = null;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Get web socket url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set socket url.
     *
     * @param string $url  Websocket url
     * @return RPC
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->connected = null;
        return $this;
    }

    /**
     * Is RPC connected.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Get socket instance.
     *
     * @return Client
     */
    public function getSocket()
    {
        if (null === $this->socket) {
            try {
                $url = $this->getUrl();
                $socket = new Client(new Version3X($url), new RPCLogger());
                $socket->initialize();
                $socket->of('/');
                $this->socket = $socket;
            }
            catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
        return $this->socket;
    }

    public function emit($event, $data = [])
    {
        if ($socket = $this->getSocket()) {
            $socket->emit($event, $data);
            return true;
        }
    }

    /**
     * Query for an event.
     *
     * @param string $event
     * @param array $data
     * @return mixed
     */
    public function query($event, $data = [])
    {
        $result = null;
        if ($socket = $this->getSocket()) {
            $socket->emit($event, $data);
            if ($packet = $socket->wait($event)) {
                $result = $packet->data;
            }
        }
        return $result;
    }

    public function bootstrap()
    {
        if ($retval = $this->query('self-test')) {
            list($server, ) = explode('-', $retval);
            if ('DPFPBRIDGE' === $server) {
                $this->connected = true;
                return true;
            }
        }
    }
}