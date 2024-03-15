<?php

use Slim\Http\Request;

function createResponse($response, $status = 200, $state = 0, $message = 'ok', $data = null, $attributes = null)
{

    return $response
        ->withStatus($status)
        ->withJson([
            'data' => $data ?? [],
            //'attributes' => $attributes,
            'state' => $state,
            'message' => $message,
            'ts' => time(),
        ]);
}

/**
 * TODO 判断值是否是大于0的正整数
 *
 * @param $value
 * @return bool
 */
function isPositiveInteger($value): bool
{
    if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
        return true;
    } else {
        return false;
    }
}


/**
 * TODO 使用正则验证数据
 *
 * @access public
 * @param string $value :要验证的数据
 * @param string $rule :验证规则
 * @return boolean
 */
function regex(string $value, string $rule): bool
{
    $validate = array(
        'require' => '/\S+/',
        'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'mobile' => '/^(((13[0-9]{1})|(14[5,7]{1})|(15[0-35-9]{1})|(17[0678]{1})|(18[0-9]{1}))+\d{8})$/',
        'phone' => '/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/',
        'url' => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
        'currency' => '/^\d+(\.\d+)?$/',
        'number' => '/^\d+$/',
        'zip' => '/^\d{6}$/',
        'integer' => '/^[-\+]?\d+$/',
        'double' => '/^[-\+]?\d+(\.\d+)?$/',
        'english' => '/^[A-Za-z]+$/',
        'bankcard' => '/^\d{14,19}$/',
        'safepassword' => '/^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}$/',
        'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        'oddsid' => '/^([+]?\d+)|\*$/',//验证赔率设置id
        'qq' => '/^[1-9]\\d{4,14}/',//验证qq格式
    );
    // 检查是否有内置的正则表达式
    if (isset ($validate [strtolower($rule)]))
        $rule = $validate [strtolower($rule)];
    return 1 === preg_match($rule, $value);
}

/**
 * TODO 随机长度的字符串
 *
 * @param $length
 * @return string
 */
function getRandStr($length): string
{
    //字符组合
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $len = strlen($str) - 1;
    $randStr = '';
    for ($i = 0; $i < $length; $i++) {
        $num = mt_rand(0, $len);
        $randStr .= $str[$num];
    }

    return $randStr;
}

function app()
{
    return Utils\App::getContainer();
}

if (!function_exists('merge_request')) {
    /**
     * @throws ReflectionException
     */
    function merge_request($request, array $data)
    {
        $body = $request->getParsedBody() ?: [];
        $input = array_merge($body, $data);

        $rec = new ReflectionProperty($request, 'bodyParsed');
        $rec->setAccessible(true);
        $rec->setValue($request, $input);
    }
}

if (!function_exists('merge_request_query')) {
    /**
     * @param Request $request
     * @param array $data
     * @return void
     * @throws ReflectionException
     */
    function merge_request_query(Request $request, array $data)
    {
        $query = $request->getQueryParams();
        $input = array_merge($query, $data);

        $rec = new ReflectionProperty($request, 'queryParams');
        $rec->setAccessible(true);
        $rec->setValue($request, $input);
    }
}

if (!function_exists('opensslPkeyNew')) {
    function opensslPkeyNew($digest = 'sha256', $bits = 1024, $private_key_type = OPENSSL_KEYTYPE_RSA): array
    {
        $config = array(
            "digest_alg" => $digest,
            "private_key_bits" => $bits,
            "private_key_type" => $private_key_type,
        );

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $private_key);
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];
        $private_key = str_replace(['-----BEGIN PRIVATE KEY-----', "\n", '-----END PRIVATE KEY-----'], '', $private_key);
        $public_key = str_replace(['-----BEGIN PUBLIC KEY-----', "\n", '-----END PUBLIC KEY-----'], '', $public_key);

        return [
            'private_key' => $private_key,
            'public_key' => $public_key,
        ];
    }
}
if (!function_exists('generateDealNumber')) {
    function generateDealNumber($rand = 999999999, $length = 9): string
    {
        return date('mdhis') . str_pad(mt_rand(1, $rand), $length, '0', STR_PAD_LEFT);
    }
}

