<?php


class MapController extends YFrontController
{
    public function actionIndex(){
        $this->render('index');
    }
	
	public function actionBasket(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$sql = "SELECT bsb.ID, bsb.NAME, bsb.DELAY, bsb.QUANTITY, bsb.PRICE*bsb.QUANTITY AS total, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE1') AS dt_start, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE2') AS dt_end FROM b_sale_basket AS bsb WHERE bsb.DELAY='N' AND bsb.FUSER_ID=:uid AND bsb.ORDER_ID IS NULL GROUP BY bsb.ID";
		
		//$sql = "SELECT bsb.*, PRICE*QUANTITY AS total, bsbp1.VALUE AS dt_start, bsbp2.VALUE AS dt_end FROM b_sale_basket AS bsb LEFT JOIN b_sale_basket_props AS bsbp1 ON bsb.ID=bsbp1.BASKET_ID LEFT JOIN b_sale_basket_props AS bsbp2 ON bsb.ID=bsbp2.BASKET_ID WHERE bsb.FUSER_ID=:uid AND bsb.ORDER_ID IS NULL GROUP BY bsb.ID";
		
		$comm = Yii::app()->db2->createCommand($sql);
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		$res = $comm->queryAll();
		$this->render('basket',array("data"=>$res));
	}
	
	public function actionDelayedItems(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$sql = "SELECT bsb.ID, bsb.NAME, bsb.DELAY, bsb.QUANTITY, PRICE*QUANTITY AS total, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE1') AS dt_start, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE2') AS dt_end FROM b_sale_basket AS bsb WHERE bsb.DELAY='Y' AND bsb.FUSER_ID=:uid AND bsb.ORDER_ID IS NULL GROUP BY bsb.ID";
				
		$comm = Yii::app()->db2->createCommand($sql);
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		$res = $comm->queryAll();
		$this->render('basket',array("data"=>$res));
	}
	
	public function actionPrintTotalBasket(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT SUM(PRICE*QUANTITY) AS total  FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL AND DELAY='N'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		$res = $comm->queryScalar();
		echo number_format($res, 0, ' ', ' ')." руб.";
		Yii::app()->end();
	}
	
	public function actionPrintBasket($delay="N"){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		
		$sql = "SELECT bsb.ID, bsb.NAME, bsb.DELAY, bsb.QUANTITY, bsb.PRICE*bsb.QUANTITY AS total, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE1') AS dt_start, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE2') AS dt_end FROM b_sale_basket AS bsb WHERE bsb.DELAY=:delay AND bsb.FUSER_ID=:uid AND (bsb.ORDER_ID IS NULL OR bsb.ORDER_ID = 0) GROUP BY bsb.ID";
				
		$comm = Yii::app()->db2->createCommand($sql);
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		$comm->bindParam(":delay",$delay,PDO::PARAM_STR);
		$res = $comm->queryAll();
		$this->renderPartial('_basket-table',array("data"=>$res));
		Yii::app()->end();
	}
	
	public function actionDelItem($id){
		if (Yii::app()->request->isPostRequest){
			$sql = "DELETE FROM b_sale_basket_props WHERE BASKET_ID=:uid; DELETE FROM b_sale_basket WHERE ID=:id";
			$transaction=Yii::app()->db2->beginTransaction();
			try{
				Yii::app()->db2->createCommand("DELETE FROM b_sale_basket_props WHERE BASKET_ID=:uid")->bindParam(":uid",$id,PDO::PARAM_INT)->execute();
				Yii::app()->db2->createCommand("DELETE FROM b_sale_basket WHERE ID=:uid")->bindParam(":uid",$id,PDO::PARAM_INT)->execute();
				$transaction->commit();				
			}catch(Exception $e){
				$transaction->rollback();
				var_dump($e);
				throw new CHttpException(400, 'Error while deleting records.');
			}
			Yii::app()->end();
		}else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
	}
	
