<?php

/*
* @version		$Id: edit.php 2.4.0 2014-05-15 $
* @package		Joomla
* @copyright   Copyright (C) 2013-2014 Jom Classifieds
* @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
$config = JomclUtils::getCfg();
JHtml::_('bootstrap.tooltip');
$itemid = JRequest::getInt('Itemid')  ? '&Itemid=' . JRequest::getInt('Itemid') : '';
$link = 'index.php?option=com_jomclassifieds&view=user';
$item = $this->item;

$userid = JFactory::getUser()->get('id');

$imagecount = JomclUtils::getColumn('premium','image_count',$item->membership);
$this->addScript($config->showmap);
JHTML::_('script', JURI::root().'components/com_jomclassifieds/js/bfvalidateplus.js', true, true);
$document = JFactory::getDocument();
$document->addScriptDeclaration("

	function jomclCollapseAll(){
	   jQuery('#jomclslide-postad .collapse:not(.in)').collapse('show');
		return;
	}

    updateimagecount($imagecount);

	function valJomclEditForm() {
		f = document.jomclForm;
		document.formvalidator.setHandler('list', function (value) {
			return (value != -1);
		});

		if (document.formvalidator.isValid(f)) {
				return true;
			} else {
			    var jomclCollapse = new jomclCollapseAll();
			   	alert('" . JText::_("ERROR_VALIDATION") . "');
				return false;
			}
	 }
");


$editor = JFactory::getEditor();
$params = array('mode'=> 'advanced');

$ua = '';
if($config->showmap == 1) {
	$ua = 'onblur="updateAddress();"';
}
$input = '<input type="text" name="price" class="jomclPricefield validate-numeric" placeholder="0.00" size="10" value="' . $item->price . '" />';
$Listcurrency= JomclHTML::ListCurrency($item->currency);
if($config->showprice == 1){
	$price = $input.'&nbsp;'.$Listcurrency;
} elseif ($config->showprice == 2){
	$price =  $Listcurrency.'&nbsp;'.$input;
} else {
	$price ='';
}
?>


<?php
/* anandbabu - 07-30-2015 - task#3587 */
//setting default country
$defLocation = 'USA';
/* anandbabu - 07-30-2015 - task#3587 */
// query to get all state name
	$db = JFactory::getDBO();
	$query = "SELECT id,alias,name FROM #__jomcl_locations";
	$db->setQuery( $query );
	$full_location_results = $db->loadObjectList();
?>
<?php
/* anandbabu - 07-30-2015 - task#3587 */
// query to get all state name
if(!empty($item->region)){
	$db = JFactory::getDBO();
	$query = "SELECT id,alias,name FROM #__jomcl_locations where id =".$item->region;
	$db->setQuery( $query );
	$location_details = $db->loadObjectList();
	//print_r($location_details[0]->name);
	/*for($u=0;$u<count($location_details);$u++){
		echo $location_details->$u;
		if($location_details->$u['id'] == $item->region){
			//echo $location_details->$u['name'];
			$location_name = $location_details[$u]->name;
		} else {
			$location_name = $item->region;
		}
	}*/
} else {

}

?>
<div id="jomclassifieds" class="jomclassifieds<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<div class="pretext"><?php echo trim($this->params->get('pretext')); ?></div>
<form action="<?php echo JRoute::_($link.'&task=save'.$itemid); ?>" method="post" name="jomclForm" id="jomclForm" class="jomcl-form-horizontal" enctype="multipart/form-data">
    <div class="page-header jomcl-header-text">
        <?php if ($this->params->get('show_page_heading') == 1) { ?>
        <h2><?php echo $this->escape($this->params->get('page_heading')); ?></h2>
        <?php } else if( isset($this->title) ) {  ?>
        <h2><?php echo $this->title; ?></h2>
        <?php } ?>
    </div>
	<span class="clear"></span>
