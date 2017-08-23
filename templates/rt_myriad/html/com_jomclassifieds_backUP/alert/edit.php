<?php

/*
 * @version		$Id: default.php 2.6.0 2014-07-15 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
$itemid = JRequest::getInt('Itemid')  ? '&Itemid=' . JRequest::getInt('Itemid') : '';
$link   = 'index.php?option=com_jomclassifieds&view=alert'.$itemid;
$cancel_link = 'index.php?option=com_jomclassifieds&view=alert'.$itemid;
$config = JomclUtils::getCfg();
$item = $this->item;
$mainframe = JFactory::getApplication();	
JHTML::_('script', JURI::root().'components/com_jomclassifieds/js/bfvalidateplus.js', true, true);
$document = JFactory::getDocument();
$document->addScriptDeclaration("	
	function valJomclAddForm() { 	
		f = document.jomclForm;		
		
		document.formvalidator.setHandler('list', function (value) {			
        	return (value != -1);
		});
		
        if (document.formvalidator.isValid(f)) { 			
           	return true;    
        } else {			
           	alert('" . JText::_("ERROR_VALIDATION") . "'); 
			return false;
        }    
    }    
");
$userid = JFactory::getUser()->get('id');
?>

<div class="jomclassifieds" id="jomclassifieds">
<div class="page-header jomcl-header-text">
	<?php if ($this->params->get('show_page_heading') == 1) { ?>
   		<h2><?php echo JText::_('Edit Alert'); ?></h2>
	<?php }	?>
</div>
<span class="clear">&nbsp;</span>


  <div class="jomclassifiedsalertbox" style="border: 1px solid #f5f5f5;padding: 10px;">
    <form action="<?php echo JRoute::_('index.php?option=com_jomclassifieds&view=alert&task=save&'.$itemid); ?>" method="post" name="jomclForm" id="jomclForm" class="form-horizontal" enctype="multipart/form-data">
      <div style="margin-left:69px; margin-bottom:18px;">
        <p><?php echo JText::_('CUSTOMIZE_YOUR_ALERT_FREE_DESC'); ?></p>
      </div>

      <div class="control-group">
        <label class="control-label"><?php echo JText::_('CELLPHONE'); ?><span class="mandatory">*</span></label>
        <div class="controls">
          <input type="text" id="cellphone" name="cellphone" class="required validate-phone" size="50" value="<?php echo $item->cellphone;?>" />
        </div>
      </div>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('CATEGORY'); ?><span class="mandatory">*</span></label>
        <div class="controls"> <?php echo JomclHTML::ListParentCategories($this->parentcategory); ?>
        <span id="jcschildcategories_0"><?php echo JomclHTML::ListChildCategories($this->parentcategory, $item->catids); ?></span> </div>
      </div>
      
      <?php if($config->alertaitportid) : ?>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('AIRPORT_IDENTIFIER'); ?><span class="mandatory">*</span></label>
        <div class="controls">
          <input type="text" id="airportid" name="airportid" class="required key" size="50"   onkeyup="jomcl_lookup(this.value);" value="<?php echo $item->airportid;?>" />
          <div id="suggestions"></div>
        </div>
      </div>
      <?php endif; ?>
      <?php /*if( $config->alertairportname && $config->alertaitportid) : ?>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('AIRPORT_NAME'); ?><span class="mandatory">*</span></label>
        <div class="controls">
          <input type="text" id="airportname" name="airportname" readonly  class="required key" size="50" />
        </div>
      </div>
      <?php endif; ?>
      <?php if( $config->showalertaddress && $config->alertaitportid) : ?>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('ADDRESS'); ?></label>
        <div class="controls">
          <input type="text" id="airaddress" name="address" readonly class="key" size="50" />
        </div>
      </div>
      <?php endif; ?>
      <?php if($config->airportlocation && $config->alertaitportid) : ?>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('LOCATION'); ?></label>
        <div class="controls">
          <input type="text" id="airlocation" name="location" readonly class="key" size="50"  />
        </div>
      </div>
      <?php endif; */ ?>  
	  <?php if( $config->showtags) : ?>   
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('TAGS'); ?><span class="mandatory">*</span></label>
        <div class="controls"> <?php echo JomclHTML::ListTagsAlert('postform',$item->tagids); ?> </div>
      </div>
	  <?php endif; ?>
	  
      <div id="jcsextrafields_0" class="adForm"><?php 	  
	  echo JomclExtraFields::ListExtraFields($item->catids,-1,$item->id); ?></div>
      
       <!--<div class="control-group">
        <label class="control-label"><?php echo JText::_('PRICE'); ?><span class="mandatory">*</span></label>
        <div class="controls">
          <input type="text" id="pricemin" name="pricemin" class="required validate-price" placeholder="Min" value="<?php echo $item->pricemin;?>"  size="50" />
          <input type="text" id="pricemax" name="pricemax" class="required validate-price" placeholder="Max" value="<?php echo $item->pricemax;?>"  size="50" />
        </div>
      </div>-->
       <div class="control-group">
        <label class="control-label"><?php echo JText::_('ALERT_PERIOD'); ?><span class="mandatory">*</span></label>
        <div class="controls"><?php echo JText::_('FROM'); ?>
          <!-- <input type="date" id="alert_startdate" name="alert_startdate" class="required validate-date" size="50" />-->
          <?php echo JHTML::calendar($item->alert_startdate,'alert_startdate', 'alert_startdate', '%Y-%m-%d',array('size'=>'8','maxlength'=>'10','class'=>' required validate-date', 'placeholder'=>'Y-m-d')); ?> <?php echo JText::_('TO'); ?>
          <!--<input type="date" id="alert_enddate" name="alert_enddate" class="required validate-date" size="50" />-->
          <?php echo JHTML::calendar($item->alert_enddate,'alert_enddate', 'alert_enddate', '%Y-%m-%d',array('size'=>'8','maxlength'=>'10','class'=>' required validate-date', 'placeholder'=>'Y-m-d')); ?> </div>
      </div>
     
     <div class="jomclsubmitblock">
        <!-- Hidden Fields -->
        <input type="hidden" name="mode" value="edit" />
        <input type="hidden" name="id" value="<?php echo $item->id; ?>" />
        <input type="hidden" name="userid" value="<?php echo $item->userid; ?>" />     
         <input type="hidden" name="username" value="<?php echo $item->username; ?>" />
        <input type="hidden" name="email" value="<?php echo $item->email; ?>" />
       
         <button type="submit" class="btn btn-success" onclick="return valJomclAddForm();"><i class="icon-new icon-white"></i> <?php echo JText::_('Update Alert'); ?></button>
         <a class="btn btn-default" href="<?php echo JRoute::_($cancel_link); ?>" ><i class="icon-unpublish"></i> <?php echo JText::_('CANCEL'); ?> </a>
    </div>
      <?php echo JHTML::_( 'form.token' ); ?>
    </form>
  </div>
