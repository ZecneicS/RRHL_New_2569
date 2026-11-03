<?php

namespace app\models;

use Yii;
use dektrium\user\models\Profile as BaseProfile;
use app\models\Office;

class SsoProfile extends BaseProfile
{
    public static function getDb()
    {
        return Yii::$app->sso;
    }

    public function rules()
    {
        return [
            // 'bioString'            => ['bio', 'string'],
            // 'timeZoneValidation'   => ['timezone', 'validateTimeZone'],
            'publicEmailPattern'   => ['public_email', 'email'],
            'gravatarEmailPattern' => ['gravatar_email', 'email'],
            // 'websiteUrl'           => ['website', 'url'],
            'nameLength'           => ['name', 'string', 'max' => 255],
            'publicEmailLength'    => ['public_email', 'string', 'max' => 255],
            'gravatarEmailLength'  => ['gravatar_email', 'string', 'max' => 255],
            // 'locationLength'       => ['location', 'string', 'max' => 255],
            // 'websiteLength'        => ['website', 'string', 'max' => 255],
            // [['department_id'], 'string','max'=>10],
            // [['position','level','position_type'], 'string', 'max' => 150],
            [['prefix'], 'string', 'max' => 50],
            [['firstname','lastname'], 'string', 'max' => 255],
            [['position'], 'string', 'max' => 200],
            [['department'], 'string', 'max' => 150],
            [['province'], 'string', 'max' => 2],
            [['tel'], 'string', 'max' => 50],
            // [['group'], 'string', 'max' => 20]
        ];
    }

    public function getFullname()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getOffice(){
        return $this->hasOne(Office::className(),['office_id'=>'department_id']);
    }

    public function getOfficeName(){
        return $this->canGetProperty('office') ? $this->office->office_name : null;
    }

    //Dune
    public function getOfficeRegion(){
        return $this->canGetProperty('office') ? $this->office->office_region : null;
    }  

    public function attributeLabels()
    {
        return [
            'name'           => \Yii::t('user', 'ชื่อ'),
            'public_email'   => \Yii::t('user', 'อีเมล์ (public)'),
            'gravatar_email' => \Yii::t('user', 'Gravatar email'),
            'location'       => \Yii::t('user', 'สถานที่'),
            'website'        => \Yii::t('user', 'เว็บไซต์'),
            'bio'            => \Yii::t('user', 'Bio'),
            'timezone'       => \Yii::t('user', 'โซนเวลา'),
            'department_id'  => 'หน่วยงาน',
            'position'       => 'ตำแหน่ง',
            'level'          => 'ระดับ',
            'prefix'         => 'คำนำหน้า',
            'firstname'      => 'ชื่อ',
            'lastname'       => 'นามสกุล',
            'position_type'  => 'ประเภทตำแหน่ง',
			'expert'  => 'เชี่ยวชาญ',
            'department'  => 'หน่วยงาน',
            'province'  => 'จังหวัด',
            'tel'  => 'เบอร์โทรศัพท์',
        ];
    }
}