<?php echo JHtml::_('bootstrap.startAccordion', 'jomclslide-postad', array('active' => 'details1')); ?>
<!-- Ad Details -->
	<?php  echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad',  JText::_('AD_DETAILS'), 'details1'); ?>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('TITLE'); ?><span class="mandatory">*</span>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_TITLE_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls">
              <input type="text" id="title" name="title" class="required key" size="40"  value="<?php echo $item->title; ?>" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('CATEGORY'); ?><span class="mandatory">*</span>
            <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_CATEGORY_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls"> <?php echo JomclHTML::ListParentCategories($this->parentcategory); ?>
              <span id="jcschildcategories_0"><?php echo JomclHTML::ListChildCategories($this->parentcategory, $item->catid); ?></span>
            </div>
        </div>
        <div class="control-group">
			<label class="control-label"><?php echo JText::_('AIRPORT_IDENTIFIER'); ?>
			<span class="mandatory">*</span>
			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_CATEGORY_TOOLTIP_DESC'); ?>
			</label>
  <div class="controls">
    <input type="text" id="airportid" name="airportid" class="required key" size="50"  onkeyup="jomcl_lookup(this.value);" value="<?php echo $item->airportid ;?>"/>
    <div id="suggestions" class="sug"></div>
  </div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('AIRPORT_NAME'); ?><span class="mandatory">*</span></label>
  <div class="controls">
    <input type="text" id="airportname" name="airportname" class="required key" size="50" value="<?php echo $item->airportname;?>" readonly <?php //echo $ua; ?>/>
  </div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('ADDRESS'); ?></label>
  <div class="controls">
    <input type="text" id="airaddress" name="address" class="key" size="50" value="<?php echo $item->address ;?>" />
    <input type="hidden" id="region" name="region" value="<?php echo $item->region ; ?>"  />
    <input type="hidden" id="country" name="country"  value="<?php echo $item->country;?>"/>
  </div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('City'); ?></label>
  <div class="controls">
    <input type="text" id="city" name="city" class="key" size="50" readonly value="<?php echo $item->city ;?>"/>
  </div>
</div>
<!-- /* anandbabu - 07-30-2015 - task#3587 */ -->
<div class="control-group">
  <label class="control-label"><?php echo JText::_('State'); ?></label>
  <div class="controls">
    <input type="text" id="state" name="state" class="key" size="50" readonly value="<?php echo $location_details[0]->name;?>"/>
  </div>
</div>
<!-- /* ends here */ -->
<div class="control-group">
  <label class="control-label"><?php echo JText::_('POSTALCODE'); ?></label>
  <div class="controls">
    <input type="text" id="postalcode" name="postalcode" class="key" size="50" readonly <?php echo $ua; ?> value="<?php echo $item->postalcode ;?>" / >
  </div>
</div>
<?php if($config->showtags) : ?>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('SELECT_A_TAG'); ?></label>
        <div class="controls"> <span><?php echo JomclHTML::ListTags($item->tagid); ?></span> </div>
      </div>
      <?php endif; ?>
       <div id="jcsextrafields_0" class="adForm"><?php echo JomclExtraFields::ListExtraFields($item->catid, 0, $item->id); ?></div>
        <?php if($config->showprice) : ?>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('PRICE'); ?></label>
        <div class="controls">
	<?php echo $price; ?></div>
      </div>
      <?php endif; ?>
      <div class="control-group">
        <label class="control-label"><?php echo JText::_('DESCRIPTION'); ?></label>
        <div class="controls desc_postads jomclspan6">
		<?php echo $editor->display( 'description', $item->description, 'auto', '175', '70', '20', 1, null, null, null, $params ); ?>
         </div>
      </div>
