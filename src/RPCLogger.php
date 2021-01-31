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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RPCLogger implements LoggerInterface
{
    protected $level = [
        LogLevel::DEBUG => 'debug', 
        LogLevel::CRITICAL => 'critical',
        LogLevel::ALERT => 'alert',
        LogLevel::EMERGENCY => 'emergency',
        LogLevel::WARNING => 'warning',
        LogLevel::ERROR => 'error',
        LogLevel::NOTICE => 'notice',
        LogLevel::INFO => 'info',
    ];

    public function log($level, $message, $context = [])
    {
        $message = is_string($message) ? $message : var_export($message, true);
        $message = sprintf('%s> %s', $this->level[$level], $message);
        if (!empty($context)) {
            $message = sprintf('%s [%s]', $message, json_encode($context));
        }
        error_log($message);
    }

    public function debug($message, $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function critical($message, $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function alert($message, $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function emergency($message, $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function warning($message, $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function error($message, $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function notice($message, $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
}
