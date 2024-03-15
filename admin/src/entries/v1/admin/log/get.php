<?php


use Admin\src\resource\AdminLogFormatter;
use Logic\Admin\BaseController;
use Model\AdminLogModel;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        $row = AdminLogModel::filter($this->request->getParams())->latest('id')->paginate($this->pageSize);
        return AdminLogFormatter::make($row);
    }
};