<?php 
    	 $isFacebook = JomclUtils::getColumn('premium', 'facebook_display', $item->membership);    	
    	if($isFacebook) : ?>
    	 <div class="control-group ">
      		<label class="control-label"><?php echo JText::_('FACEBOOK_LINK'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_FACEBOOK_LINK_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
        		<input class="key" type="text" name="facebook_link"  size="40" value="<?php echo $item->facebook_link; ?>" />
      		</div>
    	</div>
    	<?php endif; ?>
    <?php 
    	 $isWebsite = JomclUtils::getColumn('premium', 'website_display', $item->membership);    	
    	if($isWebsite) : ?>	
 	<div class="control-group " >
      		<label class="control-label"><?php echo JText::_('WEBSITE_LINK'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_WEBSITE_LINK_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
        		<input class="key" type="text" name="website_link" size="40" value="<?php echo $item->website_link; ?>" />
      		</div>
	</div>
<?php endif; ?>

    	<!--<div id="jcsextrafields_0"><?php echo JomclExtraFields::ListExtraFields($item->catid, 0, $item->id); ?></div>
		<div class="control-group ">
      		<label class="control-label"><?php echo JText::_('FACEBOOK_LINK'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_FACEBOOK_LINK_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
        		<input class="key" type="text" name="facebook_link"  size="40" value="<?php echo $item->facebook_link; ?>" />
      		</div>
    	</div>
 <div class="control-group " >
      		<label class="control-label"><?php echo JText::_('WEBSITE_LINK'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_WEBSITE_LINK_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
        		<input class="key" type="text" name="website_link" size="40" value="<?php echo $item->website_link; ?>" />
      		</div>
</div>

		<?php if($config->showprice) : ?>
            <div class="control-group">
            <label class="control-label"><?php echo JText::_('PRICE'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_PRICE_TOOLTIP_DESC'); ?>
             </label>
            <div class="controls">
            <?php echo $price; ?></div>
            </div>
        <?php endif; ?>
		<?php if($config->showtags) : ?>
            <div class="control-group">
            <label class="control-label"><?php echo JText::_('SELECT_A_TAG'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_TAG_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls"> <span><?php echo JomclHTML::ListTags($item->tagid); ?></span> </div>
            </div>
        <?php endif; ?>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('DESCRIPTION'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_DESCRIPTION_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls">
                <?php echo $editor->display( 'description', $item->description, 'auto', '175', '70', '20', 1, null, null, null, $params ); ?>
             </div>
            </div>-->
    <?php  echo JHtml::_('bootstrap.endSlide'); ?>
<!-- Gallery Images -->
	<?php echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad', JText::_('MEDIA'), 'media2'); ?>
<?php 
    	 $isYouTube = JomclUtils::getColumn('premium', 'youtube_display', $item->membership);    	
    	if($isYouTube) : ?>	
		<?php if($config->showvideo) : ?>
            <div class="control-group">
            <label class="control-label"><?php echo JText::_('YOUTUBE_VIDEO'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_YOUTUBE_VIDEO_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls">
              <input class="key" type="text" name="video" size="40" value="<?php echo $item->video; ?>" />
            </div>
            </div>
       	 <?php endif; ?>
        <?php endif; ?>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('IMAGES'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_IMAGES_TOOLTIP_DESC'); ?>
            </label>
              <span id="delete_image"></span>
            <div class="controls">
              <ul id="images">
              </ul>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">&nbsp;</label>
            <div class="controls">
             <a href="javascript:void(0);" id="addimage" class="btn btn-small btn-success"><span class="icon-new icon-white"></span><?php echo JText::_('ADD_NEW_IMAGE'); ?></a>
            </div>
        </div>
    <?php  echo JHtml::_('bootstrap.endSlide'); ?>
<!-- Contact Details -->
	<?php echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad', JText::_('CONTACT_DETAILS'), 'contact3'); ?>
        <!--<div class="control-group">
            <label class="control-label"><?php echo JText::_('ADDRESS'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_ADDRESS_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls">
              <textarea name="address" rows="3" cols="50" <?php echo $ua; ?>><?php echo $item->address; ?></textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('LOCATION'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_LOCATION_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls"> <?php echo JomclHTML::ListCountries($item->country, 0, $config->showmap); ?>
             <span id="jcsregions_0"><?php echo JomclHTML::ListRegions($item->country, $item->region, $config->showmap); ?></span> </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('POSTAL_CODE'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_POSTAL_CODE_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls">
              <input class="key" type="text" name="postalcode" size="40" <?php echo $ua; ?> value="<?php echo $item->postalcode; ?>" />
            </div>
        </div>-->
        <?php if(!$userid) : ?>
            <!--<div class="control-group">
                <label class="control-label"><?php echo JText::_('ADVERTISER_NAME'); ?><span class="mandatory">*</span></label>
                <div class="controls">
                    <input type="text" name="advertisername" id="advertisername" class="required key"  value="<?php echo $item->advertisername; ?>" size="50"/>
                </div>
            </div>-->

			<!-- extra fields advertiser name and company name  are added here  added -->
				<div class="control-group">
					<label class="control-label">Advertiser's Name<span class="mandatory">*</span><span title="" class="hasTooltip" data-original-title="&lt;strong&gt;Enter your full name.&lt;/strong&gt;"> <img alt="Enter your full name." src="https://www.hangartrader.com/components/com_jomclassifieds/assets/tooltip.png"></span></label>
					<div class="controls jomcl-textfield"><input type="text" value="" name="exf_12" id="exf_12" class="input-large"></div>
				</div>
				<div class="control-group">
					<label class="control-label">Company<span class="mandatory">*</span><span title="" class="hasTooltip" data-original-title="&lt;strong&gt;Enter your Company name.&lt;/strong&gt;"> <img alt="Enter your Company name." src="https://www.hangartrader.com/components/com_jomclassifieds/assets/tooltip.png"></span></label>
					<div class="controls jomcl-textfield"><input type="text" value="" name="exf_21" id="exf_21" class="input-large"></div>
				</div>
			<!-- ends here -->
        <?php endif; ?>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('PHONE_NUMBER'); ?><span class="mandatory">*</span>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_PHONE_NUMBER_TOOLTIP_DESC'); ?>
            </label>
            <div class="controls">
              <input class="required key" type="text" name="phonenumber" size="40" value="<?php echo $item->phonenumber; ?>" />
            </div>
        </div>
		<?php if($config->showemail) : ?>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('EMAIL'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_EMAIL_TOOLTIP_DESC'); ?>
            </label>
             <div class="controls">
            <input type="email" name="email"  class="key validate-email"  size="50" value="<?php echo $item->email; ?>"  />
            </div>
        </div>
        <?php endif; ?>
    <?php  echo JHtml::_('bootstrap.endSlide'); ?>
<!-- Show Meta -->
	<?php if($config->showmeta) : ?>
		<?php echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad', JText::_('SEO_SETTINGS'), 'seo4'); ?>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('META_KEYWORDS'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_META_KEYWORDS_TOOLTIP_DESC'); ?>
            </label>
             <div class="controls">
            <textarea name="meta_keywords" rows="3" cols="50" placeholder="<?php echo JText::_('META_KEYWORDS_DESCRIPTION'); ?>" ><?php echo $item->meta_keywords; ?></textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo JText::_('META_DESCRIPTION'); ?>
             <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_META_DESCRIPTION_TOOLTIP_DESC'); ?>
            </label>
             <div class="controls">
                    <textarea name="meta_description" rows="3" cols="50" ><?php echo $item->meta_description; ?></textarea>
            </div>
        </div>
    <?php  echo JHtml::_('bootstrap.endSlide'); ?>
    <?php endif; ?>
<?php echo JHtml::_('bootstrap.endAccordion'); ?>
	<?php if(trim($config->termsandcond) != '') : ?>
    <div class="control-group" style="margin-top:15px;">
        <label class="control-label">&nbsp;</label>
        <div class="controls">
          <label for="terms" class="checkbox">
          <a class="terms" href="<?php echo $config->termsandcond; ?>" target="_blank"><?php echo JText::_('AGREE_TERMS_AND_CONDITIONS'); ?><span class="mandatory">*</span></a>
          <input type="checkbox" name="terms[]" id="terms" class="required">
          </label>
        </div>
    </div>
    <?php endif; ?>
    <div class="jomclsubmitblock">
        <!-- Hidden Fields -->
        <input type="hidden" name="mode" value="edit" />
        <input type="hidden" name="id" value="<?php echo $item->id; ?>" />
        <input type="hidden" id="extImages" name="extImages" value="<?php echo $item->images; ?>" />
        <!-- Tool Bar -->
        <button type="submit" class="btn btn-success" onclick="return valJomclEditForm();" ><i class="icon-new icon-white"></i> <?php echo JText::_('UPDATE_AD'); ?></button>
         <button type="button" class="btn btn-default" onclick="location.href='<?php echo JRoute::_($link.$itemid); ?>'" ><i class="icon-unpublish"></i> <?php echo JText::_('CANCEL'); ?></button>
     <?php echo JHTML::_( 'form.token' ); ?>
     </div>
</form>
<div class="posttext"><?php echo trim($this->params->get('posttext')); ?></div>
</div>
<div class="clear"></div>


<script type="text/javascript">


	// assigning location results to js variable /* anandbabu - 07-30-2015 - task#3587 */
	var location_name = <?php echo json_encode($full_location_results); ?>;
	var $autosearch = jQuery.noConflict();
    $autosearch(document).ready(function() {
	  var cssObj = { 'box-shadow' : '#888 5px 10px 10px',
		'-webkit-box-shadow' : '#888 5px 10px 10px',
		'-moz-box-shadow' : '#888 5px 10px 10px'};
		$autosearch("#suggestions").css(cssObj);

		 $autosearch("input").blur(function(){
	 		$autosearch('#suggestions').fadeOut();
			if(document.getElementById("airportid").value == '') {
					$autosearch('#airportname').val('');
					$autosearch('#airaddress').val('');
					$autosearch('#city').val('');
					$autosearch('#region').val('');
					$autosearch('#postalcode').val('');
					/* anandbabu - 07-30-2015 - task#3587 */ //emptying state field if airport identifier is empty
					$autosearch('#state').val('');
					$autosearch('#country').val('');
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
				var Responce = s.replace('<input type="hidden" id="jomclbase" value="https://www.hangartrader.com" />','');
					Responce = Responce.replace('<input type="hidden" id="joclpreloaderText" value="Loading Please wait...." />','');
				var jomclJsonObj = jQuery.parseJSON(Responce);
				// below code is check for location name
				var state_id = jomclJsonObj[0].state;
				for(var u=0; u < location_name.length; u++){
					if(location_name[u]['id'] == state_id){
						var state_name = location_name[u]['name'];
					}
				}
				var addr = jomclJsonObj[0].address + ',' +jomclJsonObj[0].city
					$autosearch('#airportname').val(jomclJsonObj[0].name);
					$autosearch('#airaddress').val(addr);
					$autosearch('#city').val(jomclJsonObj[0].city);
					$autosearch('#region').val(jomclJsonObj[0].state);
					$autosearch('#postalcode').val(jomclJsonObj[0].zipcode);
					/* anandbabu - 07-30-2015 - task#3587 */
					$autosearch('#state').val(state_name);
					$autosearch('#country').val(state_id);
					updateAddress(jomclJsonObj[0].name);
				});

			});

			});
		}
	}
</script>
<script>
	// added this script to get values of hidden elements in adForm to add those to contact block elements
	jQuery(document).ready(function(){
		jQuery('#contact3 .jomcl-textfield #exf_12').val(jQuery('#jcsextrafields_0 .jomcl-textfield #exf_12').val());
		jQuery('#contact3 .jomcl-textfield #exf_21').val(jQuery('#jcsextrafields_0 .jomcl-textfield #exf_21').val());
		jQuery('.accordion-group .accordion-heading a.accordion-toggle').addClass('jomclToggleactive');
		jQuery('.accordion-group .accordion-body.collapse').addClass('in');
	});
</script>
