<?php

namespace Lib\Validate;


use Illuminate\Database\Capsule\Manager as Db;
use Lib\Exception\ClassNotFoundException;
use Lib\Exception\ParamsException;

class BaseValidate extends Validate
{
    /**
     * 用于对参数进行批量校验
     *
     * @param string $scene 支持场景教研
     * @param $request
     * @param $response
     * @param bool $batch
     * @return bool
     * @throws ParamsException
     */
    public function paramsCheck(string $scene, $request, $response, bool $batch = false): bool
    {
        $scene = strtolower(trim($scene));
        $params = $request->getParams(); // 获取所有参数
        $result = $this->scene($scene)->batch($batch)->check($params); // 批量校验
        if (!$result) {
            $newResponse = createResponse($response, 400, 10, $this->error);
            throw new ParamsException($request, $newResponse);
        }

        return true;
    }

    // 不允许为空
    protected function isNotEmpty($value): bool
    {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    // 必须是正整数
    protected function isPositiveInteger($value, $field = '')
    {
        if (isPositiveInteger($value)) {
            return true;
        }
        return $field . '必须是正整数';
    }

    /**
     * 按照正则来判断参数是否合法
     *
     * @param $value
     * @param string $rule
     * @return bool|string
     */
    protected function checkValueByRegex($value, string $rule = '')
    {
        if (empty($value)) {
            return false;
        }

        return regex($value, $rule);
    }

    /**
     * @param $value
     * @return bool
     * 字段存在则校验,不存在则不校验
     */
    protected function requireByCreated($value): bool
    {
        $method = app()->request->getMethod();
        if ($method == 'post' && empty($value)) {
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @param $rule 'model,field,not field,model namespace'
     * @param array $data
     * @param $field
     * @return bool
     */
    protected function exists($value, string $rule, array $data, $field): bool
    {

        $rule = explode(',', $rule);

        if (false !== strpos($rule[0], '\\')) {
            // 指定模型类
            $db = new $rule[0];
        } else {
            try {
                $namespace = strval($rule[3] ?? '');
                $db = $this->model($rule[0], $namespace);

            } catch (ClassNotFoundException $e) {
                $db = Db::table($rule[0]);
            }
        }

        $key = $rule[1] ?? $field;
        $db = $db->where($key, $value);
        if (isset($rule[2]) && isset($data[$rule[2]])) {
            $db->where($rule[2], '<>', $data[$rule[2]]);
        }

        if ($db->exists()) {
            return true;
        }

        return false;
    }

}