<?php
namespace GTZ\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\TestFramework\Event\Magento;

class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'simpleshipping';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;



    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        //\Magento\Catalog\Model\ProductRepository $productRepository,

        array $data = []


        ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->_logger = $logger;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
     parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

    }


    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'simpleshipping' => 'GTZ Demo Shipping Method'
        , 'simpleshippingFastRate' => 'GTZ Demo Shipping Method Fast Rate'
        );
    }




/**
 * Load product from productId
 *
 * @param $id
 * @return $this
 */
public function getProductById($id)
{
    return $this->_productRepo
        ->getById($id);
}




    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {


        if (!$this->getConfigFlag('active')) {
            return false;
        };
      $_RateName = $this->getConfigData('rateName');
      $_Error_Message = $this->getConfigData('specificerrmsg');


        if(strlen($_RateName) <=1){
            $_RateName = "GTZ Rate";
        }

        $originCountry = $this->_scopeConfig->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $request->getStoreId());
    

        $originzip = $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $request->getStoreId());
        

        $originCity = $this->_scopeConfig->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $request->getStoreId());

        $originState= $this->_scopeConfig->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $request->getStoreId());

        $originAddress= $this->_scopeConfig->getValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS1,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        $request->getStoreId());


       

        
         $ProductClass="";

        $items = $request->getAllItems();
        $quantity = 0;
        $totalPrice = 0;
        $length = 0;
        $width = 0;
        $height = 0;
        $Itemlength = 0;
        $Itemwidth = 0;
        $Itemheight = 0;
        $ItemsArray = array();
        foreach($items as $item) {
            $totalPrice += $item->getPrice() * (int)$item->getQty();
            $quantity += $item->getQty();
            $ProductName =  $item->getName();

            $productId = $item->getProducId();
            $product = $item->getProduct();
            $product->load($item->getProduct()->getId());
            $ProductClass = $product->getData('class');
            $Weight = (int)$product->getWeight();
           
            if(is_null($product->getLength())){
                    $length = $product->getData('length');
            }else {
                $length = $product->getLength();
            }

            if(is_null($product->getWidth())){
                $width = $product->getData('width');
            } else {
                $width = $product->getWidth();
            }
            
            if(is_null($product->getHeight())){
                $height = $product->getData('height');
            } else {
                $height = $product->getHeight();
            }
            $item = array(
                "PieceCount"=> $quantity,
                "PalletCount"=> "1", 
                "Length"=> $length,
                "Width"=> $width,
                "Height"=> $height,
                "Weight"=> $Weight,
                "WeightType"=> 1,
                "ProductClass"=> $ProductClass,
                "LinearFeet"=> 8,
                "NmfcNumber"=> "WooCommerce12345",
                "Description"=> "Woocommerce Automated Shipping Quote",
                "PackageType"=> 0,
                "Hazmat"=> false,
                "HazmatClass"=> "",
                "PackingGroupNumber"=> "",
                "UnPoNumber"=> "",
                "Stackable"=> false
            );

            array_push($ItemsArray, $item);
              $custom = [];



              
        };

        if($this->getConfigData('usegenericclass')){
            $ProductClass =  $this->getConfigData('class');
        }





        $d_City = $request->getDestCity();
        $d_address = $request->getDestStreet();
        $d_zip = $request->getDestPostcode();
        $d_State = $request->getDestRegionCode();



                $currentDate = (string)date("m/d/Y");

                $curl = curl_init();
                $data = json_encode(array(
                    "PickupDate"=> htmlspecialchars($currentDate),
                    "ExtremeLength"=> null,
                    "ExtremeLengthBundleCount"=> null,
                    "Stackable"=> false,
                    "TerminalPickup"=> false,
                    "ValueOfGoods"=> $totalPrice,
                    "ShipmentNew"=> false,
                    "Origin" =>array( 
                    "Street"=> "",
                    "City"=>  $originCity,
                    "State"=> $originState,
                    "Zip"=> $originzip,
                    "Country"=> "USA"
                    ),
                    "Destination"=> array(
                    "Street"=> $d_address,
                    "City"=> $d_City,
                    "State"=> $d_State,
                    "Zip"=> $d_zip,
                    "Country"=> "USA" 
                    ),
                    "Items" => $ItemsArray,
                    "Accessorials" => array()
                    ));


         
                        $username =$this->getConfigData('username');
                        $password = $this->getConfigData('password');
                        $apikey = $this->getConfigData('apikey');
                        
                       
              

                        // Generate Auth Header / endpoint 
                        $externalURI = "http://api.globaltranz.com/GTZIntegrate/LTL/1.0/RateRequestPartner?apiKey=".$apikey;
                        $creds = $username . ":" . $password;
                        $authString = "Authorization: Basic " . base64_encode($creds); 
    

                        
                curl_setopt_array($curl, array(
                CURLOPT_URL => $externalURI,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                         $authString,
                        "Cache-Control: no-cache",
                        "Content-Type: application/json",
                        "Postman-Token: b4293079-a642-462b-8649-1dda8bf3ef57"
                    )
                ));
                $response = curl_exec($curl);
    
                $json = json_decode($response, true);
                $err = curl_error($curl);
                $err = false;
                curl_close($curl);
                
                try{
                if ($err) {
                # If error, show error notice on screen 
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($_RateName);
                $errorMsg = $this->getConfigData('specificerrmsg');
                $error->setErrorMessage(__(
                        $errorMsg
                    )
                );
                return $error;
               
                
                } elseif(isset($json['LtlAmount'])){


                    $result = $this->_rateResultFactory->create();

                    $lowPrice = $json['LtlAmount'];
                    $fastDays = $json['LtlServiceDays'];
                    /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                    $method = $this->_rateMethodFactory->create();

                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($_RateName);

                    $method->setMethod("Fast Rate");
                    $method->setMethodTitle("Fastest Delivery - " . $fastDays . " Days.");


                    $method->setPrice($lowPrice);
                    $method->setCost($lowPrice);
                    $result->append($method);
                }elseif (isset($json['LowestCostRate']) && isset($json['QuickestTransitRate'])) {
                        $json = json_decode($response, true);
                        $OnlyOne = False;
    
                        $lowPrice = $json['LowestCostRate']['LtlAmount'];
                        $fastPrice = $json['QuickestTransitRate']['LtlAmount'];
    
                        $lowDays = $json['LowestCostRate']['LtlServiceDays'];
                        $fastDays = $json['QuickestTransitRate']['LtlServiceDays'];
    
                        if( $lowPrice && $fastPrice){
    
                        } else {
    
                                $onePrice = $json['LtlAmount'];
                                $OnlyOne = True;
                                $days = $json['LtlServiceDays'];
                        }
    
    
                if ($OnlyOne == True && $err == False){
                  
                                if((double)$onePrice <=1 ){
    
                                }else {

                                        
                                       /** @var \Magento\Shipping\Model\Rate\Result $result */

                                $result = $this->_rateResultFactory->create();

                                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                                $method = $this->_rateMethodFactory->create();

                                $method->setCarrier("simpleshipping");
                                $method->setCarrierTitle($_RateName);

                                $method->setMethod("simpleshipping");
                                $method->setMethodTitle("Best Rate");


                                $method->setPrice($onePrice);
                                $method->setCost($onePrice);

                                $result->append($method);

                                }
    
                                
                        } else {
    
                      
                            if((double)$lowPrice <=1 || (double)$fastPrice <=1){
    
                            }else {
 

                                
                                $result = $this->_rateResultFactory->create();

                                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                                $method = $this->_rateMethodFactory->create();

                                $method->setCarrier($this->_code);
                                $method->setCarrierTitle($_RateName);

                                $method->setMethod("Fast Rate");
                                $method->setMethodTitle("Fastest Delivery - " . $fastDays . " Days.");


                                $method->setPrice($fastPrice);
                                $method->setCost($fastPrice);
                                $result->append($method);




                                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                                $method2 = $this->_rateMethodFactory->create();

                                $method2->setCarrier($this->_code);
                                $method2->setCarrierTitle($_RateName);

                                $method2->setMethod($this->_code);
                                $method2->setMethodTitle("Lowest Rate - ". $lowDays . " Days.");


                                $method2->setPrice($lowPrice);
                                $method2->setCost($lowPrice);
                                $result->append($method2);

                            }
    
                        }
                } else {
                    $error = $this->_rateErrorFactory->create();
                    $error->setCarrier($this->_code);
                    $error->setCarrierTitle($_RateName);
                    $errorMsg = $this->getConfigData('specificerrmsg');
                    $error->setErrorMessage(__(
                            $errorMsg
                        )
                    );
                    return $error;

                }} catch(Exception $e){
                    $error = $this->_rateErrorFactory->create();
                    $error->setCarrier($this->_code);
                    $error->setCarrierTitle($_RateName);
                    $errorMsg = $this->getConfigData('specificerrmsg');
                    $error->setErrorMessage(__(
                            $errorMsg
                        )
                    );
                    return $error;
                }
    
      
        return $result;
    }
}