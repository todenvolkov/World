<?php

/**
 * This is the model class for table "shop_shipping_method".
 *
 * The followings are the available columns in table 'shop_shipping_method':
 * @property string $id
 * @property string $title
 * @property integer $tax_id
 * @property double $price
 */
class ShippingMethod extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ShippingMethod the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'shop_shipping_method';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, tax_id, price', 'required'),
			array('tax_id', 'numerical', 'integerOnly'=>true),
			array('price', 'numerical'),
			array('description, weight_range', 'safe'),
			array('title', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, title, tax_id, price', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'tax' => array(self::BELONGS_TO, 'Tax', 'tax_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => Shop::t('ID'),
			'weight_range' => Shop::t('Weight range'),
			'title' => Shop::t('Title'),
			'description' => Shop::t('Description'),
			'percent' => Shop::t('Tax percent'),
			'tax_id' => Shop::t('Tax'),
			'price' => Shop::t('Price'),
		);
	}

	public function getPrice() {
		return (float) $this->price * ($this->tax->percent / 100 + 1);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('tax_id',$this->tax_id);
		$criteria->compare('price',$this->price);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
