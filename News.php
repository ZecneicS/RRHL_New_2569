<?php

namespace app\models;

use Yii;
// use app\models\NewsFact;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string|null $news_title
 * @property string|null $keyword
 * @property string|null $site
 * @property string|null $content_request
 * @property string|null $content
 * @property int|null $news_type
 * @property int|null $rq
 * @property string|null $sender
 * @property string|null $office
 * @property string|null $email
 * @property string|null $time_stamp
 * @property string|null $image
 * @property int|null $user_id
 * @property string|null $user_fullname
 */
class News extends \yii\db\ActiveRecord
{
    public $factName;
    public $assign_to_fullname;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        // scenario ปกติ (default) ใช้เหมือนเดิม
        $scenarios['special_form'] = [
            'news_title',
            'site',
            'content_request',
            'news_type',
            'rq',
            'sender',
            'fact',
            'publish',
            'content'
        ];

        return $scenarios;
    }

    public function rules()
    {
        return [
            [['news_title', 'site', 'content_request', 'news_type', 'rq', 'sender'], 'required'],
            [['content_request', 'content','url_reference'], 'string'],
            [['user_id'], 'integer'],
            [['time_stamp', 'accept_timestamp', 'news_type', 'publish_timestamp','fact','publish'], 'safe'],
            [['news_title', 'keyword', 'office', 'image'], 'string', 'max' => 255],
            [['site'], 'url', 'defaultScheme' => 'https'],
            [['sender'], 'string', 'max' => 10],
            [['email', 'user_fullname'], 'string', 'max' => 100],
            // บังคับกรอก office เฉพาะเมื่อ sender == 2
            ['office', 'required', 'when' => function ($model) {
                return $model->sender == '2';
            }, 'whenClient' => "function (attribute, value) {
                return $('input[name=\"News[sender]\"]:checked').val() == '2';
            }"],

            // rule สำหรับ scenario พิเศษ
            [['fact', 'publish', 'content'], 'required', 'on' => 'special_form'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_title' => 'หัวข้อข่าว',
            'keyword' => 'คำค้น',
            'site' => 'ลิงก์เว็บไซต์ของข่าว',
            'content_request' => 'ข้อมูลที่ต้องการแจ้งให้ตรวจสอบ',
            'content' => 'เนื้อหาข่าว',
            'news_type' => 'กลุ่มข่าว',
            'rq' => 'ความเร่งด่วน',
            'sender' => 'ผู้ส่ง',
            'office' => 'หน่วยงาน',
            'email' => 'Email',
            'time_stamp' => 'วันที่ส่งเรื่อง',
            'image' => 'รูปภาพ',
            'user_id' => 'User ID',
            'user_fullname' => 'User Fullname',
            'fact' => 'ข้อเท็จจริง',
            'publish' => 'เผยแพร่',
        ];
    }

    public function getNewsType()
    {
        return $this->hasOne(NewsType::className(), ['id' => 'news_type']);
    }

    public function getSla()
    {
        return $this->hasOne(NewsSla::className(), ['id' => 'rq']);
    }

    public function getNewsAssign()
    {
        return $this->hasMany(NewsAssign::className(), ['news_id' => 'id']);
    }

    public function getNewsAssignUse()
    {
        return $this->hasOne(NewsAssign::className(), ['news_id' => 'id'])->onCondition(['use_status' => '1']);
    }

    public function getNewsAssignLast()
    {
        return $this->hasOne(NewsAssign::className(), ['news_id' => 'id'])->orderBy(['news_assign.id' => SORT_DESC]);
    }

    public function getNewsFact()
    {
        return $this->hasOne(NewsFact::className(), ['id' => 'fact'])->via('newsAssignUse');
    }

    public function getLastFact()
    {
        return $this->hasOne(NewsFact::className(), ['id' => 'fact'])->via('newsAssignLast');
    }

    public function getExternalSite()
    {
        $url = $this->site;
        if (empty($url)) {
            return '#';
        }
        // เช็กว่ามี protocol หรือยัง
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            return "http://" . $url;
        }
        return $url;
    }
}
