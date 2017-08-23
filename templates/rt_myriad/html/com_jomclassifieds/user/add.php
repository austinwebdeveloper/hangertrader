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

//ver2.6.0
$memebership = $this->memebership;
$promotion = $this->promotion;
$isFreeMemebership = $this->isFreeMemebership;
$ini = 0;
$userid = JFactory::getUser()->get('id');



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
?>
	<!-- Show Membership  -->
		<div id="jcsemail"></div>
        <?php echo JomclPremium::ListMembership(JFactory::getUser()->get('email')); ?>    
	<!-- End Membership  -->
	
<!-- Tool Bar -->
    <div id="jomclsubmitblock" class="jomclsubmitblock">
        <!-- Hidden Fields -->
        <input type="hidden" name="mode" value="new" />
        <input type="hidden" name="pageid" value="<?php echo JRequest::getInt('Itemid'); ?>" />
        <input type="hidden" name="userid" value="<?php echo JFactory::getUser()->get('id'); ?>" />
        <?php if(JFactory::getUser()->get('guest') == 0 ) :?>
        <input type="hidden" name="email" value="<?php echo JFactory::getUser()->get('email'); ?>" />
        <?php endif; ?>
        <button type="submit" class="btn btn-success" onclick="return valJomclAddForm();"><i class="icon-new icon-white"></i> <?php echo JText::_('NEXT_STEP'); ?></button>
    </div>
    <!-- Tool End -->
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div class="posttext"><?php echo trim($this->params->get('posttext')); ?></div>
</div>

<script type="text/javascript">
var $postjomClassifieds = jQuery.noConflict();
    $postjomClassifieds(document).ready(function() {
        $postjomClassifieds('#jomclTopay,#confirm, .jomcl-hidden-elements').hide();
        	
        	//onclickMembership('2','Free','0.00','90','0','90');
        	//JomclHiddenElements(0,0,0,0);
    });
</script>
