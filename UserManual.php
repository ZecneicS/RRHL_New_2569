<?php

namespace app\models;

use yii\helpers\ArrayHelper;

//use Yii;

/**
 * This is the model class for table "user_manual".
 *
 * @property int $id
 * @property string|null $category
 * @property string|null $filename
 * @property string|null $filename_original
 * @property string|null $detail
 * @property string|null $url
 * @property string|null $filetype
 * @property string|null $item_type
 */
class UserManual extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_manual';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['detail','category'], 'required'],
            ['url', 'url'],
            [['detail'], 'string', 'max' => 200],
            [['keyword'], 'string', 'max' => 255],
			[['view'], 'integer'],
            [['filename','filetype','filename_original','item_type','update_at','enable'], 'safe'],
            ['url', 'required', 'message' => 'กรุณาระบุลิงค์ URL', 'when' => function ($model) {
                return $model->item_type == 'Link';
            }, 'whenClient' => "function (attribute, value) {
                return $('#itemType').val() == 'Link';
            }"],
            /*
            [['url'], 'string'],
            [['category', 'filetype'], 'string', 'max' => 50],
            [['filename'], 'string', 'max' => 100],
            [['filename_original'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 200],
            [['item_type'], 'string', 'max' => 10],
             * 
             */
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category' => 'เรื่องที่เกี่ยวข้อง',
            'filename' => 'Filename',
            'filename_original' => 'Filename Original',
            'detail' => 'ชื่อเรื่อง',
            'keyword' => 'คำค้น',
            'url' => 'Url',
            'filetype' => 'Filetype',
            'item_type' => 'Item Type',
            'update_at' => 'ปรับปรุงล่าสุด',
        ];
    }
    
//    public function getCate()
//    {
//        return self::itemsAlias('category');
//    }
//    
//    public static function itemsAlias($key)
//    {
//        $items = [
//            'category' => [
//                '1' => 'แหล่งเรียนรู้',
//                '2' => 'ตัวชี้วัด',
//                '3' => 'สถานีตรวจวัดอากาศ',
//                //'6' => 'รถจ้างเหมา',
//            ],
//
//        ];
//        return ArrayHelper::getValue($items, $key, []);
//    }
    
//    public function getCateName()
//    {
//        return ArrayHelper::getValue($this->getCate(), $this->category);
//    }
    
    public function getCateName()
    {
        return $this->hasOne(SModules::className(), ['id' => 'category']);
    }
    
}