	public function actionDelay($id){
		if (Yii::app()->request->isPostRequest){
			$sql = "UPDATE b_sale_basket SET DELAY='Y' WHERE ID=:uid";
			$comm = Yii::app()->db2->createCommand($sql);
			$comm->bindParam(":uid",$id,PDO::PARAM_INT);
			$res = $comm->query();
			Yii::app()->end();
		}else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
	}
	public function actionUndelay($id){
		if (Yii::app()->request->isPostRequest){
			$sql = "UPDATE b_sale_basket SET DELAY='N' WHERE ID=:uid";
			$comm = Yii::app()->db2->createCommand($sql);
			$comm->bindParam(":uid",$id,PDO::PARAM_INT);
			$res = $comm->query();
			Yii::app()->end();
		}else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
	}
		
	public function getItemsCount(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT COUNT(id) FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL AND DELAY='N'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		return $comm->queryScalar();
	}
	
	public function getDelayItemsCount(){
		$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand("SELECT COUNT(id) FROM b_sale_basket WHERE FUSER_ID=:uid AND ORDER_ID IS NULL AND DELAY='Y'");
		$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
		return $comm->queryScalar();
	}
	
	public function actionOrder($type="make"){
		
		$conn = Yii::app()->db2;
		
		switch($type){
			case "active": {
				$uid = $_COOKIE["BITRIX_SM_LOGIN"];
				$sql = "SELECT bso.*, DATE_FORMAT(DATE_INSERT,'%d.%m.%Y %H:%i:%s') AS DATE_INSERT FROM b_sale_order AS bso LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE usr.LOGIN=:uid AND bso.CANCELED='N' AND bso.STATUS_ID IN ('N','P') ORDER BY bso.DATE_INSERT DESC";
				$comm = $conn->createCommand($sql);
				$comm->bindParam(":uid",$uid,PDO::PARAM_STR);
				
				$title = "АКТИВНЫЕ";
				break;
			}
			
			case "finished": {
				$uid = $_COOKIE["BITRIX_SM_LOGIN"];
				$sql = "SELECT bso.*, DATE_FORMAT(DATE_INSERT,'%d.%m.%Y %H:%i:%s') AS DATE_INSERT FROM b_sale_order AS bso LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE usr.LOGIN=:uid AND bso.CANCELED='N' AND bso.STATUS_ID IN ('F') ORDER BY bso.DATE_INSERT DESC";
				$comm = $conn->createCommand($sql);
				$comm->bindParam(":uid",$uid,PDO::PARAM_STR);
				
				$title = "ВЫПОЛНЕННЫЕ";
				break;
			}
			
			case "canceled": {
				$uid = $_COOKIE["BITRIX_SM_LOGIN"];
				$sql = "SELECT bso.*, DATE_FORMAT(DATE_INSERT,'%d.%m.%Y %H:%i:%s') AS DATE_INSERT FROM b_sale_order AS bso LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE usr.LOGIN=:uid AND bso.CANCELED='Y' ORDER BY bso.DATE_INSERT DESC";
				$comm = $conn->createCommand($sql);
				$comm->bindParam(":uid",$uid,PDO::PARAM_STR);
				
				$title = "ОТМЕНЕННЫЕ";
				break;
			}
		}
		if($type=="make"){
			
			if($_POST["Order"]["step"]=="2"){
				
				$conn = Yii::app()->db2;
				$user_id = $conn->createCommand("SELECT ID FROM b_user WHERE LOGIN=:login")->bindParam(":login",$_COOKIE["BITRIX_SM_LOGIN"],PDO::PARAM_STR)->queryScalar();
				$transaction = $conn->beginTransaction();
				try{
					$create_order = "INSERT INTO b_sale_order(`AFFILIATE_ID`, `SUM_PAID`, `STAT_GID`, `TAX_VALUE`, `USER_DESCRIPTION`, `DELIVERY_ID`, `PAY_SYSTEM_ID`, `USER_ID`, `DISCOUNT_VALUE`, `CURRENCY`, `PRICE`, `PRICE_DELIVERY`, `STATUS_ID`, `CANCELED`, `PAYED`, `PERSON_TYPE_ID`, `LID`, DATE_STATUS, DATE_INSERT, DATE_UPDATE) VALUES( NULL , '0', 'BITRIX_SM.MTg0OC44ODEuTjAuLi5zMQ==', '0',  NULL ,  NULL ,  NULL , :user_id, '0', 'RUB', :total_price, '0', 'N', 'N', 'N', '1', 's1', now(), now(), now())";
					$command=$conn->createCommand($create_order);
					$command->bindParam(":user_id",$user_id,PDO::PARAM_STR);
					$command->bindParam(":total_price",ShoppingCart::getTotalPrice(),PDO::PARAM_STR);
					$command->execute();
					
					$order_id = Yii::app()->db2->getLastInsertID();
					
					foreach($_POST["Order"] as $order_prop_key=>$order_prop_value){
						if(stripos($order_prop_key,"ORDER_PROP_")!==false){
							$prop_id = explode("_",str_ireplace("ORDER_PROP_","",$order_prop_key));
							$prop_name = $conn->createCommand("SELECT NAME FROM b_sale_order_props WHERE ID=:id")->bindParam(":id",$prop_id[1],PDO::PARAM_INT)->queryScalar();
							$prop_value_sql = "INSERT INTO b_sale_order_props_value(`CODE`, `VALUE`, `NAME`, `ORDER_PROPS_ID`, `ORDER_ID`) VALUES(:code, :value, :name, :prop_id, :order_id)";
							$command=$conn->createCommand($prop_value_sql);
							$command->bindParam(":code",$prop_id[0],PDO::PARAM_STR);
							$command->bindParam(":value",$order_prop_value,PDO::PARAM_STR);
							$command->bindParam(":name",$prop_name,PDO::PARAM_STR);
							$command->bindParam(":prop_id",$prop_id[1],PDO::PARAM_STR);
							$command->bindParam(":order_id",$order_id,PDO::PARAM_STR);
							$command->execute();
							
							if( $prop_id[0]=="FIO" ){
								$id = Yii::app()->db2->getLastInsertID();
								$prop_value_sql = "INSERT INTO b_sale_user_props_value(`VALUE`, `NAME`, `ORDER_PROPS_ID`, `USER_PROPS_ID`) VALUES(:value, :name, :code, :pid)";
								$command=$conn->createCommand($prop_value_sql);
								$command->bindParam(":code",$prop_id[1],PDO::PARAM_STR);
								$command->bindParam(":value",$order_prop_value,PDO::PARAM_STR);
								$command->bindParam(":name",$prop_name,PDO::PARAM_STR);
								$command->bindParam(":pid",$id,PDO::PARAM_STR);
								$command->execute();
							}
						}
					}
					
					$update_basket_items = "UPDATE b_sale_basket SET ORDER_ID=:order_id WHERE FUSER_ID=:uid AND ( ORDER_ID IS NULL OR ORDER_ID=0 )";
					$command=$conn->createCommand($update_basket_items);
					$command->bindParam(":order_id",$order_id,PDO::PARAM_INT);
					$command->bindParam(":uid",$_COOKIE["BITRIX_SM_SALE_UID"],PDO::PARAM_INT);
					$command->execute();
					
					$added_order_info = "SELECT *, DATE_FORMAT(DATE_INSERT, '%d.%m.%Y') as DATE_INSERT_FORMAT FROM b_sale_order WHERE ID=:order_id";
					$command=$conn->createCommand($update_basket_items);
					
					$transaction->commit();
					
					Yii::app()->user->setFlash(YFlashMessages::NOTICE_MESSAGE, Yii::t('page', 'Ваш заказ принят, в ближайшее время с Вами свяжется менеджер!'));
					$this->redirect(Yii::app()->createUrl("/shop/map/basket"));
					
				}catch(Exeotion $e){
					var_dump($e);
					$transaction->rollback();
				}
				
				
			}else{
				
				$uid = $_COOKIE["BITRIX_SM_SALE_UID"];
				$conn = Yii::app()->db2;
				$comm = $conn->createCommand("SELECT bsb.ID, bsb.NAME, bsb.PRICE, bsb.QUANTITY, bsb.PRICE*bsb.QUANTITY AS total, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE1') AS dt_start, (SELECT VALUE FROM b_sale_basket_props WHERE BASKET_ID=bsb.ID AND CODE='DATE2') AS dt_end FROM b_sale_basket AS bsb WHERE bsb.DELAY='N' AND bsb.FUSER_ID=:uid AND (bsb.ORDER_ID IS NULL OR bsb.ORDER_ID = 0) ORDER BY bsb.NAME");
				$comm->bindParam(":uid",$uid,PDO::PARAM_INT);
				$items = $comm->queryAll();
				
				$login = $_COOKIE["BITRIX_SM_LOGIN"];
				$comm = $conn->createCommand("SELECT ID, NAME, TYPE, REQUIED, CODE FROM b_sale_order_props ORDER BY SORT");
				//$comm->bindParam(":login",$login,PDO::PARAM_STR);
				$fields = $comm->queryAll();
				
				$this->render('neworder',array("basket_items"=>$items, "fields"=>$fields));
			}
		}else{
			/*$uid = $_COOKIE["BITRIX_SM_LOGIN"];
			$conn = Yii::app()->db2;
			$comm = $conn->createCommand($sql);
			$comm->bindParam(":uid",$uid,PDO::PARAM_STR);*/
			$res = $comm->queryAll();
			$this->render('order',array("data"=>$res, "title"=>$title));
		}
		
		
	}
	
