<?php

use Logic\Admin\BaseController;
use Admin\src\resource\SettingsFormatter;
use Model\SettingsModel;

//列表
return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    public function run(): SettingsFormatter
    {
        $row = SettingsModel::query()->orderBy('sort')->orderByDesc('id')->paginate($this->pageSize);
        return SettingsFormatter::make($row);
    }
};