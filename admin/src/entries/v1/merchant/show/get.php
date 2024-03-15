<?php

use Admin\src\resource\MerchantFormatter;
use Logic\Admin\BaseController;
use Model\MerchantModel;

return new class extends BaseController{
    public function run($id)
    {
      $row = MerchantModel::findOrFail($id);
      return $this->response->withJson(MerchantFormatter::make($row)->toArray());
    }
};