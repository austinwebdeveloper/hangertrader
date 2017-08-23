<?php

/*
 * @version		$Id: contact.php 3.0 2015-02-16 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
$item = $this->item;
 if($item->video !='') {
		$video = JomclUtils::getYouTubeVideoId($item->video);
 ?>
    <div id="jomclvideo" class="jomcl-popup jomcl-popup-jomclVideo"> 
        <div class="jomcl-modal-header jomclassifiedsvideo"> 
            <div class="jomcl-form-horizontal">
                 <div class="jomcl-close"></div> 
                  <iframe src="https://www.youtube.com/embed/<?php echo $video; ?>?wmode=opaque&showinfo=0&rel=0&modestbranding=1" width="100%" height="380" frame allowfullscreen></iframe>  
                  </div>    
             </div>
    </div> 
<?php } ?>  


