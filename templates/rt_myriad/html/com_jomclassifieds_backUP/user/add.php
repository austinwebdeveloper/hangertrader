<?php
/*
 * @version		$Id: user.php 2.4.0 2014-05-15 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
$config = JomclUtils::getCfg();
$currency = $config->paycurrency;
JHtml::_('bootstrap.tooltip');

if($config->deflocation == '-1') {
	$defLocation = 'USA';
} else {
	$defLocation = JomclUtils::getColumn('locations', 'name', $config->deflocation);
}

$itemid = JRequest::getInt('Itemid')  ? '&Itemid=' . JRequest::getInt('Itemid') : '';
$link   = 'index.php?option=com_jomclassifieds&view=user';
$this->addScript($config->showmap);
JHTML::_('script', JURI::root().'components/com_jomclassifieds/js/bfvalidateplus.js', true, true);

$document = JFactory::getDocument();
$document->addScriptDeclaration("
	function jomclCollapseAll(){
	   jQuery('#jomclslide-postad .collapse:not(.in)').collapse('show');
		return;
	}
	function valJomclAddForm() {
		f = document.jomclForm;
		document.formvalidator.setHandler('list', function (value) {
        	return (value != -1);
		});
        if (document.formvalidator.isValid(f)) {
			if(document.getElementById('membership6') === null) {
				return false;
			}
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
$defCurrency = $config->defcurrency;
$ua = '';
if($config->showmap == 1) {
	$ua = 'onblur="updateAddress();"';
}
$input = '<input type="text" class="jomclPricefield validate-numeric"  placeholder="0.00" name="price" size="10" />';
$Listcurrency= JomclHTML::ListCurrency($defCurrency);
if($config->showprice == 1){
	$price = $input.'&nbsp;'.$Listcurrency ;
} elseif ($config->showprice == 2){
	$price =  $Listcurrency.'&nbsp;'.$input;
} else {
	$price ='';
}
//ver2.6.0
$memebership = $this->memebership;
$promotion = $this->promotion;
$isFreeMemebership = $this->isFreeMemebership;
$ini = 0;
$userid = JFactory::getUser()->get('id');
/* Below query is to get the extra fields are required assigned in backend */ 
/* anandbabu - 09/30/2015 - task#3763 */
$db =  JFactory::getDBO();
$query = "SELECT label,required FROM #__jomcl_extrafields WHERE id IN (12,21)";
$db->setQuery($query);
$res = $db->loadObjectList();
/* ends here */

/*anandbabu -04/07/2016 - task#3903*/ 
$db =  JFactory::getDBO();
//$query ="SELECT id FROM #__jomcl_adverts WHERE userid = '".$userid; 
$query1 = "SELECT * FROM jos_jomcl_extrafields_values WHERE advertid IN ( SELECT id FROM `jos_jomcl_adverts` WHERE userid = $userid ORDER BY advertid ASC ) AND fieldid IN (12, 21) ORDER BY advertid ASC";
$db->setQuery($query1);
$res1 = $db->loadObjectList();
//echo "<pre>"; print_r($res1); exit;

?>

<div id="jomclassifieds" class="jomclassifieds <?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<div class="pretext"><?php echo trim($this->params->get('pretext')); ?></div>
<form action="<?php echo JRoute::_($link.'&task=save'.$itemid); ?>" method="post" name="jomclForm" id="jomclForm" class="jomcl-form-horizontal" enctype="multipart/form-data">
<div class="page-header jomcl-header-text">
	<?php if ($this->params->get('show_page_heading') == 1) { ?>
   		<h2><?php echo $this->escape($this->params->get('page_heading')); ?></h2>
	<?php } else if( isset($this->title) ) {  ?>
    	<h2><?php echo $this->title; ?></h2>
	<?php } ?>
</div>
<span class="clear">&nbsp;</span>

