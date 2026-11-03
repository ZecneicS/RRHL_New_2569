<?php

namespace app\models;

use dektrium\user\models\LoginForm as BaseLoginForm;
use Yii;

class SsoLoginForm extends BaseLoginForm
{
    private $_maxAttempts;
    private $_lockMins;

    public function init()
    {
        parent::init();
        $this->_maxAttempts = 4;
        $this->_lockMins = 15;
    }
    /**
     * ดักตรวจสอบก่อนเริ่ม Validate (เช่น เช็คว่าโดนบล็อกอยู่ไหม)
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            // หา User โดยรองรับการค้นหาจาก username หรือ email
            $user = \app\models\SsoUser::find()
                ->where(['username' => $this->login])
                ->orWhere(['email' => $this->login])
                ->one();

            if ($user && !$user->getIsBlocked()) {
                $lockTime = $this->_lockMins * 60; // 15 นาที
                $timeDiff = time() - $user->failed_login_at;
                if ($timeDiff > $lockTime) {
                    $user->updateAttributes([
                        'failed_login' => 0,
                        'failed_login_at' => null,
                    ]);
                }
            } else if ($user && $user->getIsBlocked()) {
                \Yii::$app->session->setFlash('danger', 'บัญชีของคุณถูกระงับการใช้งานแล้ว กรุณาติดต่อผู้ดูแลระบบ');
                    return false;
            }

            // if ($user && $user->failed_login >= $this->_maxAttempts) {
            //     $lockTime = $this->_lockMins * 60; // 15 นาที
            //     $timeDiff = time() - $user->failed_login_at;

            //     if ($user->getIsBlocked()) {
            //         \Yii::$app->session->setFlash('danger', 'บัญชีของคุณถูกระงับการใช้งานแล้ว กรุณาติดต่อผู้ดูแลระบบ');
            //         return false;
            //     }
            // }
            return true;
        }
        return false;
    }

    /**
     * ดักหลังจาก Validate เสร็จ (ถ้าผ่านหรือพลาด จะมาทำงานที่นี่)
     */
    public function afterValidate()
    {
        parent::afterValidate();

        // หา User เพื่ออัปเดตสถานะ
        $user = \app\models\SsoUser::find()
            ->where(['username' => $this->login])
            ->orWhere(['email' => $this->login])
            ->one();

        if ($user) {
            if ($this->hasErrors()) {
                // กรณี Login พลาด: บวกจำนวนครั้ง และบันทึกเวลาล่าสุด
                $newCount = $user->failed_login + 1;
                $user->updateAttributes([
                    'failed_login' => $newCount,
                    'failed_login_at' => time(),
                ]);

                // เพิ่มการแจ้งเตือน Flash Message
                if ($newCount < $this->_maxAttempts) {
                    $remaining = $this->_maxAttempts - $newCount;

                    $msg = ($remaining == 1) ? "คุณหลือโอกาสอีก $remaining ครั้ง <br>หรือรอ $this->_lockMins นาที แล้วลองใหม่ภายหลัง" :
                        "ผิดพลากครั้งที่ $newCount/$this->_maxAttempts  <br>เหลือโอกาสอีก $remaining ครั้ง";
                    $this->addError('warn', $msg);
                } else {
                    $user->block();
                    $this->addError('warn', "บัญชีของคุณถูกระงับการใช้งานแล้ว <br>กรุณาติดต่อผู้ดูแลระบบ");
                }
            } else {
                // กรณี Login สำเร็จ: Reset ค่าเป็น 0
                $user->updateAttributes([
                    'failed_login' => 0,
                    'failed_login_at' => null,
                ]);
            }
        }
    }
}
