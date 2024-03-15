<?php

namespace Logic\Admin;

use Logic\Logic;
use Logic\Define\CacheKey;
use Lib\Exception\BaseException;
use Model\AdminRoleModel;

/**
 * @method getMemberControls(int|mixed $roleId)
 * @property mixed $redis
 * @property mixed $response
 * @property mixed $request
 */
class AdminAuth extends Logic
{
    const PREFIX_ADMIN = 'admin.cache.manager.';
    const ADMIN_USER = self::PREFIX_ADMIN . 'admin_user:';
    const KEY_ADMIN_USER = 'admin_user_cache';
    const KEY_REFRESH_TOKEN = 'admin_refresh_token';
    const KEY_ROLE_AUTH = 'admin_role_auth';
    const KEY_ROLE_AUTH_FLAT = 'admin_role_auth_flat';
    const KEY_ROLE_MEMBER_CONTROL = 'admin_role_member_control';
    const DEFAULT_EXPIRE = 3600 * 24 * 15;
    // 是否需要判断权限
    protected $needAuth = true;

    public function __construct($ci)
    {
        parent::__construct($ci);
    }

    /**
     * TODO 获取管理员初始化权限
     *
     * @param string $name
     * @return mixed
     * @note 改为前端节点配置
     */
    public function authOrigin(string $name = '../../config/adminroutes.json')
    {
        return json_decode(file_get_contents($name), true);
    }

    /**
     * @param int $roleId
     * @return array
     */
    public function getAuths(int $roleId): array
    {
        if (empty($roleId)) return [];
        $authArr = explode(',', AdminRoleModel::query()->where('id', $roleId)->value('auth'));
        foreach ($authArr as $authValue) {
            $list[] = intval($authValue);
        }
        sort($list);
        return $list;
    }

    /**
     * 根据用户id保存token
     */
    public function saveAdminWithToken($adminId, $token = '', $ttl = 3600)
    {
        $key = CacheKey::$perfix['adminCacheToken'] . $adminId;
        $this->redis->setex($key, $ttl, $token);

    }

    /**
     * 刷新用户访问时间
     */
    public function refreshAdminToken($adminId, $ttl = 3600)
    {
        $key = CacheKey::$perfix['adminCacheToken'] . $adminId;
        $this->redis->expire($key, $ttl);
    }

    /**
     * 根据用户id和token检验token是否有效
     *
     * @param $adminId
     * @param string $token
     *
     * @throws BaseException
     */
    public function checkAdminWithToken($adminId, string $token = '')
    {
        $key = CacheKey::$perfix['adminCacheToken'] . $adminId;
        if ($token !== $this->redis->get($key)) {
            $newResponse = createResponse($this->response, 401, 10041, '该账号已在别处登录，请重新登录！');
            throw new BaseException($this->request, $newResponse);
        }
    }

    /**
     * 刷新token
     *
     * @param $refreshToken
     * @param $accessToken
     * @param $expire
     *
     * @return void
     */
    public function saveRefreshToken($refreshToken, $accessToken, $expire = self::DEFAULT_EXPIRE): void
    {
        $cache = $this->redis;
        $key = self::KEY_REFRESH_TOKEN . ':' . $refreshToken;
        $cache->set($key, $accessToken);
        $cache->expire($key, $expire);
    }

    /**
     * 删除 token
     *
     * @param $adminId
     */
    public function removeToken($adminId)
    {
        $key = CacheKey::$perfix['adminCacheToken'] . $adminId;
        $this->redis->del($key);
    }
}
