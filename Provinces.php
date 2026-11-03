<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "provinces".
 *
 * @property int $id
 * @property string $code
 * @property string $name_th
 * @property string $name_en
 * @property string|null $moph
 * @property int $geography_id
 * @property string|null $sort
 */
class Provinces extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'provinces';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name_th', 'name_en'], 'required'],
            [['geography_id'], 'integer'],
            [['code', 'moph', 'sort'], 'string', 'max' => 2],
            [['name_th', 'name_en'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name_th' => 'Name Th',
            'name_en' => 'Name En',
            'moph' => 'Moph',
            'geography_id' => 'Geography ID',
            'sort' => 'Sort',
        ];
    }
}
