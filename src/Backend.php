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

use NTLAB\JS\Backend as Base;
use NTLAB\JS\DependencyResolverInterface;
use NTLAB\JS\Manager;
use NTLAB\JS\Script;
use NTLAB\JS\Util\Asset;

class Backend extends Base implements DependencyResolverInterface
{
    /**
     * @var array
     */
    protected $js = [
        'first' => [],
        'default' => [],
    ];

    /**
     * @var array
     */
    protected $css = [
        'first' => [],
        'default' => [],
    ];

    /**
     * Constructor.
     *
     * @param boolean $useCDN
     */
    public function __construct($useCDN = false)
    {
        if ($useCDN) {
            $manager = Manager::getInstance();
            $manager->parseCdn(json_decode(file_get_contents($manager->getCdnInfoFile()), true));
            $manager->parseCdn(json_decode(file_get_contents(__DIR__.'/../data/cdn.json'), true));
        }
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Backend::addAsset()
     */
    public function addAsset($asset, $type = self::ASSET_JS, $priority = self::ASSET_PRIORITY_DEFAULT, $attributes = null)
    {
        switch ($type) {
            case static::ASSET_JS:
                if (!in_array($asset, array_merge($this->js['first'], $this->js['default']))) {
                    if ($priority === static::ASSET_PRIORITY_FIRST) {
                        $this->js['first'][] = $asset;
                    } else {
                        $this->js['default'][] = $asset;
                    }
                }
                break;
            case static::ASSET_CSS:
                if (!in_array($asset, array_merge($this->css['first'], $this->css['default']))) {
                    if ($priority === static::ASSET_PRIORITY_FIRST) {
                        $this->css['first'][] = $asset;
                    } else {
                        $this->css['default'][] = $asset;
                    }
                }
                break;
        }
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Backend::generateAsset()
     */
    public function generateAsset(Asset $asset, $name, $type = self::ASSET_JS)
    {
        $dir = '/cdn';
        if ($repo = $asset->getRepository()) {
            $dir .= '/'.$repo;
        }
        if (strlen($assetDir = $asset->getDirName($type))) {
            $dir .= '/'.$assetDir;
        }

        return $dir.'/'.$name;
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\DependencyResolverInterface::resolve()
     */
    public function resolve($dep)
    {
        return sprintf('Demo\\Script\\%s', $dep);
    }

    /**
     * Create script helper.
     *
     * @param string $scriptName
     * @return \NTLAB\JS\Script
     */
    public function createScript($scriptName)
    {
        return Script::create($scriptName);
    }

    public function useStylesheet($stylesheet)
    {
        $this->addAsset($stylesheet, static::ASSET_CSS);
    }

    public function useJavascript($javascript)
    {
        $this->addAsset($javascript, static::ASSET_JS);
    }

    public function includeStylesheets()
    {
        $css = [];
        foreach (array_merge($this->css['first'], $this->css['default']) as $stylesheet) {
            $css[] = sprintf('<link rel="stylesheet" href="%s">', $stylesheet);
        }

        return implode("\n", $css);
    }

    public function includeJavascripts()
    {
        $js = [];
        foreach (array_merge($this->js['first'], $this->js['default']) as $javascript) {
            $js[] = sprintf('<script type="text/javascript" src="%s"></script>', $javascript);
        }

        return implode("\n", $js);
    }
}