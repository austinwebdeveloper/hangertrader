<?php

/*
 * @version		$Id: default.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
$config = JomclUtils::getCfg();
JHtml::_('bootstrap.tooltip');

$itemid = JRequest::getInt('Itemid');
$items = $this->items;

$isFreeMemebership = $this->isFreeMemebership;
$allowfeatured = JomclUtils::getColumn('premium', 'published', 1);
$paths ='index.php?option=com_jomclassifieds&view=user&layout=add&Itemid=';
$newAd = JRoute::_($paths.$itemid);
$dashboard = JRoute::_('index.php?option=com_jomclassifieds&view=user&layout=dashboard&Itemid='.$itemid);
$editprofile = JRoute::_('index.php?option=com_users&view=profile&layout=edit&Itemid='.$itemid);
?>

<div id="jomclassifieds" class="jomclassifieds<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
  <div class="pretext"><?php echo trim($this->params->get('pretext')); ?></div> 
 <div class="clear"></div>       
  <form action="#" method="post" name="jcsForm" id="jcsForm" enctype="multipart/form-data">
  	<?php if ($this->params->get('show_page_heading') == 1) { ?>	
		<h2 class="jomcl-left jomcl-header-filter"><?php echo $this->escape($this->params->get('page_heading')); ?></h2>	
   <?php } else if( isset($this->title) ) {  ?>
  		<h2 class="jomcl-left jomcl-header-filter"><?php echo $this->title; ?></h2>
    <?php } ?>  
    
    <div class="jomcl-right jomcl-header-filter-user">
        <div class="btn-group">
            
              <a class="btn btn-default" href="<?php echo $editprofile; ?>"><i class="icon-pencil-2 icon-white"></i><?php echo JText::_('Edit Profile'); ?></a>
              <a class="btn btn-default" href="<?php echo $dashboard; ?>"><i class="icon-dashboard icon-white"></i><?php echo JText::_('DASHBOARD'); ?></a>
              <a class="btn btn-success" href="<?php echo $newAd; ?>"><i class="icon-new icon-white"></i><?php echo JText::_('POST_AN_AD'); ?></a>
        </div>  
        <?php if($config->showorderbylist) : ?>    	
            <?php echo $this->lists['orderby']; ?>
        <?php endif; ?>  
    </div>
    <div class="clear"></div>
  </form>   
   
<div id="jomcl-table" class="jomcl-user-page"> 
  <div class="jomcl-table-row table-row-header">  	  
     <div class="jomcl-table-col jomcl-col-4">
     	<div class="content"></div>
     </div>
     <div class="jomcl-table-col jomcl-col-1">
     	<div class="content"><?php echo JText::_('#AD Number'); ?></div>
     </div>
     <div class="jomcl-table-col jomcl-col-1">
 	   <div class="content"><?php echo JText::_('STATUS'); ?></div>
     </div>      
     <div class="jomcl-table-col jomcl-col-2">
   		 <div class="content"><?php echo JText::_('EXPIRY_DATE'); ?></div>
     </div>
     <div class="jomcl-table-col jomcl-col-2">
      	<div class="content"><?php echo JText::_('ACTION'); ?></div>
     </div>
  </div>
    <?php
	  	$i = 0;
		foreach($items as $item) {
			$k = $i++ % 2;
			$status = ($item->published == 1) ? 'icon-publish text-success' : 'icon-unpublish text-error';
			$statusText = ($item->published == 1) ? 'Published' : 'Un published';
			
			$markup = JomclHTML::labelMarkUps($config, $item);
			$item->slug = $item->id.':'.$item->alias;
			$item->catslug = $item->catid.':'.$item->catalias;
			$detailPage = JRoute::_('index.php?option=com_jomclassifieds&view=advert&id='.$item->slug.'&Itemid='.$itemid);
			$categoryPage = JRoute::_('index.php?option=com_jomclassifieds&view=category&id='.$item->catslug.'&Itemid='.$itemid);
			$edit = JRoute::_('index.php?option=com_jomclassifieds&view=user&task=edit&id='.$item->slug.'&Itemid='.$itemid.'&'.JSession::getFormToken().'=1');
			$advertdetailnextpage = JRoute::_('index.php?option=com_jomclassifieds&view=user&task=addtwo&id='.$item->id.'&Itemid='.$itemid.'&'.JSession::getFormToken().'=1');
			$isPromote = 0;	
			if($allowfeatured) {
				if(!$item->featured && !$item->renew ) {
					$isPromote = 1;
					$promote = JRoute::_('index.php?option=com_jomclassifieds&view=payment&task=promote&id='.$item->slug.'&Itemid='.$itemid);
				}
			}
			$isPromote = 0;		
			if($item->membership > 0 ){
				$isPromote = JomclUtils::getColumn('premium', 'promotion_display', $item->membership);
			}

			$mode = 'text-success jomcl-active';
			if($item->published == 0) {				
				$mode = 'text-error expired';
			} else if($item->renew) {
				$mode = 'text-warning renew';
			}
			$_view = ($item->renew == 1 && $isFreeMemebership == 0) ? 'user&task=renew' : 'payment' ;		
			$renew = JRoute::_('index.php?option=com_jomclassifieds&view='.$_view.'&id='.$item->slug.'&Itemid='.$itemid.'&'.JSession::getFormToken().'=1');
			//$delete = JRoute::_('index.php?option=com_jomclassifieds&view=user&task=delete&id='.$item->slug.'&Itemid='.$itemid.'&'.JSession::getFormToken().'=1');
			$delete = JURI::root().'index.php?option=com_jomclassifieds&view=user&task=userdelete&id='.$item->slug.'&Itemid='.$itemid;
			//echo $delete;
			
			if($item->images != '') {
				$image = explode(',', $item->images);
				$image = JomclUtils::getImage($image[0], '_list');
			} else {
				$image = JOMCLASSIFIEDS_DEFAULT_IMG;
			}	
			$premiumName = JomclUtils::getColumn('premium','name',1);
			$expiryDate = ( $item->expirydate == '0000-00-00 00:00:00') ? '0000-00-00 00:00:00' : JHtml::date($item->expirydate , 'D, M j, Y g:i a', null);
					
	  ?>
   <div class="jomcl-table-row"> 
     <div class="jomcl-table-col jomcl-col-4"> 
         <div class="jomcl-col-image" style="width:100px;">
        	 <img src="<?php echo $image; ?>" alt="<?php echo basename($image); ?>" onclick="javascript:location.href='<?php echo $detailPage; ?>'" >
         </div>
         <div class="jomcl-col-content">
         	<h3> <a href="<?php echo $detailPage; ?>"><?php echo JomclUtils::Truncate($item->title, $config->titlecharslimit); ?></a><?php echo $markup; ?>
            <?php if ( $item->featured > 0) { ?>
             <span class="label label-default"><i class="icon-star-empty"></i><?php echo $premiumName; ?></span>
            <?php } ?> 
            </h3>       
        	<div class="catblock"><a href="<?php echo $categoryPage; ?>"><span class="icon-folder-open"></span><?php echo $item->category; ?></a></div>            
         </div>        
     </div>
     <div class="jomcl-table-col jomcl-col-1">
          <span class="jomcl-desk-hide"><?php echo JText::_('COM_JOMCLASSIFIEDS_MEMBERSHIP');?></span>
         <!-- <div class="content muted"><?php if($item->pname ==''){ echo '<span class="expired"> n/a </span>';}else{ echo $item->pname;} ?></div>-->
          <div class="content muted"><?php echo $item->id; ?></div>
     </div>      
     <div class="jomcl-table-col jomcl-col-1">
     <span class="jomcl-desk-hide"><?php echo JText::_('STATUS');?></span>
          <div class="content muted">
             <span class="hasTooltip" title="" data-original-title="<?php echo $statusText; ?>">
             <span class="<?php echo $status; ?>"></span>   
             </span>    
          </div>
     </div> 	  
     
     <div class="jomcl-table-col jomcl-col-2">
    	  <span class="jomcl-desk-hide"><?php echo JText::_('EXPIRY_DATE');?></span>
   		 <div class="content <?php echo $mode; ?>"><?php echo $expiryDate; ?></div>
     </div>
     <div class="jomcl-table-col jomcl-col-2 user-actions"> 
         <span class="jomcl-desk-hide"><?php echo JText::_('ACTION');?></span> 
         <div class="btn-group jomcl-btn-block">
            	<a class="btn btn-default btn-small" href="<?php echo $edit; ?>">
                <span class="hasTooltip" title="" data-original-title="<?php echo JText::_('Edit your ad'); ?>"><img src="<?php echo JURI::root(); ?>components/com_jomclassifieds/assets/edit1.png" alt="" class="text-warning"></img> <?php echo JText::_('Edit'); ?></span>
                </a>
                                       
                <?php if($item->membership == -1){ ?>
                     <a class="btn btn-default btn-small" href="<?php echo $renew; ?>">
                     <span class="hasTooltip" title="" data-original-title="<?php echo JText::_('COM_JOMCLASSIFIEDS_MEMBERSHIP_PAY'); ?>"><span class="icon-warning text-error"></span></span>
                     </a>                
            <?php } else if($item->renew == 1 || ($item->expired == 1 && $item->published == 0) ) {?>
                     <a class="btn btn-default btn-small renew-icon" href="<?php echo $renew; ?>">
                     <span class="hasTooltip" title="" data-original-title="<?php echo JText::_('Renew your ad'); ?>"><span class="icon-loop text-warning"></span> <?php echo JText::_('Renew'); ?></span>
                     </a>  
			  <?php } ?>  
                          
             <?php if($item->membership == -1 || $item->published !=1 ){ ?>              
            <?php } else if($isPromote) { ?>
                <a class="btn btn-default btn-small jomcl-block pro-icon" href="<?php echo $promote; ?>">
               <span class="hasTooltip" title="" data-original-title="<?php echo JText::_('Promote your ad'); ?>"><img src="<?php echo JURI::root(); ?>components/com_jomclassifieds/assets/upgrade_icon.png" alt="" class="text-warning"></img></span>
                 </a> 
             <?php } ?>
             
              <a class="btn btn-default btn-small jomcl-block" href="<?php echo $renew; ?>">
               <span class="hasTooltip" title="" data-original-title="<?php echo JText::_('Upgrade your ad'); ?>"><img src="<?php echo JURI::root(); ?>components/com_jomclassifieds/assets/upgrade_icon.png" alt="" class="text-warning"></img> <?php echo JText::_('Upgrade'); ?></span>
                 </a>  
             
             <a class="btn btn-default btn-small delete" href="javascript:confirmDelete('delete_<?php echo $item->id; ?>')">
                    <form id="delete_<?php echo $item->id; ?>" action="<?php echo $delete; ?>" method="post">
                        <span class="hasTooltip" title="" data-original-title="<?php echo JText::_('Expire your ad'); ?>"><img src="<?php echo JURI::root(); ?>components/com_jomclassifieds/assets/cancel_icon.png" alt="" class="text-warning"></img> <?php echo JText::_('Expire'); ?></span>
                     </form>
                </a> 
               </div>                
        </div>     
  </div>  
    <?php } ?>  
</div> 
  
   <div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
  <div class="posttext"><?php echo trim($this->params->get('posttext')); ?></div>
</div>
