<?php
/*
 * @version		$Id: jomclPesapal.php 3.0 2015-02-16
 * @package		Joomla
 * @copyright   Copyright (C) 2013 - 2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'utils.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'payments.php');
if (!class_exists('OAuthException')) {
  require_once(JPATH_ROOT.DS.'plugins'.DS.'jomclassifiedspayment'.DS.'jomclPesapal'.DS.'OAuth.php');
}


class plgJomClassifiedsPaymentJomclPesapal extends JPlugin {			
	function plgJomClassifiedsPaymentJomclPesapal( &$subject, $params ) {
		parent::__construct( $subject, $params );
		$config = JomclUtils::getCfg();
		$params["plugin_name"] = "jomclPesapal";           
        $params["currency_code"] = $config->paycurrency; 
		$params["mode"] = $this->params->get('mode');
		$params["img"]= "pesapal.jpg";
		$params["name"]= JText::_('COM_JOMCLASSIFIEDS_PESAPAL_NAME');
		$params["desc"]= JText::_('COM_JOMCLASSIFIEDS_PESAPAL_DESC');	
		$params["mode"] = $this->params->get('mode');
		$params["sid"]= $this->params->get('sid');
		$params["sec_word"]= $this->params->get('secretword');
		$params["log"]= $this->params->get('log', 0);
		if($params["log"] > 0){
			ini_set('log_errors', true);
			ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');
        }	
        $this->params = $params;
	}
		
	function onProcessPayment($val = NULL) {
		$_action = JRequest::getVar('action');
		switch($_action) {		
			case 'notify' :		    
				$this->onPaymentIPN();
				break;	
			case 'return' :				
				$this->onPaymentSuccess();
				break;		
			default :
				$this->doPayment($val);
		}
	}
	
	function onPaymentMethodList(){
     		$html ='';
			$description = JText::_('COM_JOMCLASSIFIEDS_PLUGIN_CONFIGURE_NOTIFY');	
       		if ($this->params['currency_code'] != '' && $this->params['sid'] != '' ) {	
			 $description = $this->params["desc"];
			}			
			$html .='<div class="jomcl-table-row">';
		  	$html .='<label class="jomcl-table-col jomcl-col-1 text-center"><input type="radio" name="ptype" value="'.$this->params["plugin_name"].'" ></label>';
		  	$html .='<label class="jomcl-table-col jomcl-col-2">'.$this->params["name"].'</label>';
		    $html .='<small class="jomcl-table-col jomcl-col-6 jomcl-mobile-hide muted ">'.$description.'</small>';			
			$html .='<label class="jomcl-table-col jomcl-col-1 text-center"><img src="'.JURI::root().'plugins/jomclassifiedspayment/'.$this->params["plugin_name"].'/assets/'.$this->params["img"].'" alt="'.$this->params["plugin_name"].'"></label>';
			$html .='</div>';			
		
        return $html;
    }

	
	
	function doPayment($val) {
		$token = $params = NULL;
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		$iframelink = 'https://www.pesapal.com/API/PostPesapalDirectOrderV4';
			if($this->params["mode"] == 'N'){
			$_action = 'https://www.pesapal.com/API/PostPesapalDirectOrderV4';
			}
			else{
			$_action = 'https://www.demo.pesapal.com/API/PostPesapalDirectOrderV4';
			}
		$_type = 'MERCHANT';		
		
		$_advId = $val["advert_id"];
		$_title = $val["advert_title"];
		$_orderId = $val["order_id"];
		$_amount =  number_format($val["order_amount"], 2, '.', '');
						
		$_userId = jomclUtils::getColumn('adverts', 'userid', $_advId);	
	    $user = JFactory::getUser($_userId);
		$_email = $user->email;	
		$_description = jomclUtils::getColumn('adverts', 'description', $_advId);
		$_desc = ($_description == '') ? $_title : $_description;		
		$_itemId = JRequest::getInt('Itemid');
		$_currency = $this->params["currency_code"];
		$consumer_key = $this->params["sid"];
		$consumer_secret = $this->params["sec_word"];					
		
		$_linkbase = JRoute::_( JURI::root().'index.php?option=com_jomclassifieds&view=payment&task=process&ptype='.$this->params["plugin_name"]);		
		$_notify = JRoute::_($_linkbase.'&action=notify&id='.$_advId.'&Itemid='.$_itemId);
		
		$callback_url = JRoute::_($_linkbase.'&action=return&id='.$_advId.'&Itemid='.$_itemId);

		$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 			xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" Amount=\"".$_amount."\" Description=\"".$_desc."\" Type=\"".$_type."\" Reference=\"".$_orderId.','.$_advId.','.$_amount.','.$_currency."\" Name=\"".htmlspecialchars($_title)."\" Email=\"".$_email."\" Currency=\"".$_currency."\" xmlns=\"http://www.pesapal.com\" />";
		$post_xml = htmlentities($post_xml);

		$consumer = new OAuthConsumer($consumer_key, $consumer_secret);

    	$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
		$iframe_src->set_parameter("oauth_callback", $callback_url);
		$iframe_src->set_parameter("pesapal_request_data", $post_xml);
		$iframe_src->sign_request($signature_method, $consumer, $token);
		?>

		<iframe src="<?php echo $iframe_src;?>" width="100%" height="700px"  scrolling="no" frameBorder="0">
		<p>Browser unable to load iFrame</p>
		</iframe>
		<?php
	}	

	function onPaymentIPN(){	
	$consumer_key = $this->params["sid"];
	$consumer_secret = $this->params["sec_word"];
		if($this->params["mode"] == 'N'){
		$statusrequestAPI = 'https://www.pesapal.com/api/querypaymentstatus';
		}
		else{
		$statusrequestAPI = 'https://www.demo.pesapal.com/api/querypaymentstatus';
		}
		
	// Parameters sent to you by PesaPal IPN
	$pesapalNotification= @$_GET['pesapal_notification_type'];
	$pesapalTrackingId=$_GET['pesapal_transaction_tracking_id'];
	$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];

		if($pesapalNotification=="CHANGE" && $pesapalTrackingId!=''){
   		$token = $params = NULL;
   		$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
   		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

   		//get transaction status
   		$request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
   		$request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
   		$request_status->set_parameter("pesapal_transaction_tracking_id",$pesapalTrackingId);
   		$request_status->sign_request($signature_method, $consumer, $token);

   		$ch = curl_init();
   		curl_setopt($ch, CURLOPT_URL, $request_status);
   		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   		curl_setopt($ch, CURLOPT_HEADER, 1);
   		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
   			if(defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True'){
   	   		$proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
   	   		curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
   	   		curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
       		curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
   			}

   		$response = curl_exec($ch);

   		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
   		$raw_header  = substr($response, 0, $header_size - 4);
   		$headerArray = explode("\r\n\r\n", $raw_header);
   		$header      = $headerArray[count($headerArray) - 1];

   		//transaction status
   		$elements = preg_split("/=/",substr($response, $header_size));
   		$status = $elements[1];  

   		curl_close ($ch);
	   
   		$_pstxn_Id = $pesapalTrackingId; 
   		$pesapal_merchant_reference = explode(",",$pesapal_merchant_reference);
   		$_psorder_Id = $pesapal_merchant_reference[0];
   		$_psadvId = $pesapal_merchant_reference[1];
		$_psamount = $pesapal_merchant_reference[2];
		$_pscurrency = $pesapal_merchant_reference[3];
	
	
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__jomcl_payments WHERE advertid='.$_psadvId.' AND id='.$_psorder_Id;
    	$db->setQuery($query);
    	$item = $db->loadObject();						
		$_amount = $item->amount;
		$_txn_id = $item->transactionid;	
		$_currency = $this->params["currency_code"];	
		
		if($item->status == "completed")
		return;
		
	
		//Conditions check
		$error = 0;	
			if($_currency != $_pscurrency) {  
		 		$this->errorLog('Invalid Currency '.$_pscurrency);           
				$error = 1;	
        	}	
		
			if($_amount != $_psamount) {  
		 		$this->errorLog('Invalid Amount '.$_psamount);           
				$error = 1;		
        	}
        
        	if($_txn_id == $_pstxn_Id) {  
		  		$this->errorLog('Duplicate Transaction ID '.$_pstxn_Id);                   
				$error = 2;		
        	}	
   
   			if($status = 'COMPLETED') {
				if($error == 0){        		
					JomclPayments::paySuccess($_psadvId,$_psorder_Id,'completed',$_pstxn_Id);
					$this->errorLog('Status'.$status);					
				$resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
      				ob_start();
      				echo $resp;
     				ob_flush();
     				exit;


				}
				else if($error == 1){				    
					JomclPayments::payError($_psadvId,$_psorder_Id,'canceled',$_pstxn_Id);		
					$this->errorLog('Status'.'canceled');  
				$resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
      				ob_start();
      				echo $resp;
     				ob_flush();
     				exit; 
				}			
			} 
		}   
  }
  
  function errorLog($message){ 		
		if($this->params["log"] > 0) {		
			error_log('Start error log'.$message);
		}
	}
	
 function onPaymentSuccess() {
		$mainframe = JFactory::getApplication();
		$itemid = JRequest::getInt('Itemid');
		$link = JRoute::_( JURI::root().'index.php?option=com_jomclassifieds&view=user&Itemid='.$itemid );
		$msg = JText::_('PAYMENT_SUCCESS');
  		$mainframe->redirect($link, $msg);
	}
}