<?php
/* anandbabu - 07-30-2015 -task#3587 */ //moved this code from bottom to top, so that membership appears on top of page
echo JHtml::_('bootstrap.startAccordion', 'jomclslide-postad', array('active' => 'details1')); ?>
	<!-- Show Membership  -->
		<div id="jcsemail"></div>
		<?php // removed this condition so that guest user can also view the membership form /* anandbabu - 07-30-2015 -task#3587 */
			//if(JFactory::getUser()->get('guest') == 0 ) :?>
        <?php echo JomclPremium::ListMembership(JFactory::getUser()->get('email')); ?>
        <?php //endif; /* anandbabu - 07-30-2015 -task#3587 */ ?>
	<?php echo JHtml::_('bootstrap.endAccordion'); ?>
	<!-- End Membership  -->

	<!-- Ad Details -->
	<?php  echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad', JText::_('AD_DETAILS'), 'details1'); ?>
	<div class="control-group">
 		<label class="control-label"><?php echo JText::_('TITLE'); ?>
  		<span class="mandatory">*</span>
   		 <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_TITLE_TOOLTIP_DESC'); ?>
 		</label>
 		<div class="controls"><input type="text" id="title" name="title" class="required key"  /></div>
	</div>
    <div class="control-group">
      	<label class="control-label"><?php echo JText::_('CATEGORY'); ?>
      	<span class="mandatory">*</span>
      	 <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_CATEGORY_TOOLTIP_DESC'); ?>
      	</label>
      	<div class="controls">
            <?php echo JomclHTML::ListParentCategories(); ?>
            <span id="jcschildcategories_0"><?php echo JomclHTML::ListChildCategories(); ?></span>
      	</div>
    </div>
    <div class="control-group">
		<label class="control-label"><?php echo JText::_('AIRPORT_IDENTIFIER'); ?>
		<span class="mandatory">*</span>
		<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_AIRPORT_IDENTIFIER_TOOLTIP_DESC'); ?>
		</label>
  <div class="controls">
    <input type="text" id="airportid" name="airportid" class="required key" size="50"  onkeyup="jomcl_lookup(this.value);" />
    <div id="suggestions" class="sug"></div>
  </div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('AIRPORT_NAME'); ?><span class="mandatory">*</span></label>
  <div class="controls">
    <input type="text" id="airportname" name="airportname" class="required key" readonly size="50" value="" <?php echo $ua; ?>/>
  </div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('ADDRESS'); ?></label>
  <div class="controls">
    <input type="text" id="airaddress" name="address" class="key" size="50" />
    <input type="hidden" id="region" name="region"  />

    <input type="hidden" id="country" name="country"  value="<?php echo $defLocation;?>"/>

  </div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('City'); ?></label>
  <div class="controls">
    <input type="text" id="city" name="city" class="key" size="50" readonly />
  </div>
</div>
<!-- Starts here custom field added by anandbabu as client asked to added statefield in the form -->
<div class="control-group">
  <label class="control-label"><?php echo JText::_('State'); ?></label>
  <div class="controls">
    <input type="text" id="state" name="state" class="key" size="50" readonly />
    <input type="hidden" id="state_current_id" name="state_current_id" class="key" size="50" />
  </div>
</div>
<!-- ends here "statefield" -->
<!--<div class="control-group">
  <label class="control-label"><?php echo JText::_('POSTALCODE'); ?></label>
  <div class="controls">
    <input type="text" id="postalcode" name="postalcode" class="key" readonly size="50" <?php echo $ua; ?>/>
  </div>
</div>-->
<?php if($config->showtags) : ?>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('TAG'); ?></label>
  <div class="controls"> <span><?php echo JomclHTML::ListTags(); ?></span> </div>
</div>
<?php endif; ?>
<div id="jcsextrafields_0" class="adForm"><?php $extra_field = JomclExtraFields::ListExtraFields(0, 0);

