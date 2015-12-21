<?php

namespace cszchen\httpclient;

class Request extends Message
{
    
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_PATCH   = 'PATCH';
    
    public $url;
    
    public $method;
    
    protected $methods = [
        self::METHOD_GET,
        self::METHOD_POST,
        //self::METHOD_OPTIONS,
        //self::METHOD_HEAD,
        //self::METHOD_PUT,
        //self::METHOD_DELETE,
        //self::METHOD_PATCH,
    ];
    
    protected $defaultHeaders = [
        'Connection' => 'keep-alive',
        'Cache-Control' => 'max-age=0',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'UPGRADE_INSECURE_REQUESTS' => '1',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36',
        'ACCEPT_ENCODING' => 'gzip, deflate, sdch'
    ];
    
    protected $onceHeaders = [];
    
    protected $transfer;
    
    public function __construct()
    {
        $this->setHeader($this->defaultHeaders);
    }
    
    /**
     * 
     * @param string $url
     * @param string $method
     * @param mix $data
     * @param array $options 
     * options keys:auth, ssl;
     */
    public function processRequest($url, $method = self::METHOD_GET, $data = '', $options = [])
    {
        $method = strtoupper($method);
        if (isset($options['headers'])) {
            $this->onceHeaders = array_merge($this->onceHeaders, $options['headers']);
        }
        if (isset($options['auth']['user']) && isset($options['auth']['password'])) {
            $this->auth($options['auth']['user'], $options['auth']['password']);
        }
        $this->url = $url;
        $this->method = $method;
        //if ($method == self::METHOD_POST && is_string($data)) {
            $this->content = $this->data = $data;
            //$this->onceHeaders['Content-Type'] = 'text/plain';
        //}
        //$this->content = $data;
        $this->setHeader('Cookie', $this->composeCookie());
        $response = $this->getTransfer()->send($this, $options);
        $this->onceHeaders = [];
        return $response;
    }

    public function setTransfer($transfer)
    {
        $this->transfer = $transfer;
    }
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getHeaders()
    {
        return array_merge($this->headers, $this->onceHeaders);
    }
    
    public function auth($user, $pwd)
    {
        $this->onceHeaders['Authorization'] = 'Basic ' . base64_encode($user. $pwd);
    }
    
    protected function getTransfer()
    {
        if (!$this->transfer) {
            $className = __NAMESPACE__ . '\\transfer\\' . (function_exists('curl_init') ? 'CurlTransfer' : 'SocketTransfer');
            $this->transfer = new $className;
        }
        return $this->transfer;
    }
    
    protected function composeCookie()
    {
        $cookies = [];
        foreach ($this->cookies as $cookie)
        {
            $cookies[] = $cookie->name . "=" . urlencode($cookie->value);
        }
        return join(';', $cookies);
    }
    
    public function __call($method, $params)
    {
        if (in_array(strtoupper($method), $this->methods)) {
            if (isset($params[0]) && is_string($params[0])) {
                $uri = $params[0];
            } else {
                throw new \Exception("Url must be set.");
            }
            $data = isset($params[1]) ? $params[1] : [];
            $options = isset($params[2]) && is_array($params[2]) ? $params[2] : [];
            return call_user_func_array([$this, 'processRequest'], [$uri, $method, $data, $options]);
        }
        throw new \Exception("method dosn't exists");
    }
}
