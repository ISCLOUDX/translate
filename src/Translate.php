<?php

namespace iscms\Translate;

use GuzzleHttp\Client;

class Translate
{
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }


    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function translate($text)
    {
        $translator = new \TranslateApi(new Client(), $this->config);
        return $translator->translate($text);
    }

    public function translateToCN($text){
        $translator = new \TranslateApi(new Client(), $this->config);
        return $translator->translate($text,'auto','zh');
    }

    public function translateToEn($text){
        $translator = new \TranslateApi(new Client(), $this->config);
        return $translator->translate($text,'auto','en');
    }
}
