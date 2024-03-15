<?php

namespace Utils;


use Logic\Logic;
use GuzzleHttp\Client;
use Logic\Define\CacheKey;
use GuzzleHttp\Exception\ClientException;

/**
 *  获取对应的第三方游戏实例
 * @author lwx
 *
 */
class Logs extends Logic
{

    static $LOG = LOG_PATH;
    public $nameSpace = 'Logic\Game\Third';
    protected $gameType = '';

    public function init($gameType = '', $namespace = '')
    {
        if (!$gameType)
            return;

        $this->gameType  = $gameType;
        $this->nameSpace = $namespace ? $namespace : $this->nameSpace;
    }


    public function callback($action = 'login')
    {
        return $this->$action();
    }

    public static function addRequestLog(string $url, string $path, array $req, string $resp)
    {
        $data = [
            'url'      => $url,
            'request'  => $req,
            'response' => $resp,
        ];
        self::addElkLog($data, $path);
    }

    public static function addElkLog($data, $path = 'game', $filename = '')
    {
        if (!is_dir(self::$LOG . $path) && !mkdir(self::$LOG . $path, 0777, true)) {
            $path = '';
        }

        $file   = self::$LOG . $path . '/' . ($filename ? $filename . '-' : '')  . date('Y-m-d') . '.log';

        $stream = @fopen($file, "aw+");
        if (isset($data['response']) && !is_array($data['response']) && self::is_json($data['response'])) {
            $data['response'] = json_decode($data['response'], true);
        }
        $str = '[ ' . date('Y-m-d H:i:s') . ' ] ' . urldecode(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . PHP_EOL;
        @fwrite($stream, $str);
        @fclose($stream);
    }

    public static function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * XML解析成数组
     */
    public function parseXML($xmlSrc)
    {
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $xmlSrc, true)) {
            xml_parser_free($xml_parser);
            return $xmlSrc;
        }
        $xml  = simplexml_load_string($xmlSrc);
        $data = $this->xml2array($xml);
        return $data;
    }

    public function xml2array($xmlobject)
    {
        if ($xmlobject) {
            foreach ((array)$xmlobject as $k => $v) {
                $data[$k] = !is_string($v) ? $this->xml2array($v) : $v;
            }
            return $data;
        }
    }

}
