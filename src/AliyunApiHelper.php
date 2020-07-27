<?php
/**
 * Created by PhpStorm.
 * User: geekman
 * Date: 2020/7/23
 * Time: 2:19 PM
 */

namespace Geekmancn\Aliyun\Gateway;

use Geekmancn\Aliyun\Gateway\Constant\ContentType;
use Geekmancn\Aliyun\Gateway\Constant\HttpHeader;
use Geekmancn\Aliyun\Gateway\Constant\HttpMethod;
use Geekmancn\Aliyun\Gateway\Constant\SystemHeader;
use Geekmancn\Aliyun\Gateway\Http\HttpClient;
use Geekmancn\Aliyun\Gateway\Http\HttpRequest;

class AliyunApiHelper
{
    protected $appKey;
    protected $appSecret;
    protected $host;
    
    public function __construct($appKey,$appSecret,$host)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->host = $host;
    }

    /**
     * @param string $path
     * @param array $querys
     * @param array $params
     * @param array $headers
     * @return Http\HttpResponse
     */
    public function request($method, $path, $querys = [], $params = [], $headers = []) {
        //域名后、query前的部分
        $request = new HttpRequest($this->host, $path, $method, $this->appKey, $this->appSecret);

        //设定Content-Type，根据服务器端接受的值来设置
        if($method == HttpMethod::POST){
            $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_TYPE, ContentType::CONTENT_TYPE_FORM);
        }else{
            $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_TYPE, ContentType::CONTENT_TYPE_TEXT);
        }


        //设定Accept，根据服务器端接受的值来设置
        $request->setHeader(HttpHeader::HTTP_HEADER_ACCEPT, ContentType::CONTENT_TYPE_JSON);

        //指定参与签名的header
        $request->setSignHeader(SystemHeader::X_CA_TIMESTAMP);

        //注意：业务header部分，如果没有则无此行(如果有中文，请做Utf8ToIso88591处理)
        //mb_convert_encoding("headervalue2中文", "ISO-8859-1", "UTF-8");
        foreach($headers as $key => $val){
            $request->setHeader($key,$val);
            $request->setSignHeader($key);
        }

        //注意：业务query部分，如果没有则无此行；请不要、不要、不要做UrlEncode处理
        foreach ($querys as $key => $val){
            $request->setQuery($key,$val);
        }

        //注意：业务body部分，如果没有则无此行；请不要、不要、不要做UrlEncode处理
        foreach ($params as $key => $val){
            $request->setBody($key, $val);
        }

        return HttpClient::execute($request);
    }
}