	public function actionOrderDetail($id){
		
		$this->checkAuth(Yii::app()->createUrl("shop/map/order/".$id),"Для просмотра деталей заказа необходимо авторизоваться");
		
		$sql = "SELECT bsb.ID, bsb.PRICE, bsb.NAME, bsb.QUANTITY FROM `b_sale_order` AS bso RIGHT JOIN `b_sale_basket` AS bsb ON bsb.ORDER_ID=bso.ID LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE bso.ID=:id AND usr.LOGIN=:uid ORDER BY bsb.NAME";
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand($sql);
		$comm->bindParam(":id",$id,PDO::PARAM_INT);
		$comm->bindParam(":uid",$_COOKIE["BITRIX_SM_LOGIN"],PDO::PARAM_STR);
		$items = $comm->queryAll();
		
		$sql = "SELECT bso.ID, bso.PAYED, bso.ALLOW_DELIVERY, bso.PRICE_DELIVERY, bso.DELIVERY_ID, bso.STATUS_ID, bso.PAY_SYSTEM_ID, DATE_FORMAT(DATE_INSERT,'%d.%m.%Y %H:%i:%s') AS DATE_INSERT FROM b_sale_order AS bso LEFT OUTER JOIN b_user AS usr ON bso.USER_ID=usr.ID WHERE usr.LOGIN=:uid AND bso.ID=:id";
		$conn = Yii::app()->db2;
		$comm = $conn->createCommand($sql);
		$comm->bindParam(":id",$id,PDO::PARAM_INT);
		$comm->bindParam(":uid",$_COOKIE["BITRIX_SM_LOGIN"],PDO::PARAM_STR);
		$order = $comm->queryRow();
		
		$this->render('order_details', array("id"=>$id, "data"=>$items, "order"=>$order));
	}
	
	public function checkAuth($url="/",$msg=NULL){
		if(!Yii::app()->user->biuser){
			Yii::app()->user->setFlash(YFlashMessages::NOTICE_MESSAGE, $msg);
			$this->redirect(Yii::app()->createUrl("user/account/bilogin?backurl=".urlencode($url)));
		}
	}
}