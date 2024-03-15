<?php

namespace Utils;

use Logic\Define\CacheKey;

class CacheDb
{
    /**
     * 缓存 key
     * @var
     */
    protected $cacheKey;

    /**
     * 缓存 value
     * @var
     */
    protected $value;

    protected $redisHandler;

    public function __construct(string $cacheKey, $value)
    {
        $this->cacheKey     = $cacheKey;
        $this->value        = $value;
        $this->redisHandler = app()->cache;
    }

    public static function make(string $cacheKey, $value)
    {
        $class = new static($cacheKey, $value);
        return $class;
    }

    /**
     * 缓存 key 前缀
     * @return string
     */
    public function getCacheKeyPrefix(): string
    {
        return CacheKey::$perfix[$this->cacheKey] ?? $this->cacheKey;
    }

    /**
     * 缓存值
     */
    public function getData()
    {
        $data = $this->value;
        if ($this->value instanceof \Closure) {
            $data = ($this->value)();
            if ($data instanceof \Illuminate\Database\Eloquent\Model) {
                $data = $data->toArray();
            }
        }
        if (is_null($data)) {
            throw new \InvalidArgumentException('the data to cached is empty');
        }
        return $data;
    }

    /**
     * 设置string类型
     * @return mixed
     */
    public function set()
    {
        $cacheKeyPrefix = $this->getCacheKeyPrefix();
        $redisHandler   = app()->redis;
        $val            = $this->getData();
        if (is_array($val)) {
            $val = json_encode($val);
        }
        $redisHandler->setex($cacheKeyPrefix, app()->get('settings')['app']['general_cache_timeout'] ?? 86400, $val);
        return $val;
    }

    /**
     * 获取string类型
     * @return mixed
     */
    public function get()
    {
        $cacheKeyPrefix = $this->getCacheKeyPrefix();
        $redisHandler   = app()->redis;
        $data           = $redisHandler->get($cacheKeyPrefix);
        if ($data) {
            return $data;
        }
        $val = $this->getData();
        if (is_array($val)) {
            $val = json_encode($val);
        }
        $redisHandler->setex($cacheKeyPrefix, app()->get('settings')['app']['general_cache_timeout'] ?? 86400, $val);
        return $val;
    }

    /**
     * 设置hash类型
     * @return mixed
     */
    public function hSet()
    {
        $cacheKeyPrefix = $this->getCacheKeyPrefix();
        $rows           = $this->getData();
        $this->redisHandler->hmset($cacheKeyPrefix, $rows);
        $this->redisHandler->expire($cacheKeyPrefix, app()->get('settings')['app']['general_cache_timeout'] ?? 86400);
        return $rows;
    }

    /**
     * 获取hash类型
     * @return mixed
     */
    public function hGet()
    {
        $cacheKeyPrefix = $this->getCacheKeyPrefix();
        $redisHandler   = app()->redis;
        $data           = $redisHandler->hGet($cacheKeyPrefix, 'id');
        if ($data === '0') {
            return null;
        }

        //if has Cache
        if ($data) {
            $data = $redisHandler->hgetAll($cacheKeyPrefix);
            return $data;
        }

        $rows = $this->getData();
        //No Cache
        if (is_null($rows)) {
            //缓存默认值，防止缓存穿透
            $redisHandler->hSet($cacheKeyPrefix, 'id', 0);
            $redisHandler->expire($cacheKeyPrefix, app()->get('settings')['app']['temporary_cache_timeout'] ?? 3600);
            return null;
        }

        $redisHandler->hMset($cacheKeyPrefix, $rows);
        $redisHandler->expire($cacheKeyPrefix, app()->get('settings')['app']['general_cache_timeout'] ?? 86400);

        return $rows;
    }

    /**
     * 查找Set元素
     * @param string $member 元素
     * @return bool
     */
    public function sIsmember(string $member): bool
    {
        $cacheKeyPrefix = $this->getCacheKeyPrefix();
        $redisHandler   = app()->redis;
        $data           = $redisHandler->sismember($cacheKeyPrefix, $member);
        //if has Cache
        if ($data === 1) {
            return true;
        }
        try{
            $rows = $this->getData();
        }catch (\InvalidArgumentException $e){
            return false;
        }
        //No Cache
        if (is_null($rows)) {
            return false;
        }
        $redisHandler->sadd($cacheKeyPrefix, $member);

        return true;
    }

}