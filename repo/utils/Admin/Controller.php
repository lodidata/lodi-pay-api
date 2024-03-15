<?php

namespace Utils\Admin;

use Admin\src\resource\ResponseFormatter;
use Awurth\SlimValidation\Validator;
use Exception;
use Logic\Define\Lang;
use Slim\Http\Response;
use Slim\Exception\SlimException;
use Utils\Client;

class Controller
{

    /**
     * 支持版本
     * @var [type]
     */
    public $supportVersions = ['v1', 'v2', 'v3'];

    /**
     * 默认版本号
     * @var string
     */
    public $defaultVersion = 'v1';

    /**
     * 目录
     * @var string
     */
    public $dir = 'entries';

    public $path;

    public $ci;

    public $obj;

    public $initObj;

    protected $newResponse;

    public function __construct($path, $ci)
    {
        $this->path = $path;
        $this->ci = $ci;
        $this->ci->db->getConnection('default');
    }

    public function withRes($status = 200, $state = 0, $message = '操作成功', $data = null, $attributes = null)
    {
        $website = $this->ci->get('settings')['website'];
        // 写入访问日志
        if (in_array($this->request->getMethod(), ['GET', 'POST', 'PUT', 'PATCH', "DELETE"])) {
            // 头 pl平台(pc,h5,ios,android) mm 手机型号 av app版本 sv 系统版本  uuid 唯一标识
            $headers = $this->request->getHeaders();
            if (isset($website['ALog']) && $website['ALog']) {
                $this->logger->info("ALog", [
                    'ip' => Client::getIp(),
                    'method' => $this->request->getMethod(),
                    'params' => $this->request->getParams(),
                    'httpCode' => $status,
                    // 'data' => $data,
                    'attributes' => $attributes,
                    'state' => $state,
                    'message' => $message,
                    'headers' => [
                        'pl' => isset($headers['pl']) ?? '',
                        'mm' => isset($headers['mm']) ?? '',
                        'av' => isset($headers['av']) ?? '',
                        'sv' => isset($headers['sv']) ?? '',
                        'uuid' => isset($headers['uuid']) ?? '',
                        'token' => isset($headers['HTTP_AUTHORIZATION']) ?? '',
                    ],
                    'cost' => round(microtime(true) - COSTSTART, 4)
                ]);
            }
        }
        if (is_array($attributes)) {
            isset($attributes['page']) && $attributes['page'] = (int)$attributes['page'];
            isset($attributes['page_size']) && $attributes['page_size'] = (int)$attributes['page_size'];
            isset($attributes['total']) && $attributes['total'] = (int)$attributes['total'];
        } else {
            isset($attributes->number) && $attributes->number = (int)$attributes->number;
            isset($attributes->size) && $attributes->size = (int)$attributes->size;
            isset($attributes->total) && $attributes->total = (int)$attributes->total;
        }
        $this->newResponse = $response = $this->response
            ->withStatus($status)
            ->withJson([
                'data' => $data ?: [],
                'attributes' => $attributes ?: [],
                'state' => $state,
                'message' => $message,
                // 'ts' => time(),
            ]);
        return $response;
    }

    /**
     * 解析url
     * @return array [type] [description]
     */
    protected function parseUri(): array
    {
        $uri = $this->request->getUri()->getPath();
        $uris = explode('/', $uri);

        $args = [];
        $uris2 = [];
        foreach ($uris as $v) {
            if (is_numeric($v)) {
                $args[] = $v;
            } else {
                $uris2[] = $v;
            }
        }

        // $uris2 = array_filter($uris2);
        if (empty($uris2)) {
            $uris2[] = $this->defaultVersion;
        } else {
            if (!in_array($uris2[1], $this->supportVersions)) {
                $uris2 = array_merge([$this->defaultVersion], $uris2);
            }
        }

        $dir = array_merge([$this->path, $this->dir], $uris2);
        $dir = join(DIRECTORY_SEPARATOR, $dir);
        $file = $dir . DIRECTORY_SEPARATOR . strtolower($this->request->getMethod()) . '.php';
        $succ = is_file($file);
        return [str_replace('//', '/', $dir), str_replace('//', '/', $file), $succ, $args];
    }

    /**
     * @throws Exception
     */
    public function run()
    {

        $website = $this->ci->get('settings')['website'];

        // 打印sql
        if (isset($website['DBLog']) && $website['DBLog']) {
            $this->db->getConnection()->enableQueryLog();
        }

        list($dir, $file, $succ, $args) = $this->parseUri();

        // 增加网页options请求
        if (strtolower($this->request->getMethod()) == 'options' && is_dir($dir)) {
            return $this->response
                ->withStatus(200)->write('Allow Method GET, POST, PUT, PATCH, DELETE');
        }

        if ($succ) {
            $this->obj = $obj = require $file;
            try {
                $obj->init($this->ci);
                $this->initObj = $obj;
                if (empty($args)) {
                    $res = $obj->run();
                } else {
                    $res = call_user_func_array([$obj, 'run'], $args);
                }
                // 写入sql
                if (isset($website['DBLog']) && $website['DBLog']) {
                    foreach ($this->db->getConnection()->getQueryLog() ?? [] as $val) {
                        $this->logger->info('DBLog', $val);
                    }
                }

            } catch (Exception $e) {
                if ($e instanceof SlimException) {
                    // This is a Stop exception and contains the response
                    return $this->newResponse = $e->getResponse();
                }
                return $this->withRes(500, -1, $e->getMessage());
            }
            $this->newResponse = $this->response;
            if ($res instanceof Validator || $res instanceof \Respect\Validation\Validator) {
                $errors = $res->getErrors();
                return $this->withRes(400, -4, current(current($errors)));
            } else if ($res instanceof Lang) {
                list($status, $state, $msg, $data, $attributes) = $res->get();
                return $this->withRes($status, $state, $msg, $data, $attributes);
            } else if (is_array($res) || is_string($res) || empty($res)) {
                return $this->withRes(200, 0, '操作成功', $res);
            } else if ($res instanceof Response) {
                return $res;
            } else if ($res instanceof ResponseFormatter) {
                $data = $res->toArray();
                return $this->withRes(200, 0, '操作成功', $data['data'], $data['attributes']);
            } else {
                return $this->withRes(500, -2, 'action not found error!');
            }
        } else {
            return $this->withRes(404, -3, 'action not found error!' . print_r([$dir, $file, $succ, $args, $this->request->getUri()->getPath()], true));
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

    public function __destruct()
    {
//        return true;
        //操作日志
        $this->request->getUri()->getPath();

    }

}