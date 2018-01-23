<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\UtilsModel;
use app\models\SessionModel;
use app\models\UserModel;

/**
 * miniprogram controller
 */
class MiniprogramController extends Controller
{

    public function actionIndex()
    {
        // TODO: miniprogram index

        echo "hello miniprogram!";
    }

    // 数据库连接测试
    public function actionDbtest()
    {
        $post = Yii::$app->db->createCommand('SELECT * FROM db_test')->queryAll();

        if ($post) {
            var_dump($post);
        }

        return $post;
    }

    // 微信用户登录：code换取session、openid
    public function actionLogin()
    {
        $code = $_REQUEST['code'];
        $appID = Yii::$app->params['miniprogram']['miniprogram_appid'];
        $appSecret = Yii::$app->params['miniprogram']['miniprogram_appsecret'];

        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=". $appID ."&secret=". $appSecret ."&js_code=". $code ."&grant_type=authorization_code";

        $utilsModel = new UtilsModel();

        $sessionData = $utilsModel->curlGet($url);

        $sessionModel = new SessionModel(null, $sessionData);

        $sessionId = $sessionModel->create_session();

        if ($sessionId) {
            $output = [
                'code' => 0,
                'desc' => '',
                'data' => ['session_id' => $sessionId],
            ];

            return json_encode($output);
        } else {
            // 返回的session_id为空，插入数据库失败，妈的暂时不想处理
        }
    }

    // 检查自主管理的session是否过期
    public function actionCheckSession()
    {
        $sessionId = $_REQUEST['session_id'];

        $sessionModel = new SessionModel($sessionId, null);

        if ($sessionModel->check_session_id()) {
            $output = [
                'code' => 0,
                'desc' => '',
            ];

            return json_encode($output);
        } else {
            $output = [
                'code' => Yii::$app->params['errorCode']['session_timeout'],
                'desc' => 'session timeout',
            ];

            return json_encode($output);
        }
    }

    // 保存或更新用户信息
    public function actionSaveUserinfo()
    {
        $sessionId = $_REQUEST['session_id'];
        $userinfo = json_decode($_REQUEST['userinfo'], true);

        $sessionModel = new SessionModel($sessionId, null);
        $openid = $sessionModel->getOpenid();

        if ($openid) {
            $userModel = new UserModel($openid, $userinfo);

            if ($userModel->saveUserinfo()) {
                // 成功
                $output = [
                    'code' => 0,
                ];

                return json_encode($output);
            } else {
                // 失败
            }
        } else {
            // 返回openid获取错误
        }
    }
}


 ?>