</div>

<script type="text/javascript"> 
	var $autosearch = jQuery.noConflict();
    $autosearch(document).ready(function() {	
	
	 var airportid = document.getElementById("airportid").value;	 
		//jomcl_lookup(airportid);
		
		$autosearch.post("<?php echo JURI::root(); ?>index.php?option=com_jomclassifieds&format=raw&task=updatelocation&aId="+airportid, function(s) {
					 
		//	var base = $autosearch('#jomclbase').val();
		//var Text = $autosearch('#joclpreloaderText').val();					 
			 
		var Responce = s.replace('<input type="hidden" id="jomclbase" value="http://localhost/quickstart/hangar33/" />','');
			Responce = Responce.replace('<input type="hidden" id="joclpreloaderText" value="Loading Please wait...." />','');
		var jomclJsonObj = jQuery.parseJSON(Responce);
				
		$autosearch('#airportname').val(jomclJsonObj[0].name);	
		$autosearch('#airaddress').val(jomclJsonObj[0].address);	
		$autosearch('#airlocation').val(jomclJsonObj[0].city);	
		
		});		
					
					
		
	  var cssObj = { 'box-shadow' : '#888 5px 10px 10px', 
		'-webkit-box-shadow' : '#888 5px 10px 10px', 
		'-moz-box-shadow' : '#888 5px 10px 10px'}; 
		$autosearch("#suggestions").css(cssObj);
		
		$autosearch("input").blur(function(){
	 		$autosearch('#suggestions').fadeOut();
			if(document.getElementById("airportid").value == '') {
				$autosearch('#airportname').val('');	
				$autosearch('#airaddress').val('');	
				$autosearch('#airlocation').val('');	
				//$autosearch('#postalcode').val('');	
			
			}
			
			
		 });		
		
      });
	  
	   function jomcl_lookup(inputString) {	  
	  	   	  
		if(inputString.length == 0) {
			$autosearch('#suggestions').fadeOut(); 
			
			
		} else {
			$autosearch.post("<?php echo JURI::root(); ?>index.php?option=com_jomclassifieds&format=raw&task=autofil", {key: ""+inputString+""}, function(data) {
				$autosearch('#suggestions').fadeIn(); 
				$autosearch('#suggestions').html(data); 				
				
				$autosearch("a.clickable").click(function(event){
            			event.preventDefault();											
						$autosearch("#airportid").val($autosearch(this).html());	
						
					 var airportid = document.getElementById("airportid").value;
				     $autosearch.post("<?php echo JURI::root(); ?>index.php?option=com_jomclassifieds&format=raw&task=updatelocation&aId="+airportid, function(s) {
					 
				//	var base = $autosearch('#jomclbase').val();
					//var Text = $autosearch('#joclpreloaderText').val();					 
			 
			 		var Responce = s.replace('<input type="hidden" id="jomclbase" value="http://www.hangar33.devsoho.com/" />','');
					Responce = Responce.replace('<input type="hidden" id="joclpreloaderText" value="Loading Please wait...." />','');
					var jomclJsonObj = jQuery.parseJSON(Responce);
					
					$autosearch('#airportname').val(jomclJsonObj[0].name);	
					$autosearch('#airaddress').val(jomclJsonObj[0].address);	
					$autosearch('#airlocation').val(jomclJsonObj[0].city);	
					//$autosearch('#postalcode').val(jomclJsonObj[0].zipcode);
								
					
			});										
						
           			// $autosearch("input#textbox").val($autosearch(this).html());
      			  });  
				  
				
			});
		}
		
	}
	  
    </script>
  
