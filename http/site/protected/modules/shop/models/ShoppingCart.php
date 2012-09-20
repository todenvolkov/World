<?php

class ShoppingCart extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return Yii::app()->controller->module->shoppingCartTable;
	}

	public static function getCartsOfOwner($cartowner = 'notset') {
		if($cartowner == 'notset')
			$cartowner = Yii::app()->User->getState('cartowner');

		return ShoppingCart::model()->findAll('cartowner = :cartowner', array(':cartowner' => $cartowner));
	}

	public function rules()
	{
		return array(
			array('product_id, cartowner', 'required'),
			array('product_id, customer_id', 'numerical', 'integerOnly'=>true),
			array('amount, cartowner', 'numerical'),
			array('cart_id, amount, product_id, customer_id', 'safe', 'on'=>'search'),
		);
	}

	public function relations()
	{
		return array(
			'Customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'Product' => array(self::BELONGS_TO, 'Products', 'product_id'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'cart_id' => Yii::t('shop', 'Cart'),
			'amount' => Yii::t('shop', 'Amount'),
			'product_id' => Yii::t('shop', 'Product'),
			'customer_id' => Yii::t('shop', 'Customer'),
		);
	}

	public function search()
  {
		$criteria=new CDbCriteria;

		if($this->cart_id == 0) 
		$criteria->compare('cart_id',$this->cart_id);

		$criteria->compare('amount',$this->amount);
		$criteria->compare('product_id',$this->product_id);
		$criteria->compare('customer_id',$this->customer_id);
		$criteria->compare('cartowner',$this->cartowner);

		return new CActiveDataProvider('ShoppingCart', array(
			'criteria'=>$criteria,
		));
	}
	
	public function getItemsCount(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT COUNT(id) FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL AND DELAY='N'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		return $comm->queryScalar();
	}
	
	public function getActiveOrdersCount(){
		$uid = $_COOKIE["BITRIX_SM_LOGIN"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT COUNT(bso.id) FROM b_sale_order AS bso LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE usr.LOGIN=:uid AND bso.CANCELED='N' AND bso.STATUS_ID IN ('N','P')");
		$comm->bindParam(":uid",$uid,PDO::PARAM_STR);
		return isset($_COOKIE["BITRIX_SM_UIDH"])?$comm->queryScalar():0;
	}
	
	public function getFinishedOrdersCount(){
		$uid = $_COOKIE["BITRIX_SM_LOGIN"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT COUNT(bso.id) FROM b_sale_order AS bso LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE usr.LOGIN=:uid AND bso.CANCELED='N' AND bso.STATUS_ID='F'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_STR);
		return isset($_COOKIE["BITRIX_SM_UIDH"])?$comm->queryScalar():0;
	}
	
	public function getCanceledOrdersCount(){
		$uid = $_COOKIE["BITRIX_SM_LOGIN"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT COUNT(bso.id) FROM b_sale_order AS bso LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE usr.LOGIN=:uid AND bso.CANCELED='Y'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_STR);
		return isset($_COOKIE["BITRIX_SM_UIDH"])?$comm->queryScalar():0;
	}
	
	public function getDelayItemsCount(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT COUNT(id) FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL AND DELAY='Y'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		return $comm->queryScalar();
	}
	
	public function getTotalPrice(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT SUM(PRICE*QUANTITY) AS total  FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL AND DELAY='N'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		return $comm->queryScalar();
	}
}
