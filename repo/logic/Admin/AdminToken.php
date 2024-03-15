<?php

namespace Logic\Admin;

use Logic\Captcha\Captcha;
use Logic\Logic;
use Logic\Set\SetConfig;
use Model\AdminModel;
use Model\AdminRoleRelationModel;
use Model\MerchantModel;
use PDO;
use Utils\Client;
use Illuminate\Database\Capsule\Manager as Capsule;
use Lib\Exception\BaseException;
use DB;

/**
 * json web token
 * 保证web api验证信息不被篡改
 *
 * @property mixed $lang
 * @property mixed $request
 * @property mixed $response
 * @property mixed $redis
 */
class AdminToken extends Logic
{

    const KEY = 'this is secret use for jwt';

    const EXPIRE = 3600 * 72;

    protected $Db;

    protected $adminAuth;

    protected $playLoad = [
        'admin_id' => 0, // 0 匿名用户
        'role_id' => [], // 0 默认权限
        'type' => 1, // 1 普通用户; 2 平台用户
        'nick_name' => '',
        'admin_name' => '',
        'real_name' => '',
        'ip' => '',
        'client_id' => '',
    ];

    public function __construct($ci)
    {
        parent::__construct($ci);
        $this->Db = new Capsule();
        $this->Db->setFetchMode(PDO::FETCH_ASSOC);
        $this->adminAuth = new AdminAuth($ci);
    }

    /**
     * 创建token
     *
     * @param array $data
     * @param string $publicKey
     * @param float|int $ext
     * @param string $digital
     *
     * @return mixed
     */
    public function createToken(array $data = [], string $publicKey = self::KEY, $ext = self::EXPIRE, string $digital = '')
    {
        // 校验基于用户名获取用户信息
        $userObj = AdminModel::where('admin_name', $data['admin_name'])->first();
        if (empty($userObj))
            return $this->lang->set(4);
        else
            $user = $userObj->toArray();

        // 校验用户状态
        if ($user['status'] != 1)
            return $this->lang->set(5);

        // 校验用户密码
        if (!password_verify($data['password'], $user['password']))
            return $this->lang->set(4);
        else
            unset($user['password']);

        // 校验验证码
        $checkRes = (new Captcha($this->ci))->validateImageCode($data['token'], $data['code']);
        if (!$checkRes)
            return $this->lang->set(121);

        // 校验用户角色权限 并封装数据 //jimmy:用户可以多角色
//        $userObj->load('role');
//        $roleIds = $userObj->role->pluck('id');
//        //如果非超管没有角色,异常
//        if($roleIds->isEmpty() && $userObj->id != 1 ){
//            return $this->lang->set(143);
//        }
        //jimmy: 查找用户 Auth
        // 校验用户角色权限 并封装数据
        $roleId = AdminRoleRelationModel::query()->where('admin_id', $user['id'])->value('role_id');
        $user['role_id'] = $roleId ?? 0; // 如果缺少role，则为0
        $user['merchant_account'] = $user['merchant_id'] ? MerchantModel::query()->where('id', $user['merchant_id'])->value('account') : '';
        $routes = $this->adminAuth->getAuths($user['role_id']);
        $userData = [
            'admin_id' => self::fakeId($user['id'], $digital),
            'role_id' => self::fakeId(intval($user['role_id']), $digital),
            'routes' => $routes,
            'nick_name' => $user['nick_name'] ?? '',
            'admin_name' => $user['admin_name'] ?? '',
            'real_name' => $user['real_name'] ?? '',
            'merchant_id' => $user['merchant_id'] ?? '',
            'ip' => Client::getIp(),
            'mac' => Client::ClientId(),
        ];

        // 生成header
        $header = ['alg' => "HS256", 'typ' => "JWT"];
        $header = base64_encode(json_encode($header));

        // 生成payload
        $payload = base64_encode(json_encode(array_merge(["iss" => "lxz", "exp" => time() + $ext], $userData)));

        // 生成Signature
        $signature = hash_hmac('sha256', $header . '.' . $payload, $publicKey);

        // 封装并设置token
        $token = $header . '.' . $payload . '.' . $signature;
        $this->adminAuth->saveAdminWithToken($user['id'], $token, $ext);

        // 更新登录信息
        $res = DB::table('admin')->where('id', $user['id'])->update(['last_login_ip' => $userData['ip'], 'last_login_time' => date('Y-m-d H:i:s', time())]);
        if (!$res)
            return $this->lang->set(130);

        // 返回结果
        return $this->lang->set(1, [], ['token' => $token, 'info' => $user, 'route' => $routes]);
    }

