<?php

/*
 * @version		$Id: default_list.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

/* for importing model of advert  */
jimport('joomla.application.component.model');
require_once(JPATH_COMPONENT.DS.'models'.DS.'advert.php');
$db = JFactory::getDBO();
/* ends here */

$config = JomclUtils::getCfg();
$view   = JRequest::getCmd('view');
$task   = JRequest::getCmd('task');
$itemid = JRequest::getInt('Itemid');

$items = $this->items;

?>

<div id="jomclassifieds" class="jomclassifieds<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<div class="pretext"><?php echo trim($this->params->get('pretext')); ?></div> 
<form action="<?php echo JURI::getInstance()->toString(); ?>" method="post" name="jcsForm" id="jcsForm" enctype="multipart/form-data">
    <div class="jomcl-header-text">	
		<?php if( isset($this->title) ) {  ?>
               <h2><?php echo $this->title; ?></h2>
        <?php } ?>    			
     </div>   
    <!--Views Changer & OrderList-->
  <?php if($config->showviewslist || $config->showorderbylist) : ?>
         <div class="jomcl-right jomcl-header-filter">
             <?php if($config->showviewslist && $view != 'user') echo $this->lists['views']; if($config->showorderbylist) echo $this->lists['orderby']; ?>
         </div>
  <?php endif; ?>
  <span class="clear">&nbsp;</span>   
