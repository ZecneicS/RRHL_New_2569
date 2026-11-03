<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\News;

/**
 * ItemsSearch represents the model behind the search form of `app\models\Items`.
 */
class NewsPublic extends News
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['news_title', 'factName'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = News::find()->joinWith(['newsFact nf']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            //            'item_date' => $this->item_date,
            //            'category' => $this->category,
            //            'item_type' => $this->item_type,
            //            'life_time' => $this->life_time,
        ]);

        $query->andFilterWhere(['like', 'news_title', $this->news_title])
            ->andFilterWhere(['like', 'nf.name', $this->factName]);
        //     ->andFilterWhere(['=', 'is_online_host', $this->is_online_host])
        //     ->andFilterWhere(['=', 'room_id', $this->room_id]);
        //            ->andFilterWhere(['like', 'brand', $this->brand])
        //            ->andFilterWhere(['like', 'model', $this->model])
        //            ->andFilterWhere(['like', 'use_status', $this->use_status])
        //            ->andFilterWhere(['like', 'service_status', $this->service_status]);

        return $dataProvider;
    }
}