    /**
     * @throws BaseException
     */
    public function verifyToken()
    {
        if (!$this->playLoad['role_id'] || !$this->playLoad['admin_id']) $this->getToken();

        return $this->playLoad;
    }

    public function remove($adminId)
    {
        $this->adminAuth->removeToken($adminId);
    }

    /**
     * @throws BaseException
     */
    protected function getToken()
    {
        $header = trim($this->request->getHeaderLine('Authorization'));
        // 判断header是否携带token信息
        if (!$header) {
            $newResponse = createResponse($this->response, 401, 10041, '缺少验证信息！');
            throw new BaseException($this->request, $newResponse);
        }

        $config = $this->ci->get('settings')['jsonwebtoken'];
        $token = substr($header, 7);
        if ($token && $data = $this->decode($token, $config['public_key'] ?? self::KEY)) {
            $adminId = $this->originId($data['admin_id'], $config['uid_digital'] ?? '');
            $key = SetConfig::SET_GLOBAL;
            $cache = json_decode($this->redis->get($key), true);
            $loginCheck = $cache['base']['Duplicate_LoginCheck'] ?? '';
            if ($loginCheck)
                $this->adminAuth->checkAdminWithToken($adminId, $token);
            $roleId = $this->originId($data['role_id'] ?? 0, $config['uid_digital'] ?? '');
            $this->playLoad = array_merge(
                $this->playLoad,
                [
                    'role_id' => $roleId,
                    'admin_id' => $adminId,
                    'nick_name' => $data['nick_name'] ?? '',
                    'ip' => Client::getIp(),
                    'routes' => $data['routes'],
                    'admin_name' => $data['admin_name'],
                    'real_name' => $data['real_name'],
                    'merchant_id' => $data['merchant_id'],
                ]
            );
            $GLOBALS['playLoad'] = $this->playLoad;
        } else {
            $newResponse = createResponse($this->response, 401, 10041, '验证信息不合法！');
            throw new BaseException($this->request, $newResponse);
        }
    }


    /**
     * @param $token
     * @param string $publicKey
     *
     * @return mixed|null
     * @throws BaseException
     */
    protected function decode($token, string $publicKey = self::KEY)
    {
        if (substr_count($token, '.') != 2) {
            return null;
        }
        list($header, $payload, $signature) = explode('.', $token, 3);
        if (hash_hmac('sha256', $header . '.' . $payload, $publicKey) != $signature) {
            $newResponse = createResponse($this->response, 401, 10041, '验证不通过！');
            throw new BaseException($this->request, $newResponse);
        }

        // 是否过期
        $_payload = json_decode(base64_decode($payload, true), true);
        if ($_payload['exp'] <= time()) {
            $newResponse = createResponse($this->response, 401, 10041, '登录超时！');
            throw new BaseException($this->request, $newResponse);
        }

        return $_payload;
    }

    /**
     * 伪uid
     *
     * @param int $adminId
     * @param int $digital
     *
     * @return int
     */
    public static function fakeId(int $adminId, int $digital): int
    {
        return ~$digital - $adminId;
    }

    /**
     * 原uid
     *
     * @param int $fakeId
     * @param int $digital
     *
     * @return int
     */
    public function originId(int $fakeId, int $digital): int
    {
        return ~($fakeId + $digital);
    }
}