</form> 
<div class="listview">
    <?php
	$i = 0;
	foreach($items as $item) :
		$k = $i++ % 2;
		$markup = JomclHTML::labelMarkUps($config, $item);
		$tags = JomclHTML::LabelTags($config, $item);	
		$user = JFactory::getUser($item->userid);		
		// $detailPage = JRoute::_('index.php?option=com_jomclassifieds&view=advert&id='.$item->id.':'.$item->alias.'&Itemid='.$itemid);
		$detailPage = JRoute::_('index.php?option=com_jomclassifieds&view=advert&id='.$item->id.':'.$item->alias.'&Itemid=213');
		$categoryPage = JRoute::_('index.php?option=com_jomclassifieds&view=category&id='.$item->catid.':'.$item->catalias.'&Itemid='.$itemid);
		$countryPage = JRoute::_('index.php?option=com_jomclassifieds&view=location&id='.$item->country.':'.$item->ctryalias.'&Itemid='.$itemid);	
		$regionPage = JRoute::_('index.php?option=com_jomclassifieds&view=location&id='.$item->region.':'.$item->regalias.'&Itemid='.$itemid);
		$userPage = JRoute::_('index.php?option=com_jomclassifieds&view=userads&id='.$item->userid.':'.$user->username.'&Itemid='.$itemid);
		if($item->images != '') {
			$image = explode(',', $item->images);
			$imgCount = count($image);
			$image = JomclUtils::getImage($image[0], '_list');
		} else {
			$image = JOMCLASSIFIEDS_DEFAULT_IMG;
			$imgCount = 0;
		}
		if(!$item->category) {
			$item->category = 'n/a';
		}
		$currency = $item->currency == -1 ? '': $item->currency;
		if($item->price == '0.00') {
			$price = 'n/a';
		} else {			
			if($config->showprice == 1){
				$price = number_format($item->price, 2) . ' ' . $currency; 
			} elseif ($config->showprice == 2){
				$price = $currency . ' ' . number_format($item->price, 2); 
			} else {
				$price = number_format($item->price, 2) . ' ' . $currency;
			}
		}
		if($item->country == '-1'){		
			$location ='n/a';	
		} else if($item->country == $item->region || $item->region =='' ){						
			$location ='<a href="'.$countryPage.'">'.$item->ctryname.'</a>';		
		} else {				
			$location ='<a href="'.$regionPage.'">'.$item->regname.'</a>';
			$location .=',';
			$location .='<a href="'.$countryPage.'">'.$item->ctryname.'</a>';
		}	
		$mem_Class_suffix = JomclUtils::getSuffixclass($item->membership);	
		$pre_Class_suffix = '';
		if($item->featured > 0 ){
			$pre_Class_suffix = JomclUtils::getSuffixclass(1);
		}
		/* below code is for getting extra fields and date from advert.php in models */
		$query = "SELECT * FROM #__jomcl_extrafields WHERE published=1";
		$query .= " AND catids LIKE ' 0 '";
		if($item->catid > 0) {
			$query .= " OR catids LIKE '% " . $item->catid . " %'";
		}
		$query .= ' ORDER BY ordering';
		$db->setQuery($query); 
		//echo $query;
		$extrafields = $db->loadObjectList();

		foreach($extrafields as $id => $extrafield) {
			$value = JomClassifiedsModelAdvert::RenderValues($extrafield, $item->id);
			if($value) {
				$extrafield->value = $value;
			} else {
				unset($extrafields[$id]);
			}
		}
		
			//echo '<pre>'; print_r($item); echo '</pre>';
  ?>
  <!--- custom layout start here --> 
  <div class="full-row">
	  <div class="rt-flex-container<?php if($mem_Class_suffix !=''){ echo ' '.$mem_Class_suffix; } ?><?php if($pre_Class_suffix !=''){ echo ' '.$pre_Class_suffix; } ?>">
      <div class="rt-grid-12 full-title-show">
            <h4>
              <?php if(!$item->expired) { ?>
              <a href="<?php echo $detailPage; ?>">
              <?php } ?>
                <?php 
                    if($item->title){
                        echo ucfirst(strtolower(JomclUtils::Truncate($item->title, $config->titlecharslimit)));
                        if($item->category) echo ','; 
                    }
                ?>
              <?php if(!$item->expired) { ?>
              </a>
              <?php } ?>
             <?php  if($item->category)	
					echo '<strong class="adtype-label adtype-font">'.$item->category.'</strong>';  
					if($item->airportname) echo ',';
			 ?>
              <?php if(!$item->expired) { ?>
				  <a href="<?php echo $detailPage; ?>">
			  <?php } ?>
                <?php 
                    if($item->airportname) echo ' <span>'.ucfirst(strtolower($item->airportname)).'</span>'; 
                ?>
              <?php if(!$item->expired) { ?>
				  </a>
			  <?php } ?>
            </h4>
           </div>
		  <div class="rt-grid-4">
			  <div class="detai">
				  <?php //echo '<pre>'; print_r($item); echo '</pre>'; ?>
				  <?php if($item->featured) : ?>
					<div class="jomclfeaturedrow"><?php echo $item->memname;?></div>
				  <?php endif; ?>
				  <?php if($item->expired) : ?>
					<div class="jomclfeaturedrow-expired"><?php echo 'Expired';?></div>
				  <?php endif; ?>
				  
				  <div class="jomclimgthumbnils">         
					<?php if($imgCount) : ?>
						<!--<span class="jomcl-photo-count"><?php //echo $imgCount; ?></span>-->
					<?php endif; ?>
					<?php /*if($item->video !=''){ ?>
						<span class="jomcl-video-list icon-camera-2"></span>
					<?php } */ ?>
				   <img src="<?php echo $image; ?>"  alt="<?php echo basename($image); ?>" width="<?php echo $config->listthumbwid;?>" height="<?php echo $config->listthumbhei;?>" <?php if(!$item->expired) { ?>onclick="javascript:location.href='<?php echo $detailPage; ?>'" <?php } ?>> 
				  </div>
				  <div class="cat-detials">
					<?php 	
						if($item->price)   echo '<strong>$ '.number_format($item->price).'</strong><br>';
						// echo '<strong>'.$item->price.'</strong><br>';
						
						if(count($extrafields) > 0 ) :
							foreach($extrafields as $extrafield) :	
								
								switch($extrafield->label){
									case 'Hangar Width':
										$HangerWidth = $extrafield->value;
										break;
									case 'Hangar Height':
										$HangerHeight = $extrafield->value;
										break;
									case 'Hangar Depth':
										$HangerDepth = $extrafield->value;
										break;
								}
							endforeach;
							
							echo '<strong>' . $HangerWidth . 'X' . $HangerDepth. 'X' . $HangerHeight. '</strong>';
						endif;
					?>
				</div>
			</div>
		  </div>
		  <div class="rt-grid-5 desc_sec">
			  <?php 
				if($item->description) {
					echo '<strong>'.trim(substr( strip_tags($item->description),0,150)).'...</strong>'; 
				} else {
					echo '<strong> No Description </strong>';
				}
			  ?>
		  </div>
		  <div class="rt-grid-3 contacts-info">
				<?php if(!$item->expired) :  ?>
				<div class="contact-info">
				  <h4>Contact Information</h4>
				  <?php 
				  $advertisers_name = '';
				  $company_name = '';
				  foreach($extrafields as $id => $extrafield) {
						@$value = JomClassifiedsModelAdvert::RenderValues($extrafield, $item->id);
						if($extrafield->label == "Advertiser's Name"){
							$advertisers_name = $extrafield->value;
						} 
						if($extrafield->label == 'Company'){
							$company_name  	  =  $extrafield->value;
						}
					}
					echo '<strong>Name:</strong> '; if($advertisers_name){ echo $advertisers_name; } else { echo ' N/A '; } echo '<br>';
					echo '<strong>Company:</strong> '; if($company_name){ echo $company_name; } else { echo ' N/A '; } echo '<br>';
					echo '<strong>Ph:</strong> '; if($item->phonenumber){ echo $item->phonenumber; } else { echo ' N/A '; } echo '<br>';
					echo '<strong>Email:</strong> '; if($item->email){ echo $item->email;  } else echo ' N/A '; echo '<br>';
					echo '<a href="'.$detailPage.'"><div class="detail-page"><strong>Details</strong></div></a>';
				  ?>
			   </div> 
			  <?php endif; ?>
		  </div>
	  </div>
  </div>
  
  <?php /*
 <div class="jomcllist-items<?php if($mem_Class_suffix !=''){ echo ' '.$mem_Class_suffix; } ?><?php if($pre_Class_suffix !=''){ echo ' '.$pre_Class_suffix; } ?>">
      <!--Top row Start-->
     <div class="jomcltoprow">
       <!--Featrured row Start-->
  	 	<?php if($item->featured) : ?>
   			<div class="jomclfeaturedrow"><?php echo $item->memname;?></div>
   		<?php endif; ?>     
        <!--Top row First column-->
      <div class="jomcltoprow-col-first" style="width:<?php echo $config->listthumbwid;?>px;">
          <div class="jomclimgthumbnils">         
            <?php if($imgCount) : ?>
        	    <span class="jomcl-photo-count"><?php echo $imgCount; ?></span>
            <?php endif; ?>
            <?php if($item->video !=''){ ?>
            	<span class="jomcl-video-list icon-camera-2"></span>
            <?php } ?>
           <img src="<?php echo $image; ?>"  alt="<?php echo basename($image); ?>" width="<?php echo $config->listthumbwid;?>" height="<?php echo $config->listthumbhei;?>" onclick="javascript:location.href='<?php echo $detailPage; ?>'" > </div>
      </div>
        <!--Top row second column -->
      <div class="jomcltoprow-col-second">
          <!-- Title Start-->
      	<div class="jomclitem-entry">
          <div class="entry-first-row">
            <div class="titleblock">
        		<h3><a href="<?php echo $detailPage; ?>"><?php echo JomclUtils::Truncate($item->title, $config->titlecharslimit); ?></a></h3><?php echo $markup; ?>
                <?php if($config->showtags && $tags !='') : echo $tags; endif; ?> 
           </div> 
        <!--currency Mobile view Start--> 
        <?php if($config->showprice) : ?><?php if($price != 'n/a'){ ?>         
          	 <div class="jomcldesc jomcl-desk-hide"><h4><?php echo $price; ?></h4></div>
        <?php } ?><?php endif; ?>       
        <!-- Desc Start -->
        <?php if($item->description !='') {  ?>
        	  <div class="jomcldesc muted"><?php echo JomclUtils::Truncate($item->description, $config->desccharslimit); ?></div>
        <?php } ?>
        <!--user,date,views blocks Start-->
        <div class="jomclblocks">
        <?php if($config->showusername) : ?>
            <div class="userblock"><span class="icon-user"></span><a href="<?php echo $userPage; ?>"><?php echo $user->name; ?></a></div>
        <?php endif; ?>
        <?php if($config->showdateadded) : ?>
            <div class="dateblock"><span class="icon-clock"></span><?php echo JomclUtils::getRelativeTime($item->createddate); ?></div>
        <?php endif; ?>
        <?php if($config->showviewscount) : ?>
            <div class="viewsblock "><span class="icon-eye"></span><?php echo $item->views.'&nbsp;'; ?><small class="muted"><?php echo JText::_('VIEWS'); ?></small></div>
         <?php endif; ?> 
          </div>
        <!--user,date,views blocks End-->
      </div>       
      <?php if($config->showprice) : ?><?php if($price != 'n/a'){ ?>         
          	 <div class="currency-row jomcl-mobile-hide"><h4><?php echo $price; ?></h4></div>
      <?php } ?><?php endif; ?>
       <!--currency row End--> 
       </div>
    </div>
      <!--Top row second column End-->
    </div>
    <!--Top row End-->
    <!--Bottomrow Start-->
    <?php if( ( $config->showcatname > 0 ) || ( $config->showlocname > 0 ) ) {  ?>
    <div class="jomclbottomrow">
    	 <?php if($config->showcatname) : ?>
        	<div class="catblock"><span class="icon-folder-open"></span><a href="<?php echo $categoryPage; ?>"><?php echo $item->category; ?></a></div>
        <?php endif; ?>     
    	<?php if($config->showlocname) : ?>
       		<?php if($location !='n/a') { ?>
        	<div class="locblock"><span class="icon-location"></span><?php echo $location; ?></div>
        	<?php } ?>
        <?php endif; ?>         
    </div>
    <?php } ?>
      <!--Bottomrow End-->
    </div> 
    */?>
    <?php endforeach; ?>
  </div>
  <div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
  <div class="posttext"><?php echo trim($this->params->get('posttext')); ?></div>
</div>
