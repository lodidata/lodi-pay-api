<?php

use Logic\Admin\BaseController;
use Model\UserModel;
use Admin\src\resource\UserFormatter;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        $row = UserModel::filter($this->request->getParams())->with([
            'tags' => function ($query) {
                return $query->select(['tag.id', 'name']);
            }, 'merchant' => function ($query) {
                return $query->select(['merchant.id', 'name', 'account']);
            }
        ])->latest('id')->paginate($this->pageSize);
        return UserFormatter::make($row);
    }
};