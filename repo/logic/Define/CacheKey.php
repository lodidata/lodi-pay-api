<?php

namespace Logic\Define;

/**
 * Class CacheKey
 * 缓存key定义 前缀定义类
 * @package Logic\Define
 */
class CacheKey
{

    /**
     * key
     * @var array
     */
    public static $perfix = [
        //管理员token
        'adminCacheToken'             => 'admin:cache:token:',

        //验证码
        'authVCode'                   => 'image:code:',

        'websiteAccount'            => 'website:account:generate' //网站账号生成
    ];

}