<?php

namespace WC\Utilities;

class Request
{
    private static $data = null;
    private $schema_http = "http://";
    private $schema_https = "https://";
    private $allowedContentType = array('application/json','application/json; charset=utf-8','application/x-www-form-urlencoded','application/x-www-form-urlencoded; charset=utf-8','multipart/form-data-encoded','multipart/form-data');
    private $allowedMethods = array('GET','PUT','DELETE','POST');

    public function __construct(array $config=array())
    {
        if (self::$data === null)
        {
            $this->cloudflareSSL();
            self::$data['header'] = array();
            self::$data['headerParamKeys'] = array();
            self::$data['params'] = array();
            self::$data['postData'] = array();
            self::$data['deleteData'] = array();
            self::$data['isHttps'] = (int)$_SERVER['SERVER_PORT']===443||(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])&&$_SERVER['HTTP_X_FORWARDED_PROTO']==="https")||isset($_SERVER['HTTP_X_FORWARDED_SSL'])||isset($_SERVER['HTTPS'])?true:false;
            self::$data['host'] = $_SERVER["HTTP_HOST"];
            self::$data['method'] = strtoupper($_SERVER["REQUEST_METHOD"]);
            self::$data['isAjax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' : false;;
            self::$data['referer'] = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "";
            self::$data['pathInfo'] = new PathInfo($_SERVER["REQUEST_URI"]);
            $this->loadGlobals();
        }
    }

    private function cloudflareSSL() {
        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            if (preg_match('/https/i', $_SERVER['HTTP_CF_VISITOR'])) {
                $_SERVER['HTTPS'] = 'On';
                $_SERVER['HTTP_X_FORWARDED_PORT'] = 443;
                $_SERVER['SERVER_PORT'] = 443;
            }
        }
    }

    protected function getParam(string $key) {$v = $this->getRequestParam($key);if (!$v) {$v = $this->getHeaderParam($key);}return $v;}

    protected function getParamKeys(): array {return array_keys(self::$data['params']);}

    protected final function add(string $key, $value) {if (!isset(self::$data[$key])) {self::$data[$key] = $value;}}

    protected final function get(string $key) {return isset(self::$data[$key]) ? self::$data[$key] : null;}

    public static function loadInstance() {new Request();}

    public function hasFiles(): bool {return isset($_FILES) && is_array($_FILES) && sizeof($_FILES) > 0;}

    public function getFiles(): array {return $this->hasFiles() ? end($_FILES) : [];}

    private function loadGlobals() {

        $this->validateSubmitSize();

        // header data
        self::$data['header'] = getallheaders();
        $obj = $this->getHeaderParam('Access-Control-Request-Headers');
        if (!empty($obj)) {
            self::$data['headerParamKeys'] = is_array($obj) || is_object($obj) ? json_encode($obj) : $obj;
        }
        else {
            self::$data['headerParamKeys'] = implode(',', array_keys(self::$data['header']));
        }

        // request data
        self::$data['postData'] = is_array($_POST) ? $_POST : array();

        if ($this->method() === "POST") {

            if (sizeof(self::$data['postData']) === 0) {
                $requestBody = file_get_contents("php://input");
                if ($requestBody) {
                    if (EncodingUtil::isValidJSON($requestBody)) {
                        self::$data['postData'] = json_decode($requestBody, true);
                    }
                    else {
                        parse_str($requestBody, self::$data['postData']);
                    }

                    $arrKeys = array_keys(self::$data['postData']);

                    if (strpos($arrKeys[0], '------') !== false && strpos($arrKeys[0], 'Content-Disposition:_form-data;_name') !== false) {
                        self::$data['postData'] = array();
                        $this->parseRawHttpRequest(self::$data['postData']);
                    }
                }
            }
        }
        else if ($this->method() === "DELETE") {

            if (sizeof(self::$data['deleteData']) === 0) {
                $requestBody = file_get_contents("php://input");
                if ($requestBody) {
                    if (EncodingUtil::isValidJSON($requestBody)) {
                        self::$data['deleteData'] = json_decode($requestBody, true);
                    }
                    else {
                        parse_str($requestBody, self::$data['deleteData']);
                    }
                }
            }
        }

        if (is_array($_GET)) {
            self::$data['params'] = array_merge(self::$data['params'], $_GET);
        }
        self::$data['params'] = array_merge(self::$data['params'], self::$data['postData'], self::$data['deleteData']);
    }

    public function isAllowedContentType(): bool {return in_array($this->getHeaderParam('Content-Type'), $this->allowedContentType);}

    public function isAllowedMethod(): bool {return in_array($this->method(), $this->allowedMethods);}

    public function isHttps(): bool {return self::$data['isHttps'];}

    public function protocol() {return $this->isHttps() ? $this->schema_https : $this->schema_http;}

    public function getUrl(): string {return $this->protocol() . $this->host() . '/' . ltrim($this->uri(), '/');}

    public function host(): string {return self::$data['host'];}

    public function originHost(): string {return $this->getHeaderParam('Origin');}

    public function getHeaderKeysAsString() {return self::$data['headerParamKeys'];}

    public function queryString() { return rawurldecode($_SERVER["QUERY_STRING"]); }

    public function method() {return self::$data['method'];}

    public function getPostData(): array {return self::$data['postData'];}

    public function getRequestParamsAsArray():array {return self::$data['params'];}

    public function getHeaderParamsAsArray():array {return self::$data['header'];}

    public function setRequestParam($param, $value) { self::$data['params'][$param] = $value; }

    public function removeRequestParam($param) { if ($this->hasRequestParam($param)) {unset(self::$data['params'][$param]);} }

    public function hasRequestParam($key): bool { return isset(self::$data['params'])&&isset(self::$data['params'][$key]); }

    public function getRequestParam($param, $default = null) {if ($this->hasRequestParam($param)) {$val = self::$data['params'][$param];return is_string($val) && strlen($val) ? rawurldecode($val) : $val;}return $default;}

    public function hasHeaderParam($key): bool {  return isset(self::$data['header'])&&isset(self::$data['header'][$key]); }

    public function getHeaderParam($param, $default = ''): string {
        if ($this->hasHeaderParam($param)) {
            $val = self::$data['header'][$param];
            return is_string($val) ? rawurldecode($val) : json_encode($val);
        }
        return $default;
    }

    public function hasHeaderAuthorization(): bool {return sizeof($this->getCredentials());}

    public function getCredentials(): array {
        $authorization = $this->getHeaderParam('Authorization');
        if ($authorization) {
            $authorization = str_replace(array('Basic ','Digest '), '', $authorization);
            if (EncodingUtil::isBase64Encoded($authorization)) {
                $authorization = explode(':', base64_decode($authorization));
                if (sizeof($authorization) === 2) {
                    return array('username' => $authorization[0], 'password' => $authorization[1]);
                }
            }
        }
        $username = $this->getRequestParam('username');
        $password = $this->getRequestParam('password');
        if ($username && $password) {
            return array('username' => $username, 'password' => $password);
        }
        return array();
    }

    public function uri() {return self::pathInfo()->getUri();}

    public function getReferrer(): string {return self::$data['referer'];}

    public function parseRawHttpRequest(array &$a_data)
    {
        // read incoming data
        $input = file_get_contents('php://input');

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        if (isset($matches[1])) {
            $boundary = $matches[1];

            // split content by boundary and get rid of last -- element
            $a_blocks = preg_split("/-+$boundary/", $input);
            array_pop($a_blocks);

            // loop data blocks
            foreach ($a_blocks as $id => $block) {
                if (empty($block))
                    continue;

                // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

                // parse uploaded files
                if (strpos($block, 'application/octet-stream') !== FALSE) {
                    // match "name", then everything after "stream" (optional) except for prepending newlines
                    preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                } // parse all other fields
                else {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                }
                $a_data[$matches[1]] = isset($matches[2]) ? $matches[2] : "";
            }
        }
    }

    public function forceSSL() {if ($this->protocol() === $this->schema_http) {header('Location: ' . str_replace($this->schema_http, $this->schema_https, $this->getUrl()));}}

    public function isPOST(): bool {return $this->method()==='POST';}

    public function isGET(): bool {return $this->method()==='GET';}

    public function isDELETE(): bool {return $this->method()==='DELETE';}

    public function isPUT(): bool {return $this->method()==='PUT';}

    private function validateSubmitSize() {
        if ($this->isPOST()) {
            $n1 = $_SERVER['CONTENT_LENGTH'];
            $n2 = StringUtil::convertToBytes(ini_get('post_max_size'));
            if (!$n1) {
                CustomResponse::render(400, 'The submitting content size is 0.');
            }
            else if ($n1 > $n2) {
                CustomResponse::render(400, 'The submitting content size is too large.');
            }
        }
    }

    public function isAjaxRequest(): bool {return self::$data['isAjax'];}

    public function pathInfo(): PathInfo {return self::$data['pathInfo'];}
}