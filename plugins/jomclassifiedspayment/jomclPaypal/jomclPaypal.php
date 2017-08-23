<?php

/*
 * @version		$Id: jomclPaypal.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013 - 2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'utils.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'payments.php');

class plgJomClassifiedsPaymentJomclPaypal extends JPlugin {	
	
	public  function __construct( &$subject, $params ) {
		parent::__construct( $subject, $params );	
		
		$config = JomclUtils::getCfg();	
		
		$params["plugin_name"] = "jomclPaypal";           
        $params["currency_code"] = $config->paycurrency;   
		$params["img"]= "Paypal.png";
		$params["name"]= JText::_('COM_JOMCLASSIFIEDS_PAYPAL_NAME');
		$params["desc"]= JText::_('COM_JOMCLASSIFIEDS_PAYPAL_DESC');	
		$params["email"]= $this->params->get('email');
		$params["mode"] = $this->params->get('mode');
		$params["cancel"]= $this->params->get('cancel');
		$params["return"]= $this->params->get('return');
		$params["log"]= $this->params->get('log', 0);
		if($params["log"] > 0){
			ini_set('log_errors', true);
			ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');	
         }	
               
        $this->params = $params;	
	}
	
	function onProcessPayment($val) {
		$_action = JRequest::getVar('action');
		switch($_action) {		
			case 'notify' :
				$this->onPaymentIPN();
				break;
			case 'return' :				
				$this->onPaymentSuccess();
				break;
			case 'cancel' :
				$this->onPaymentError();
				break;			
			default :
				$this->doPayment($val);
		}
	}
	
	function onPaymentMethodList(){
       		$html ='';
			$description = JText::_('COM_JOMCLASSIFIEDS_PLUGIN_CONFIGURE_NOTIFY');	
       		if ($this->params['currency_code'] != '' && $this->params["email"] != '' ) {
				 $description = $this->params["desc"];
			}			    
			$html .='<div class="jomcl-table-row">';
		  	$html .='<label class="jomcl-table-col jomcl-col-1 text-center"><input type="radio" name="ptype" value="'.$this->params["plugin_name"].'" ></label>';
		  	$html .='<label class="jomcl-table-col jomcl-col-2">'.$this->params["name"].'</label>';
		    $html .='<small class="jomcl-table-col jomcl-col-6 jomcl-mobile-hide muted ">'.$description.'</small>';			
			$html .='<label class="jomcl-table-col jomcl-col-1 text-center"><img src="'.JURI::root().'plugins/jomclassifiedspayment/'.$this->params["plugin_name"].'/assets/'.$this->params["img"].'"  alt="'.$this->params["plugin_name"].'"></label>';
			$html .='</div>';
	
        return $html;
    }
	
	function doPayment($val) {
	
		$_advId = $val["advert_id"];
		$_title = $val["advert_title"];
		$_orderId = $val["order_id"];
		$_orderAmount = $val["order_amount"];		
		$_action = $this->getPaypalHost();	
		$_email = $this->params["email"];
		$_currency = $this->params["currency_code"];	
		$_cancel = $this->params["cancel"];
		$_return = $this->params["return"];	
		$_itemId = JRequest::getInt('Itemid');		
		
		$_linkbase = JRoute::_( JURI::root().'index.php?option=com_jomclassifieds&view=payment&task=process&ptype='.$this->params["plugin_name"]);
		$_notify = JRoute::_($_linkbase.'&action=notify&id='.$_advId.'&Itemid='.$_itemId);
		
		if($_cancel == '') {
			$_cancel = JRoute::_($_linkbase.'&action=cancel&id='.$_advId.'&Itemid='.$_itemId);
		}		
		if($_return == '') {
			$_return = JRoute::_($_linkbase.'&action=return&id='.$_advId.'&Itemid='.$_itemId);	
		}
		
		echo JText::_('REDIRECTING_TO_PAYPAL');		
		$html  = '<form id="paypalform" action="https://'.$_action.'/cgi-bin/webscr" method="post">';
		$html .= '<input type="hidden" name="cmd" value="_xclick">';
		$html .= '<input id="custom" type="hidden" name="custom" value="'.$_advId.'">';
		$html .= '<input type="hidden" name="business" value="'.$_email.'">';
		$html .= '<input type="hidden" name="currency_code" value="'.$_currency.'">';
		$html .= '<input type="hidden" name="item_name" value="'.$_title.'">';
		$html .= '<input type="hidden" name="item_number" value="'.$_orderId.'">';
		$html .= '<input type="hidden" name="amount" value="'.$_orderAmount.'">';		
		$html .= '<input type="hidden" name="cancel_return" value="'.$_cancel.'">';
		$html .= '<input type="hidden" name="notify_url" value="'.$_notify.'">';
		$html .= '<input type="hidden" name="return" value="'.$_return.'">';
		$html .= '</form>';
		$html .= '<script type="text/javascript">customFormSubmit("paypalform");</script>';
		echo $html;
	}	
	
	function onPaymentIPN() {
		
				
		$listener = new IpnListener();
		$listener->paypal_host = $this->getPaypalHost();
		$id = JRequest::getInt('id');	
		
		
		try {
    		$listener->requirePostMethod();
    		$verified = $listener->processIpn();
			error_log('verified'.$verified);
			
		} catch (Exception $e) {
			$this->errorLog($e->getMessage());
    		exit(0);
		}
		
				
		if($verified) {
			$db = JFactory::getDBO();			
			$_advId = JRequest::getInt('id');
			$_email = $this->params["email"];					
			$_currency = $this->params["currency_code"];			
			$item_id = $_POST['item_number'];//get itemid from ipn		
										
			$query = 'SELECT * FROM #__jomcl_payments WHERE advertid='.$_advId.' AND id='.$item_id;//get amount and txn_id in payments table
       		$db->setQuery($query);
       		$item = $db->loadObject();				
			$_amount = $item->amount;
			$_txn_id = $item->transactionid;		
			
			$error = 0;	
			$status = $_POST['payment_status'];
			$account_type = ($this->params["mode"] == 'sandbox') ? 1 : 0 ;	
			
					
			if ($_POST['receiver_email'] != $_email) {			
				$this->errorLog('email mismatch'.$_POST['receiver_email']);
				++$error;
    		}
			
			if ($_POST['mc_gross'] != $_amount) {	
				$this->errorLog('Amount mismatch'.$_POST['mc_gross']);
				++$error;
    		}
			
			if ($_POST['mc_currency'] != $_currency) {
				$this->errorLog('Currency mismatch'.$_POST['mc_currency']);
				++$error;
    		}
			
			if ($_POST['txn_id'] == $_txn_id) {
				$this->errorLog('txn_id mismatch'.$_POST['txn_id']);
				++$error;
    		}
			
			//if not found on errors
			
			$this->errorLog('status'.$status);
			
			$this->errorLog('$error'.$error);
			
			$this->errorLog('$account_type'.$account_type);	
			
			// return JomclPayments::paySuccess($_advId,$item_id,'completed',$_POST['txn_id']);		
	
			
			if( ($status=='Completed' && $error == '0') || ($status =='Pending' && $account_type ==1) ){			
				 return JomclPayments::paySuccess($_advId,$item_id,'completed',$_POST['txn_id']);
			} else {
				 return JomclPayments::payError($_advId,$item_id,'canceled',$_POST['txn_id']);				
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
	
	function onPaymentError() {
		$mainframe = JFactory::getApplication();
		$itemid = JRequest::getInt('Itemid');
		$link = JRoute::_( JURI::root().'index.php?option=com_jomclassifieds&view=user&Itemid='.$itemid );
		$msg = JText::_('PAYMENT_FAILURE');
  		$mainframe->redirect($link, $msg);
	}
	

	function getPaypalHost() {
        if ($this->params["mode"] == 'sandbox') {
			return 'www.sandbox.paypal.com';
		} else {
        	return 'www.paypal.com';
		}
    }
	
}

class IpnListener {     
    
	var $paypal_host = ''; 
    var $post_data = array();
    var $response_status = '';
    var $response = ''; 
	var $use_ssl = true;  
    
    function fsockPost($encoded_data) {
		if ($this->use_ssl) {
            $uri = 'ssl://'.$this->paypal_host;
            $port = '443';
        } else {
            $uri = $this->paypal_host;
            $port = '80';
        }
		
        $fp = fsockopen($uri, $port, $errno, $errstr, 120);        
        if (!$fp) { 
            throw new Exception("fsockopen error: [$errno] $errstr");
        } 

        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Host: ".$this->paypal_host."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($encoded_data)."\r\n";
        $header .= "Connection: Close\r\n\r\n";
        
        fputs($fp, $header.$encoded_data."\r\n\r\n");
        
        while(!feof($fp)) { 
            if (empty($this->response)) {
                $this->response .= $status = fgets($fp, 1024); 
                $this->response_status = trim(substr($status, 9, 4));
            } else {
                $this->response .= fgets($fp, 1024); 
            }
        } 
        
        fclose($fp);
    }    
    
    function getResponse() {
        return $this->response;
    }

    function getResponseStatus() {
        return $this->response_status;
    }
   
    function processIpn() {
        $encoded_data = 'cmd=_notify-validate';
        
        if (!empty($_POST)) {
        	$this->post_data = $_POST;
            $encoded_data .= '&'.file_get_contents('php://input');
        } else {
            throw new Exception("No POST data found.");
        }

        $this->fsockPost($encoded_data);
        
        if (strpos($this->response_status, '200') === false) {
            throw new Exception("Invalid response status: ".$this->response_status);
        }
        
        if (strpos($this->response, "VERIFIED") !== false) {
            return true;
        } elseif (strpos($this->response, "INVALID") !== false) {
            return false;
        } else {
            throw new Exception("Unexpected response from PayPal.");
        }
    }
    
    function requirePostMethod() {
        if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Allow: POST', true, 405);
            throw new Exception("Invalid HTTP request method.");
        }
    }
	
}