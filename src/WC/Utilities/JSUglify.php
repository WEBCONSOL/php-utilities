<?php

namespace WC\Utilities;

class JSUglify
{
    /** @var string Path to the uglifyjs script */
    private $uglifyBinaryPath = 'uglifyjs';
    /** @var array List of options allowable to be used for the program */
    private static $options = [
        'source-map' => 'string',
        'source-map-root' => 'string',
        'source-map-url' => 'string',
        'source-map-include-sources' => 'bool',
        'in-source-map' => 'string',
        'screw-ie8' => 'bool',
        'expr' => 'string',
        'prefix' => 'string',
        'beautify' => 'string',
        'mangle' => 'string',
        'reserved' => 'string',
        'compress' => 'string',
        'define' => 'string',
        'encode' => 'string',
        'comments' => 'string',
        'preamble' => 'string',
        'stats' => 'bool',
        'acorn' => 'bool',
        'spidermonkey' => 'bool',
        'self' => 'bool',
        'wrap' => 'string',
        'export-all' => 'bool',
        'lint' => 'bool',
        'verbose' => 'bool',
        'version' => 'bool',
        'noerr' => 'bool',
        'bare-returns' => 'bool',
        'keep-fnames' => 'bool',
        'reserved-file' => 'array',
        'reserve-domprops' => 'bool',
        'mangle-props' => 'bool',
        'mangle-regex' => 'bool',
        'name-cache' => 'string',
        'pure-funcs' => 'array',
        'dump-spidermonkey-ast' => 'bool',
        'quotes' => 'string'
    ];

    /**
     * This allows you to set the binary path to the uglify executable. This is helpful on systems where
     * it is globally registered and may not be on your PATH
     * @param string $uglifyBinaryPath Either the name of the binary of path to the binary and name of it
     */
    public function setUglifyBinaryPath($uglifyBinaryPath) {
        $this->uglifyBinaryPath = $uglifyBinaryPath;
    }

    /**
     * @return string Location of the uglifyjs script
     */
    public function getUglifyBinaryPath() {
        return $this->uglifyBinaryPath;
    }

    /**
     * Internal function used to validate that the uglifyJS script exists and works (to some degree)
     * @return bool
     */
    public function checkUglifyJsExists() {
        $command = $this->uglifyBinaryPath . " -V";
        exec($command, $outputText, $returnCode);
        return ($returnCode == 0);
    }

    /**
     * Calls the uglifyjs script and minifies the Javascript
     * @param array $files List of filenames to minimise
     * @param string $outputFilename Filename to output the javascript to
     * @param array $options Options to pass to the script
     * @param string|null $finalJsHeaderFilename Path to file of header to place at the top of the JS file
     * @return string Full output of the executable
     * @throws UglifyJSException Thrown when something goes wrong with running the script
     */
    public function uglify(array $files, $outputFilename, array $options = [], $finalJsHeaderFilename=null) {
        foreach($files as $filename) {
            if(!is_readable($filename)) {
                throw new UglifyJSException("Filename " . $filename . " is not readable");
            }
        }
        $optionsString = $this->validateOptions($options);
        $fileNames = implode(' ', array_map('escapeshellarg', $files));

        $tmpUglifyJsOutput = tempnam(sys_get_temp_dir(), "uglify_js_intermediate_out_");
        $safeShellTmpUglifyJsFilename = escapeshellarg($tmpUglifyJsOutput);

        $commandString = $this->uglifyBinaryPath . " {$fileNames} --output {$safeShellTmpUglifyJsFilename} {$optionsString}";

        exec($commandString, $output, $returnCode);
        if($returnCode !== 0) {
            throw new UglifyJSException("Failed to run uglifyjs, something went wrong... command: " . $commandString);
        }

        if($finalJsHeaderFilename) {
            //If we have provided a header filename then we are going to get the uglified file then prepend the data
            $context = stream_context_create();
            //Open both files in stream mode so we dont load the entire file into memory, streams are the best!
            $uglifyJsOutputFileHandler = fopen($tmpUglifyJsOutput, 'r', false, $context);
            $jsHeaderFileHandler = fopen($finalJsHeaderFilename, 'r',false, $context);

            $tmpFinalOutput = tempnam(sys_get_temp_dir(), 'php_uglify_js_out_');
            file_put_contents($tmpFinalOutput, $jsHeaderFileHandler);
            file_put_contents($tmpFinalOutput, $uglifyJsOutputFileHandler, FILE_APPEND);

            //Close unlink and move the files we dont need
            fclose($uglifyJsOutputFileHandler);
            fclose($jsHeaderFileHandler);
            unlink($tmpUglifyJsOutput);
            rename($tmpFinalOutput, $outputFilename);
        }else{
            //Dont try and add any files, just move the temporary file into the final location
            rename($tmpUglifyJsOutput, $outputFilename);
        }

        return $output;
    }

    /**
     * Internal functions to validate and produce the options string
     * @param array $options Array of options to pass to the script
     * @return string Full options string to be used for the script
     * @throws UglifyJSException Thrown when something is passed in as an option that isnt valid
     */
    private function validateOptions($options) {
        $optionsString = '';
        foreach($options as $option => $value) {
            if(!array_key_exists($option, self::$options)) {
                throw new UglifyJSException('Option not supported');
            }

            $optionType = self::$options[$option];
            if($optionType === 'bool') {
                $optionValue = ($value ? 'true' : 'false');
                $optionsString .= "--{$option}={$optionValue} ";
            }elseif($optionType === 'string') {
                if($value === '') {
                    $optionsString .= "--{$option} ";
                }else{
                    $optionValue = escapeshellarg($value);
                    $optionsString .= "--{$option}={$optionValue} ";
                }

            }elseif($optionType === 'array') {
                throw new UglifyJSException('Array type not supported yet');
            }else{
                throw new UglifyJSException('Option type ' . $option . ' not supported');
            }
        }
        return $optionsString;
    }

}