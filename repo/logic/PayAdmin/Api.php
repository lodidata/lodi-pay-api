<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Logic\PayAdmin;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Logic\Logic;
use Slim\Container;

/**
 * @method adminConfigSync() 系统配置同步
 * @method payConfigSync(array $data) 第三方支付配置同步
 * @method reject(array $data) 提款订单-驳回
 * @method uploadCert(array $data) 充值订单-订单上传凭证
 */
class Api extends Logic
{
    private $privateKey = '35Gu7aOaXT12rFF4qYZwb316JNi2o1vq';
    private $api;

    const STATUS_SUCCESS = 6;
    const STATUS_FAIL = 9;

    //充值单
    const ORDER_TYPE_COLLECT = 1;
    //提现单
    const ORDER_TYPE_PAY = 2;

    public function __construct(Container $ci)
    {
        parent::__construct($ci);
        $this->api = $this->ci->get('settings')['web_api']['value'] ?: 'https://pay-api.caacaya.com';
    }

    /**
     * @throws GuzzleException
     */
    public function __call(string $funName, array $arguments)
    {
        $method = 'POST';
        $options = $this->getOptions($method, $arguments[0] ?? []);

        $url = $this->getUrl($funName);
        return $this->getResult($method, $options, $url);
    }

    /**
     * 修改订单状态
     * @return mixed
     * @throws Exception
     * @throws GuzzleException
     */
    public function changeStatus(string $innerOrderSn, int $status)
    {

        $method = 'POST';
        $params['inner_order_sn'] = $innerOrderSn;
        $params['status'] = $status;
        $options = $this->getOptions($method, $params);

        $url = $this->getUrl('change_status');
        return $this->getResult($method, $options, $url);
    }

    /**
     * 发起代付
     * @return mixed
     * @throws Exception
     * @throws GuzzleException
     */
    public function pay(array $params)
    {

        $method = 'POST';
        $options = $this->getOptions($method, $params);

        $url = $this->getUrl('pay');
        return $this->getResult($method, $options, $url);
    }

    /**
     * 修改商户信息
     * @return mixed
     * @throws Exception
     * @throws GuzzleException
     */
    public function changeMerchant(array $params)
    {

        $method = 'POST';
        $options = $this->getOptions($method, $params);

        $url = $this->getUrl('change_merchant');
        return $this->getResult($method, $options, $url);
    }

    private function getOptions(string $method, array $params): array
    {
        $params['sign'] = $this->getSign($params, $this->privateKey);
        switch ($method) {
            case 'POST':
                $list = ['form_params' => $params];
                break;
            case 'JSON':
                $list = ['json' => $params];
                break;
            default:
                $list = $params;
        }
        return $list;
    }


    private function getSign(array $data, $privateKey): string
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        ksort($data);

        $str = '';
        foreach ($data as $k => $v) {
            if (is_null($v) || $v === '') continue;
            $str .= $k . '=' . $v . '&';
        }
        $str = trim($str, '&');

        return md5(md5($str) . $privateKey);
    }

    private function getUrl(string $type): string
    {
        switch ($type) {
            case 'change_status':
                $url = $this->api . '/api/backend/order/change_status';
                break;
            case 'pay':
                $url = $this->api . '/api/backend/order/pay';
                break;
            case 'change_merchant':
                $url = $this->api . '/api/backend/sync_cache/merchant';
                break;
            case 'adminConfigSync':
                $url = $this->api . '/api/backend/sync_cache/admin_config';
                break;
            case 'payConfigSync':
                $url = $this->api . '/api/backend/sync_cache/pay_config';
                break;
            case 'merchantSecretSync':
                $url = $this->api . '/api/backend/sync_cache/merchant_secret';
                break;
            case 'reject':
                $url = $this->api . '/api/backend/order/reject';
                break;
            case 'uploadCert':
                $url = $this->api . '/api/backend/order/upload_cert';
                break;
            default:
                $url = '';
        }
        return $url;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function getResult(string $method, array $options, string $url)
    {
        $client = new Client(['verify' => false]);
        try {
            $res = $client->request($method, $url, $options);
            $result = json_decode($res->getBody()->getContents(), true);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $result;
    }

}