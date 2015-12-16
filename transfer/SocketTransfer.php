<?php

namespace cszchen\httpclient\transfer;

class SocketTransfer
{
    public function send($request, $options)
    {
        
    }
    
    public function socketOpen($host, $port, $timeout = 30)
    {
        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            throw new \Exception('Socket error: #' . $errno . ' - ' . $errstr);
        }
        return $fp;
    }
    
}
