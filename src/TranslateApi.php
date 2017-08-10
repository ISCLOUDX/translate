<?php

namespace iscms\Translate;

use GuzzleHttp\Client;
use iscms\Translate\Exceptions\TranslationErrorException;

/**
 * User: Yoruchiaki
 * Date: 2017/8/8
 * Time: 15:04
 */
class TranslateApi
{
    private $http, $config;
    protected $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate';

    public function __construct(Client $http, array $config = [])
    {
        $this->http = $http;
        $this->config = $config;
    }


    /**
     * 获取翻译结果
     * @param $text
     * @param string $from
     * @param string $to
     * @return mixed
     */
    public function translate($text, $from = 'auto', $to = 'en')
    {
        if(empty($text)){
            return '';
        }
        $text=$this->removeSegment($text);
        $args = [
            'q' => $text,
            'appid' => $this->config['app_id'],
            'salt' => rand(10000, 99999),
            'from' => $from,
            'to' => $to,
        ];
        $args['sign'] = $this->buildSign($text, $args['salt']);
        $result = $this->getTranslatedText($text, $args);
        return $result;
    }


    /**
     * 构造签名
     * @param $query
     * @param $salt
     * @return string
     */
    private function buildSign($query, $salt)
    {
        return md5($this->config['app_id'] . $query . $salt . $this->config['secret']);
    }


    private function getTranslatedText($text, $args)
    {
        $response = $this->http->get($this->url, ['query' => $this->convert($args)]);
        return $this->getTranslation(json_decode($response->getBody(), true));
    }

    private function getTranslation(array $translateResponse)
    {
        if (!array_key_exists('error_code', $translateResponse)) {
            return $this->getTranslatedTextFromResponse($translateResponse);
        }
        if (is_int($this->error_msg($translateResponse['error_code']))) {
            throw new TranslationErrorException('翻译出现异常,错误代码 : ' . $translateResponse['error_code'] . '. Refer url: http://ai.youdao.com/docs/api.s');
        } else {
            throw new TranslationErrorException("异常信息" . $this->error_msg($translateResponse['error_code']));
        }
    }


    private function getTranslatedTextFromResponse(array $translateResponse)
    {
        return $translateResponse['trans_result'][0]['dst'];
    }


    /**
     * Remove segment #.
     *
     * @param $text
     *
     * @return mixed
     */
    private function removeSegment($text)
    {
        return str_replace('#', '', ltrim($text));
    }


    private function convert(&$args)
    {
        $data = '';
        if (is_array($args)) {
            foreach ($args as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $data .= $key . '[' . $k . ']=' . rawurlencode($v) . '&';
                    }
                } else {
                    $data .= "$key=" . rawurlencode($val) . "&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }


    public function error_msg($error_code)
    {
        $messages = [
            52000 => "成功",
            52001 => "请求超时，请重试",
            52002 => "系统错误，请重试",
            52003 => "未授权用户,检查您的 appid 是否正确",
            54000 => "必填参数为空，检查是否少传参数",
            54001 => "签名错误,请检查您的签名生成方法",
            54003 => "访问频率受限,请降低您的调用频率",
            54004 => "账户余额不足,请前往管理控制平台为账户充值",
            54005 => "长query请求频繁,请降低长query的发送频率，3s后再试",
            58000 => "客户端IP非法,检查个人资料里填写的 IP地址 是否正确可前往管理控制平台修改IP限制，IP可留空",
            58001 => "译文语言方向不支持,检查译文语言是否在语言列表里",
        ];
        if (array_key_exists($error_code, $messages)) {
            return $messages[$error_code];
        } else {
            return $error_code;
        }
    }

}