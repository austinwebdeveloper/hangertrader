<?php

/*
 * @version		$Id: jomclOffline.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013 - 2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'utils.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'payments.php');


class plgJomClassifiedsPaymentJomclOffline extends JPlugin {	
		
	function plgJomClassifiedsPaymentJomclOffline( &$subject, $params ) {
		parent::__construct( $subject, $params );
		
		$config = JomclUtils::getCfg();		
		$params["plugin_name"] = "jomclOffline";           
        $params["currency_code"] = $config->paycurrency;   
		$params["img"]= "Offline.png";
		$params["name"]= JText::_('COM_JOMCLASSIFIEDS_OFFLINE_NAME');
		$params["desc"]= JText::_('COM_JOMCLASSIFIEDS_OFFLINE_DESC');	
		$params["offline_desc"]= $this->params->get('offline_desc');
        $this->params = $params;		
				
	}
	
	function onProcessPayment($val = NULL) {
		$_action = JRequest::getVar('action');
		switch($_action) {				
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
       		if ($this->params['offline_desc'] != '') {				 
				 $description = $this->params["desc"];
			}		
			$html .='<div class="jomcl-table-row">';
		  	$html .='<label class="jomcl-table-col jomcl-col-1 text-center"><input type="radio" name="ptype" value="'.$this->params["plugin_name"].'" ></label>';
		  	$html .='<label class="jomcl-table-col jomcl-col-2">'.$this->params["name"].'</label>';
		    $html .='<small class="jomcl-table-col jomcl-col-6 muted jomcl-mobile-hide">'.$description.'</small>';			
			$html .='<label class="jomcl-table-col jomcl-col-1 text-center"><img src="'.JURI::root().'plugins/jomclassifiedspayment/'.$this->params["plugin_name"].'/assets/'.$this->params["img"].'" alt="'.$this->params["plugin_name"].'"></label>';
			$html .='</div>';		
		
        return $html;
    }
	
	
	function doPayment($val) {		
	
		$_itemId = JRequest::getInt('Itemid');			
		$_off_desc = $this->params["offline_desc"];		
		$_advId = $val["advert_id"];
		$_title = $val["advert_title"];
		$_orderId = $val["order_id"];
		$_orderAmount = $val["order_amount"];
		
		$_linkbase = JRoute::_( JURI::root().'index.php?option=com_jomclassifieds&view=payment&task=process&ptype='.$this->params["plugin_name"]);
		$_notify = JRoute::_($_linkbase.'&action=return&id='.$_advId.'&orderid='.$_orderId.'&Itemid='.$_itemId);
				
		echo JText::_('REDIRECTING_TO_OFFLINE_PAYMENT');	
		$html  = '<form id="offlineform" action='.$_notify.' method="post">';	
		$html .= '<input type="hidden" name="Off_amount" value="'.$_orderAmount.'" />'; 
		$html .= '<input type="hidden" name="Off_advtitle" value="'.$_title.'" />'; 
		$html .= '<input type="hidden" name="Off_advid" value="'.$_advId.'" />'; 			
		$html .= '</form>';
		$html .= '<script type="text/javascript">customFormSubmit("offlineform");</script>';
				
		echo $html;
	
		
	}	
	
	
	function onPaymentSuccess() {
		$document = JFactory::getDocument(); 
		$document->addStyleSheet(JURI::root()."components/com_jomclassifieds/css/jomClassifieds.css", 'text/css', "screen");		
		$_id = JRequest::getInt('id');
		$_orderid = JRequest::getInt('orderid');
		$_title = JRequest::getVar('Off_advtitle');
		$_amount = JRequest::getVar('Off_amount');		
		$_off_desc = $this->params["offline_desc"];	
		$_currency = $this->params["currency_code"];	
		$_itemId = JRequest::getInt('Itemid');	
		
		//Notify user send mail
		$this->notifyPayment($_id,$_orderid, $_title, $_amount);	
		
		$user = JFactory::getUser();			
		if ($user->guest) {
			$_action = JRoute::_('index.php?option=com_jomclassifieds&view=user&layout=add&Itemid='.$_itemId);			
		} else {
			$_action = JRoute::_('index.php?option=com_jomclassifieds&view=user&Itemid='.$_itemId);
		}
		
		$html  = '<div id="jomclassifieds" class="jomclOffline">';
		$html .= '<form id="offlineform" action='.$_action.' method="post">';	
		$html .= '<div class="plg_header">'. JText::_('COM_JOMCLASSIFIEDS_OFFLINE_NAME') .'</div>';				
		$html .= '<div class="plg_content">';
		$html .= '<div class="advid">'.JText::_('PLG_ADVERT_ID').$_id.'</div>';
		$html .= '<div class="title">'.JText::_('PLG_ADVERT_TITLE').$_title.'</div>';		
		$html .= '<div class="price">'.JText::_('PLG_ADVERT_AMOUNT').$_amount.'&nbsp;'.$_currency.'</div>';					
		$html .= '</div>';		
		$html .= '<div class="plg_desc">'.$_off_desc;			
		$html .= '<input type="submit"  class="button btn btn-primary" style="padding:10px;" value="'.JText::_('PROCEED_TO_BACK').'" />';	
		$html .= '</div>';		
		$html .= '</form>';
		$html .= '</div>';			
		echo $html;
		
	}
	
	function notifyPayment($_advId,$_orderid, $_title, $_amount) {
		$host = JFactory::getURI()->getHost();
		if($host == 'localhost') {
			return 1;
		}
		
		$db = JFactory::getDBO();
		$itemId = JRequest::getInt('Itemid');	
		
		$userid = jomclUtils::getColumn('adverts', 'userid', $_advId);	
		$query = "SELECT * FROM #__users WHERE id=".$db->Quote($userid);									
		$db->setQuery($query);			
		$db->query();
		$item = $db->loadObject();
			
		if($item->block == '1') {
			return 1;
		}
		
		//Display premium datas 
		$query = "SELECT payment FROM #__jomcl_payments WHERE id IN(".$_orderid.") ";		
		$db->setQuery( $query );		
   		$orderId = $db->loadResult(); 	
		
		$query = "SELECT name,type FROM #__jomcl_premium WHERE id IN(".$orderId.") ";		
		$db->setQuery( $query );		
   		$pitems = $db->loadObjectList(); 		
				
		$membership_name='';
		$promotion_name='';	
		
		foreach($pitems as $pitem){
			$_type = $pitem->type;				
			if($_type == 'membership'){				
					$membership_name = $pitem->name;
					$membership_name = ' ';
					$membership_name .= JText::_('COM_JOMCLASSIFIEDS_MEMBERSHIP');				
		 	}
			 	
			if($_type == 'featured'){		 		
				 $promotion_name = $pitem->name;
				  $promotion_name = ' ';
				 $promotion_name .= JText::_('COM_JOMCLASSIFIEDS_PROMOTION');					 			 
			 } 			
		}			
		$count = (count($pitems) > 1 ) ? ' and ' : ' ' ;
		$premium = $membership_name  . $count . $promotion_name;
		
		$config = JFactory::getConfig();
		$emailFrom = $config->get('mailfrom');
		$fromName = $config->get('fromname');	
		$siteName = $config->get('sitename');
		
		$emailSubject = JText::sprintf('OFFLINE_PAYMENT_EMAIL_SUBJECT', $siteName);			
		$_off_desc = $this->params["offline_desc"];	
		$_currency = $this->params["currency_code"];
		$price = $_amount.'&nbsp;'. $_currency;
		
		
		$published = jomclUtils::getColumn('adverts', 'published', $_advId);				
		if ($published > 0) {
			$link = JURI::root().'index.php?option=com_jomclassifieds&view=userads&id='.$item->id.':'.$item->username.'&Itemid='.$itemId;						
		} else{
			$link = JURI::root().'index.php?option=com_jomclassifieds&view=user&id='.$_advId;								
		}
			
		$href = '<a href="'.$link.'" >'. $link .'</a>';
		$emailBody  = JText::sprintf('OFFLINE_PAYMENT_EMAIL_BODY',  $item->username, $premium, $_advId, $_title, $price, $_off_desc, $href);
			
		$return = JFactory::getMailer()->sendMail($emailFrom, $fromName, $item->email, $emailSubject, $emailBody,true);
		return ($return != true) ? 0 : 1;
	}
	
	
}


