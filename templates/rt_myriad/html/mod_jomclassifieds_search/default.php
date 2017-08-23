<?php

/*
 * @version		$Id: default.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013 - 2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

$mainframe = JFactory::getApplication();
$uid = $module->id;
$catid = -1;
$defItemId = JRequest::getInt('Itemid');
$itemId = $params->get('itemid', $defItemId);


$option = JRequest::getCmd('option');
$view = JRequest::getCmd('view');
$session = JFactory::getSession();
if($option == 'com_jomclassifieds' && $view == 'search') {
	$reset = $session->get('jomclreset');
} else {
	$uri = JFactory::getURI();
	$session->set('jomclreset', $uri);
	$reset = '';
}
$config = JomclUtils::getCfg();

$Currency = JRequest::getVar('currency');
$currency = ($Currency == '') ? '-1' : $Currency;
$hidden =  '<input type="hidden" name="currency" value="'. $config->defcurrency .'" />';
$ListCurrency = ($params->get('mulcurrency') == '1') ?  "". JomclHTML::ListCurrency($currency) ."" : "" . $config->defcurrency . $hidden . "";
$promtionEnabled = JomclUtils::getColumn('premium','published',1);
$isFree = JomClassifiedsSearchHelper::isFreeMemebership();

$action = JRoute::_('index.php?option=com_jomclassifieds&view=search&Itemid='.$itemId);

?>

<div class="jomClassifiedsSearch <?php echo $params->get('moduleclass_sfx'); ?>">

<form action="<?php echo $action ; ?>" method="get" name="jcsSearchForm" class="<?php echo $params->get('layout','form-vertical'); ?>" id="jcsSearchForm" enctype="multipart/form-data">

<?php
$uri = $_SERVER["REQUEST_URI"];
$uriArray = explode('/', $uri);
$page_url = $uriArray[1];
if($page_url!=''){
?>
 <!--Keyword Field-->
 <?php if($params->get('iskeyword')) :   ?>
  <?php if( $params->get('layout') == 'form-vertical' ) { ?>
 <div class="control-group">
    <div class="controls">
    <?php  } ?>
      <input class="input-block-level"  type="text" name="search" placeholder="<?php echo 'Ad Number';?>"  value="<?php echo JRequest::getVar('search', '', 'get', 'string');?>">
   <?php if( $params->get('layout') == 'form-vertical' ) { ?>
      </div>
  </div>
   <?php  } ?>
 <?php  endif; ?>

 <?php } // if page url close ?>

<?php //echo JRequest::getVar('city', '', 'get', 'string');?>
 <!-- Custom Field  "City" is added by anandbabu for this module /* anandbabu - 07-30-2015 - task#3588 */-->
 <div class="control-group">
	<div class="controls">
		<input class="input-block-level"  type="text" name="city" placeholder="<?php echo JText::_('City');?>"  value="<?php echo JRequest::getVar('city', '', 'get', 'string');?>">
	</div>
 </div>
 <!-- Custom Field  "City" is added by anandbabu for this module ends here  -->

 <!--Location Field-->
 <?php if($params->get('islocation') ) :  ?>
 <div class="control-group">
    	<!-- <label class="control-label"><?php echo JText::_('LOCATIONS'); ?></label> -->
        <div class="controls"><?php echo JomclHTML::ListCountries(JRequest::getInt('country'), $uid); ?></div>
 </div>
 <?php  endif; ?>

 <!--Region Field-->
 <?php if($params->get('islocation') ) :  ?>

        <span id="jcsregions_<?php echo $uid;?>"><?php echo JomclHTML::ListRegions(JRequest::getInt('country'), JRequest::getInt('region')); ?></span>

 <?php  endif; ?>

  <!--Categories Field-->
 <?php if($params->get('iscategories') ) :
 		$catid = (JRequest::getInt('cat_c') > -1) ? JRequest::getInt('cat_c') : JRequest::getInt('cat_p');
  ?>
 <div class="control-group">
    	<!-- <label class="control-label"><?php echo JText::_('CATEGORIES'); ?></label> -->
        <div class="controls"><?php echo JomclHTML::ListParentCategories(JRequest::getInt('cat_p'), $uid); ?></div>
 </div>
 <?php  endif; ?>

 <!--Sub-Categories Field-->
 <?php if($params->get('iscategories') ) :  ?>
 <!--<div class="control-group">    	-->
     <div class="jomcl-inline" id="jcschildcategories_<?php echo $uid;?>"><?php echo JomclHTML::ListChildCategories(JRequest::getInt('cat_p'), JRequest::getInt('cat_c'),$uid); ?></div>
<!-- </div>-->
 <?php  endif; ?>

  <?php if($params->get('isairid') ) :  ?>
      <div class="control-group">
        <!--<label class="control-label"><?php echo JText::_('AIRPORT_IDENTIFIER'); ?></label>-->
        <div class="controls">
          <input type="text" name="airid" class="input-block-level" placeholder="<?php echo JText::_('AIRPORT_IDENTIFIER'); ?>" />
         <!-- <div id="suggestions"></div>-->
        </div>
      </div>

        <?php  endif; ?>

