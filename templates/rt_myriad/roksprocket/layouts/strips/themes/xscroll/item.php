<?php
/**
 * @version   $Id: item.php 24174 2014-11-20 15:38:43Z arifin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

?>
<li class="sprocket-strips-xscroll-block" data-strips-item>
	<div class="sprocket-strips-xscroll-item" data-strips-content>

		<div class="sprocket-strips-xscroll-image-container">
			<?php if ($item->getPrimaryImage()) :?>
				<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" alt="image" />
			<?php endif; ?>
			<?php if ($item->getText()) :?>
				<span class="sprocket-strips-xscroll-text">
					<?php echo $item->getText(); ?>

					<?php if ($item->getPrimaryLink()) : ?>
					<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="sprocket-strips-xscroll-readmore"><span><?php rc_e('READ_MORE'); ?></span></a>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</div>

		<?php if ($item->getTitle()) : ?>
		<h4 class="sprocket-strips-xscroll-title" data-strips-toggler>
			<?php if ($item->getPrimaryLink()) : ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
				<?php echo $item->getTitle();?>
			<?php if ($item->getPrimaryLink()) : ?></a><?php endif; ?>
		</h4>
		<?php endif; ?>
	</div>
</li>
