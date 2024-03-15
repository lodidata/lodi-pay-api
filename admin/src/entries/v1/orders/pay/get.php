<?php

use Admin\src\resource\OrderPayFormatter;
use Logic\Admin\BaseController;
use Service\OrdersService;

return new class() extends BaseController {

    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): OrderPayFormatter
    {
        $params = $this->request->getParams();
        $service = new OrdersService($params);

        return OrderPayFormatter::make($service->dataList());

    }
};