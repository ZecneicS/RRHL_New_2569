<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
//use yii\filters\AccessControl;
//use yii\filters\VerbFilter;
use app\models\SocialAccount;

class AuthController extends Controller {

    /** @inheritdoc */
//    public function behaviors() {
//        return [
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'disconnect' => ['post'],
//                    'delete' => ['post'],
//                    'delete-social' => ['post'],
//                ],
//            ],
//            'access' => [
//                'class' => AccessControl::className(),
//                'rules' => [
//                    [
//                        'allow' => true,
//                        'actions' => ['profile', 'account', 'networks', 'disconnect', 'delete', 'balance', 'line_connect', 'line_callback', 'line','delete-social'],
//                        'roles' => ['@'],
//                    ],
//                    [
//                        'allow' => true,
//                        'actions' => ['confirm'],
//                        'roles' => ['?', '@'],
//                    ],
//                ],
//            ],
//        ];
//    }

    public function actionLine_connect() {
        return $this->render('line_connect');
    }

    public function actionLine_callback() {
        if (isset($_GET["code"])) {
            try {
                // Step 1. GET Access Token 
                $post_data = [
                    "grant_type" => 'authorization_code',
                    "client_id" => '1655102675',
                    "client_secret" => '79cd8ade8c1b326a59af09831b392f52',
                    "code" => $_GET["code"],
                    "redirect_uri" => 'https://region4.anamai.moph.go.th/office/user/auth/line_callback'
                ];

                $headers[] = "Content-Type: application/x-www-form-urlencoded";

                $url = "https://api.line.me/oauth2/v2.1/token";
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = json_decode(curl_exec($ch), true);
                curl_close($ch);

//                print_r($response);
                // Step 2. GET User Profile
                $accessToken = $response['access_token'];
                if (!empty($accessToken)) {
                    $headerData = [
                        "content-type: application/x-www-form-urlencoded",
                        "charset=UTF-8",
                        'Authorization: Bearer ' . $accessToken,
                    ];

                    $ch2 = curl_init();
                    curl_setopt($ch2, CURLOPT_HTTPHEADER, $headerData);
                    curl_setopt($ch2, CURLOPT_URL, "https://api.line.me/v2/profile");
                    curl_setopt($ch2, CURLINFO_HEADER_OUT, true);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
                    $result = curl_exec($ch2);
                    $httpcode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                    curl_close($ch2);

                    $result = json_decode($result, true);

                    if ($httpcode == 200) {

                        $data = [
                            'userId' => $result['userId'],
                            'name' => $result['displayName'],
                            'picture' => $result['pictureUrl'],
                        ];

                        // ตรวจสอบว่ามีการผูก Line นี้หรือเปล่า
                        $socialUsed = $this->checkSocialUsed($result['userId'], 'line');
                        if ($socialUsed) {
                            $identity = \app\models\SsoUser::findOne($socialUsed['user_id']);
                            Yii::$app->user->login($identity, 1209600);

                            return $this->redirect(['/site/index']);
                        } else {
                            //  ไม่พบ User ที่เชื่อมกับ Line นี้
                            // Set Flash message
                            $message = 'ไม่พบผู้ใช้งานที่เชื่อมกับ Line Account นี้';
                            Yii::$app->session->setFlash('error', $message);
                            return $this->redirect(['/user/login']);
                        }
                    } else {
                        // redirect status faild
                    }
                }
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
        return $this->redirect(['/user/login']);
    }

    public function getSocialAcc($user_id, $provider) {
        $social = SocialAccount::findOne(['user_id' => $user_id, 'provider' => $provider]);
        return $social;
    }

    public function checkSocialUsed($client_id, $provider) {
        $used = SocialAccount::findOne(['client_id' => $client_id, 'provider' => $provider]);
        return $used;
    }

}
