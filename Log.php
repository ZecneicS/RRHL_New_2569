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
class Log
{
    public static function write($id, $job, $by ,$remark=null) {
        $conn = Yii::$app->db;
        $conn->createCommand()->insert('manage_log', [
                    'id' => $id,
                    'job' => $job,
                    'uid' => $by,
                    'datelog' => date('Y-m-d H:i:s'),
                    'remark' => $remark,
                ])
                ->execute();
    }
	
	public static function handbook_search($keyword=null,$category=null, $result=0) {
        $conn = Yii::$app->db;
        $conn->createCommand()->insert('handbook_log', [
                    'keyword' => $keyword,
                    'category' => $category,
                    'result' => $result,
                    'datelog' => date('Y-m-d H:i:s'),
                ])
                ->execute();
    }
	
	public static function schedule($id, $job, $by ,$remark=null) {
        $conn = Yii::$app->db;
        $conn->createCommand()->insert('schedule_log', [
                    'id' => $id,
                    'job' => $job,
                    'uid' => $by,
                    'datelog' => date('Y-m-d H:i:s'),
                    'remark' => $remark,
                ])
                ->execute();
    }
}
