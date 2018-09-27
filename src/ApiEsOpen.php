<?php

namespace EsOpen;


class ApiEsOpen
{
    private $params = [];    // 请求参数
    private $config = [];    // 应用标识
    private $index = '';    // 目标索引
    private $handle = 'query';

    public function __construct($config = [])
    {
        if(!empty($config)){
            $this->config = $config;
        }
    }

    public function setParams($params){
        if(is_array($params)){
            if(isset($params['where'])){
                foreach($params['where'] as &$value){
                    if(is_array($value) && $value[0] === 'like'){
                        $value[1] = str_replace('%','*',$value[1]);
                    }
                }
            }
            $this->params = $params;
        }
    }

    public function setIndex($index){
        if(is_string($index)) $this->index = $index;
    }

    public function setHandle($handle){
        if(is_string($handle)) $this->handle = $handle;
    }

    private function curl($url, $postFields = null)
    {
        $headers = array('Content-Type: application/x-www-form-urlencoded;charset=UTF-8;');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $reponse = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('CURL请求异常:' . curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new \Exception('HTTP服务器返回状态异常 [状态码 ' . $httpStatusCode . '] :' . $reponse);
            }
        }
        curl_close($ch);
        return $reponse;
    }

    /**
     * 获取参数
     */
    public function execute()
    {
        if(empty($this->params)) return ['status'=>false, 'error_message'=>'params not define'];
        if(empty($this->config)) return ['status'=>false, 'error_message'=>'config not define'];
        if(empty($this->index)) return ['status'=>false, 'error_message'=>'index not define'];
        if(empty($this->handle)) return ['status'=>false, 'error_message'=>'handle not define'];
        $curlUrl = $this->config['ES_OPENAPI_URL'].'/'.$this->handle.'/'.$this->index;
        $this->params['docker'] = $this->config['docker'];
        try{
            $ret = $this->curl($curlUrl, json_encode($this->params));
        }catch (\Exception $e){
            return ['status'=>false, 'error_message'=>$e->getMessage()];
        }
        return json_decode($ret, true);
    }
}