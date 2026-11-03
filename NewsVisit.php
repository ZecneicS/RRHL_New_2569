<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "news_visit".
 *
 * @property int $id
 * @property string|null $sid
 * @property int|null $news_id
 * @property string|null $date_created
 */
class NewsVisit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_visit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['news_id'], 'integer'],
            [['date_created'], 'safe'],
            [['sid'], 'string', 'max' => 30],
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
            'news_id' => 'News ID',
            'date_created' => 'Date Created',
        ];
    }
}
