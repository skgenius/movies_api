<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class WebService
{
    public $api_key;
    private $api_url_prefix = 'http://api.themoviedb.org/3/';

    public function __construct($api_key)
    {
        $this->api_key       = $api_key; 
    }

    public function getApiKey()
    {
        return $this->api_key;
    }
    public function getApiUrlPrefix()
    {
        return $this->api_url_prefix;
    }

    public function getData($path, array $parameters = array())
    {
        $default_parameters = array(
            'api_key' => $this->getApiKey()
            , 'language' => 'en-US'
        );
        $parameters = array_merge($default_parameters, $parameters);
        $query = http_build_query($parameters);

        // Setting the HTTP Request Headers
        $request_headers   = []; 
        $request_headers[] = 'Content-type: application/json';
        $request_headers[] = 'Accept: application/json'; 
        $request_headers[] = 'Cache-Control: no-cache';
        
        // Performing the HTTP request
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->getApiUrlPrefix().$path.'?'.$query);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_PORT, 80);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "");
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Performs the Request
        $response = curl_exec($ch); 
        
        // Performs error checks
        if (curl_errno($ch)) { 
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
        } else {
            $response = json_decode( $response, true );
            
        } 

        curl_close($ch);  
        
        return $response;
    }
}