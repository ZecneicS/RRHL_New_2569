<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ฟอร์มเพิ่มผู้ใช้งานใหม่ (user + profile) โดยเลือกหน่วยงานจาก list
 * department_id = office.id (ตรงกับที่ NewsController::getOffice() ใช้ join profile)
 */
class UserCreateForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $prefix;
    public $firstname;
    public $lastname;
    public $position;
    public $tel;
    public $province;
    public $department_id;
    public $must_change_pw = 1;

    public function rules()
    {
        return [
            [['username', 'email', 'firstname', 'lastname', 'department_id'], 'required'],
            [['username', 'email', 'firstname', 'lastname'], 'trim'],
            ['email', 'email'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[-a-zA-Z0-9_\.@]+$/'],
            ['username', 'unique', 'targetClass' => SsoUser::class, 'targetAttribute' => 'username'],
            ['email', 'unique', 'targetClass' => SsoUser::class, 'targetAttribute' => 'email'],
            ['password', 'required', 'when' => function () {
                $m = Yii::$app->getModule('user');
                return !($m && $m->enableGeneratingPassword);
            }],
            ['password', 'string', 'min' => 6],
            ['prefix', 'string', 'max' => 50],
            [['firstname', 'lastname'], 'string', 'max' => 255],
            ['position', 'string', 'max' => 200],
            ['tel', 'string', 'max' => 50],
            ['province', 'string', 'max' => 2],
            ['province', 'exist', 'targetClass' => Provinces::class, 'targetAttribute' => 'code'],
            ['department_id', 'exist', 'targetClass' => Office::class, 'targetAttribute' => 'id'],
            ['must_change_pw', 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username'       => 'Username',
            'email'          => 'อีเมล',
            'password'       => 'รหัสผ่าน',
            'prefix'         => 'คำนำหน้า',
            'firstname'      => 'ชื่อ',
            'lastname'       => 'นามสกุล',
            'position'       => 'ตำแหน่ง',
            'tel'            => 'เบอร์โทรศัพท์',
            'province'       => 'จังหวัด',
            'department_id'  => 'สังกัด/หน่วยงาน',
            'must_change_pw' => 'บังคับเปลี่ยนรหัสผ่านเมื่อเข้าสู่ระบบครั้งแรก',
        ];
    }

    /**
     * @return SsoUser|false
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $office = Office::findOne($this->department_id);
        $db = SsoUser::getDb();
        $tx = $db->beginTransaction();
        try {
            /** @var SsoUser $user */
            $user = Yii::createObject(SsoUser::class);
            $user->setScenario('create');
            $user->username = $this->username;
            $user->email = $this->email;
            $user->password = $this->password;

            // dektrium create(): ตั้ง confirmed_at, สุ่มรหัสผ่านถ้าว่าง, สร้าง profile เปล่า
            if (!$user->create()) {
                throw new \RuntimeException('สร้างผู้ใช้ไม่สำเร็จ: ' . json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE));
            }

            $extra = [];
            if ($user->hasAttribute('name')) {
                $extra['name'] = trim($this->firstname . ' ' . $this->lastname);
            }
            if ($user->hasAttribute('must_change_pw')) {
                $extra['must_change_pw'] = (int) $this->must_change_pw;
            }
            if ($extra) {
                $user->updateAttributes($extra);
            }

            $profile = SsoProfile::findOne(['user_id' => $user->id]);
            if ($profile === null) {
                $profile = new SsoProfile(['user_id' => $user->id]);
            }
            $profile->prefix        = $this->prefix;
            $profile->firstname     = $this->firstname;
            $profile->lastname      = $this->lastname;
            $profile->name          = trim($this->firstname . ' ' . $this->lastname);
            $profile->position      = $this->position;
            $profile->tel           = $this->tel;
            $profile->province      = $this->province;
            $profile->department_id = (string) $office->id;
            $profile->department    = $office->office_name;
            if ($profile->hasAttribute('workstatus')) {
                $profile->workstatus = '1'; // NewsController::getProfile() กรอง workstatus="1"
            }
            if (!$profile->save()) {
                throw new \RuntimeException('บันทึกโปรไฟล์ไม่สำเร็จ: ' . json_encode($profile->getErrors(), JSON_UNESCAPED_UNICODE));
            }

            $tx->commit();
            return $user;
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            $this->addError('username', $e->getMessage());
            return false;
        }
    }
}
