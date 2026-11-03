<?php

namespace app\models;

use Yii;
use dektrium\user\models\User as BaseUser;
//use app\models\SocialAccount;

class SsoUser extends BaseUser
{

    public static function getDb()
    {
        return Yii::$app->sso;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->ssoid = $this->generateRandomString();
            }
            return true;
        }
        return false;
    }

    public function generateRandomString($length = 32)
    {
        $bytes = (int) $length/2;
        return bin2hex(openssl_random_pseudo_bytes($bytes));
    }

}
