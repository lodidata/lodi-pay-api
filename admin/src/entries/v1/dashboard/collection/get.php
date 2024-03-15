<?php

use Logic\Admin\BaseController;
use Service\DashboardService;
use Model\OrdersCollectionModel;

//代充统计
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        try {
            $params = $this->request->getParams();
            $service = new DashboardService($params);

            return $service->dataCount((new OrdersCollectionModel())->getTable());
        }catch (\Exception $exception){
            throw $exception;
        }

    }
};