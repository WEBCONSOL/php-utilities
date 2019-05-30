<?php

namespace WC\Utilities;

class ClientlibManager
{
    private $pathInfo;
    private $isMinify = false;
    private $root;
    private $content = "";
    private $files = array();
    private $filePath = "";
    private $dirPath = "";
    private $isStyle = false;
    private $isScript = false;
    private $isJSON = false;
    private $lessVars = array();
    private $sassVars = array();
    private $assetDir = '';
    private $patterns = null;
    private $replaces = null;

    public function __construct(string $root, string $q, array $vars=array(), bool $isMinify=false)
    {
        $parts = explode('/', $root);
        $this->assetDir = '/'.$parts[sizeof($parts)-1] . '/' . pathinfo($q, PATHINFO_FILENAME);

        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        $this->lessVars = isset($vars['less']) && is_array($vars['less']) ? $vars['less'] : array();
        $this->sassVars = isset($vars['sass']) && is_array($vars['sass']) ? $vars['sass'] : array();
        $this->root = $root;
        $this->pathInfo = new PathInfo($q);
        $this->isMinify = $isMinify ? $isMinify : $this->pathInfo->isMinify();
        $this->isStyle = $this->pathInfo->getExtension() === FileExtension::CSS;
        $this->isScript = $this->pathInfo->getExtension() === FileExtension::JS;
        $this->isJSON = $this->pathInfo->getExtension() === FileExtension::JSON;

        $file1 = $this->root . DS . $this->pathInfo->getFileFullName();
        $file2 = $this->root . DS . $this->pathInfo->getFile();

        if (file_exists($file1)) {
            $this->filePath = $file1;
            $this->loadStatic();
        }
        else if ($this->pathInfo->isMinify() && file_exists($file2)) {
            $this->filePath = $file2;
            $this->loadStatic();
        }
        else {
            $this->dirPath = $this->root . DS . $this->pathInfo->getPathFullName();
            if (!file_exists($this->dirPath) && $this->pathInfo->isMinify()) {
                $this->dirPath = substr($this->dirPath, 0, -4);
            }
            $this->loadDynamic();
        }
    }

    public static function renderHBSTemplates(string $root, string $q) {
        header('Content-Type: application/javascript; charset=utf-8');
        $pattern = $root.$q.'/*.hbs';
        $list = glob($pattern);
        if (!empty($list)) {
            $buffer = [];
            foreach ($list as $item) {
                $name = pathinfo($item, PATHINFO_FILENAME);
                $buffer[] = 'Ezpz.hbs.set("'.$name.'", phpjs.base64_decode("'.base64_encode(file_get_contents($item)).'"));';
            }
            echo implode('', $buffer);
        }
    }

    public function setPatterns($p) {$this->patterns=$p;}
    public function setReplaces($r) {$this->replaces=$r;}

    public function getContent(): string {return $this->content;}

    public function isStyle(): bool {return $this->isStyle;}
    public function isScript(): bool {return $this->isScript;}
    public function isJSON(): bool {return $this->isJSON;}
    public function getContentTypeHeader(): string {
        if ($this->isStyle()) {return 'text/css; charset=utf-8';}
        else if ($this->isScript()) {return 'application/javascript; charset=utf-8';}
        else if ($this->isJSON()) {return 'application/json; charset=utf-8';}
        else {return "text/html; charset=utf-8";}
    }

    public function setRenderHeaderContentType() {
        if ($this->isStyle) {
            header('Content-Type: text/css; charset=utf-8');
        }
        else if ($this->isScript) {
            header('Content-Type: application/javascript; charset=utf-8');
        }
        else if ($this->isJSON) {
            header('Content-Type: application/json; charset=utf-8');
        }
    }

    public function renderContent()
    {
        $this->setRenderHeaderContentType();
        if ($this->patterns === null && $this->replaces === null) {
            $this->patterns = ['../fonts'];
            $this->replaces = [$this->assetDir.'/fonts'];
        }
        echo str_replace($this->patterns, $this->replaces, $this->content);
    }

    private function loadStatic()
    {
        $isWCAG = strpos($this->filePath, 'accessibility/Standards') !== false;

        if ($this->isStyle)
        {
            if ($this->pathInfo->isMinify()) {
                $this->content = Minify::css(file_get_contents($this->filePath));
            } else {
                $this->content = file_get_contents($this->filePath);
            }
        }
        else if ($this->isScript)
        {
            if ($this->pathInfo->isMinify())
            {
                if ($isWCAG) {
                    $this->content = str_replace(array('_global.', '_global['), array('window.', 'window['), Minify::js(file_get_contents($this->filePath)));
                } else {
                    $this->content = Minify::js(file_get_contents($this->filePath));
                }
            }
            else
            {
                if ($isWCAG) {
                    $this->content = str_replace(array('_global.', '_global['), array('window.', 'window['), file_get_contents($this->filePath));
                } else {
                    $this->content = file_get_contents($this->filePath);
                }
            }
        }
        else if ($this->isJSON)
        {
            $this->content = file_get_contents($this->filePath);
        }
    }

