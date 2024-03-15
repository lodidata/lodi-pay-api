<?php

namespace Utils;

class App{
    private  static  $app;

    public static function setApp($app)
    {
        static::$app = $app;
    }

    public static function getApp()
    {
        if (!static::$app) {
            throw new \Exception('app not found');
        }

        return static::$app;
    }

    public static function getContainer()
    {
        return (static::getApp())->getContainer();
    }
}