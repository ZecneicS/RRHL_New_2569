<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "news_fact".
 *
 * @property int $id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $color
 * @property string|null $created_at
 */
class NewsFact extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_fact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image_overlay'], 'string', 'max' => 30],
            [['code'], 'string', 'max' => 2],
            [['name'], 'string', 'max' => 200],
            [['color'], 'string', 'max' => 100],
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
            'name' => 'Name',
            'color' => 'Color',
            'created_at' => 'Created At',
        ];
    }
}
