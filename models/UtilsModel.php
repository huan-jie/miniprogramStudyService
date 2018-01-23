<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * 工具类，主要提供以下功能:
 * 1. curl: get, post 请求，之后再考虑合并，通过传参做设置
 * 2. 返回值为原始参数，具体格式之后再处理
 */
class UtilsModel extends Model
{
    // curl get
    public function curlGet($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
        // return json_decode($output, $arrayFlag);
    }

    // curl post
    public function curlPost($url, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);

        curl_close($ch);

        return json_decode($output, true);
    }
}


 ?>
