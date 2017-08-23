<?php 
/*
 * @version		$Id: default.php 2.6.0 2014-07-15 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access'); 
require_once(JPATH_ROOT.DS.'components'.DS.'com_jomclassifieds'.DS.'etc'.DS.'utils.php');
$config = JomclUtils::getCfg();
$link    = 'index.php?option=com_jomclassifieds&view=advert';
$catlink = 'index.php?option=com_jomclassifieds&view=category';
$defItemId = JRequest::getInt('Itemid');
$itemId = $params->get('itemid', $defItemId);
if($params->get('showcarousel')==1) {
	$col = 0;
	$cols = $params->get('column');
	//$width = floor(100 / $cols);
	$width = 99;
} else {
	$col = 0;
	$cols = $params->get('column');
	$width = floor(100 / $cols);
	$document = JFactory::getDocument();
	$style = '@media (max-width: 760px) {
				div.jomClassifiedsAdverts .gridview .itemgroup {
				width:99%!important;
				float:none!important;
				}
			  }';
	$document->addStyleDeclaration( $style );
}
/*echo "<pre>"; 
print_r($items);
echo "</pre>"; */
$db = JFactory::getDBO();
$sql_query = "SELECT id,name FROM #__jomcl_locations";
$db->setQuery( $sql_query );
$state_data = $db->loadObjectList();
?>
<?php if($params->get('showcarousel')==1) { ?>
		<div class="jomClassifiedscarousel <?php echo $moduleclass_sfx; ?>">
			<div class="slider jomclautoplay">  
<?php } else {  ?>
		<div class="jomClassifiedsAdverts<?php echo $moduleclass_sfx; ?>">
			<div class="gridview">
<?php } ?>
<div class="itemgroup" style="width:<?php echo $width*$cols; ?>%">
    <?php		
		$i=1;
 		foreach($items as $item) {
			if($col == $cols) {
				$col = 0;
				if($params->get('showcarousel')==1) {
				//echo '<div></div>';
				} else {
				echo '<div  class="clear divider"></div>';
				}
			}
			$col++;
			//echo '<pre>'; print_r($item); echo '</pre>';
			/*$markup = '';
			if($config->showtags) {
				if($item->tagtype == 'custom') {
					$style ='style="border-color:'.$item->tagbdrcolor.'; background-color:'.$item->tagbgcolor.'; color:'.$item->tagtxtcolor.'; '.$item->tagstyle.'"'; 
					$markup .= '<span class="tag" '.$style.'>'.$item->tagname.'</span>';
				} else if($item->tagtype == 'image') {
					$markup .= '<img class="tag" src="'.$item->tagimage.'" style="width:'.$config->tagimgwid.'px; height:'.$config->tagimghei.'px;" />';
				} 
			}*/
			@$detailPage = JRoute::_('index.php?option=com_jomclassifieds&view=advert&id='.$item->id.':'.$item->alias.'&Itemid='.$itemId);
			@$categoryPage = JRoute::_('index.php?option=com_jomclassifieds&view=category&id='.$item->catid.':'.$item->catalias.'&Itemid='.$itemId);
			@$countryPage = JRoute::_('index.php?option=com_jomclassifieds&view=location&id='.$item->country.':'.$item->ctryalias.'&Itemid='.$itemId);	
			@$regionPage = JRoute::_('index.php?option=com_jomclassifieds&view=location&id='.$item->region.':'.$item->regalias.'&Itemid='.$itemId);
			if($item->images != '') {
				$image = explode(',', $item->images);
				$image = JomclUtils::getImage($image[0], '_grid');
				//echo $image;
			} else {
				$image = JURI::ROOT().'components/com_jomclassifieds/assets/noimage.gif';
			}

			if(!$item->category) {
				$item->category = 'n/a';
			}
			$currency = $item->currency == -1 ? '': $item->currency;
			if($item->price == '0.00') {
				$price = 'n/a';
			} else {
			if($params->get('isprice') == 1){
				$price = number_format($item->price, 2) . ' ' . $currency; 
			} elseif ($params->get('isprice') == 2){
				$price =  $currency . ' ' . number_format($item->price, 2); 
			} else {
				$price = number_format($item->price, 2) . ' ' . $currency;
			}
		}

		if($item->country == '-1'){		
				@$location ='n/a';	
		} else if($item->country == $item->region || $item->region =='' ){						
			@$location ='<a href="'.$countryPage.'">'.$item->ctryname.'</a>';		
		} else {				
				@$location ='<a href="'.$regionPage.'">'.$item->regname.'</a>';
				@$location .=', ';
				@$location .='<a href="'.$countryPage.'">'.$item->ctryname.'</a>';
		}		
	?>
      <div class="item" style="width:<?php echo ($width); ?>px; float:left; ">
     	<?php if($params->get('isimage')) : ?>
      		<div class="imgblock">
            	<img class="resize" src="<?php echo $image; ?>" border="1" onclick="javascript:location.href='<?php echo $detailPage; ?>'" />
           	</div>
        <?php endif; ?>
        <!-- commented by anandbabu - 07-29-2015 -->
        <!--<h3> 
			<a href="<?php echo $detailPage; ?>"><?php echo JomclUtils::Truncate($item->title, $params->get('titlecharslimit')); ?></a>
		</h3>-->
		<!-- commented by anandbabu - 07-29-2015 -->
        <?php if($params->get('isdescription')) : ?>
      		<div class="descblock"><?php echo JomclUtils::Truncate($item->description, $params->get('desccharslimit')); ?></div>
        <?php endif; ?>
		<?php if($params->get('iscatname')) : ?>
				<div class="catblock"> 
					<!--<span class="key"><?php echo JText::_('CATEGORY'); ?></span> -->
					<!--<a href="<?php echo $categoryPage; ?>"><span class="value"><?php echo $item->category; ?></span></a>-->
				</div>
        <?php endif; ?>
         <?php if($params->get('islocname')) : ?>
      		<div class="catblock"> 
      			<!--<span class="key"><?php echo JText::_('LOCATION'); ?></span> 
                <span class="value"> <?php echo $location; ?></span>    		-->
      		</div>
        <?php endif; ?>
        <?php if($params->get('isprice')) : ?>
				<div class="priceblock"> 
					<!--<span class="key"><?php echo JText::_('PRICE'); ?></span> -->
					<span class="value"><?php echo $price; ?></span> 
				</div>
        <?php endif; ?>
        <?php if($params->get('isdate')) : ?>
      		<div class="dateblock"> 
      			<!--<span class="key"><?php echo JText::_('DATE_ADDED'); ?></span>-->
        		<span class="value"><?php echo JomclUtils::getRelativeTime($item->createddate); ?></span> 
      		</div>
        <?php endif; ?>
        <?php if($params->get('isviews')) : ?>
			<!--<span class="key"><?php echo JText::_('VIEWS'); ?></span>
				<span class="views"><?php echo $item->views; ?>&nbsp;<?php echo JText::_('VIEWS'); ?></span>-->
        <?php endif; ?>
        <?php /* anandbabu - 05/27/2015- task# 3486 */ ?>
        
		<?php /*if(!empty($item->airportid)){ ?>
				<!--<span class="key"><?php echo 'Airport Name'; ?></span>-->
				<span class="views"><b><?php echo $item->airportname.'('.$item->airportid.')'; ?></b></span> -
      	<?php }*/
			  /*if(!empty($item->address)){ ?>
				<!---<span class="key"><?php echo 'Airport Address'; ?></span>
				<span class="views"><B><?php echo $item->address; ?></B></span>
				<span class="views"><B><?php echo $item->city; ?></B></span>
				<span class="views"><B><?php echo $item->name; ?></B></span>
				<span class="views"><B><?php echo $item->postalcode; ?></B></span>-->
      	<?php } */ ?>
      	<a href="<?php echo $detailPage; ?>"><br />
      	<?php 
			/* anandbabu - 07-29-2015 -3586 */
			if(!empty($item->airportid)){
				echo $item->airportid;
				echo ' - ';
			}
			if(!empty($item->city))
				echo $item->city.', '; 
			foreach($state_data as $state){
				//echo $state->id.''.$item->region;
				if($state->id == $item->region){
					echo $state->name;
					//echo ' - ';
				}
			}
			
			if($item->catid == 1) 
				echo '<br />Hangars for Sale';
			else 
				echo '<br />Hangars for Rent'; 
			/* anandbabu - 07-29-2015 -3586 */

      	?>
      	</a>
      		<!--<span class="key"><?php echo 'Category'; ?></span>-->
			<!--<span class="views"><B><?php if($item->catid == 1) echo 'Hangars for Sale';  else echo 'Hangars for Rent'; ?></B></span>-->
			<br/>
      </div>
    <?php 
	/* reddy - 10-12-2016 -4130 */
		if($i%($cols*4)==0){ 
			if($i != count($items)){ 
				echo '</div><div class="itemgroup" style="width:<?php echo $width*$cols; ?>%">';
			}  
		}  
	/* reddy - 10-12-2016 -4130 */
	$i++;
	}   ?> 
    </div>  
  </div>  
</div>
<div style="clear:both;"></div>
<script>
jQuery(document).ready(function(){
	var item_width=Math.round((jQuery('.itemgroup').width()/<?php echo $cols; ?>)-5);
	jQuery('.itemgroup .item').width(item_width);
});
</script>
