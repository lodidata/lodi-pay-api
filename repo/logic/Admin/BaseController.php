<?php

namespace Logic\Admin;

use DB;
use Utils\Admin\Action;
use Utils\Client;
use Utils\Encrypt;
use Lib\Exception\BaseException;

/**
 * @property $lang
 * @property $redis
 */
class BaseController extends Action
{
    protected $playLoad = [
        'admin_id' => 0, // 0 匿名用户
        'role_id' => 0, // 0 默认角色
        'type' => 1, // 1 普通用户; 2 平台用户
        'nick_name' => '',
        'admin_name' => '',
        'real_name' => '',
        'ip' => '',
        'client_id' => '',
    ];
    protected $adminToken;
    protected $lotteryToken = "do3e28ae0e6";

    protected $page = 1; // 默认页码
    protected $pageSize = 20; // 每页默认显示记录数

    protected $module, $moduleChild, $moduleFunName;

    public function init($ci)
    {
        parent::init($ci);
        $this->adminToken = new AdminToken($this->ci);
        if (!empty($pageSize = $ci->request->getParam('page_size'))) {
            $this->pageSize = $pageSize;
        }
        $this->before();
    }

    /**
     * 校验token
     * @throws BaseException
     */
    public function verifyToken()
    {
        $this->playLoad = $this->adminToken->verifyToken();
    }

    /**
     * 获取管理员姓名
     */
    public function getAdminUserName()
    {
        $data = (array)DB::table('admin')
            ->select('admin_name')
            ->where('id', '=', $this->playLoad['admin_id'])
            ->get()
            ->first();
        return $data['admin_name'];
    }

    public function getRequestDir(): string
    {
        $ver = ['v1', 'v2', 'v3', 'v4'];
        $dir = explode('/', $this->request->getUri()->getPath());
        $res = [];
        foreach ($dir as $v) {
            if ($v == $ver) continue;
            if (!is_numeric($v)) {//排除id值的put方法和patch方法
                $res[] = $v;
            }
        }
        return implode('/', $res);
    }

    /**
     * 校验权限
     * @throws BaseException
     */
    public function authorize(): bool
    {
        $roleId = $this->playLoad['role_id'];
        if ($roleId == 0 || $roleId == 1) {
            return true;
        }
        $dir = rtrim($this->getRequestDir(), '/');//获取请求地址
        $allow = DB::table('admin_role_auth')->where('method', $this->request->getMethod())
            ->where('path', $dir)->value('id');

        $auth = DB::table('admin_role')->where('id', $roleId)->value('auth');
        if (empty($allow) || empty($auth) || !in_array($allow, explode(',', $auth))) {
            $newResponse = $this->response->withStatus(401);
            $newResponse = $newResponse->withJson([
                'state' => -1,
                'message' => '您无权限操作，请联系管理员添加',
                'ts' => time(),
            ]);
            throw new BaseException($this->request, $newResponse);
        }

        return true;
    }

    /**
     * TODO 检查id
     *
     * @throws BaseException
     */
    public function checkID($id): bool
    {
        if (empty($id)) {
            $newResponse = $this->response->withStatus(400);
            $newResponse = $newResponse->withJson([
                'state' => -1,
                'message' => 'id不能为空',
                'ts' => time(),
            ]);
            throw new BaseException($this->request, $newResponse);
        }

        if (is_numeric($id) && is_int($id + 0) && ($id + 0) > 0) {
            return true;
        }

        $newResponse = $this->response->withStatus(400);
        $newResponse = $newResponse->withJson([
            'state' => -1,
            'message' => 'id必须为正整数',
            'ts' => time(),
        ]);
        throw new BaseException($this->request, $newResponse);
    }

    public function makePW($password): array
    {
        $salt = Encrypt::salt();

        return [md5(md5($password) . $salt), $salt];
    }

