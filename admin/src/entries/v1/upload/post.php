<?php /** @noinspection PhpUnused */
/** @noinspection PhpParamsInspection */

// 引入鉴权类
use Aws\Command;
use Qiniu\Auth;

// 引入上传类
use Qiniu\Storage\UploadManager;

use OSS\OssClient;
use Slim\Http\UploadedFile;
use Logic\Admin\BaseController;

return new class extends BaseController {
    const TYPE = "multipart/form-data";


    protected $beforeActionList = [
        'verifyToken',
        'authorize'
    ];

    public function run()
    {
        ini_set('max_execution_time', '200');
        if (empty($_FILES) || !isset($_FILES['file'])) {
            return $this->lang->set(10010);
        }
        $settings = $this->ci->get('settings')['upload'];
        $name_type = $this->request->getParam('name_type', 1);
        $dir = trim($this->request->getParam('dir', ''), '/');
        $file = $_FILES['file'];

        if ($name_type == 1) {
            $fileName = $this->getUploadName($file);
        } else {
            $fileName = $file['name'];
        }

        //验证文件类型及文件大小
        $type = explode('/', $file['type']);
        if ($type[0] == 'image') {
            $fileSetting = $settings['image'];
        } else {
            $fileSetting = $settings['file'];
        }

        if ($file['size'] > $fileSetting['size']) {
            return $this->lang->set(11022);
        }
        $temp = explode('.', $file['name']);
        $fileExt = strtolower(end($temp));
        if ('jpeg' == $fileExt) {
            $fileExt = 'jpg';
        }
        if (!in_array($fileExt, explode(',', $fileSetting['ext']))) {
            return $this->lang->set(11023);
        }

        $obj = $settings['useDsn'];
        $res = $this->$obj($settings['dsn'][$obj], $file, $fileName, $dir);
        return empty($res) ? $this->lang->set(10015) : $res;
    }

    /**
     * 七牛上传
     * @param $config
     * @param $file
     * @param $fileName
     * @return mixed [type]         [description]
     */
    protected function qiniu($config, $file, $fileName)
    {
        // 构建鉴权对象
        $auth = new Auth($config['accessKey'], $config['secretKey']);
        // 生成上传 Token
        $token = $auth->uploadToken($config['bucket']);
        $key = $config['dir'] . '/' . $fileName;
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list(, $err) = $uploadMgr->putFile($token, $key, $file['tmp_name']);
        if ($err !== null) {
            return $this->lang->set(886, ['local:' . json_encode($err)], [], ['error' => 'qiniu:' . print_r($err, true)]);
        } else {
            return $this->lang->set(0, [], ['url' => $config['domain'] . '/' . $key]);
        }
    }

    /**
     * 阿里云OSS上传
     * @param $config
     * @param $file
     * @param $fileName
     * @param $dir
     * @return mixed [type]         [description]
     */
    protected function oss($config, $file, $fileName, $dir)
    {
        $ossClient = new OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);

        $object = $config['dir'] . '/' . $dir . '/' . $fileName;
        $object = str_replace('//', '/', $object);
        $content = file_get_contents($file['tmp_name']);
        $ossClient->putObject($config['bucket'], $object, $content);
        return $this->lang->set(0, [], ['url' => $config['domain'] . '/' . $object]);
    }

    /**
     * 本地上传
     * @param array $config 配置项
     * @param array $file 上传的文件信息
     * @param string $fileName 目录文件
     * @return array
     */
    protected function local(array $config, array $file, string $fileName): array
    {
        try {
            if (!is_dir($config['imgDir'] . '/' . $config['dir']) && !mkdir($config['imgDir'] . '/' . $config['dir'], 0755, true)) {
                throw new Exception($config['dir'] . ' 目录不存在');
            }
            $object = $config['imgDir'] . '/' . $config['dir'] . '/' . $fileName;
            $upload = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error'], true);
            $upload->moveTo($object);
            return $this->lang->set(0, [], ['url' => $config['domain'] . '/' . $config['dir'] . '/' . $fileName]);
        } catch (Exception $e) {
            return $this->lang->set(15, [], [], ['error' => 'local":' . $e->getMessage()]);
        }
    }

    /**
     * AWS S3上传文件
     * @param array $config S3配置
     * @param array $file
     * @param string $fileName 文件名称
     * @return mixed
     */
    protected function awsS3(array $config, array $file, string $fileName)
    {
        //设置超时
        set_time_limit(0);
        //证书 AWS access KEY ID  和  AWS secret  access KEY 替换成自己的
        $credentials = new Aws\Credentials\Credentials($config['accessKeyId'], $config['accessKeySecret']);
        //s3客户端
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            //地区 亚太区域（新加坡）    AWS区域和终端节点： http://docs.amazonaws.cn/general/latest/gr/rande.html
            'region' => $config['region'],
            //加载证书
            'credentials' => $credentials,
            //开启bug调试
            'debug' => $config['debug']
        ]);

        //存储桶 获取AWS存储桶的名称
        //需要上传的文件
        $source = $file['tmp_name']; //ROOT_PATH项目根目录，文件的本地路径例:D:/www/abc.jpg;
        $object = $config['dir'] . '/' . $fileName;
        //多部件上传
        $uploader = new Aws\S3\MultipartUploader($s3, $source, [
            //存储桶
            'bucket' => $config['bucket'],
            //上传后的新地址
            'key' => $object,
            //设置访问权限  公开,不然访问不了
            'ACL' => 'public-read',
            //分段上传
            'before_initiate' => function (Command $command) {
                // $command is a CreateMultipartUpload operation
                $command['CacheControl'] = 'max-age=3600';
            },
            'before_upload' => function (Command $command) {
                // $command is an UploadPart operation
                $command['RequestPayer'] = 'requester';
            },
            'before_complete' => function (Command $command) {
                // $command is a CompleteMultipartUpload operation
                $command['RequestPayer'] = 'requester';
            },
        ]);

        try {
            $uploader->upload();
            return $this->lang->set(0, [], ['url' => '/' . $object]);
        } catch (Aws\Exception\MultipartUploadException $e) {
            return $this->lang->set(15, [], [], ['error' => 's3":' . $e->getMessage()]);
        }
    }


    /**
     * 取得上传后文件名称
     * @param  [type] $fileName [description]
     * @return string [type]           [description]
     */
    protected function getUploadName($file): string
    {
        $temp = explode('.', $file['name']);
        $fileExt = strtolower(end($temp));
        return md5(time() . mt_rand(0, 999999)) . '.' . $fileExt;
    }

    /**
     * 移除临时文件
     * @param  [type] $file [description]
     * @return bool [type]       [description]
     */
    protected function remove($file): bool
    {
        return @unlink($file['tmp_name']);
    }
};
