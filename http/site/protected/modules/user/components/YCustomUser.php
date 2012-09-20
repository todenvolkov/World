<?php
class YCustomUser extends CWebUser
{
	
	public $biuser = false;
	
	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by starting session,
	 * performing cookie-based authentication if enabled, and updating the flash variables.
	 */
	public function init()
	{
		parent::init();
		if($_SESSION["SESS_AUTH"]["AUTHORIZED"]=='Y'){
			$this->biuser = true;
			//Yii::app()->user->setState('nick_name',$_SESSION["SESS_AUTH"]["LOGIN"]);
		}
	}
	
	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 * @return boolean whether the current application user is a guest.
	 */
	public function getIsGuest()
	{
		return $this->biuser?false:$this->getState('__id')===null;
	}
}
?>