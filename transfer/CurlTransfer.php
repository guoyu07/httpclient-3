<?php

namespace cszchen\httpclient\transfer;

use cszchen\httpclient\Response;

class CurlTransfer
{
    public function send($request, $options)
    {
        $curlOptions = $this->prepare($request);
        $curl = $this->initCurl($curlOptions);
        
        $responseHeaders = [];
        $this->setHeaderOutput($curl, $responseHeaders);
        $responseContent = curl_exec($curl);
        
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);
        curl_close($curl);
        if ($errorNumber > 0) {
            throw new \Exception('Curl error: #' . $errorNumber . ' - ' . $errorMessage);
        }
        //return $responseContent;
        return new Response($request, $responseHeaders, $responseContent);
    }
    
    private function initCurl(array $curlOptions)
    {
        $curlResource = curl_init();
        foreach ($curlOptions as $option => $value) {
            curl_setopt($curlResource, $option, $value);
        }
        return $curlResource;
    }
    /*
    private function composeCurlOptions(array $options)
    {
        static $optionMap = [
            'maxRedirects' => CURLOPT_MAXREDIRS,
            'sslCapath' => CURLOPT_CAPATH,
            'sslCafile' => CURLOPT_CAINFO,
        ];
        $curlOptions = [];
        foreach ($options as $key => $value) {
            if (is_int($key)) {
                $curlOptions[$key] = $value;
            } else {
                if (isset($optionMap[$key])) {
                    $curlOptions[$optionMap[$key]] = $value;
                } else {
                    $key = strtoupper($key);
                    if (strpos($key, 'SSL') === 0) {
                        $key = substr($key, 3);
                        $constantName = 'CURLOPT_SSL_' . $key;
                        if (!defined($constantName)) {
                            $constantName = 'CURLOPT_SSL' . $key;
                        }
                    } else {
                        $constantName = 'CURLOPT_' . strtoupper($key);
                    }
                    $curlOptions[constant($constantName)] = $value;
                }
            }
        }
        return $curlOptions;
    }
    */
    
    private function prepare($request)
    {
        //$request->prepare();
        $curlOptions = [];
        $method = strtoupper($request->getMethod());
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                break;
            default:
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        }
        $content = $request->getContent();
        if ($content !== null) {
            $curlOptions[CURLOPT_POSTFIELDS] = $content;
        }
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $curlOptions[CURLOPT_URL] = $request->getUrl();
        $headers = [];
        foreach ($request->getHeaders() as $k=>$v) {
            $headers[] = "$k:$v";
        }
        $curlOptions[CURLOPT_HTTPHEADER] = $headers;
        return $curlOptions;
    }
    
    private function setHeaderOutput($curlResource, array &$output)
    {
        curl_setopt($curlResource, CURLOPT_HEADERFUNCTION, function($resource, $headerString) use (&$output) {
            $header = trim($headerString, "\n\r");
            if (strlen($header) > 0) {
                $output[] = $header;
            }
            return mb_strlen($headerString, '8bit');
        });
    }
}
