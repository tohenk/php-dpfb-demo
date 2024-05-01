<?php

/*
 * The MIT License
 *
 * Copyright (c) 2021-2024 Toha <tohenk@yahoo.com>
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

use NTLAB\JS\Manager;
use NTLAB\JS\BackendInterface;

/**
 * @return \Demo\Backend
 */
function _get_backend()
{
    return Manager::getInstance()->getBackend();
}

function create_script($scriptName)
{
    return _get_backend()->createScript($scriptName);
}

function use_stylesheet($stylesheet)
{
    _get_backend()->addAsset($stylesheet, BackendInterface::ASSET_CSS);
}

function use_javascript($javascript)
{
    _get_backend()->addAsset($javascript, BackendInterface::ASSET_JS);
}

function include_stylesheets()
{
    if ($stylesheets = _get_backend()->includeStylesheets()) {
        return $stylesheets;
    }
}

function include_javascripts()
{
    if ($javascripts = _get_backend()->includeJavascripts()) {
        return $javascripts;
    }
}

function include_script()
{
    return Manager::getInstance()->getScript(true);
}

function __($message)
{
    return $message;
}
