<?php

namespace cszchen\httpclient;

class Response extends Message
{
    
    protected $statusCode;
    
    protected $protocolVersion;
    
    protected $reasonPhrase;
    
    public function __construct($request, $headers, $content)
    {
        $this->parseHeaders($headers);
        $this->content = $content;
    }
    
    public function getData()
    {
        if ($this->data) {
            return $this->data;
        }
        $contentType = $this->getHeader('Content-Type');
        if (stripos($contentType, 'json') !== false) {
            $this->data = json_decode($this->content, true);
        } elseif (stripos($contentType, 'xml') !== false) {
            $this->data = $this->convertXmlToArray($this->content);
        } elseif (stripos($contentType, 'urlencoded') !== false) {
            parse_str($this->content, $this->data);
        }
        return $this->data;
    }
    
    protected function parseCookie($cookieString)
    {
        $params = [];
        $pairs = explode(';', $cookieString);
        foreach ($pairs as $number => $pair) {
            $pair = trim($pair);
            if (strpos($pair, '=') === false) {
                $params[$pair] = true;
            } else {
                list($name, $value) = explode('=', $pair, 2);
                if ($number === 0) {
                    $params['name'] = $name;
                    $params['value'] = urldecode($value);
                } else {
                    $params[$name] = urldecode($value);
                }
            }
        }
        return new Cookie($params);
    }
    
    protected function parseHeaders($headers)
    {
        foreach ($headers as $n=>$header) {
            if ($n == 0) {
                list($this->protocolVersion, $this->statusCode, $this->reasonPhrase) = explode(' ', $header);
                continue;
            }
            list($key, $value) = explode(':', $header);
            
            if (trim($key) == 'Set-Cookie') {
                $cookie = $this->parseCookie(trim($value));
                $this->setCookie($cookie->name, $cookie);
                continue;
            }
            $this->setHeader(trim($key), trim($value));
        }
    }
    
    protected function convertXmlToArray($xml)
    {
        if (!is_object($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $result = (array) $xml;
        foreach ($result as $key => $value) {
            if (is_object($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }
    
}