    private function loadDynamic()
    {
        if ($this->isStyle) {
            $style = $this->dirPath . DS . 'css.txt';
            if (file_exists($style)) {
                $styles = explode("\n", file_get_contents($style));
                foreach ($styles as $file) {
                    $file = $this->dirPath . DS . trim($file);
                    if (file_exists($file)) {
                        $this->files[] = $file;
                    }
                }
            }
            else {
                if (file_exists($this->dirPath . DS . 'style')) {
                    $this->fetchFiles($this->dirPath . DS . 'style', '/('.FileExtension::CSS.'|'.FileExtension::LESS.'|'.FileExtension::SASS.')/');
                }
                else {
                    $this->fetchFiles($this->dirPath, '/('.FileExtension::CSS.'|'.FileExtension::LESS.'|'.FileExtension::SASS.')/');
                }
            }
        }
        else if ($this->isScript) {
            $script = $this->dirPath . DS . 'js.txt';
            if (file_exists($script)) {
                $scripts = explode("\n", file_get_contents($script));
                foreach ($scripts as $file) {
                    $file = $this->dirPath . DS . trim($file);
                    if (file_exists($file)) {
                        $this->files[] = $file;
                    }
                }
            }
            else {
                if (file_exists($this->dirPath . DS . 'script')) {
                    $this->fetchFiles($this->dirPath . DS . 'script', '/('.FileExtension::JS.')/');
                }
                else {
                    $this->fetchFiles($this->dirPath, '/('.FileExtension::JS.')/');
                }
            }
        }

        if (!empty($this->files))
        {
            $htmlBuffer = array();
            $lessBuffer = array();
            $sassBuffer = array();
            $pattern = '/@import "(.[^"]*)";/';
            foreach ($this->files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === FileExtension::SASS) {
                    $bfr = file_get_contents($file);
                    $matches = PregUtil::getMatches($pattern, $bfr);
                    if (sizeof($matches)) {
                        $varFile = dirname($file) . '/' . $matches[1][0] . '.less';
                        if (file_exists($varFile)) {
                            $bfr = str_replace('@import "'.$matches[1][0].'";', file_get_contents($varFile), $bfr);
                        }
                        else {
                            die('File: ' . $matches[1][0].' in @import "'.$matches[1][0].'"; does not exist.');
                        }
                    }
                    $sassBuffer[] = $bfr;
                }
                else if (pathinfo($file, PATHINFO_EXTENSION) === FileExtension::LESS) {
                    $bfr = file_get_contents($file);
                    $matches = PregUtil::getMatches($pattern, $bfr);
                    if (sizeof($matches)) {
                        $varFile = dirname($file) . '/' . $matches[1][0] . '.less';
                        if (file_exists($varFile)) {
                            $bfr = str_replace('@import "'.$matches[1][0].'";', file_get_contents($varFile), $bfr);
                        }
                        else {
                            die('File: ' . $matches[1][0].' in @import "'.$matches[1][0].'"; does not exist.');
                        }
                    }
                    $lessBuffer[] = $bfr;
                }
                else {
                    $htmlBuffer[] = file_get_contents($file);
                }
            }
            try {
                if (sizeof($lessBuffer)) {
                    $vars = array();
                    if (sizeof($this->lessVars)) {
                        foreach ($this->lessVars as $lessVar) {
                            if (file_exists($lessVar)) {
                                $vars[] = file_get_contents($lessVar);
                            }
                        }
                    }
                    $less = new \WC\Utilities\Less\Compiler();
                    $htmlBuffer[] = $less->compile(implode('', $vars).implode('', $lessBuffer));
                }
                if (sizeof($sassBuffer)) {
                    if (sizeof($this->sassVars)) {
                        $vars = array();
                        foreach ($this->sassVars as $sassVar) {
                            if (file_exists($sassVar)) {
                                $vars[] = file_get_contents($sassVar);
                            }
                        }
                    }
                    $sass = new \WC\Utilities\Sass\Compiler();
                    $htmlBuffer[] = $sass->compile(implode('', $vars).implode('', $sassBuffer));
                }

                if ($this->isMinify) {
                    if ($this->isStyle) {
                        $this->content = Minify::css(implode('', $htmlBuffer));
                    }
                    else if ($this->isScript) {
                        $this->content = Minify::js(implode('', $htmlBuffer));
                    }
                }
                else {
                    $this->content = implode('', $htmlBuffer);
                }
                unset($htmlBuffer, $lessBuffer, $sassBuffer);
            }
            catch (\Exception $e) {
                $this->content = 'Error.' . "\n" . 'File: ' . $e->getFile() . "\n" . 'Message: ' . $e->getMessage();
            }
        }
    }

    private function fetchFiles(string $dir, string $extRegex) {
        if (is_dir($dir) && file_exists($dir)) {
            $list = glob($dir . '/*');
            foreach ($list as $path) {
                if (is_dir($path)) {
                    $this->fetchFiles($path, $extRegex);
                }
                else if (preg_match($extRegex, $path)) {
                    $this->files[] = $path;
                }
            }
        }
    }
}
