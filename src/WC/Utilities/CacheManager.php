<?php

namespace WC\Utilities;

use WC\Models\ListModel;

class CacheManager
{
    private $root = '';
    private $host = '';
    private $filePath = '';
    private $content = '';
    private $hasFile = false;
    private $cacheIgnoreConfig = null;
    private $reqPath = '';

    public function __construct($root, $host=null, $path=null)
    {
        $this->createDir($root);
        $pathInfo = new PathInfo();
        $ext = $pathInfo->getExtension();
        $this->host = $host !== null ? $host : $pathInfo->getHost();
        if ($path === null) {$path=$pathInfo->getPath();}
        $this->reqPath = (!$path||$path==='/'?'home':$path).($ext?'.'.$ext:'');
        $this->root = $root . DS . $this->host;
        $this->filePath = $this->root.'/'.$this->reqPath;
        $this->hasFile = file_exists($this->filePath);
        $this->loadContent();
    }

    public function hasCache(): bool {return $this->hasFile;}

    public function getCacheContent(): string {return $this->content;}

    public function save(string $cacheIgnoreConfigPath, string $buffer) {
        $this->loadCacheIgnoreConfig($cacheIgnoreConfigPath);
        if ($this->cacheable($this->reqPath)) {
            $this->write($this->filePath, $buffer);
        }
    }

    private function loadCacheIgnoreConfig(string $cacheIgnoreConfigPath) {
        if (file_exists($cacheIgnoreConfigPath)) {
            $str = file_get_contents($cacheIgnoreConfigPath);
            if ($str && EncodingUtil::isValidJSON($str)) {
                $this->cacheIgnoreConfig = json_decode($str, true);
            }
        }
    }

    private function hasConfig() {return $this->cacheIgnoreConfig !== null && (isset($this->cacheIgnoreConfig['global']) || isset($this->cacheIgnoreConfig[$this->host]));}

    private function cacheable(string $path): bool {
        if (!$this->hasConfig()) {return true;}
        $global = isset($this->cacheIgnoreConfig['global']) ? $this->cacheIgnoreConfig['global'] : array();
        if (sizeof($global)) {
            foreach ($global as $item) {
                if ($item === $path) {return false;}
                else if (StringUtil::isRegExp($item)) {
                    $item = StringUtil::toRegex($item);
                    $match = PregUtil::getMatches($item, $path);
                    if (sizeof($match)) {return false;}
                }
            }
        }
        $data = isset($this->cacheIgnoreConfig[$this->host]) ? $this->cacheIgnoreConfig[$this->host] : array();
        if (sizeof($data)) {
            foreach ($data as $item) {
                if ($item === $path) {return false;}
                else if (StringUtil::isRegExp($item)) {
                    $match = PregUtil::getMatches($item, $path);
                    if (sizeof($match)) {return false;}
                }
            }
        }
        return true;
    }

    private function loadContent() {if ($this->hasFile) {$this->content = file_get_contents($this->filePath);}}

    private function write($file, &$buffer)
    {
        @set_time_limit(ini_get('max_execution_time'));

        // If the destination directory doesn't exist we need to create it
        if (!file_exists(dirname($file)))
        {
            $this->createDir(dirname($file));
        }

        $file = $this->clean($file);

        if (file_exists($file)) {
            $fh = fopen($file, 'a');
        } else {
            $fh = fopen($file, 'w');
        }
        if (is_resource($fh)) {
            fwrite($fh, $buffer);
            fclose($fh);
        }
    }

    private function createDir($path, $mode = 0744) {

        static $nested = 0;

        // Check to make sure the path valid and clean
        $path = $this->clean($path);

        // Check if parent dir exists
        $parent = dirname($path);

        if (!is_dir($this->clean($parent)))
        {
            // Prevent infinite loops!
            $nested++;

            if (($nested > 20) || ($parent == $path))
            {
                throw new Exception(__METHOD__ . ': Infinite loop detected');
            }

            try
            {
                // Create the parent directory
                if ($this->create($parent, $mode) !== true)
                {
                    // Folder::create throws an error
                    $nested--;

                    return false;
                }
            }
            catch (\Exception $exception)
            {
                $nested--;

                throw $exception;
            }

            // OK, parent directory has been created
            $nested--;
        }

        // Check if dir already exists
        if (is_dir($this->clean($path)))
        {
            return true;
        }

        // We need to get and explode the open_basedir paths
        $obd = ini_get('open_basedir');

        // If open_basedir is set we need to get the open_basedir that the path is in
        if ($obd != null)
        {
            if (\defined('PHP_WINDOWS_VERSION_MAJOR'))
            {
                $obdSeparator = ";";
            }
            else
            {
                $obdSeparator = ":";
            }

            // Create the array of open_basedir paths
            $obdArray = explode($obdSeparator, $obd);
            $inBaseDir = false;

            // Iterate through open_basedir paths looking for a match
            foreach ($obdArray as $test)
            {
                $test = $this->clean($test);

                if (strpos($path, $test) === 0 || strpos($path, realpath($test)) === 0)
                {
                    $inBaseDir = true;
                    break;
                }
            }

            if ($inBaseDir == false)
            {
                // Throw a FilesystemException because the path to be created is not in open_basedir
                throw new \Exception(__METHOD__ . ': Path not in open_basedir paths');
            }
        }

        // First set umask
        $origmask = @umask(0);

        // Create the path
        if (!$ret = @mkdir($path, $mode))
        {
            @umask($origmask);

            throw new \Exception(__METHOD__ . ': Could not create directory.  Path: ' . $path);
        }

        // Reset umask
        @umask($origmask);
    }

    private function clean($path, $ds = DIRECTORY_SEPARATOR)
    {
        if (!\is_string($path))
        {
            throw new \UnexpectedValueException('J$this->clear $path is not a string.');
        }

        $stream = explode("://", $path, 2);
        $scheme = '';
        $path = $stream[0];

        if (\count($stream) >= 2)
        {
            $scheme = $stream[0] . '://';
            $path = $stream[1];
        }

        $path = trim($path);

        if (empty($path))
        {
            $path = JPATH_ROOT;
        }
        elseif (($ds == '\\') && ($path[0] == '\\' ) && ( $path[1] == '\\' ))
            // Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
            // If dealing with a UNC path don't forget to prepend the path with a backslash.
        {
            $path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
        }
        else
        {
            $path = preg_replace('#[/\\\\]+#', $ds, $path);
        }

        return $scheme . $path;
    }
}