<?php

use Logic\Admin\BaseController;
use Model\AdminLogModel;
use Model\TagModel;
use Model\UserModel;
use Logic\Admin\Log;

return new class extends BaseController {
    protected $beforeActionList = [
        'verifyToken', 'authorize'
    ];

    protected $module = Log::MODULE_USER;
    protected $moduleChild = '用户管理';
    protected $moduleFunName = '修改用户';

    public function run($id)
    {
        $params = $this->request->getParams();
        /**@var UserModel $row * */
        $row = UserModel::query()->findOrFail($id);
        $result = true;
        DB::pdo()->beginTransaction();
        try {
            if (!empty($params['tags'])) {
                $tags = array_unique($params['tags']);
                //判断 tag 是否存在
                $originTags = TagModel::query()->whereIn('id', $tags)->pluck('id')->toArray();
                if (count($originTags) != count($tags)) {
                    $diff = implode(',', array_diff($tags, $originTags));
                    throw new Exception('tag id为' . $diff . '的数据不存在');
                }
                $row->tagRelation()->delete();
                foreach ($tags as $tag_id) {
                    $row->tagRelation()->create(compact('tag_id'));
                }
            }
            $row->update($params);
            Db::pdo()->commit();
        } catch (Exception $e) {
            Db::pdo()->rollBack();
            $result = false;
        }
        $logArr = [
            'status' => $result ? AdminLogModel::STATUS_ON : AdminLogModel::STATUS_OFF,
            'record' => $params,
            'remark' => '【' . $this->playLoad['admin_name'] . '】修改用户【' . $row->username . '】信息',
        ];
        $this->writeAdminLog($logArr);

        return $this->lang->set($result ? 0 : -2);
    }
};