<?php
use Respect\Validation\Validator as V;
use Logic\Admin\AdminToken;
use Model\AdminAuthModel;

V::with('Utils\\Validation\\Rules\\');

//auth校验
//$app->add(function ($req, $res, $next) use ($app) {
//    $response = $next($req, $res);
//    $pathAuthExclude = [
//        '/admin/login',
//        '/admin/logout',
//        '/admin/login/code'
//    ];
//    $path = $req->getUri()->getPath();
//    if (in_array($path, $pathAuthExclude)) {
//        return $response;
//    }
//    //校验 token
//    $jwt = new AdminToken($app->getContainer());
//    $jwt->verifyToken();
//
//    global $playLoad;
//    $admin_id = $playLoad['admin_id'];
//    $routes = $playLoad['routes'];
//    //查询 path id
//    $auth = AdminAuthModel::where('path' , $path)->select(['id'])->first();
//    if(null === $auth){
//        throw  new \Exception('无此权限' , 401);
//    }
//
//    if ( $admin_id > 1 && !in_array($auth->id , $routes)) {
//        throw  new \Exception('无此权限' , 401);
//    }
//
//   return $response;
//});
