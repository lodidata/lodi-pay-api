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
       $row = TagModel::select(['id','name'])->filter($this->request->getParams())->latest('id')->get();
       return TagFormatter::make($row);
    }
};