//echo '<pre>';print_r($extra_field);
 ?></div>
 <div class="control-group jomcl-hidden-elements" id="facebook-jomcl-hidden-elements">
      		<label class="control-label"><?php echo JText::_('FACEBOOK_LINK'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_FACEBOOK_LINK_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
        		<input class="key" type="text" name="facebook_link" size="40" />
      		</div>
    	</div>
 <div class="control-group jomcl-hidden-elements" id="website-jomcl-hidden-elements" >
      		<label class="control-label"><?php echo JText::_('WEBSITE_LINK'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_WEBSITE_LINK_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
        		<input class="key" type="text" name="website_link" size="40" />
      		</div>
</div>



<?php if($config->showprice) : ?>
<div class="control-group">
  <label class="control-label"><?php echo JText::_('PRICE'); ?></label>
  <div class="controls"> <?php echo $price; ?> </div>
</div>
<?php endif; ?>

<div class="control-group">
  <label class="control-label"><?php echo JText::_('DESCRIPTION'); ?></label>
  <div class="controls desc_postads jomclspan6"> <?php echo $editor->display( 'description', '', 'auto', '175', '70', '20', 1, null, null, null, $params ); ?> </div>
</div>
<!-- Show Map -->
<?php if($config->showmap) : ?>
<input type="hidden" id="latitude" name="latitude" value="" />
<input type="hidden" id="langtitude" name="langtitude" value="" />
<input type="hidden" id="defLocation" name="defLocation" value="<?php echo $defLocation; ?>"/>
<div class="control-group">
  <label class="control-label">&nbsp;</label>
  <div class="controls">
    <div id="map_canvas" class="jomcl-responsive-container"></div>
  </div>
</div>
<?php endif; ?>

	<!--<div id="jcsextrafields_0" class="adForm"><?php echo JomclExtraFields::ListExtraFields(0, 0); ?></div>
	<?php if($config->showprice) : ?>
    <div class="control-group">
      	<label class="control-label"><?php echo JText::_('PRICE'); ?>
        	 <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_PRICE_TOOLTIP_DESC'); ?>
      	</label>
      	<div class="controls"> <?php echo $price; ?> </div>
    </div>
    <?php endif; ?>
	<?php if($config->showtags) : ?>
    <div class="control-group">
      	<label class="control-label"><?php echo JText::_('TAG'); ?>
       		<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_TAG_TOOLTIP_DESC'); ?>
      	</label>
      	<div class="controls">
      		<?php echo JomclHTML::ListTags(); ?>
       	</div>
    </div>
    <?php endif; ?>
    <div class="control-group">
      	<label class="control-label"><?php echo JText::_('DESCRIPTION'); ?>
       		<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_DESCRIPTION_TOOLTIP_DESC'); ?>
      	</label>
      	<div class="controls"><?php echo $editor->display( 'description', '', 'auto', '175', '70', '20', 1, null, null, null, $params ); ?></div>
    </div>-->
	<?php  echo JHtml::_('bootstrap.endSlide'); ?>

<!-- Gallery Images -->
	<?php echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad',JText::_('MEDIA'), 'media2'); ?>
	<?php if($config->showvideo) : ?>
    	<div class="control-group jomcl-hidden-elements" id="jomcl-yt-hidden-element">
      		<label class="control-label"><?php echo JText::_('YOUTUBE_VIDEO'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_YOUTUBE_VIDEO_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
        		<input class="key" type="text" name="video"  />
      		</div>
    	</div>
    <?php endif; ?>
    <div class="control-group">
      		<label class="control-label"><?php echo JText::_('IMAGES'); ?>
       			<?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_IMAGES_TOOLTIP_DESC'); ?>
     		</label>
      		<div class="controls">
       			 <ul id="images"></ul>
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
	<?php echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad',JText::_('CONTACT_DETAILS'), 'contact3'); ?>
    <!--<div class="control-group">
          <label class="control-label"><?php echo JText::_('ADDRESS'); ?>
           <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_ADDRESS_TOOLTIP_DESC'); ?>
          </label>
          <div class="controls">
            <textarea name="address" rows="3" cols="50" <?php echo $ua; ?>></textarea>
          </div>
    </div>
    <div class="control-group">
          <label class="control-label"><?php echo JText::_('LOCATION'); ?>
           <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_LOCATION_TOOLTIP_DESC'); ?>
          </label>
          <div class="controls"> <?php echo JomclHTML::ListCountries(-1, 0, $config->showmap); ?> <span id="jcsregions_0"></span> </div>
    </div>
    <div class="control-group">
          <label class="control-label"><?php echo JText::_('POSTAL_CODE'); ?>
           <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_POSTAL_CODE_TOOLTIP_DESC'); ?>
          </label>
          <div class="controls">
            <input class="key" type="text"  name="postalcode" <?php echo $ua; ?>/>
          </div>
    </div>-->

    <?php if(!$userid) : ?>
    <!--<div class="control-group">
        <label class="control-label"><?php //echo JText::_('ADVERTISER_NAME'); ?><span class="mandatory">*</span></label>
        <div class="controls">
            <input type="text" name="advertisername" id="advertisername" class="required key"  size="50"/>
        </div>
    </div>-->
    <!-- extra fields advertiser name and company name  are added here  added -->
    <div class="control-group">
		<label class="control-label">Advertiser's Name<?php if($res[0]->required) { ?><span class="mandatory">*</span><?php } ?><span title="" class="hasTooltip" data-original-title="&lt;strong&gt;Enter your full name.&lt;/strong&gt;"> <img alt="Enter your full name." src="https://www.hangartrader.com/components/com_jomclassifieds/assets/tooltip.png"></span></label>
		<div class="controls jomcl-textfield"><input type="text" value="" name="exf_12" id="exf_12" class="input-large <?php if($res[0]->required) echo 'required'; ?>"></div>
	</div>
	<div class="control-group">
		<label class="control-label">Company<?php if($res[1]->required) { ?><span class="mandatory">*</span><?php } ?><span title="" class="hasTooltip" data-original-title="&lt;strong&gt;Enter your Company name.&lt;/strong&gt;"> <img alt="Enter your Company name." src="https://www.hangartrader.com/components/com_jomclassifieds/assets/tooltip.png"></span></label>
		<div class="controls jomcl-textfield"><input type="text" value="" name="exf_21" id="exf_21" class="input-large <?php if($res[1]->required) echo 'required'; ?>"></div>
	</div>
	<!-- ends here -->
	<?php else : 
		if(count($res1) > 0) {
			$rescount = count($res1); 
			//echo "<pre>"; print_r($res1); exit;
			for ($z = 0; $z <= $rescount; $z++){
				if( $res1[$z]->fieldid == '12' && ($res1[$z]->value != "" || $res1[$z]->value != NULL )) {
					//echo "exf_11".$res1[$z]->value; 
				?>
					<input type="hidden" value="<?php echo $res1[$z]->value; ?>" name="exf_12" id="exf_12" class="input-large" />
				<?php 
				$z = $rescount;
				} 
			}
			
			for ($z = 0; $z <= $rescount; $z++){
				if(  $res1[$z]->fieldid == '21' && ($res1[$z]->value != "" || $res1[$z]->value != NULL )) {
					//echo "exf_21".$res1[$z]->value; 
				?>
					<input type="hidden" value="<?php echo $res1[$z]->value; ?>" name="exf_21" id="exf_21" class="input-large" />
				<?php 
				$z = $rescount;
				} 
			} 
			
		}
	?>
	<?php endif; ?>

    <div class="control-group">
          <label class="control-label"><?php echo JText::_('PHONE_NUMBER'); ?><span class="mandatory">*</span>
           <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_PHONE_NUMBER_TOOLTIP_DESC'); ?>
          </label>
          <div class="controls">
            <input class="key required" type="text" name="phonenumber"   />
          </div>
    </div>
<!-- Show Email -->
	<?php if(!$userid  && $config->allowpost > 0 ) : ?>
    <div class="control-group">
          <label class="control-label"><?php echo JText::_('EMAIL'); ?>
          <span class="mandatory">*</span>
          <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_EMAIL_TOOLTIP_DESC'); ?>
          </label>
          <div class="controls">
            <input type="email" name="email" id="jomcluserEmail"  class="required validate-email key"    />
            <!-- onchange="ListMembership(this.value);" -->
          </div>
    </div>
	<?php endif; ?>
<!-- Show Map -->
	<?php // if($config->showmap) : ?>
        <!--<input type="hidden" id="latitude" name="latitude" value="" />
        <input type="hidden" id="langtitude" name="langtitude" value="" />
        <input type="hidden" id="defLocation" name="defLocation" value="<?php echo $defLocation; ?>"/>
        <div class="control-group">
              <label class="control-label">&nbsp;</label>
              <div class="controls">
                <div id="map_canvas" class="jomcl-responsive-container"></div>
              </div>
        </div>-->
    <?php //endif; ?>
	<?php  echo JHtml::_('bootstrap.endSlide'); ?>

<!-- Show Meta -->
	<?php if($config->showmeta) : ?>
	<?php echo JHtml::_('bootstrap.addSlide', 'jomclslide-postad', JText::_('SEO_SETTINGS'), 'seo4'); ?>
    <div class="control-group">
          <label class="control-label"><?php echo JText::_('META_KEYWORDS'); ?>
           <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_META_KEYWORDS_TOOLTIP_DESC'); ?>
          </label>
          <div class="controls">
            <textarea name="meta_keywords" rows="3" cols="50" placeholder="<?php echo JText::_('META_KEYWORDS_DESCRIPTION'); ?>" ></textarea>
          </div>
    </div>
    <div class="control-group">
          <label class="control-label"><?php echo JText::_('META_DESCRIPTION'); ?>
           <?php echo JomclUtils::setTooltip('COM_JOMCLASSIFIEDS_META_DESCRIPTION_TOOLTIP_DESC'); ?>
          </label>
          <div class="controls">
            <textarea name="meta_description" rows="3" cols="50" ></textarea>
          </div>
    </div>
	<?php  echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endif; ?>



	<?php if(trim($config->termsandcond) != '') : ?>
        <div class="control-group" style="margin-top:15px;">
              <label class="control-label">&nbsp;</label>
              <div class="controls">
                <label for="terms" class="checkbox">
                <a class="terms" href="<?php echo $config->termsandcond; ?>" target="_blank"><?php echo JText::_('AGREE_TERMS_AND_CONDITIONS'); ?>
                <span class="mandatory">*</span></a>
                <input type="checkbox" name="terms[]" id="terms" class="required" />
                </label>
              </div>
        </div>
    <?php endif; ?>
<!-- Tool Bar -->
    <div class="jomclsubmitblock">
        <!-- Hidden Fields -->
        <input type="hidden" name="mode" value="new" />
        <input type="hidden" name="extImages" value="" />
        <input type="hidden" name="userid" value="<?php echo JFactory::getUser()->get('id'); ?>" />
        <?php if(JFactory::getUser()->get('guest') == 0 ) :?>
        <input type="hidden" name="email" value="<?php echo JFactory::getUser()->get('email'); ?>" />
        <?php endif; ?>
        <input type="hidden" id="paycurrency" value="<?php echo $currency; ?>" />
         <button type="submit" class="btn btn-success" onclick="return valJomclAddForm();"><i class="icon-new icon-white"></i> <?php echo JText::_('POST_AD'); ?></button>
         <button type="button" class="btn btn-default" onclick="location.href='<?php echo JRoute::_($link.$itemid); ?>'" ><i class="icon-unpublish"></i> <?php echo JText::_('CANCEL'); ?></button>
    </div>
    <!-- Tool End -->
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div class="posttext"><?php echo trim($this->params->get('posttext')); ?></div>
</div>


<?php
	// query to get all state name from table "jomcl_locations" /* anandbabu - 07-30-2015 - task#3587 */
	$db = JFactory::getDBO();
	$query = "SELECT id,alias,name FROM #__jomcl_locations";
	$db->setQuery( $query );
	$location_details = $db->loadObjectList();
	/* ends here */
?>
<script type="text/javascript">
	// assigning location results to js variable /* anandbabu - 07-30-2015 - task#3587 */
	var location_name = <?php echo json_encode($location_details); ?>;
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
					$autosearch('#state').val('');

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
            $autosearch("a.clickable").click(function(event){ event.preventDefault();
            $autosearch("#airportid").val($autosearch(this).html());

            var airportid = document.getElementById("airportid").value;
            $autosearch.post("<?php echo JURI::root(); ?>index.php?option=com_jomclassifieds&format=raw&task=updatelocation&aId="+airportid, function(s) {

                    var Responce = s.replace('<input type="hidden" id="jomclbase" value="https://www.hangartrader.com" />','');
                    Responce = Responce.replace('<input type="hidden" id="joclpreloaderText" value="Loading Please wait...." />','');
                    var jomclJsonObj = jQuery.parseJSON(Responce);

                    //var addr = jomclJsonObj[0].address + ',' +jomclJsonObj[0].city
                    var addr = jomclJsonObj[0].address;

                    // below code is check for location name written by  /* anandbabu - 07-30-2015 - task#3587 */
                    var state_id = jomclJsonObj[0].state;
					for(var u=0; u < location_name.length; u++){
						if(location_name[u]['id'] == state_id){
							var state_name = location_name[u]['name'];
						}
					}
					/* anandbabu - 07-30-2015 - task#3587 */
                    $autosearch('#airportname').val(jomclJsonObj[0].name);
                    //$autosearch('#airaddress').val(addr);
                    $autosearch('#city').val(jomclJsonObj[0].city);
                    $autosearch('#region').val(jomclJsonObj[0].state);
                    $autosearch('#postalcode').val(jomclJsonObj[0].zipcode);
                    /* anandbabu - 07-30-2015 - task#3587 */ // updating state field
                    $autosearch('#state').val(state_name);
					$autosearch('#country').val(state_id);
                    updateAddress(jomclJsonObj[0].name);
            });

			});

			/* extra function for tabbing the input code */
			$autosearch("#airportid").blur(function(event){
				event.preventDefault();

				var airportid =$autosearch("#airportid").val();

			   // var airportid = document.getElementByName("airportid").value;
			   $autosearch.post("<?php echo JURI::root(); ?>index.php?option=com_jomclassifieds&format=raw&task=updatelocation&aId="+airportid, function(s) {

						var Responce = s.replace('<input type="hidden" id="jomclbase" value="https://www.hangartrader.com" />','');
						Responce = Responce.replace('<input type="hidden" id="joclpreloaderText" value="Loading Please wait...." />','');
						var jomclJsonObj = jQuery.parseJSON(Responce);

							// below code is check for location name // below code is check for location name written by  /* anandbabu - 07-30-2015 - task#3587 */
						    var state_id = jomclJsonObj[0].state;
							for(var u=0; u < location_name.length; u++){
								if(location_name[u]['id'] == state_id){
									var state_name = location_name[u]['name'];
								}
							}
							// below code is check for location name written by  /* anandbabu - 07-30-2015 - task#3587 */

						//var addr = jomclJsonObj[0].address + ',' +jomclJsonObj[0].city;
						var addr = jomclJsonObj[0].address;
						if(addr == 'undefined'){
							$autosearch('#airportname').val('');
							$autosearch('#airaddress').val('');
							$autosearch('#city').val('');
							$autosearch('#region').val('');
							$autosearch('#postalcode').val('');
							/* anandbabu - 07-30-2015 - task#3587 */ // emptying state field
							$autosearch('#state').val('');
							$autosearch('#country').val('');

						} else {
							
							$autosearch('#airportname').val(jomclJsonObj[0].name);
							//$autosearch('#airaddress').val(addr);
							$autosearch('#city').val(jomclJsonObj[0].city);
							$autosearch('#region').val(jomclJsonObj[0].state);
							$autosearch('#postalcode').val(jomclJsonObj[0].zipcode);
							/* anandbabu - 07-30-2015 - task#3587 */ // updating state field
							$autosearch('#state').val(state_name);
							$autosearch('#country').val(state_id);
							updateAddress(jomclJsonObj[0].name);
						}
				});

			});
			/* extra function for tabbing the input code */

			});
		}

	}

    </script>
    <script>
		jQuery(document).ready(function(){
			//jomclCollapseAll();
			 //jQuery('#jomclslide-postad .collapse:not(.in)').collapse('show');
			 //jQuery('#jomclslide-postad a').addClass('jomclToggleactive');
			 jQuery('.accordion-group .accordion-heading a.accordion-toggle').addClass('jomclToggleactive');
			 jQuery('.accordion-group .accordion-body.collapse').addClass('in');
			 jQuery('#jomcluserEmail').blur(function(){
					setTimeout(function(){
						jQuery('#jcsemail').find('#membership6').addClass("in");
						jQuery('#jcsemail').find('.accordion-toggle').addClass("jomclToggleactive");
					},
					1000);
			 });
		});
    </script>
    <script>
		jQuery(document).ready(function(){
			jQuery('.jomclRadio').click(function(){
				jQuery('.jomclRadio').removeAttr('checked');
				(jQuery('input[name=membership]:checked', '#jomclForm').val());
				(jQuery(this).attr('checked', 'checked'));
			});
		});
	</script>


