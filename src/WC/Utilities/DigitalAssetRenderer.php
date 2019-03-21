<?php

namespace WC\Utilities;

class DigitalAssetRenderer
{
    private $debug = false;
    private $root = '';

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function render()
    {
        $extConfigFile = __DIR__.'/data/mimetypes.json';
        $exts = file_exists($extConfigFile) ? json_decode(file_get_contents($extConfigFile), true) : [];
        $requestFile = ltrim($_SERVER['REQUEST_URI'], '/');
        $file = StringUtil::removeDoubleSlashes($this->root.'/'.$requestFile);
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if(!empty($exts) && file_exists($file) && isset($exts[$ext])) {
            if ($this->canAccess($_SERVER['REQUEST_URI'])) {
                $adapter = new \League\Flysystem\Adapter\Local($this->root);
                $fileSystem = new \League\Flysystem\Filesystem($adapter);
                $mimeType = $exts[$ext];
                try {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: '.$mimeType);
                    header('Content-disposition: attachment; filename="'.md5($requestFile).'.'.$ext.'"');
                    header('Content-Length: ' . $fileSystem->getSize($requestFile));
                    echo $fileSystem->read($requestFile);
                }
                catch (\League\Flysystem\FileNotFoundException $e) {
                    Logger::error('digital_asset_renderer. '.$e->getMessage());
                    header('Location: /error-404.html');
                }
            }
            else {
                header('HTTP/1.1 403 Forbidden');
                die('You are not allwed to access: '.$_SERVER['REQUEST_URI']);
            }
        }
        else if (!$this->debug) {
            header('Location: /error-404.html');
        }
    }

    protected function canAccess(string $path): bool {return true;}

    public function setDebug(bool $b){$this->debug=$b;}
}