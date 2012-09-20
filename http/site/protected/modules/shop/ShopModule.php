<?php

class ShopModule extends YWebModule
{
	public $version = '0.7';

	// Is the Shop in debug Mode?
	public $debug = false;

  // Whether the installer should install some demo data
	public $installDemoData = true;

	// Enable this to use the shop module together with the yii user
	// management module
	public $useWithYum = false;

	// Names of the tables
	public $categoryTable = 'shop_category';
	public $productsTable = 'shop_products';
	public $orderTable = 'shop_order';
	public $orderPositionTable = 'shop_order_position';
	public $customerTable = 'shop_customer';
	public $addressTable = 'shop_address';
	public $imageTable = 'shop_image';
	public $shippingMethodTable = 'shop_shipping_method';
	public $paymentMethodTable = 'shop_payment_method';
	public $taxTable = 'shop_tax';
	public $productSpecificationTable = 'shop_product_specification';
	public $productVariationTable = 'shop_product_variation';
	public $currencySymbol = '$';

	public $logoPath = 'logo.jpg';
	public $slipView = '/order/slip';
	public $invoiceView = '/order/invoice';
	public $footerView = '/order/footer';

	public $dateFormat = 'd/m/Y';
	
	public $imageWidthThumb = 100;
	public $imageWidth = 200;

	public $notifyAdminEmail = null;

	public $termsView = '/order/terms';
	public $successAction = array('//shop/order/success');
	public $failureAction = array('//shop/order/failure');

	public $loginUrl = array('/site/login');

	// Where the uploaded product images are stored:
	public $productImagesFolder = 'productimages'; // Approot/...

	//public $layout = 'application.modules.shop.views.layouts.shop';

	public function init()
	{
		$this->setImport(array(
			'shop.models.*',
			'shop.components.*',
		));
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			return true;
		}
		else
			return false;
	}
	
	public function getNavigation()
    {
        return array(
            Yii::t('shop','Products') => '/shop/products/admin/',
            Yii::t('shop','Porduct Specification')   => '/shop/productSpecification/admin',
            Yii::t('shop','Orders')       => '/shop/order/admin',
            Yii::t('shop','Taxes') => '/shop/tax/admin'
        );
    }
	
	public function getName()
    {
        return Yii::t('shop', 'Shop');
    }

    public function getCategory()
    {
        return Yii::t('shop', 'Shop');
    }

    public function getDescription()
    {
        return Yii::t('shop', 'Модуль для управления магазином');
    }

    public function getAuthor()
    {
        return Yii::t('shop', 'yupe team');
    }

    public function getAuthorEmail()
    {
        return 'shop';
    }

    public function getUrl()
    {
        return 'http://shop.ru';
    }

    public function getVersion()
    {
        return '0.7';
    }

    public function getIcon()
    {
        return 'shopping-cart';
    }
}
