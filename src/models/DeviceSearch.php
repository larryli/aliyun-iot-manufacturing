<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DeviceSearch represents the model behind the search form of `app\models\Device`.
 */
class DeviceSearch extends Device
{
    /**
     * @var string
     */
    private $_applyTitle;
    /**
     * @var string
     */
    private $_productName;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'apply_id', 'state'], 'integer'],
            [['serial_no', 'device_name', 'applyTitle', 'productName', 'productKey'], 'safe'],
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
        $query = Device::find()->joinWith('apply');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id',
                    'apply_id',
                    'applyTitle' => [
                        'asc' => ['apply.title' => SORT_ASC],
                        'desc' => ['apply.title' => SORT_DESC],
                    ],
                    'productName' => [
                        'asc' => ['apply.product_name' => SORT_ASC],
                        'desc' => ['apply.product_name' => SORT_DESC],
                    ],
                    'productKey' => [
                        'asc' => ['apply.product_key' => SORT_ASC],
                        'desc' => ['apply.product_key' => SORT_DESC],
                    ],
                    'serial_no',
                    'device_name',
                    'state',
                    'updated_at',
                ],
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ],
            ],
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
            'apply_id' => $this->apply_id,
            'state' => $this->state,
        ]);

        $query->andFilterWhere(['like', 'apply.title', $this->applyTitle])
            ->andFilterWhere(['like', 'apply.product_name', $this->productName])
            ->andFilterWhere(['like', 'apply.product_key', $this->productKey])
            ->andFilterWhere(['like', 'serial_no', $this->serial_no])
            ->andFilterWhere(['like', 'device_name', $this->device_name])
            ->andFilterWhere(['like', 'device_secret', $this->device_secret]);

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function getApplyTitle()
    {
        return $this->_applyTitle;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->_productName;
    }

    /**
     * @param string $title
     */
    public function setApplyTitle($title)
    {
        $this->_applyTitle = $title;
    }

    /**
     * @param string $name
     */
    public function setProductName($name)
    {
        $this->_productName = $name;
    }
}
