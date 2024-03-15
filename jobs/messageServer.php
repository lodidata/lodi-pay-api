<?php /** @noinspection PhpObjectFieldsAreOnlyWrittenInspection */

/** @noinspection PhpUndefinedFieldInspection */

use Logic\Admin\FinancialStatementsLogic;
use Slim\App;
use Workerman\Lib\Timer;

require __DIR__ . '/../repo/vendor/autoload.php';
$alias = 'payMessageServer';


$worker = new \Workerman\Worker();
$worker->count = 2;
$worker->name = $alias;

// 防多开配置
// if ($app->getContainer()->redis->get(\Logic\Define\CacheKey::$perfix['prizeServer'])) {
//     echo 'prizeServer服务已启动，如果已关闭, 请等待5秒再启动', PHP_EOL;
//     exit;
// }

$worker->onWorkerStart = function ($worker) {
    global $app, $logger;
    /**********************config start*******************/
    $settings = require __DIR__ . '/../config/settings.php';
    \Workerman\Worker::$logFile = LOG_PATH . '/php/messageSever.log';

    $app = new App($settings);
    // Set up dependencies
    require __DIR__ . '/src/dependencies.php';

    // Register middleware
    require __DIR__ . '/src/middleware.php';


    $app->run();
    $app->getContainer()->db->getConnection('default');
    $logger = $app->getContainer()->logger;
    /**********************config end*******************/


    $processId = 0;

    //定时任务充值
    if ($worker->id === $processId) {
        $interval = 120;
        Timer::add($interval, function () use (&$app) {
//            $app->getContainer()->logger->debug("【查询代付结果】");
            $ids = DB::table('merchant_collection_balance')
                ->where('is_auto', '=', 1)
                ->whereColumn('balance', '>', 'limit_amount')
                ->latest('id')
                ->pluck('id');
            //充值
            $transfer = new  Logic\Admin\PayBalanceLogic();
            foreach ($ids as $v) {
                $transfer->chargeBalance($v);
            }
        });
    }

    //定时任务统计商户数据到financial_statements表中-每30分钟执行
    if ($worker->id === $processId) {
        $interval = 1800;
        Timer::add($interval, function () use (&$app) {
            (new FinancialStatementsLogic)->index();
        });
    }
};
\Workerman\Worker::runAll();
