<?php

namespace app\controllers;

use Yii;
use dektrium\user\controllers\SettingsController as BaseSettingsController;
// use dektrium\user\Finder;
use dektrium\user\models\Profile;
use dektrium\user\models\SettingsForm;
use yii\bootstrap4\ActiveForm;
// use dektrium\user\models\User;
// use dektrium\user\Module;
// use dektrium\user\traits\AjaxValidationTrait;
// use dektrium\user\traits\EventTrait;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
// use yii\web\Controller;
// use yii\web\ForbiddenHttpException;
// use yii\web\NotFoundHttpException;
use app\models\SocialAccount;
use app\modules\manage\models\UploadPhoto;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use app\components\GoogleAuthenticator;

class SettingsController extends BaseSettingsController {

    public function actionBalance() {
        $balance = ( $balance = Yii::$app->user->identity->profile->balance) == null ? 0 : $balance;
        return $this->render('balance', [
                    'balance' => $balance
        ]);
    }

    /** @inheritdoc */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'disconnect' => ['post'],
                    'delete' => ['post'],
                    'delete-social' => ['post'],
                	//'two-factor' => ['post']
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['profile', 'account', 'networks', 'disconnect', 'delete', 'balance', 'line_connect', 'line_callback', 'line', 'delete-social', 'photo','profile_photo','pwchange', 'two-factor', 'disable-2fa'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['confirm'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionProfile() {
        $model = $this->finder->findProfileById(\Yii::$app->user->identity->getId());

        if ($model == null) {
            $model = \Yii::createObject(Profile::className());
            $model->link('user', \Yii::$app->user->identity);
        }

        $event = $this->getProfileEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Your profile has been updated'));
            $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('profile', [
                    'model' => $model,
                    'line_connect' => $this->getSocialAcc(\Yii::$app->user->identity->getId(), 'line'),
        ]);
    }

    public function actionPhoto()
    {
        $model = $this->finder->findProfileById(\Yii::$app->user->identity->getId());
        $photo = $model->gravatar_id;

        return $this->render('photo', [
            'photo' => $photo,
            'line_connect' => $this->getSocialAcc(\Yii::$app->user->identity->getId(), 'line'),
        ]);
    }

    public function actionProfile_photo()
    {
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('photo');
            $id = Yii::$app->request->post('id');
            //$photo_id = Yii::$app->request->post('photo_id');

            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);

            $file = date('Ymd') . '-' . md5(time()) . '.png';
            $photo_path = Yii::getAlias('@uploads') . '/profile/';
            
            $image_name = $photo_path . $file;

            file_put_contents($image_name, $data);

            $currFile = Yii::$app->user->identity->profile->gravatar_id;

            if (is_file($currFile)) {
                $file_delete = $photo_path . $currFile->filename;
                @unlink($file_delete);
            }

            $profile = $this->finder->findProfileById($id);

            $profile->gravatar_id = $file;
            $profile->save(false);

            return true;
        } else {
            return '0000';
        }
    }

