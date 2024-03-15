<?php

namespace Utils;

/**
 * Redis限流类
 */
class RedisLimit
{
    protected $key      = '';
    protected $maxBurst = 0;//令牌桶容量
    protected $speed    = 0;//指定时间窗口内允许访问的次数
    protected $seconds  = 0;//指定的时间窗口，单位：秒
    protected $apply    = 1;//本次要申请的令牌数

    /**
     * RedisLimit constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key      = $key;
        $this->maxBurst = app()->get('settings')['app']['ip_limit_max_burst'] ?? 10;
        $this->speed    = app()->get('settings')['app']['ip_limit_max_speed'] ?? 5;
        $this->seconds  = app()->get('settings')['app']['ip_limit_max_seconds'] ?? 60;
        $this->apply    = app()->get('settings')['app']['ip_limit_max_apply'] ?? 1;
    }

    /**
     * 是否放行
     * @return int 0 or 1，0：放行  1:拒绝
     */
    public function isPass(): int
    {
        $ret = $this->appSignTimeout = app()->redis->executeRaw([
            'CL.THROTTLE', $this->key, $this->maxBurst, $this->speed, $this->seconds, $this->apply,
        ]);
        return $ret[0];
    }
}