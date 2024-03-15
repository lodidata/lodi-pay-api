<?php

use Logic\Admin\BaseController;
use Service\DashboardService;

//争议订单统计
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        try {
            $params = $this->request->getParams();
            $service = new DashboardService($params);

            return $service->disputeCount();
        }catch (\Exception $exception){
            throw $exception;
        }

    }
};