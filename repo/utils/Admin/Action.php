<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace Utils\Admin;

use Lib\Validate\Validate;
use ReflectionException;

/**
 * @property $request
 * @property $response
 */
class Action
{
    protected $ci;

    /**
     * 前置操作方法列表
     * @var array $beforeActionList
     * @access protected
     */
    protected $beforeActionList = [];

    /**
     * @throws ReflectionException
     */
    public function init($ci)
    {
        $this->ci = $ci;
        $this->paramsCheck();
        if (strtolower($this->request->getMethod()) == 'get') {
            $data = $this->request->getQueryParams();
//            if (!empty($data)) {
//                $data['page'] = $data['page'] ?? 1;
//            }
            $data['page'] = $data['page'] ?? 1;
            $data['page_size'] = $data['page_size'] ?? 20;
            $this->ci->request = $this->ci->request->withQueryParams($data);
        }
    }

    public function before()
    {
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method) {
                call_user_func([$this, $method]);
            }
        }
    }

    public function __get($field)
    {
        if (!isset($this->$field)) {
            return $this->ci->$field;
        } else {
            return $this->$field;
        }
    }

    /**
     * 验证
     * @return mixed
     * @throws ReflectionException
     */
    public function paramsCheck()
    {
        $uri = $this->request->getUri()->getPath();
        $uris = explode('/', ltrim($uri, '/'));
        $module = '';
        foreach ($uris as $url) {
            if (is_numeric($url)) {
                //定义为为 id=xx,为后续的验证做准备
                merge_request($this->request, ['id' => $url]);
                continue;
            }
            $module = $url;
        }

        $method = strtolower($this->request->getMethod());

        $validatePath = "\\Lib\\Validate\\Admin\\";
        $validate = $validatePath . ucfirst($module) . 'Validate';
        if (!class_exists($validate)) {
            return true;
        }
        /** @var Validate $validate */
        $validate = new $validate();
        if (!$validate->hasScene($method)) {
            return true;
        }

        return $validate->paramsCheck($method, $this->request, $this->response);
    }

}
