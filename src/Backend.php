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

namespace Demo;

use NTLAB\JS\Backend as Base;
use NTLAB\JS\CompressorInterface;
use NTLAB\JS\ConsumerInterface;
use NTLAB\JS\DependencyResolverInterface;
use NTLAB\JS\Manager;
use NTLAB\JS\Script;
use NTLAB\JS\Util\Asset;
use NTLAB\JS\Util\JSValue;
use JSMin\JSMin;

class Backend extends Base implements CompressorInterface, ConsumerInterface, DependencyResolverInterface
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
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = (array) $options;
        if (isset($this->options['cdn']) && is_readable($cdn = $this->options['cdn'])) {
            $manager = Manager::getInstance();
            $manager->parseCdn(json_decode(file_get_contents($cdn), true));
        }
    }

    /**
     * Is script embedded as response?
     *
     * @return boolean
     */
    public function isScriptEmbedded()
    {
        return isset($this->options['embed_script']) ? (bool)$this->options['embed_script'] : true;
    }

    /**
     * Get script cache dir.
     *
     * @return string
     */
    public function getScriptCacheDir()
    {
        return $this->options['root_dir'].'/var/script';
    }

    /**
     * Cache script content and return its id.
     *
     * @param string $content
     * @return string
     */
    public function cacheScript($content)
    {
        if ($content) {
            $id = sha1($content);
            if (!is_dir($cacheDir = $this->getScriptCacheDir())) {
                mkdir($cacheDir, '0777', true);
            }
            $filename = $cacheDir.DIRECTORY_SEPARATOR.$id.'.js';
            if (!is_file($filename)) {
                file_put_contents($filename, $content);
            }
            return $id;
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
     * Create script helper.
     *
     * @param string $scriptName
     * @return \NTLAB\JS\Script
     */
    public function createScript($scriptName)
    {
        return Script::create($scriptName);
    }

    /**
     * Use stylesheet asset.
     *
     * @param string $stylesheet
     */
    public function useStylesheet($stylesheet)
    {
        $this->addAsset($stylesheet, static::ASSET_CSS);
    }

    /**
     * Use javascript asset.
     *
     * @param string $stylesheet
     */
    public function useJavascript($javascript)
    {
        $this->addAsset($javascript, static::ASSET_JS);
    }

    /**
     * Get link to stylesheet element.
     *
     * @param string $href
     * @param string $rel
     * @return string
     */
    public function stylesheetTag($href, $rel = 'stylesheet')
    {
        return "<link rel=\"{$rel}\" href=\"{$href}\">";
    }

    /**
     * Generate stylesheet include elements.
     *
     * @return string
     */
    public function includeStylesheets()
    {
        $css = [];
        foreach (array_merge($this->css['first'], $this->css['default']) as $stylesheet) {
            $css[] = $this->stylesheetTag($stylesheet);
        }

        return implode("\n", $css);
    }

    /**
     * Get script element.
     *
     * @param string $src
     * @param string $type
     * @return string
     */
    public function javascriptTag($src, $type = 'text/javascript')
    {
        return "<script type=\"{$type}\" src=\"{$src}\"></script>";
    }

    /**
     * Generate script include elements.
     *
     * @return string
     */
    public function includeJavascripts()
    {
        $js = [];
        foreach (array_merge($this->js['first'], $this->js['default']) as $javascript) {
            $js[] = $this->javascriptTag($javascript);
        }

        return implode("\n", $js);
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Backend::getDefaultRepository()
     */
    public function getDefaultRepository()
    {
        return 'jquery';
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\CompressorInterface::compress()
     */
    public function compress($content)
    {
        return JSMin::minify($content);
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
     * {@inheritDoc}
     * @see \NTLAB\JS\ConsumerInterface::consume()
     */
    public function consume($vars)
    {
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\ConsumerInterface::use()
     */
    public function use($name, $value)
    {
        return JSValue::createRaw(sprintf('window.VARS.%s', $name));
    }
}