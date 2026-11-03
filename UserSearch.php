<?php

namespace app\models;

use dektrium\user\Finder;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use dektrium\user\models\UserSearch as BaseUser;

class UserSearch extends Model
{
    // public $fullname;

     /** @var integer */
     public $id;

     /** @var string */
     public $name;

     /** @var string */
     public $username;
 
     /** @var string */
     public $email;
 
     /** @var int */
     public $created_at;
 
     /** @var int */
     public $last_login_at;
 
     /** @var string */
     public $registration_ip;
 
     /** @var Finder */
     protected $finder;
 

    /** @inheritdoc */
    public function rules()
    {
        return [
            'fieldsSafe' => [['id', 'username', 'email', 'registration_ip', 'created_at', 'last_login_at','name'], 'safe'],
            'createdDefault' => ['created_at', 'default', 'value' => null],
            'lastloginDefault' => ['last_login_at', 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('user', '#'),
            'username'        => Yii::t('user', 'Username'),
            'fullname'        => Yii::t('user', 'ชื่อ-นามสกุล'),
            'email'           => Yii::t('user', 'Email'),
            'created_at'      => Yii::t('user', 'Registration time'),
            'last_login_at'   => Yii::t('user', 'Last login'),
            'registration_ip' => Yii::t('user', 'Registration ip'),
        ];
    }

    public function search($params)
    {
        $query = SsoUser::find()->joinWith('profile');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // $query->andWhere('profile.firstname like :fullname or profile.lastname like :fullname',[
        //     ':fullname' => '%'.$this->fullname.'%'
        // ]);

        $query->andFilterWhere(['like','username', $this->username])
              ->andFilterWhere(['like','user.name', $this->name])
              ->andFilterWhere(['like','email', $this->email]);
        return $dataProvider;
    }
}
