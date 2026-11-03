<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "office".
 *
 * @property integer $id
 * @property string $office_id
 * @property string $office_name
 * @property string $office_shortname
 * @property string $office_region
 */
class Office extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'office';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('sso');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['office_name','office_region'], 'required'],
            [['office_id'], 'string', 'max' => 20],
            [['office_name'], 'string', 'max' => 100],
            [['office_shortname'], 'string', 'max' => 30],
            [['office_region'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'รหัส (ID)',
            'office_id' => 'รหัสหน่วยงาน',
            'office_name' => 'ชื่อหน่วยงาน',
            'office_shortname' => 'ชื่อย่อหน่วยงาน',
            'office_region' => 'พื้นที่/เขตสุขภาพ',
        ];
    }

    public function getRegion() {
        return $this->hasOne(OfficeRegion::className(), ['region_id' => 'office_region']);
    }

    public function getCountMember() {
        return $this->hasMany(SsoProfile::className(), ['department_id' => 'id'])->count();
    }

    /**
     * @inheritdoc
     * @return OfficeQuery the active query used by this AR class.
     */
    // public static function find()
    // {
    //     return new OfficeQuery(get_called_class());
    // }
}
