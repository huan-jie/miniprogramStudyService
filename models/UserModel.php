<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * 用户类，处理用户信息
 */
class UserModel extends Model
{
    public $openid;
    public $userinfo;       // 定义为数组来使用，数据判断什么的再说吧

    function __construct($openid, $userinfo)
    {
        $this->openid = $openid;
        $this->userinfo = $userinfo;
    }

    // 保存或更新用户信息
    public function saveUserinfo()
    {
        $res = Yii::$app->db->createCommand("SELECT * FROM users WHERE openid = :openid")
                            ->bindParam(':openid', $this->openid)
                            ->queryOne();

        if ($res) {
            // 用户存在，更新用户信息
            // 注意，当用户信息没有更新时，返回的结果是false
            $res = Yii::$app->db->createCommand()->update('users', [
                'openid' => $this->openid,
                'nickname' => $this->userinfo['nickName'],
                'avatarUrl' => $this->userinfo['avatarUrl'],
                'gender' => $this->userinfo['gender'],
                'city' => $this->userinfo['city'],
                'province' => $this->userinfo['province'],
                'country' => $this->userinfo['country'],
                'language' => $this->userinfo['language']
            ])->execute();

            if ($res) {
                return true;
            } else {
                return false;
            }
        } else {
            // 用户不存在，插入新用户
            $res = Yii::$app->db->createCommand()->insert('users', [
                'openid' => $this->openid,
                'nickname' => $this->userinfo['nickName'],
                'avatarUrl' => $this->userinfo['avatarUrl'],
                'gender' => $this->userinfo['gender'],
                'city' => $this->userinfo['city'],
                'province' => $this->userinfo['province'],
                'country' => $this->userinfo['country'],
                'language' => $this->userinfo['language']
            ])->execute();

            if ($res) {
                return true;
            } else {
                return false;
            }
        }
    }
}


 ?>
