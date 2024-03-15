<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Logic\PayAdmin;


use GuzzleHttp\Exception\GuzzleException;

class PayApi extends PayService
{


    /**
     * 代付
     * @param string $apiName
     * @param array $params
     * @return mixed
     * @throws GuzzleException
     */
    public function payBehalf(string $apiName, array $params)
    {
        return $this->client('POST', $apiName, $params);
    }


}