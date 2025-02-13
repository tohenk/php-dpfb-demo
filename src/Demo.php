<?php

/*
 * The MIT License
 *
 * Copyright (c) 2021-2025 Toha <tohenk@yahoo.com>
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

use NTLAB\JS\Manager;
use NTLAB\JS\Script;

class Demo
{
    /**
     * Options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Enable/disable script minify.
     *
     * @var boolean
     */
    protected $minifyScript = false;

    /**
     * Enable/disable script debug information.
     *
     * @var boolean
     */
    protected $debugScript = true;

    /**
     * Enable/disable script embedding in response.
     *
     * When script embedding set to `false`, use an external web server
     * is a must.
     *
     * @var boolean
     */
    protected $embedScript = true;

    /**
     * Finger print server url.
     *
     * @var string
     */
    protected $fpserverUrl = 'http://localhost:9000';

    /**
     * RPC instance.
     *
     * @var \Demo\RPC
     */
    protected $rpc = null;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = (array) $options;
        $this->initialize();
    }

    /**
     * Initialize.
     */
    protected function initialize()
    {
        $manager = Manager::getInstance();
        // create backend instance
        $backend = new Backend(array_merge($this->options, ['embed_script' => $this->embedScript]));
        // set script backend
        $manager->setBackend($backend);
        // register script resolver
        $manager->addResolver($backend);
        // register script compressor
        if ($this->minifyScript) {
            $manager->setCompressor($backend);
        }
        // set script debug information
        if ($this->debugScript) {
            Script::setDebug(true);
        }
        // register script consumer
        if (!$this->embedScript) {
            $manager->setConsumer($backend);
        }
    }

    /**
     * Get the view content.
     *
     * @param string $viewName
     * @param array $vars
     * @return string
     */
    protected function useView($viewName, $vars = [])
    {
        include_once 'Helper.php';
        extract($vars);
        ob_start();
        include(__DIR__.'/../view/'.$viewName);
        $content = ob_get_clean();

        return $content;
    }

    protected function getRPC()
    {
        if (null === $this->rpc) {
            $this->rpc = new RPC($this->fpserverUrl);
            $this->rpc->bootstrap();
        }
        return $this->rpc;
    }

    protected function rpcSend($command, $data)
    {
        try {
            $rpc = $this->getRPC();
            if ($rpc->isConnected()) {
                return $rpc->emit($command, $data);
            }
        }
        catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    protected function rpcQuery($command, $data)
    {
        try {
            $rpc = $this->getRPC();
            if ($rpc->isConnected()) {
                return $rpc->query($command, $data);
            }
        }
        catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    protected function genId()
    {
        return substr(sha1(uniqid(mt_rand(), true)), 0, 8);
    }

    protected function getRoute()
    {
        $route = isset($_SERVER['SCRIPT_URL']) ? $_SERVER['SCRIPT_URL'] : $_SERVER['REQUEST_URI'];
        if ($scriptName = $_SERVER['SCRIPT_NAME']) {
            if (0 === strpos($route, $scriptName)) {
                $route = substr($route, strlen($scriptName));
            }
        }
        if (false !== ($p = strpos($route, '?'))) {
            $route = substr($route, 0, $p);
        }
        return $route;
    }

    protected function executeIndex($parameters = [])
    {
        $content = $this->useView('demo.php', ['uri' => $_SERVER['REQUEST_URI']]);
        $content = $this->useView('layout.php', ['content' => $content, 'title' => 'DPFB Demo']);

        echo $content;
    }

    protected function executeAjax($parameters = [])
    {
        $result['success'] = false;
        $cmd = $parameters['cmd'];
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    break;
                case 'POST':
                    // clear all registered templates
                    if ('fp-clear' === $cmd) {
                        if ($this->rpcSend('clear-template', [])) {
                            $result['success'] = true;
                        }
                    }
                    // Register finger
                    if ('fp-save' === $cmd) {
                        if ($data = file_get_contents('php://input')) {
                            $tmpfile = tempnam(sys_get_temp_dir(), 'fpz');
                            file_put_contents($tmpfile, $data);
                            $zip = new \ZipArchive();
                            if (true === $zip->open($tmpfile) && false !== ($tmpl = $zip->getStream('TMPL'))) {
                                $data = ['id' => $this->genId(), 'template' => utf8_encode(stream_get_contents($tmpl)), 'force' => true];
                                $retval = $this->rpcQuery('reg-template', $data);
                                if (isset($retval['success']) && $retval['success']) {
                                    $result['success'] = $retval['success'];
                                    $result['id'] = $retval['id'];
                                }
                            }
                            @unlink($tmpfile);
                        }
                    }
                    // Unregister finger
                    if ('fp-del' === $cmd && isset($_POST['id']) && is_array($_POST['id'])) {
                        $ids = $_POST['id'];
                        $deleted = [];
                        foreach ($ids as $id) {
                            $retval = $this->rpcQuery('unreg-template', ['id' => $id]);
                            if (isset($retval['success']) && $retval['success']) {
                                $deleted[] = $retval['id'];
                            }
                        }
                        $result['success'] = true;
                        $result['deleted'] = $deleted;
                    }
                    // Identify finger
                    if ('fp-verify' === $cmd) {
                        if ($data = file_get_contents('php://input')) {
                            $retval = $this->rpcQuery('identify', ['feature' => utf8_encode($data)]);
                            if (isset($retval['data']) && isset($retval['data']['matched'])) {
                                $result['id'] = $retval['data']['matched'];
                            }
                            $result['success'] = $this->getRPC()->isConnected();
                        }
                    }
                    break;
            }
        }
        catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Run the demo.
     */
    public function run()
    {
        $parameters = [];
        $route = $this->getRoute();
        if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $parameters);
        }
        $matches = null;
        if (preg_match('#^/a/(?<cmd>[a-zA-Z0-9_\-]+)$#', $route, $matches)) {
            $this->executeAjax(array_merge($parameters, ['cmd' => $matches['cmd']]));
        } else {
            $this->executeIndex($parameters);
        }
    }
}