    public function actionAccount() {
        /** @var SettingsForm $model */
        $model = \Yii::createObject(SettingsForm::className());
        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_ACCOUNT_UPDATE, $event);
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('user', 'Your account details have been updated'));
            $this->trigger(self::EVENT_AFTER_ACCOUNT_UPDATE, $event);
            return $this->refresh();
        }

        return $this->render('account', [
                    'model' => $model,
                    'line_connect' => $this->getSocialAcc(\Yii::$app->user->identity->getId(), 'line'),
        ]);
    }

    public function actionPwchange() {
        if (!Yii::$app->user->identity->must_change_pw) {
            $this->goHome();
        }
        /** @var SettingsForm $model */
        $model = \Yii::createObject(SettingsForm::className());
        //$model->scenario = 'pwchange';
        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_ACCOUNT_UPDATE, $event);
        if ($model->load(\Yii::$app->request->post()) && $model->pwchange()) {
            \Yii::$app->session->setFlash('success', \Yii::t('user', 'Your account details have been updated'));
            $this->trigger(self::EVENT_AFTER_ACCOUNT_UPDATE, $event);

            Yii::$app->getUser()->logout();

            return $this->redirect(['/user/login']);
            //return $this->refresh();
        }

        return $this->render('pwchange', [
                    'model' => $model,
                    //'line_connect' => $this->getSocialAcc(\Yii::$app->user->identity->getId(), 'line'),
        ]);
    }

    public function actionLine() {
        $line = $this->getSocialAcc(\Yii::$app->user->identity->getId(), 'line');

        return $this->render('line', [
                    'line_connect' => $line,
        ]);
    }

    public function actionLine_connect() {
        return $this->render('line_connect');
    }

    public function actionDeleteSocial($provider) {
        $user_id = \Yii::$app->user->identity->getId();
        SocialAccount::deleteAll(['AND', ['=', 'user_id', $user_id], ['=', 'provider', 'line']]);
        return $this->redirect([$provider]);
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
                    "redirect_uri" => 'https://region4.anamai.moph.go.th/office/user/settings/line_callback'
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
                    //session()->set('access_token', $accessToken);
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
//                    print_r($result);
//                    die();
                    if ($httpcode == 200) {

                        $data = [
                            'userId' => $result['userId'],
                            'name' => $result['displayName'],
                            'picture' => $result['pictureUrl'],
                        ];

                        // ตรวจสอบว่ามีการผูก Line นี้กับ account อื่นหรือเปล่า
                        $socialUsed = $this->checkSocialUsed($result['userId'], 'line');
                        if ($socialUsed) {
                            // redirect status faild
                            // Set Flash message
                            $message = 'Line Account นี้ได้มีการเชื่อมต่อกับผู้ใช้อื่นแล้ว';
                            Yii::$app->session->setFlash('error', $message);

                            return $this->redirect(['line']);
                        } else {
                            // Insert Social Account & redirect to action line
                            $save = $this->saveLineAccount($data);
                            return $this->redirect(['line']);
                        }
                    } else {
                        // redirect status faild
                        $message = 'พบข้อผิดพลาดในการเชื่อมต่อกับเครือข่ายของ Line Application';
                        Yii::$app->session->setFlash('error', $message);

                        return $this->redirect(['line']);
                    }
                }
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
//        return $this->render('line');
    }

    public function getSocialAcc($user_id, $provider) {
        $social = SocialAccount::findOne(['user_id' => $user_id, 'provider' => $provider]);
        return $social;
    }

    public function checkSocialUsed($client_id, $provider) {
        $used = SocialAccount::findOne(['client_id' => $client_id, 'provider' => $provider]);
        return $used;
    }

    public function saveLineAccount($data) {
        $user_id = \Yii::$app->user->identity->getId();

        $model = new SocialAccount;
        $model->user_id = $user_id;
        $model->client_id = $data['userId'];
        $model->provider = 'line';
        $model->data = $data['name'];
        $model->created_at = date('Y-m-d H:i:s');
        $model->save(false);

        return true;
    }

	public function actionTwoFactor()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $ga = new GoogleAuthenticator();

        // 1. ถ้ายังไม่มี Secret หรือยังไม่เคยเปิด ให้สร้าง Secret ชั่วคราวเก็บไว้ก่อน
        if (empty($user->auth_2fa_secret)) {
            $user->auth_2fa_secret = $ga->generateSecret();
            $user->save(false);
        }

        // 2. สร้าง otpauth:// URL สำหรับนำไปแปลงเป็น QR Code
        $appName = Yii::$app->name;
        $qrCodeUrl = $ga->getQrCodeUrl($appName, $user->username ?? $user->email, $user->auth_2fa_secret);

        // 3. เมื่อ User กดส่งรหัส 6 หลัก เพื่อยืนยันการเปิดใช้งาน
        if (Yii::$app->request->isPost) {
            $code = Yii::$app->request->post('totp_code');

            if ($ga->verifyCode($user->auth_2fa_secret, $code)) {
                $user->is_2fa_enabled = 1;
                $user->save(false);

                Yii::$app->session->setFlash('success', 'เปิดใช้งาน 2-Factor Authentication สำเร็จแล้ว!');
                return $this->redirect(['account']);
            } else {
                Yii::$app->session->setFlash('error', 'รหัสยืนยัน 6 หลักไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
            }
        }

        return $this->render('two-factor', [
            'user' => $user,
            'secret' => $user->auth_2fa_secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    // สำหรับปิดใช้งาน 2FA
    public function actionDisable2fa()
    {
        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;
        $user->is_2fa_enabled = 0;
        $user->auth_2fa_secret = null;
        $user->save(false);

        Yii::$app->session->setFlash('info', 'ปิดใช้งาน 2-Factor Authentication เรียบร้อยแล้ว');
        return $this->redirect(['account']);
    }

}
