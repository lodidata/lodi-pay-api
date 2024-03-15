<?php

namespace Utils;

/**
 * 验签类
 */
class AppSign
{
    /**
     * 密钥
     * @var mixed
     */
    private $secretKey;

    /**
     * 签名时长
     * @var mixed
     */
    private $appSignTimeout;

    /**
     * @param string $secretKey 密钥
     */
    public function __construct(string $secretKey)
    {
        $this->secretKey      = $secretKey;
        $this->appSignTimeout = app()->get('settings')['app']['app_sign_timeout'] ?? 3600;
    }

    /**
     * 检测验签
     * @param array $params
     * @param array $data 加密数据
     * @return boolean
     */
    public function checkSign(array $params, array $data): bool
    {
        //请求超时
        if (time() > $data['timestamp'] + $this->appSignTimeout) {
            return false;
        }
        //将sign置为空，在生成签名字符串时会自动去掉
        // $data['sign'] = '';
        $signContent  = $this->getSignContent($data);
        //生成签名
        $sign = $this->sign($signContent);
        //验签
        if (strcasecmp($params['sign'], $sign) != 0) {
            return false;
        }
        return true;
    }

    /**
     * 获取签名字符串
     * @param array $params
     * @return string
     */
    public function getSignContent(array $params): string
    {
        //按关联数组的键名做升序排序
        ksort($params);
        reset($params);
        $stringToBeSigned = '';
        $i                = 0;
        //拼接成key=value&key=value字符串形式
        foreach ($params as $key => $val) {
            if (checkEmpty($val) === false && substr($val, 0, 1) != '@') {
                if ($i == 0) {
                    $stringToBeSigned .= $key . "=" . $val;
                } else {
                    $stringToBeSigned .= "&" . $key . "=" . $val;
                }
                $i++;
            }
            unset($key, $val);
        }
        return $stringToBeSigned;
    }

    /**
     * 生成签名字符串
     * @param string $data
     * @return string
     */
    public function sign(string $data): string
    {
        $encrypt = new Encrypt($this->secretKey);
        return $encrypt->encrypt($data);
    }

    /**
     * 解密签名字符串
     * @param string $data
     * @return bool|string
     */
    public function deSign(string $data)
    {
        $encrypt = new Encrypt($this->secretKey);
        return $encrypt->aesDecrypt($data);
    }

}