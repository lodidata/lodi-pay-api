<?php

use Logic\Admin\BaseController;
use Service\DashboardService;
use Model\OrdersPayModel;

//代付统计
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        try {
            $params = $this->request->getParams();
            $service = new DashboardService($params);

            return $service->dataCount((new OrdersPayModel())->getTable());
        }catch (\Exception $exception){
            throw $exception;
        }

    }
};