    /**
     * 【管理员角色】角色权限设置（不同客服角色对会员各个资料详细权限控制）：
     *  真实姓名（只显示姓/显示全名/修改姓名）、银行卡号（显示全部/显示部分）、通讯资料隐藏/显示（比如邮箱、QQ、微信号等）
     *
     * @param array $data
     * @param int $roleId
     * @return array
     */
    public function roleControlFilter(array &$data, int $roleId = 0): array
    {
        static $names, $cards, $privates, $rid = null;

        if ($names == null) {
            $names = ['truename', 'accountname'];
        }
        if ($cards == null) {
            $cards = ['card', 'idcard'];
        }
        if ($privates == null) {
            $privates = ['email', 'mobile', 'qq', 'weixin', 'skype', 'telephone'];
        }
        if ($rid == null) {
            if (!$roleId) {
                $roleId = $this->playLoad['role_id'];
            }
            $controls = (new AdminAuth($this->ci))->getMemberControls($roleId);
        }

        if (isset($controls)) {
            foreach ($data as $key => &$item) {
                if (is_array($item)) {
                    $item = $this->roleControlFilter($item, $roleId);
                } else {
                    // 如果无姓名权限，真实姓名只显示姓
                    if (in_array($key, $names, true)) {
                        if (!$controls['true_name'] && mb_strlen($item)) {
                            $item = strpos($item, ' ') !== false ? explode(' ', $item)[0] . ' ***' : mb_substr($item, 0,
                                    mb_strlen($item) == strlen($item) ? 2 : 1) . '**';
                        }
                    }
                    // 如果无卡号权限，显示两边的部份
                    if (in_array($key, $cards, true)) {
                        if (!$controls['bank_card'] && is_numeric($item)) {
                            $card = trim(chunk_split($item, 4, ' '));
                            $cardChunk = explode(' ', $card);
                            $first = array_shift($cardChunk);
                            $last = array_pop($cardChunk);
                            $item = $first . '****' . $last;
                        }
                    }
                    // 如果无个人信息权限，只显示一部份
                    if (in_array($key, $privates, true)) {
                        if (!$controls['address_book']) {
                            if (in_array($key, ['email', 'skype']) && strlen($item)) {
                                $item = '***' . strrchr($item, '@');
                            } else {
                                if (strlen($item) > 4) {
                                    $item = substr($item, 0, 2) . '***' . substr($item, -2, 2);
                                } elseif (strlen($item)) {
                                    $item = substr($item, 0, 1) . '**' . substr($item, -1, 1);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $privates;
    }

    /**
     * 校验请求的TOKEN
     * @throws BaseException
     */
    public function verifyLotteryToken()
    {
        $token = $this->request->getHeaderLine('token');
        $lotteryToken = $this->lotteryToken . date("Ymd");
        if (($token != $lotteryToken) || empty($token)) {
            $newResponse = $this->response->withStatus(400);
            $newResponse = $newResponse->withJson([
                'state' => -1,
                'message' => '参数不对',
                'ts' => time(),
            ]);
            throw new BaseException($this->request, $newResponse);
        }
    }

    public function encrypt(string $data)
    {
        $key = $this->lotteryToken . date('Ymd');
        $encrypt = new Encrypt($key);
        return $encrypt->encrypt($data);
    }

    /**
     * TODO 更新操作校验数据是否有变更
     *
     * @param object|array $model 查询的模型或数组
     * @param array $params 请求的字段
     * @param array $filter 不用处理的字段
     * @return array
     */
    protected function checkParamsChange($model, array $params, array $filter = []): array
    {
        $flag = 0;
        // 校验$model参数
        if (is_object($model))
            $original = $model->toArray();
        else if (is_array($model))
            $original = $model;
        else
            return $this->lang->set(131);
        // 校验请求参数
        if (!$original || !$params)
            return $this->lang->set(131);
        $msg = '';
        // 检查请求参数
        foreach ($params as $key => $value) {
            foreach ($original as $k => $v) {
                if ($key == $k && $value != $v) {
                    $flag = 1;
                    if (empty($filter) || !in_array($key, $filter)) {
                        $msg .= $key . '由【' . $v . '】修改成【' . $value . '】|';
                    }
                }
            }
        }
        return [$flag, $msg];
    }

    /**
     * TODO 贴合公司业务需求，修正了写日志的逻辑
     *
     * @param $logArr
     * @return boolean
     */
    protected function writeAdminLog($logArr): bool
    {
        $playLoad = $this->playLoad;
        // 封装数据
        $data = [
            'admin_id' => $playLoad['admin_id'] ?? 0,
            'admin_name' => $playLoad['admin_name'] ?? '',
            'method' => strtoupper($this->request->getMethod()),
            'record' => $logArr['record'] ?? '',
            'status' => $logArr['status'] ?? '',
            'path' => $this->getRequestDir(),
            'ip' => !empty($playLoad['ip']) ? $playLoad['ip'] : Client::getIp(),
            'uname2' => $logArr['uname2'] ?? '',
            'uid2' => $logArr['uid2'] ?? 0,
            'module' => $logArr['module'] ?? $this->module,
            'module_child' => $logArr['module_child'] ?? $this->moduleChild,
            'fun_name' => $logArr['fun_name'] ?? $this->moduleFunName,
            'remark' => $logArr['remark'] ?? '',
        ];
        // 写日志
        return (new Log($this->ci))->write($data);
    }

    /**
     * 根据字段更新不同语言日志
     * @param string $msg log日志
     * @param array $changeArr 需要更新的字段
     * @return string
     */
    public function changeLogName(string $msg, array $changeArr = []): string
    {
        if (empty($msg) || empty($changeArr)) {
            return $msg;
        }
        foreach ($changeArr as $k => $v) {
//            $str = $this->lang->text($v);
            if (strpos($msg, $k) !== false) {
                $msg = str_replace($k, $v, $msg);
            }
        }
        return $msg;
    }
}
