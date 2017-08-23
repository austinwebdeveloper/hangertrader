<?php
/**
 * @version   $Id: item.php 24174 2014-11-20 15:38:43Z arifin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

?>
<div class="sprocket-strips-apollo-block" data-strips-item>
	<!--Plain-->
	<?php if (!$item->getPrimaryImage()) :?>
			<div class="plain">
	<?php endif; ?>
	<!--Plain-->
	<div class="sprocket-strips-apollo-item" data-strips-content>
		<div class="sprocket-strips-effect-apollo">
			<?php if ($item->getPrimaryImage()) :?>
				<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" class="sprocket-strips-apollo-image" alt="image" />
			<?php endif; ?>

			<div class="sprocket-strips-apollo-content">
				<?php if ($item->getTitle()) : ?>
				<h2 class="sprocket-strips-apollo-title" data-strips-toggler>
					<?php if ($item->getPrimaryLink()) : ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
						<?php echo $item->getTitle();?>
					<?php if ($item->getPrimaryLink()) : ?></a><?php endif; ?>
				</h2>
				<?php endif; ?>
				<div class="sprocket-strips-apollo-extended">
					<div class="sprocket-strips-apollo-extended-info">
						<?php if ($item->getText()) :?>
							<span class="sprocket-strips-apollo-text">
								<?php echo $item->getText(); ?>
							</span>
						<?php endif; ?>
						<?php if ($item->getPrimaryLink()) : ?>
						<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="sprocket-strips-apollo-readon"><?php rc_e('READ_MORE'); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--Plain-->
	<?php if (!$item->getPrimaryImage()) :?>
			</div>
	<?php endif; ?>
	<!--Plain-->
</div>