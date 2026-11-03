<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "action_log".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $status
 * @property int|null $deleted_by
 * @property string|null $ip_address
 * @property string $action
 * @property string|null $detail
 * @property string|null $created_at
 */
class ActionLog extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'action_log';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        return Yii::$app->get('sso');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'deleted_by'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['detail'], 'string'],
            [['created_at'], 'safe'],
            [['action'], 'required'],
            [['action'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 16], // Validation for binary string might be tricky, safe or limited length
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '#',
            'user_id' => 'ผู้ใช้งาน',
            'status' => 'สถานะ',
            'deleted_by' => 'ลบโดย',
            'ip_address' => 'ไอพีแอดเดรส',
            'action' => 'การกระทำ',
            'detail' => 'รายละเอียด',
            'created_at' => 'วันที่เวลา',
        ];
    }

    /**
     * Records an action log.
     *
     * @param string $action The action name
     * @param string|null $detail Additional details
     * @return bool Whether the log was saved successfully
     */
    public static function record($action, $detail = null)
    {
        $log = new self();
        $log->created_at = date('Y-m-d H:i:s');
        
        // Convert IP to binary
        $ip = Yii::$app->request->getUserIP();
        $log->ip_address = $ip ? inet_pton($ip) : null;
        
        $log->action = $action;
        $log->detail = $detail;

        if (!Yii::$app->user->getIsGuest()) {
            $log->user_id = Yii::$app->user->id;
        }

        return $log->save();
    }
}
