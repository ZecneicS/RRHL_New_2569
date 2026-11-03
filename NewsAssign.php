<?php

namespace app\models;

// use Yii;
use app\models\NewsFact;

/**
 * This is the model class for table "news_assign".
 *
 * @property int $id
 * @property int|null $news_id
 * @property string|null $assign_by_id
 * @property string|null $assign_by_fullname
 * @property string|null $assign_to_id
 * @property string|null $assign_to_fullname
 * @property string|null $answer
 * @property string|null $date_appointment
 * @property string|null $content_answer
 * @property string|null $content_answer1
 * @property string|null $content_answer2
 * @property string|null $content_answer3
 * @property string|null $content_answer4
 * @property string|null $fact
 * @property string|null $publish
 * @property string|null $assign_timestamp
 * @property string|null $accept_timestamp
 * @property string|null $reply_timestamp
 * @property int|null $use_status
 */
class NewsAssign extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_assign';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['assign_to_office', 'assign_to_id'], 'required'],
            [['news_id'], 'integer'],
            [['date_appointment', 'assign_timestamp', 'reply_timestamp', 'use_status','assign_by_fullname','assign_by_id', 'assign_to_office', 'assign_to_id', 'assign_to_fullname','answer'], 'safe'],
            [['content_answer', 'content_answer1', 'content_answer2', 'content_answer3', 'content_answer4','url_reference'], 'string'],


            // [['answer'], 'string', 'max' => 2],
            ['fact', 'required', 'when' => function ($model) {
                return $model->answer == '3';
            }, 'whenClient' => "function (attribute, value) {
                return $('input[name=\"NewsAssign[answer]\"]:checked').val() == '3';
            }"],
            // ['publish', 'required', 'when' => function ($model) {
            //     return $model->answer == '3';
            // }, 'whenClient' => "function (attribute, value) {
            //     return $('input[name=\"NewsAssign[answer]\"]:checked').val() == '3';
            // }"],
            ['content_answer3', 'required', 'when' => function ($model) {
                return $model->answer == '3';
            }, 'whenClient' => "function (attribute, value) {
                return $('input[name=\"NewsAssign[answer]\"]:checked').val() == '3';
            }"],
            ['content_answer4', 'required', 'when' => function ($model) {
                return $model->answer == '4';
            }, 'whenClient' => "function (attribute, value) {
                return $('input[name=\"NewsAssign[answer]\"]:checked').val() == '4';
            }"],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_id' => 'News ID',
            'assign_by_id' => 'ผู้มอบหมาย (ID)',
            'assign_by_fullname' => 'ผู้มอบหมาย (ชื่อ-นามสกุล)',
            'assign_to_office' => 'หน่วยงานที่มอบหมาย',
            'assign_to_id' => 'ผู้รับมอบหมาย',
            'assign_to_fullname' => 'ผู้รับมอบหมาย',
            'answer' => 'การตอบกลับ',
            'date_appointment' => 'วันที่นัดหมาย',
            'content_answer' => 'รายละเอียดการตอบกลับ',
            'content_answer1' => 'Content Answer1',
            'content_answer2' => 'Content Answer2',
            'content_answer3' => 'รายละเอียดตอบกลับ',
            'content_answer4' => 'เหตุผลการปฏิเสธ',
            'url_reference' => 'ลิงค์อ้างอิง',
            'fact' => 'ผลการตรวจสอบ',
            'publish' => 'การเผยแพร่',
            'assign_timestamp' => 'Assign Timestamp',
            'accept_timestamp' => 'Accept Timestamp',
            'reply_timestamp' => 'Reply Timestamp',
            'use_status' => 'Use Status',
        ];
    }

    public function getAnswerFact()
    {
        return $this->hasOne(NewsFact::class, ['id' => 'fact']);
    }

    public function getAssignedProfile()
    {
        return $this->hasOne(SsoProfile::class, ['user_id' => 'assign_to_id']);
    }

    public function getNewsFact() {
        return $this->hasOne(NewsFact::className(), ['id' => 'fact']);
    }
}
