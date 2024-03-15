<?php

use Logic\Admin\BaseController;
use Model\TagModel;
use Admin\src\resource\TagFormatter;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run()
    {
        $row = TagModel::filter($this->request->getParams())->with(['creator'])->withCount('userTag')->latest('id')->paginate($this->pageSize);
        return TagFormatter::make($row);
    }
};