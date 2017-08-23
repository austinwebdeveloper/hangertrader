<?php

/*
 * @version		$Id: jomcl2CO.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013 - 2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'utils.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'payments.php');

class plgJomClassifiedsPaymentJomcl2CO extends JPlugin {
		
	function plgJomClassifiedsPaymentJomcl2CO( &$subject, $params ) {
		parent::__construct( $subject, $params );
		
		$config = JomclUtils::getCfg();
		
		$params["plugin_name"] = "jomcl2CO";           
        $params["currency_code"] = $config->paycurrency;   
		$params["img"]= "2co.png";
		$params["name"]= JText::_('COM_JOMCLASSIFIEDS_2CO_NAME');
		$params["desc"]= JText::_('COM_JOMCLASSIFIEDS_2CO_DESC');	
		$params["mode"] = $this->params->get('mode');
		$params["sid"]= $this->params->get('sid');
		$params["sec_word"]= $this->params->get('secretword');
		$params["lang"]= $this->params->get('lang');
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
		    $html .='<small class="jomcl-table-col jomcl-col-6 jomcl-mobile-hide muted">'.$description.'</small>';			
			$html .='<label class="jomcl-table-col jomcl-col-1 text-center"><img src="'.JURI::root().'plugins/jomclassifiedspayment/'.$this->params["plugin_name"].'/assets/'.$this->params["img"].'" alt="'.$this->params["plugin_name"].'" ></label>';
			$html .='</div>';
			
		 
        return $html;
    }
	
	
	function doPayment($val) {	
		
		$_advId = $val["advert_id"];
		$_title = $val["advert_title"];
		$_orderId = $val["order_id"];
		$_orderAmount = $val["order_amount"];		
		
		$_itemId = JRequest::getInt('Itemid');
		$_sid = $this->params["sid"];		
		$_currency = $this->params["currency_code"];
		$_lang = $this->params["lang"];
		$_action = 'www.2checkout.com/checkout/purchase';		
		
		$_linkbase = JRoute::_( JURI::root().'index.php?option=com_jomclassifieds&view=payment&task=process&ptype='.$this->params["plugin_name"]);
		$_notify = JRoute::_($_linkbase.'&action=notify&id='.$_advId.'&Itemid='.$_itemId);
		
		echo JText::_('REDIRECTING_TO_TWOCO');			
		$html  = '<form id="tcoform" action="https://'.$_action.'" method="post">';	
		$html .= '<input type="hidden" name="sid" value="'.$_sid.'">';
		$html .= '<input type="hidden" name="mode" value="2CO" />';	
		$html .= '<input type="hidden" name="currency_code" value="'.$_currency.'">';		
		$html .= '<input type="hidden" name="li_0_type" value="product"/>';		
		$html .= '<input type="hidden" name="li_0_name" value="'.htmlspecialchars($_title).'">';
		$html .= '<input type="hidden" name="li_0_price" value="'.number_format($_orderAmount, 2, '.', '').'"/>';
		$html .= '<input type="hidden" name="li_0_tangible" value="N"/>';
		$html .= '<input type="hidden" name="li_0_product_id" value="'.$_advId.'">';
		$html .= '<input type="hidden" name="merchant_order_id" value="'.$_orderId.'">';	
		$html .= '<input type="hidden" name="lang" value="'.$_lang.'">';
		$html .= '<input type="hidden" name="page_itemid" value="'.$_itemId.'">';
		if($this->params["mode"] == 'Y'){
		 $html .= '<input type="hidden" name="demo" value="Y">';
		 $html .= '<input type="hidden" name="x_receipt_link_url" value="'.$_notify.'">';		
		}				
		$html .= '</form>';
		$html .= '<script type="text/javascript">customFormSubmit("tcoform");</script>';
		echo $html;
		
	}	
	
	function onPaymentIPN() {		
		
		$db = JFactory::getDBO();			
		$account_type  = $this->params["mode"];			
		$_currency = $this->params["currency_code"];		
		$_advId = ($account_type == 'Y') ? $_REQUEST['li_0_product_id'] : $_REQUEST['item_id_1'];	
		$item_id = ($account_type == 'Y') ? $_REQUEST['merchant_order_id'] : $_REQUEST['vendor_order_id'];
	
				
		if($account_type == 'Y'){						
			$status ='completed';				
			JomclPayments::paySuccess($_advId,$item_id,$status,$_REQUEST['order_number']);			
			return false;
									
		}	else {	
													
		$query = 'SELECT * FROM #__jomcl_payments WHERE advertid='.$_advId.' AND id='.$item_id;
       	$db->setQuery($query);
       	$item = $db->loadObject();						
		$_amount = $item->amount;
		$_txn_id = $item->transactionid;	
		
		$passback = array(); 
        $request_fields = array('sale_id',
                                'md5_hash', 
                                'vendor_id', 
                                'vendor_order_id', 
                                'invoice_id', 
                                'fraud_status',
                                'invoice_status', 
                                'order_number', 
                                'invoice_list_amount',
                                'list_currency', 
                                'message_type',
                                'message_description',
                                'message_id',								
								'item_id_1'
                                );
        
        foreach ($request_fields as $field) {
            $passback[$field] = (isset($_REQUEST[$field])) ? $_REQUEST[$field] : false;
        }
		
		$this->errorLog('Passback '.print_r($passback, true));
		
        $transaction_amount = $passback['invoice_list_amount'];
        $transaction_currency = $passback['list_currency'];        
        $status = $passback['fraud_status'];
        $invoice_status = $passback['invoice_status'];		
		$sale_id = $passback['sale_id'];	
		
		//Conditions check
		$error = 0;	
		
		if (!$this->checkHash($passback,  $this->params["sec_word"])) { 		 
		  		$this->errorLog('Wronghash '.$_advId);	
				++$error;	    	
          		
        }		
		
		if($transaction_amount != $_amount) {  
		 		$this->errorLog('Invalid Amount '.$transaction_amount);           
				++$error;	
        }
        
        if($transaction_currency != $_currency) {  
		  		$this->errorLog('Invalid Currency '.$transaction_currency);                   
				++$error;	
        }
		
		if($transaction_currency != $_currency) {  
		  		$this->errorLog('Invalid Currency '.$transaction_currency);                   
				++$error;	
        }
		
		if ($sale_id == $_txn_id) {
				$this->errorLog('txn_id mismatch'.$sale_id);
				++$error;
    	}
			
		
		if( ($status=='pass' && $error =='0') || ($status != 'fail' && $invoice_status == 'approved')) { 
		   		 $this->errorLog('Status'.$status);				
				 JomclPayments::paySuccess($_advId,$item_id,'completed',$sale_id);	
				 return ;
					
		} else {
				
			if ($status == 'fail' || $invoice_status == 'declined' || $passback['message_type'] == 'REFUND_ISSUED' || $error > 0 ) { 
					$this->errorLog('Status'.$status); 				  		
				 	JomclPayments::payError($_advId,$item_id,'canceled',$sale_id);   
					return ;          
            }			       
		}	
		
				
		// demo else	
	   }
					
	
    }
	
	function errorLog($message){ 		
		if($this->params["log"] > 0) {		
			error_log('Start error log'.$message);
		}
	}
	
	function checkHash($insMessage, $secretWord){
	
        $hashSid = $insMessage['vendor_id'];
        $hashOrder = $insMessage['sale_id'];
        $hashInvoice = $insMessage['invoice_id'];
        $StringToHash = strtoupper(md5($hashOrder . $hashSid . $hashInvoice . $secretWord));
        if ($StringToHash != $insMessage['md5_hash']) {
            return false;
        } else {
            return true;
        }
        return false;
    }
	
	function onPaymentSuccess() {
		$mainframe = JFactory::getApplication();
		$itemid = JRequest::getInt('Itemid');
		$link = JRoute::_( JURI::root().'index.php?option=com_jomclassifieds&view=user&Itemid='.$itemid);
		$msg = JText::_('PAYMENT_SUCCESS');
  		$mainframe->redirect($link, $msg);
	}
	
	
}
