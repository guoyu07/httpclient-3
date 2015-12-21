<?php

namespace cszchen\httpclient;

class Cookie
{
    public $name;
    
    public $value = '';
    
    public $domain = '';
    
    public $path = '/';
    
    public $expires = 0;
    
    //public $httponly = true;
    
    public $secure = false;
    
    public function __construct(Array $value)
    {
        foreach ($value as $property => $v) {
            $this->$property = $v;
        }
    }
    
    public function __toString()
    {
        return (string) $this->value;
    }
}