<?php
$uri = $_SERVER["REQUEST_URI"];
$uriArray = explode('/', $uri);
$page_url = $uriArray[1];
if($page_url==''){
?>
 <!--Keyword Field-->
 <?php if($params->get('iskeyword')) :   ?>
  <?php if( $params->get('layout') == 'form-vertical' ) { ?>
 <div class="control-group">
    <div class="controls">
    <?php  } ?>
      <input class="input-block-level"  type="text" name="search" placeholder="<?php echo 'Ad Number';?>"  value="<?php echo JRequest::getVar('search', '', 'get', 'string');?>">
   <?php if( $params->get('layout') == 'form-vertical' ) { ?>
      </div>
  </div>
   <?php  } ?>
 <?php  endif; ?>

 <?php } // if page url close ?>
    <?php if($params->get('isairname') ) :  ?>
      <div class="control-group">
        <!--<label class="control-label"><?php echo JText::_('AIRPORT_NAME'); ?></label>-->
        <div class="controls">
          <input type="text" name="airname" class="input-block-level" placeholder="<?php echo JText::_('AIRPORT_NAME'); ?>" />
        </div>
      </div>
      <?php  endif; ?>

 <!--ExtraFields-->
 <?php if($params->get('isextrafields') ) :  ?>
<!-- <div class="control-group"> -->
     <div class="jomcl-inline" id="jcsextrafields_<?php echo $uid;?>"><?php echo JomclExtraFields::ListExtraFields($catid, $uid); ?></div>
<!-- </div>-->
 <?php  endif; ?>

 <!--Membership Field-->
 <?php if( $params->get('ispromotion') && $promtionEnabled ) :  ?>
 <div class="control-group">
    	<label class="control-label"><?php echo JText::_('PROMOTIONS'); ?></label>
        <div class="controls"><?php echo JomclHTML::ListPromotionType(JRequest::getInt('promotion')); ?></div>
 </div>
 <?php  endif; ?>

  <!--Membership Field-->
 <?php if( $params->get('ismembership') && $isFree ) :  ?>
 <div class="control-group">
    	<label class="control-label"><?php echo JText::_('MEMBERSHIPS'); ?></label>
        <div class="controls"><?php echo JomclHTML::ListMembershipType(JRequest::getInt('membership')); ?></div>
 </div>
 <?php  endif; ?>

  <!--Tags Field-->
 <?php if( $params->get('istags')) :  ?>
 <div class="control-group">
    	<!-- <label class="control-label"><?php echo JText::_('TAGS'); ?></label> -->
        <div class="controls"><?php echo JomclHTML::ListTags(JRequest::getInt('tagid')); ?></div>
 </div>
 <?php  endif; ?>

  <!--Price  Field-->
<?php if( $params->get('isprice')) :
	$price = JRequest::getVar('price', '', '', 'array');
	$filtered = array_filter($price);
	if(count($filtered)) {
		$price = JomclUtils::castAsNumber($price);
		if(count($price) == 1) {
			$price[1] = $price[0];
		}
	} else {
		$price[0] = $price[1] = '';
	}
	$input  = '<input type="text"  name="price[]" value="'.$price[0].'" class="input-mini" placeholder="'.JText::_('MIN').'" />';
	$input .= "\n";
	$input .= '<input type="text" name="price[]" value="'.$price[1].'" class="input-mini" placeholder="'.JText::_('MAX').'" />';
 ?>
 <div class="control-group">
    	<!--<label class="control-label"><?php // echo JText::_('PRICE'); ?></label>-->
        <div class="controls">
			<?php
			if($params->get('isprice') == 1){
				echo $price = $input . ' ' . $ListCurrency;
			} elseif ($params->get('isprice') == 2){
				echo $price =  $ListCurrency . ' ' . $input;
			}  ?>
         </div>
 </div>
<?php  endif; ?>
<?php /* anandbabu - 07-31-2015- task#3588 */  //if condition is only for home page "find a hanagar"  ?>
<?php if($uid != 90 ) { 
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		if (!($menu->getActive() == $menu->getDefault( 'en-GB' ))) {
	?>
  <!--Search Expired Ads-->
  <div class="control-group">
        <div class="checkbox-inline control-label">
          <input type="checkbox" name="expired" value="1" ><span class="text-error"><?php echo JText::_('INCLUDE_EXPIRED_ADS'); ?></span>
        </div>
  </div>
<?php } } ?>
  <!--Search button and Reset button Field-->
  <div class="control-group">
  	 <div class="controls">
 		<button type="submit" class="btn btn-primary"><i class="icon-white icon-search"></i> <?php echo JText::_('SEARCH'); ?></button>
        <?php //if($reset != '') : ?>
    	<button type="reset" class="btn btn-danger" ><i class="icon-white icon-loop"></i> <?php echo JText::_('RESET'); ?>
        </button>
   		<?php /*endif; onclick="location.href='<?php echo $reset; ?>'" */ ?>
     </div>
   </div>
  <div class="clearfix"></div>
  	<input type="hidden" name="option" value="com_jomclassifieds" />
    <input type="hidden" name="view" value="search" />
    <input type="hidden" name="Itemid" value="<?php echo $itemId; ?>" />
  </form>
</div>

<script type="text/javascript">
   jQuery(document).ready(function(){

        // jQuery('#cat_p').live('change', function(){
            // jQuery('#exf_14').promise().done(function(){
                // setTimeout(function(){
                    // jQuery('#jcsextrafields_125 .control-label').hide();
                    // jQuery('#exf_14').attr('placeholder','Hangar Width');
                // }, 300);
            // })
        // })

        jQuery('#cat_p').live('change', function(){
            jQuery('#exf_14').promise().done(function(){
                jQuery('#jcsextrafields_125 .control-label').hide();
                jQuery('#exf_14').attr('placeholder','Hangar Width');
            });
        });

    });
</script>
