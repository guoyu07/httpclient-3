<?php

namespace cszchen\httpclient;

abstract class Message
{
    /**
     * message headers
     * @var array
     */
    protected $headers = [];
    
    /**
     * message cookies
     * @var array
     */
    protected $cookies = [];
    
    /**
     * raw content
     * @var string
     */
    protected $content = '';
    
    /**
     * message data
     * @var mix
     */
    protected $data = null;
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }
    
    public function setHeader($key, $value = null)
    {
        if (is_string($key)) {
            $this->headers[$key] = $value;
        } elseif (is_array($key)) {
            foreach ($key as $k=> $v) {
                $this->setHeader($k, $v);
            }
        }
    }
    
    public function getCookies()
    {
        return $this->cookies;
    }
    
    public function getCookie($key)
    {
        return isset($this->cookie[$key]) ? $this->cookie[$key] : null;
    }
    
    public function setCookie($key, $value = null)
    {
        if (is_string($key)) {
            if (is_string($value)) {
                $value = new Cookie(['name' => $key, 'value' => $value]);
            } elseif (is_array($value)) {
                $value = new Cookie($value);
            }
            $value instanceof Cookie && $this->cookies[$key] = $value;
        } elseif (is_array($key)) {
            foreach ($key as $k=> $v) {
                $this->setCookie($k, $v);
            }
        }
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function getContentType()
    {
        return $this->getHeader('Content-Type');
    }
}
