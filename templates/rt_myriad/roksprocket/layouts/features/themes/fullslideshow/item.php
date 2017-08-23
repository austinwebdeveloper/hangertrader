<?php
/**
 * @version   $Id: item.php 24174 2014-11-20 15:38:43Z arifin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $item RokSprocket_Item
 */
?>

<li class="sprocket-features-index-<?php echo $index;?>">
	<?php
		;
		if (($image = $item->getPrimaryImage())):
	?>
	<div class="sprocket-features-img-container sprocket-fullslideshow-image" style="background-image: url(<?php echo $item->getPrimaryImage()->getSource(); ?>);" data-fullslideshow-image></div>
	<?php endif; ?>
	<div class="sprocket-features-content" data-fullslideshow-content>
		<div class="sprocket-features-padding">
			<?php if ($parameters->get('features_show_title') && $item->getTitle()) : ?>
				<h2 class="sprocket-features-title">
					<?php echo $item->getTitle(); ?>
				</h2>
			<?php endif; ?>
			<?php if ($parameters->get('features_show_article_text') && ($item->getText() || $item->getPrimaryLink())) : ?>
				<div class="sprocket-features-desc">
					<?php echo $item->getText(); ?>
					<?php if ($item->getPrimaryLink()) : ?>
					<div><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="readon"><?php rc_e('READ_MORE'); ?></a></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</li>
