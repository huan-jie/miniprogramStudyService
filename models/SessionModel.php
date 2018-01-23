<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Session 类，用于管理会话。
 * 由于小程序的机制，可以稍微弱化session的安全管理。(安全：HTTPOnly，关闭透明化SessionID，User-Agent验证，TOKEN校验，登录后重置id等)
 * session 目前考虑使用数据库存储，之后也可以考虑使用redis。
 */
class SessionModel extends Model
{
    public $max_life_time = 2592000;    // 过期时间：30天
    public $session_id = '';            // session id
    public $data = '';                  // session 存储内容

    function __construct($session_id, $data)
    {
        $this->session_id = $session_id;
        $this->data = $data;
    }

    // 生成session
    public function create_session()
    {
        // 存入session的微信session_key必须存在
        if (!$this->data) {
            return false;
        }

        $this->session_id = $this->gen_session_id();

        // 返回session_id
        return $this->insert_session() ? $this->session_id : '';
    }

    // 插入session
    private function insert_session()
    {
        $res = Yii::$app->db->createCommand()->insert('sessions', [
            'session_id' => $this->session_id,
            'expiry' => time() + $this->max_life_time,
            'data' => $this->data
        ])->execute();

        return $res;
    }

    /**
    * 用于用户进入小程序时检查session_id，成功时配置两种模式：
    * 模式一：返回服务器上存储的用户信息；
    * 模式二：仅返回session有效
    */
    public function check_session_id($needUserInfo = false)
    {
        $params = [
            ':session_id' => $this->session_id,
            ':expiry' => time()
        ];

        $res = Yii::$app->db->createCommand("SELECT * FROM sessions WHERE session_id = :session_id AND expiry > :expiry")
                            ->bindValues($params)
                            ->queryOne();

        if ($res) {
            // session 存在且未过期
            return $needUserInfo ? $this->data : true;
        } else {
            // session 不存在，或已经过期
            // TODO: 增加随机删除已过期 session 的处理
            return false;
        }
    }

    // 生成session_id：md5(), uniqid(), mt_rand()组合式
    private function gen_session_id()
    {
        $session_id = md5(uniqid(mt_rand(), true));

        return $session_id;
    }

    // 使用session_id来换取用户openid
    // 那个access_key如果以后有需要就把这里重构一下吧
    public function getOpenid()
    {
        $res = Yii::$app->db->createCommand("SELECT * FROM sessions WHERE session_id = :session_id")
                            ->bindParam(':session_id', $this->session_id)
                            ->queryOne();

        if ($res) {
            // 成功获取到session存储内容
            $sessionData = json_decode($res['data'], true);

            return $sessionData['openid'];
        } else {
            // 获取session内容失败
            return false;
        }
    }
}


 ?>
