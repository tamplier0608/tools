<?php

namespace Http;

/**
 * Http Client based on cURL
 *
 * @package Http
 * @author Serhii Kukunin <kukunin.sergey@gmail.com>
 * @version 1.0
 * @since 17.09.2014
 */
class Client
{
    protected $options = array();
    protected $ch = null;
    protected $lastRequestInfo = '';

    public function init($options = array())
    {
        $defaultOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => 1,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 5.1; rv:2.0) Gecko/20100101 Firefox/4.0',
            CURLOPT_COOKIEFILE => 'cookie.txt',
            CURLOPT_TIMEOUT => 45,
        );

        $this->options = $defaultOptions;

        if (!empty($options)) {
            $this->options = array_merge($defaultOptions, $options);
        }

        return $this;
    }

    public function get($url)
    {
        $this->setOption(CURLOPT_URL, $url);

        return $this->doRequest();
    }

    public function post($url, $data = array())
    {
        $this->setOption(CURLOPT_URL, $url)
            ->setOption(CURLOPT_POST, 1)
            ->setOption(CURLOPT_POSTFIELDS, $data);

        return $this->doRequest();
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return false;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function doRequest()
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->options);
        
        $result = curl_exec($ch);
        $this->lastRequestInfo = curl_getinfo($ch);
        curl_close($ch);

        if ($result) {
            return $result;
        }
        throw new \Exception(curl_error($ch));
    }

    public function getLastRequestInfo()
    {
        return $this->lastRequestInfo;
    }
} 
