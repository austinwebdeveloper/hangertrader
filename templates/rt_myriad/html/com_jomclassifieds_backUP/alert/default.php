<?php

/*
 * @version		$Id: default_list.php 2.7.0 2014-10-29 $
 * @package		Joomla
 * @copyright   Copyright (C) 2013-2014 Jom Classifieds
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

$items = $this->items;
$itemid = JRequest::getInt('Itemid');

?>

<div id="jomclassifieds" class="jomclassifieds"> 
<div class="page-header jomcl-header-text">
	<?php if ($this->params->get('show_page_heading') == 1) { ?>
   		<h2><?php echo $this->escape($this->params->get('page_heading')); ?></h2>
	<?php }	?>
</div>
<span class="clear">&nbsp;</span>

  <table class="table table-striped">
    <tbody>
      <tr>
        <th>ID</th>
        <th>Category</th>
		<th>Airport ID</th>
        <!--<th>Hangar Type</th>
        <th>Price</th>-->
        <th>Date</th>
        <th>Actions</th>      
      </tr>
      <?php	  	
		foreach($items as $item) {
		$edit = JRoute::_('index.php?option=com_jomclassifieds&view=alert&task=edit&id='.$item->id.'&Itemid='.$itemid.'&'.JSession::getFormToken().'=1');
		$delete = JRoute::_('index.php?option=com_jomclassifieds&view=alert&task=delete&id='.$item->id.'&Itemid='.$itemid.'&'.JSession::getFormToken().'=1');
	  ?>
      <tr>
      <td><?php echo $item->id;?></td>
      <td><?php echo $item->category;?></td>
	  <td><?php echo $item->airportid;?></td>
      <!--<td><?php echo $item->tagname;?></td>
      <td><div class="text-info"><?php echo 'Min : '.$item->pricemin;?></div>
     		<div class="text-success"><?php echo 'Max : '.$item->pricemax;?></div></td>-->
      <td><div class="text-warning"><?php echo 'Start : '.$item->alert_startdate;?></div>
    	 <div class="text-success"><?php echo 'End : '.$item->alert_enddate;?></div></td>
     <td>
     	<div class="btn-group" style="display:block;">
        <span class="pull-left"><a class="btn btn-success" href="<?php echo $edit; ?>">Edit</a></span>&nbsp;
       <span class="pull-right"><a class="btn btn-danger" href="<?php echo $delete; ?>" onclick="return confirm('<?php echo JText::_('ARE_YOU_SURE_WANT_TO_DELETE_THIS_ITEM'); ?>');">Delete</a></span>
        </div>
     </td>      
      </tr>
     
      <?php } ?>
    </tbody>
  </table>
  <div class="jomcl-pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
</div>