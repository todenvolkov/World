<?php

/**
 * This is the model class for table "orders".
 *
 * The followings are the available columns in table 'orders':
 * @property string $id
 * @property string $id_customer
 * @property string $id_cart
 * @property string $id_address_delivery
 * @property string $payment
 * @property string $conversion_rate
 * @property string $module
 * @property string $shipping_number
 * @property string $total_discounts
 * @property string $total_paid
 * @property string $total_paid_real
 * @property string $total_products
 * @property string $total_products_wt
 * @property string $total_shipping
 * @property string $carrier_tax_rate
 * @property string $invoice_number
 * @property string $delivery_number
 * @property string $invoice_date
 * @property string $delivery_date
 * @property string $valid
 * @property string $date_add
 * @property string $date_upd
 */
class Order extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Order the static model class
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
		return 'orders';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_customer, id_cart, id_address_delivery, payment, invoice_date, delivery_date, date_add, date_upd', 'required'),
			array('id_customer, id_cart, id_address_delivery, carrier_tax_rate, invoice_number, delivery_number', 'length', 'max'=>10),
			array('payment, module', 'length', 'max'=>255),
			array('conversion_rate', 'length', 'max'=>13),
			array('shipping_number', 'length', 'max'=>32),
			array('total_discounts, total_paid, total_paid_real, total_products, total_products_wt, total_shipping', 'length', 'max'=>17),
			array('valid', 'length', 'max'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_customer, id_cart, id_address_delivery, payment, conversion_rate, module, shipping_number, total_discounts, total_paid, total_paid_real, total_products, total_products_wt, total_shipping, carrier_tax_rate, invoice_number, delivery_number, invoice_date, delivery_date, valid, date_add, date_upd', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'id_customer' => 'Id Customer',
			'id_cart' => 'Id Cart',
			'id_address_delivery' => 'Id Address Delivery',
			'payment' => 'Payment',
			'conversion_rate' => 'Conversion Rate',
			'module' => 'Module',
			'shipping_number' => 'Shipping Number',
			'total_discounts' => 'Total Discounts',
			'total_paid' => 'Total Paid',
			'total_paid_real' => 'Total Paid Real',
			'total_products' => 'Total Products',
			'total_products_wt' => 'Total Products Wt',
			'total_shipping' => 'Total Shipping',
			'carrier_tax_rate' => 'Carrier Tax Rate',
			'invoice_number' => 'Invoice Number',
			'delivery_number' => 'Delivery Number',
			'invoice_date' => 'Invoice Date',
			'delivery_date' => 'Delivery Date',
			'valid' => 'Valid',
			'date_add' => 'Date Add',
			'date_upd' => 'Date Upd',
		);
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
		$criteria->compare('id_customer',$this->id_customer,true);
		$criteria->compare('id_cart',$this->id_cart,true);
		$criteria->compare('id_address_delivery',$this->id_address_delivery,true);
		$criteria->compare('payment',$this->payment,true);
		$criteria->compare('conversion_rate',$this->conversion_rate,true);
		$criteria->compare('module',$this->module,true);
		$criteria->compare('shipping_number',$this->shipping_number,true);
		$criteria->compare('total_discounts',$this->total_discounts,true);
		$criteria->compare('total_paid',$this->total_paid,true);
		$criteria->compare('total_paid_real',$this->total_paid_real,true);
		$criteria->compare('total_products',$this->total_products,true);
		$criteria->compare('total_products_wt',$this->total_products_wt,true);
		$criteria->compare('total_shipping',$this->total_shipping,true);
		$criteria->compare('carrier_tax_rate',$this->carrier_tax_rate,true);
		$criteria->compare('invoice_number',$this->invoice_number,true);
		$criteria->compare('delivery_number',$this->delivery_number,true);
		$criteria->compare('invoice_date',$this->invoice_date,true);
		$criteria->compare('delivery_date',$this->delivery_date,true);
		$criteria->compare('valid',$this->valid,true);
		$criteria->compare('date_add',$this->date_add,true);
		$criteria->compare('date_upd',$this->date_upd,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}