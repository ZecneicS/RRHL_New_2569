<?php

namespace app\models;


/**
 * This is the model class for table "visitor".
 *
 * @property int $id
 * @property string|null $sid
 * @property string|null $ip
 * @property string|null $track_type
 * @property string|null $date_visit
 * @property string|null $date_created
 */
class Visitor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visitor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_visit', 'date_created'], 'safe'],
            [['ip'], 'string', 'max' => 20],
            [['track_type'], 'string', 'max' => 30],
            [['sid'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sid' => 'Sid',
            'ip' => 'Ip',
            'track_type' => 'Track Type',
            'date_visit' => 'Date Visit',
            'date_created' => 'Date Created',
        ];
    }
}
