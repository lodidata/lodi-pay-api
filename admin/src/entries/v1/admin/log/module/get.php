<?php

use Logic\Admin\Log;
use Logic\Admin\BaseController;

return new class extends BaseController {
    protected $beforeActionList = [
//        'verifyToken', 'authorize'
    ];

    public function run()
    {
        return Log::MODULE_NAME;
    }
};