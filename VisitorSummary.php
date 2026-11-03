<?php

namespace app\models;

/**
 * This is the model class for table "visitor_summary".
 *
 * @property int $id
 * @property string|null $visit_date
 * @property int|null $visit_summary
 */
class VisitorSummary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'visitor_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['visit_date'], 'safe'],
            [['visit_summary'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'visit_date' => 'Visit Date',
            'visit_summary' => 'Visit Summary',
        ];
    }
}
