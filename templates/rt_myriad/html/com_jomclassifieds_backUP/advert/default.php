<?php
/*
 * @version		$Id: default.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

$document= JFactory::getDocument();

$item = $this->item;
$extrafields = $this->extrafields;
$markup = JomclHTML::labelMarkUps($this->config, $item);
$tagMarkup = JomclHTML::LabelTags($this->config, $item);
if(!$item->category) {
	$item->category = 'n/a';
}
//$currency = $item->currency == -1 ? '': $item->currency;
$currency = $item->currency == -1 ? '': '$';
if($item->price == '0.00') {
	$price = 'n/a';
} else {

	if($this->config->showprice == 1){
		$price = number_format($item->price, 2) . ' ' . $currency; 
	} elseif ($this->config->showprice == 2){
		$price = $currency . ' ' . number_format($item->price, 2); 
	} else {
		$price = number_format($item->price, 2) . ' ' . $currency;
	}
}
$itemId = JRequest::getInt('Itemid');
$user = JFactory::getUser($item->userid);
$username = $user->username;
$Favourites = JFactory::getUser();

/* Description 3871-custom-page-tags */
if(strlen($item->description) > 0) {
	$set_desc = strip_tags($item->description);
	$document->setMetadata('description', substr($set_desc, 0, 121)); 
}
$document->setMetadata('keywords', $item->title);
/* Description 3871-custom-page-tags */

/* Title  */
//Hangar for [Sale/Rent/Transient Spaced depending on the Ad category] at [Airport Name] in [City], [State]
if( $item->category || $item->airportname || $this->state) {
	if($item->category) {
		switch($item->category){
			case 'Sale': $category_name = 'Hangar for Sale';
					break;
			case 'Rent': $category_name = 'Hangar for Rent';
					break;
			case 'Transient Space': $category_name = 'Hangar for Transient Space';
					break;
		}
		$mytitle = $category_name;
	}
	if($item->airportname) 
	$mytitle .= ' at '.$item->airportname;
	if($item->city) 
	$mytitle .= ' in '.$item->city;
	if($this->address['country']) {
		$state = explode(',',$this->address['country']);
		$mytitle .= ', '.strip_tags($state[0]);
	}
	//echo $mytitle =  ucfirst($item->title.' at '.$item->airportname.' in '.$item->city.', '.$this->state);
	$document->setTitle(ucfirst($mytitle));
}
/* Title  */

