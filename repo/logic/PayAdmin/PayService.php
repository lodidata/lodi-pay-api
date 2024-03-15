<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Logic\PayAdmin;


use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Model\PayConfigModel;
use GuzzleHttp\Client;

class PayService
{
    private $row;

    public function __construct($payMethod = 'RPAY')
    {
        //获取支付方式
        $this->row = PayConfigModel::query()->where('name', $payMethod)->firstOrFail();
    }


    /**
     * 请求接口
     * @param string $method
     * @param string $apiName
     * @param array $params
     * @return mixed
     * @throws GuzzleException
     * @throws Exception
     */
    protected function client(string $method, string $apiName, array $params)
    {
        $url = $this->getUrl($apiName);
        if (!$url) {
            throw new Exception('没有对应的接口,请查看文档');
        }
        $options = $this->getOptions($method, $params);

        $client = new Client(['verify' => false]);
        try {
            $res = $client->request($method, $url, $options);
            $result = json_decode($res->getBody()->getContents(), true);
        } catch (Exception $e) {
            throw new Exception($e);
        }
        return $result;
    }

    /**
     * 获取支付接口名称
     * @param $apiName
     * @return string
     */
    private function getUrl($apiName): string
    {
        $url = '';
        $row = $this->row;
        /**@var PayConfigModel $row */
        switch ($apiName) {
            case 'pay_half':
                //代付交易接口
                $url = $row->payurl . '/api/daifu';
                break;
            case 'me':
                //余额查询接口
                $url = $row->payurl . '/api/me';
                break;
            case 'query':
                //单笔交易查询接口
                $url = $row->payurl . '/api/query';
                break;
            case 'transfer':
                //支付交易接口
                $url = $row->payurl . '/api/transfer';
                break;
        }
        return $url;
    }

    /**
     * 获取签名
     * @param array $params
     * @return string
     */
    private function getSign(array $params): string
    {
        $str = '';
        ksort($params);
        foreach ($params as $paramKey => $paramValue) {
            $str .= $paramKey . '=' . $paramValue . '&';
        }
        $row = $this->row;
        /**@var PayConfigModel $row */
        $str .= 'key=' . $row->key;
        return md5($str);
    }

    private function getOptions(string $method, array $params): array
    {
        $row = $this->row;
        /**@var PayConfigModel $row */
        $params['merchant'] = $row->partner_id;
        $params['callback_url'] = $row->pay_callback_domain;
        $params['sign'] = $this->getSign($params);
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

}