    <?php
    /**
     * Created by PhpStorm.
     * User: hopvt
     * Date: 6/7/18
     * Time: 12:49 AM
     */

    require_once 'app/Mage.php';

    Mage::app();

    $customer = Mage::getModel('customer/customer')
        ->setWebsiteId(1)
        ->loadByEmail('hop.vth@gmail.com');

    $quote = Mage::getModel('sales/quote')
        ->setStoreId(Mage::app()->getStore('default')->getId());

    if ($customer) {
        $quote->assignCustomer($customer);
    } else {
        // for guest orders only:
        $quote->setCustomerEmail('customer@example.com');
    }

    // add product(s)
    $product = Mage::getModel('catalog/product')->load(8);
    $buyInfo = array(
        'qty' => 1,

    );
    $quote->addProduct($product, new Varien_Object($buyInfo));

    $addressData = array(
        'firstname' => 'Test',
        'lastname' => 'Test',
        'street' => 'Sample Street 10',
        'city' => 'Somewhere',
        'postcode' => '123456',
        'telephone' => '123456',
        'country_id' => 'US',
        'region_id' => 12, // id from directory_country_region table
    );

    $billingAddress = $quote->getBillingAddress()->addData($addressData);
    $shippingAddress = $quote->getShippingAddress()->addData($addressData);

    $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
        ->setShippingMethod('flatrate_flatrate')
        ->setPaymentMethod('checkmo');

    $quote->getPayment()->importData(array('method' => 'checkmo'));

    $quote->collectTotals()->save();

    $service = Mage::getModel('sales/service_quote', $quote);
    $service->submitAll();
    $order = $service->getOrder();

    printf("Created order %s\n", $order->getIncrementId());