?>
<div class="jomclassifieds <?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" id="jomclassifieds">
<div class="pretext"><?php echo trim($this->params->get('pretext')); ?></div>
  <div class="jomcl-detailview <?php if($item->featured > 0 ) { echo JomclUtils::getSuffixclass(1); } ?>">
   <div class="column-top">
     <div class="jomcl-row1">
        <div class="jomcl-left">
        	<h2 class="title"><?php echo $item->title; ?></h2>
		  	<?php echo $markup; ?>
          	<?php if ( $item->featured > 0 && count($this->buildCarousel($item->images,$item)) == 0 ) { ?>
            	 <span class="label label-default"><i class="icon-star-empty"></i><?php echo $item->prename; ?></span>
          	<?php } ?>
          	<?php echo $tagMarkup; ?>
        </div>
        <?php if($this->config->showprice) : ?>
        	<?php if($price != 'n/a') { ?>
       	 		<div class="jomcl-right"><h3 class="title"><?php echo $price; ?></h1></div>
        	<?php } ?>
        <?php endif; ?>
     </div>
     <!--Loaction,category block Start-->
     <?php if($this->config->showlocname || $this->config->showcatname )  : ?>
     <div class="jomcl-row2">
     	<?php if($this->config->showcatname) : ?>
      		<span class="catblock"><!--<span class="icon-folder-open"></span>-->
            <!--<a style="color: black;" href="<?php echo JRoute::_('index.php?option=com_jomclassifieds&view=category&id='.$item->catid.':'.$item->catalias.'&Itemid='.$itemId); ?>">-->
			<?php echo $item->category; ?><!--</a>-->
            </span>
        <?php endif; ?>  
     	<?php if ( ($this->config->showlocname)  && ($this->address['country'] !='')): ?>
     		<span class="locblock"><!--<span class="icon-location"></span>--><?php echo $this->address['country']; ?></span>
        <?php endif; ?>
        <?php if(!empty($item->airportname)): ?>
			<span class="locblock"><?php echo $item->airportname; ?></span>
        <?php endif; ?>
      </div>
     <?php endif; ?>  
    </div>
   <div class="column-left jomcl-left">
   		<!--Gallery  Start-->
      		<?php echo $this->buildCarousel($item->images,$item); ?>
        <!--Social Share Start--> 
      <div class="jomcl-box jomcl-social">
       	<div class="social-script jomcl-left"><?php echo $this->config->socialwidget; ?></div>
        <div class="social-fav-report jomcl-right">
        <?php if($this->config->allowfav) : ?>
        	<div class="text-warning jomcl-left"><i class="icon-star"></i>
            <a <?php if(!$Favourites->id) { ?> class="jomcl-popup-trigger" data-jomcl-modal="jomclFavourites" <?php } ?> id="favourites" href="javascript: void(0)"><?php echo $this->favourites; ?></a></div>
        <?php endif; ?>
        <?php if($this->config->reportabuse) : ?>
       	 <div class="text-error jomcl-right">
         	<i class="icon-flag"></i><a class="jomcl-popup-trigger" data-jomcl-modal="jomclReport" href="javascript: void(0)"><?php echo JText::_('REPORT_THIS_AD') ?></a>
         </div>
        <?php endif; ?>
        </div> 
      </div>
       <!--Description Start-->
      <?php if(strlen($item->description) > 0) {  ?>
      <div class="jomcl-box">
        <h3 class="item-details"><span class="icon-pencil"></span><?php echo JText::_('DESCRIPTION') ?></h3>
        <div class="jomcl-desc muted"> <?php echo $item->description;?> </div>
      </div>
      <?php } ?>
     <!--Extrafields Start-->
      <?php if(count($extrafields) > 0 ) {  ?>
      <div class="jomcl-box">
        <h3 class="item-details"><span class="icon-pie"></span><?php echo JText::_('MORE_DETAILS') ?></h3>
        <div class="jomcl-extra-fields">
        <table class="table table-striped">
        <thead>        
         <tr>
           <th class="span4 col-md-4"><?php echo JText::_('NAME') ?></th>
           <th class="span8 col-md-8"><?php echo JText::_('DESCRIPTION') ?></th>
         </tr>
         </thead>
         <tbody>              
        <?php 	foreach($extrafields as $extrafield) :	?>
      	<tr>
           <td class="span4 col-md-4"><?php echo $extrafield->label; ?></td>
           <td class="span8 col-md-8"><?php echo $extrafield->value; ?></td>
        </tr>
        <?php endforeach; ?>
       </tbody>
      </table>
        </div>
      </div>
      <?php } ?>
    </div>
    <!--Left column End-->
    <div class="column-right jomcl-right">
      <!--JOmclBox3-->
      <div class="jomcl-box">      
        <h3 class="item-details"><!--<span class="icon-pin"></span>--><?php echo JText::_('AD_DETAILS') ?></h3>
        <div class="ad_idblock"><!--<span class="icon-lamp"></span>--><?php echo JText::_('AD_ID'); ?>&nbsp;:&nbsp;<?php echo $item->id; ?></div>    
        <div class="ad_idblock"><!--<span class="icon-lamp"></span>--><?php echo JText::_('Date Posted'); ?>&nbsp;:&nbsp;<?php echo date('m-d-Y',strtotime($item->createddate)); ?></div>  
        <?php if($this->config->showdateadded) : ?>
        	<div class="dateblock"><!--<span class="icon-calendar"></span>--><?php echo JomclUtils::getRelativeTime($item->createddate); ?></div>
        <?php endif; ?>		
        <?php if($this->config->showcatname) : ?>
        	<div class="catblock"><!--<span class="icon-folder-open"></span>-->
            <!--<a style="color:black;" href="<?php echo JRoute::_('index.php?option=com_jomclassifieds&view=category&id='.$item->catid.':'.$item->catalias.'&Itemid='.$itemId); ?>">-->
			<?php echo $item->category; ?><!--</a>-->
            </div>
        <?php endif; ?>
         <?php if($this->config->showmap && $this->address['address'] != '') : ?>		
		<?php if(!empty($item->airportname)): ?>
		<span class="locblock"><?php echo $item->airportname; ?></span>
		<?php endif; ?>		
        <?php endif; ?>
         <?php if($this->config->showmap && $this->address['address'] != '') : ?>
       		<div class="locblock"><!--<span class="icon-location"></span>--><?php $address = explode(',', $this->address['address']); 
       		//print_r($address);echo $item->city; 
				$cnt = 0;
				foreach($address as $parts) :  if($cnt == 1 ) echo $item->city.', ';  echo $parts.', '; $cnt++; endforeach; ?>
       		</div>
        <?php endif; ?>

        <?php if($this->config->showviewscount) : ?>
       		<div class="viewsblock"><!--<span class="icon-eye"></span>--><?php echo $item->views; ?>&nbsp;<?php echo JText::_('VIEWS'); ?></div>
        <?php endif; ?>
      </div>
      <!--JOmclBox2-->
      <div class="jomcl-box">
        <h3 class="item-details"><span class="icon-pencil-2"></span><?php echo JText::_('Contact Information') ?></h3>
        <?php if($this->config->showusername) : ?>
        	<div class="userblock"><!--<span class="icon-user"></span>--><?php echo $user->name.'&nbsp;('.$this->getuseradsCount($item->userid).')'; ?></div>
        <?php endif; ?>
        <?php if(!empty($item->phonenumber)) : ?>
        	<div class="phoneblock"><!--<span class="icon-mobile"></span>--><?php echo $item->phonenumber; ?></div>
        <?php endif; ?>
        <?php if($this->config->showemail && !empty($item->email) ) : ?>
        	<div class="emailblock"><span class="icon-mail-2"></span><?php echo $item->email; ?></div>
        <?php endif; ?> 
		  <?php
        $db = JFactory::getDBO();
	$query  = "SELECT * FROM #__jomcl_premium WHERE type=".$db->quote('membership') . 'AND published=1 AND id='.$item->membership;
	$db->setQuery( $query );
	$_premium = $db->loadObject();			
	?>
         <?php if($_premium->facebook_display ) : 
		 //echo "<pre>"; print_r($item);
		 		$fblink = $item->facebook_link;
				if(!empty($fblink)){ ?>
        	<div class="emailblock"><span class="icon-out-2"></span><a href="<?php echo JomclUtils::castAsURL($item->facebook_link); ?>" target="_blank"><?php echo JText::_('FACEBOOK_TEXT'); ?></a></div>
				<?php } ?>
        <?php endif; ?> 
         <?php if($_premium->website_display) : 
		 	$weblink = $item->website_link;
			if(!empty($weblink)): ?>
        	<div class="emailblock"><span class="icon-out-2"></span><a href="<?php echo JomclUtils::castAsURL($item->website_link); ?>" target="_blank"><?php echo JText::_('WEBSITE_TEXT'); ?></a></div>
			 <?php endif; ?>
        <?php endif; ?>

        <?php if($this->config->showusername) : ?>       
       		<div class="userlinkblock"><span class="icon-arrow-right-2"></span>
            <a style= "color:black;" href="<?php echo JRoute::_('index.php?option=com_jomclassifieds&view=userads&id='.$item->userid.':'.$user->username.'&Itemid='.$itemId);?>">
			<?php echo JText::_('SHOW_USER_ADS'); ?></a>
            </div>
        <?php endif; ?>
      </div>
      <!--JOmclBox2-->   
      	<a class="jomcl-popup-trigger jomcl-btn btn btn-large btn-block btn-primary" data-jomcl-modal="jomclContact" href="javascript: void(0)">
        	<span class="icon-plus"></span> <?php echo JText::_('Email Advertiser') ?>
        </a>
		<?php
		$print_advert = JRoute::_( 'index.php?option=com_jomclassifieds&view=advert&layout=downloadAdverts&'.'id='.$item->id .':'.$item->alias.'&Itemid='.$itemId);
        ?>
         <a class="jomcl-btn btn btn-large btn-block btn-primary" href="<?php echo $print_advert; ?>" target="_blank">
        	<span class="icon-printer"></span> <?php echo JText::_('PRINT_ADVERT') ?>
        </a>

      <!--ContactAdvertiser-->
      <?php  if($item->video !='' && $this->config->showvideo > 0 ) {  ?>
      	<div class="jomcl-box jomcl-yt">
        	<a class="jomcl-popup-trigger" data-jomcl-modal="jomclVideo" href="javascript: void(0)">
            <img class="jomcl-yt-overlay" alt="youtube" src="<?php echo JURI::root(); ?>components/com_jomclassifieds/assets/youtube.png" > 
            <img class="jomcl-yt-thumb"  alt="youtubePlay" src="https://img.youtube.com/vi/<?php echo JomclUtils::getYouTubeVideoId($item->video);?>/hqdefault.jpg" >
           </a>
      	</div>
      <?php } ?>
      <?php if($this->config->showmap && $this->address['address'] != '') : ?>
       	<div class="jomcl-box jomcl_map">
        	<?php $this->addScript(); ?>
       		<div id="map_canvas"></div>
       		<input type="hidden" id="latitude" name="latitude" value="<?php echo $item->latitude; ?>" />
        	<input type="hidden" id="langtitude" name="langtitude" value="<?php echo $item->langtitude; ?>" />
        	<input type="hidden" id="defLocation" name="defLocation" value="none"/>       
        	<h3 class="item-details"><span class="icon-compass"></span>
            <a href="https://www.google.com/maps/place/<?php echo trim($this->address['map']);?>" target="_blank"><?php echo JText::_('VIEW_MAP_ON_LARGE'); ?></a>
            </h3>
      </div>
      <?php endif; ?>
    </div>
    <div style="clear:both;"> <?php echo $this->loadTemplate('comments'); ?></div>
  </div>
 <input type="hidden" id="userid" name="userid" value="<?php echo JFactory::getUser()->get('id'); ?>" />
 <input type="hidden" id="advertid" name="advertid" value="<?php echo $item->id; ?>" />
 <div class="clear"></div>
 </div>
  <div class="clearfix"></div>
<?php if($this->config->showrelatedads) : 
		echo $this->loadTemplate('related');
endif; ?>
<div class="jomcl-popup-container">
	<?php echo $this->loadTemplate('gallery'); ?>
	<?php echo $this->loadTemplate('contact'); ?>
    <?php if($this->config->reportabuse) : ?>
 		<?php echo $this->loadTemplate('report'); ?> 
    <?php endif; ?>
    <?php if($this->config->showvideo) : ?>
 		<?php echo $this->loadTemplate('video'); ?>
    <?php endif; ?> 
    <?php if(!$Favourites->id) { ?>
	<?php echo $this->loadTemplate('favourites'); ?>
    <?php } ?> 	
  	<div class="jomcl-popup-overlay"></div>
</div>
