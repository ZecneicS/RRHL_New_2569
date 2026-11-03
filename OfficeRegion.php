<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "office_region".
 *
 * @property int $id
 * @property string|null $region_id
 * @property string|null $region_name
 */
class OfficeRegion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'office_region';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region_id'], 'string', 'max' => 5],
            [['region_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region_id' => 'Region ID',
            'region_name' => 'Region Name',
        ];
    }
}
