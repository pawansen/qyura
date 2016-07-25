<?php
$order_id=isset($_GET['id'])?$_GET['id']:'';
if ( $data=Yii::app()->functions->getOrder($order_id)){
	$merchant_id=isset($data['merchant_id'])?$data['merchant_id']:'';		
}

$paymode=Yii::app()->functions->getOption('merchant_mercado_mode',$merchant_id);
$admin_mercado_id=Yii::app()->functions->getOption('merchant_mercado_id',$merchant_id);
$admin_mercado_key=Yii::app()->functions->getOption('merchant_mercado_key',$merchant_id);

/*COMMISSION*/
if ( Yii::app()->functions->isMerchantCommission($merchant_id)){	
	$paymode=Yii::app()->functions->getOptionAdmin('admin_mercado_mode');
    $admin_mercado_id=Yii::app()->functions->getOptionAdmin('admin_mercado_id');
    $admin_mercado_key=Yii::app()->functions->getOptionAdmin('admin_mercado_key');
}

$payment_description=Yii::t("default",'Payment for Food Order -');

require_once 'mercadopago/mercadopago.php';

$db_ext=new DbExt;
$payment_code=Yii::app()->functions->paymentCode("mercadopago");
$error='';

if (isset($_GET['status'])){	
	$status=$_GET['status'];	
	
	$reference=isset($_GET['external_reference'])?$_GET['external_reference']:'';		
	
	if ( $status=="success" || $status=="pending"){		
		try {
			$mp = new MP($admin_mercado_id, $admin_mercado_key);	
			$filters = array(            
               "external_reference" => $reference
            );      
            //dump($filters);
            $searchResult = $mp->search_payment($filters);           
            //dump($searchResult);            
            if (is_array($searchResult) && count($searchResult)>=1){
            	if ($searchResult['status']==200){
            		$ref=explode("-",$reference);            	
            		$package_id=isset($ref[0])?$ref[0]:'';
            		if (is_array($data) && count($data)>=1){	

            			$db_ext=new DbExt;
				        $params_logs=array(
				          'order_id'=>$order_id,
				          'payment_reference'=>$_GET['external_reference'],
				          'payment_type'=>$payment_code,
				          'raw_response'=>json_encode($_GET),
				          'date_created'=>date('c'),
				          'ip_address'=>$_SERVER['REMOTE_ADDR']
				        );				        
				        $db_ext->insertData("{{payment_order}}",$params_logs);				        
				        				        
				        $params_update=array('status'=>'paid');	        
	                    $db_ext->updateData("{{order}}",$params_update,'order_id',$order_id);
				        
				        header('Location: '.Yii::app()->request->baseUrl."/store/receipt/id/".$_GET['id']);
				        						
            		} else $error=Yii::t("default","Failed. Cannot process payment");            		        
                } else $error=Yii::t("default","Failed. Cannot process payment")." ".$searchResult['status'];
            } else $error=Yii::t("default","ERROR: Invalid response from Mercadopago");
            
		} catch (Exception $e){
			$error=$e->getMessage();
		}
			
	} else $error=Yii::t("default","Failed. Cannot process payment");
	
	if (!empty($error)){
		?>
		<div class="page-right-sidebar payment-option-page">
		    <div class="main">
		    <?php  echo "<p class=\"uk-text-danger\">".$error."</p>";?>
		    </div>
        </div>		    
		<?php	    
	}
	
} else  {
						
	if (is_array($data) && count($data)>=1){			
							    
		$amount_to_pay=isset($data['total_w_tax'])?Yii::app()->functions->standardPrettyFormat($data['total_w_tax']):'';
		$amount_to_pay=is_numeric($amount_to_pay)?unPrettyPrice($amount_to_pay):'';		
										
		try {
		   $mp = new MP($admin_mercado_id, $admin_mercado_key);	
		   
		   $reference=Yii::app()->functions->generateRandomKey();
		   $_SESSION['mcd_ref']=$reference;
		   
		   $preference_data = array(
				"items" => array(
					array(
					"title" => $payment_description." ".$data['merchant_name'],
					"currency_id" => Yii::app()->functions->adminCurrencyCode(),
					"category_id" => "services",
					"quantity" => 1,
					"unit_price" =>(float)$amount_to_pay
					)
				  ),
				"back_urls" => array(
				"success" =>Yii::app()->getBaseUrl(true)."/store/mercadoInit/id/$order_id/status/success",
				"failure" =>Yii::app()->getBaseUrl(true)."/store/mercadoInit/id/$order_id/status/failure",
				"pending" =>Yii::app()->getBaseUrl(true)."/store/mercadoInit/id/$order_id/status/pending",
			    ),
			    "auto_return"=>"approved",
			    "external_reference" => $reference,
		    );   		    
		    //dump($preference_data);
		    $preference = $mp->create_preference($preference_data);       		    
		    ?>
		    <div class="page-right-sidebar payment-option-page">
		    <div class="main">
		    <h2><?php echo Yii::t("default","Pay using Mercadopago")?></h2>
		    <?php if ($paymode=="Sandbox"):?>
		    <a href="<?php echo $preference["response"]["sandbox_init_point"];; ?>" name="MP-Checkout" class="lightblue-M-Ov-ArOn">Pay</a>
		    <?php else :?>
		    <a href="<?php echo $preference["response"]["init_point"]; ?>" name="MP-Checkout" class="lightblue-M-Ov-ArOn">
		    <?php echo Yii::t("default","Pay")?>
		    </a>
		    <?php endif;?>
		    <script type="text/javascript" src="http://mp-tools.mlstatic.com/buttons/render.js"></script>
		    <?php
		    
		    ?>
		      <div class="clear"></div>
		      		      
		    </div>
		    </div>
		    <?php		    		    
		   
		} catch (Exception $e) {			
			$error=$e->getMessage();
		}
	} else $error=Yii::t("default","Sorry but we cannot find what your are looking for.");
	
	if (!empty($error)):
	?>
	<div class="page-right-sidebar payment-option-page">
	    <div class="main">
	    <?php  echo "<p class=\"uk-text-danger\">".$error."</p>";?>
	    </div>
    </div>		    
	<?php
	endif;
}
?>