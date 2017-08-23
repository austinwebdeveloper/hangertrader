<?php
/**
* @version   $Id: social.php 24174 2014-11-20 15:38:43Z arifin $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
*/
defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

class GantryFeatureSocial extends GantryFeature {
	var $_feature_name = 'social';

	function init(){
		global $gantry;
	}

	function render($position="") {
		ob_start();
		global $gantry;
		?>
		<div class="rt-social-buttons rt-block">
			<?php if (($gantry->get('social-button-1-icon') != "") and ($gantry->get('social-button-1-link') != "")) : ?>
			<a class="social-button rt-social-button-1" href="<?php echo $gantry->get('social-button-1-link'); ?>" target="<?php echo $gantry->get('social-target'); ?>">
				<span class="<?php echo $gantry->get('social-button-1-icon'); ?>"></span>
				<?php if ($gantry->get('social-button-1-text') != "") :?>
					<span class="social-button-text"><?php echo $gantry->get('social-button-1-text'); ?></span>
				<?php endif; ?>
			</a>
			<?php endif; ?>

			<?php if (($gantry->get('social-button-2-icon') != "") and ($gantry->get('social-button-2-link') != "")) : ?>
			<a class="social-button rt-social-button-2" href="<?php echo $gantry->get('social-button-2-link'); ?>" target="<?php echo $gantry->get('social-target'); ?>">
				<span class="<?php echo $gantry->get('social-button-2-icon'); ?>"></span>
				<?php if ($gantry->get('social-button-2-text') != "") :?>
					<span class="social-button-text"><?php echo $gantry->get('social-button-2-text'); ?></span>
				<?php endif; ?>
			</a>
			<?php endif; ?>

			<?php if (($gantry->get('social-button-3-icon') != "") and ($gantry->get('social-button-3-link') != "")) : ?>
			<a class="social-button rt-social-button-3" href="<?php echo $gantry->get('social-button-3-link'); ?>" target="<?php echo $gantry->get('social-target'); ?>">
				<span class="<?php echo $gantry->get('social-button-3-icon'); ?>"></span>
				<?php if ($gantry->get('social-button-3-text') != "") :?>
					<span class="social-button-text"><?php echo $gantry->get('social-button-3-text'); ?></span>
				<?php endif; ?>
			</a>
			<?php endif; ?>

			<?php if (($gantry->get('social-button-4-icon') != "") and ($gantry->get('social-button-4-link') != "")) : ?>
			<a class="social-button rt-social-button-4" href="<?php echo $gantry->get('social-button-4-link'); ?>" target="<?php echo $gantry->get('social-target'); ?>">
				<span class="<?php echo $gantry->get('social-button-4-icon'); ?>"></span>
				<?php if ($gantry->get('social-button-4-text') != "") :?>
					<span class="social-button-text"><?php echo $gantry->get('social-button-4-text'); ?></span>
				<?php endif; ?>
			</a>
			<?php endif; ?>

			<?php if (($gantry->get('social-button-5-icon') != "") and ($gantry->get('social-button-5-link') != "")) : ?>
			<a class="social-button rt-social-button-5" href="<?php echo $gantry->get('social-button-5-link'); ?>" target="<?php echo $gantry->get('social-target'); ?>">
				<span class="<?php echo $gantry->get('social-button-5-icon'); ?>"></span>
				<?php if ($gantry->get('social-button-5-text') != "") :?>
					<span class="social-button-text"><?php echo $gantry->get('social-button-5-text'); ?></span>
				<?php endif; ?>
			</a>
			<?php endif; ?>

			<?php if (($gantry->get('social-button-6-icon') != "") and ($gantry->get('social-button-6-link') != "")) : ?>
			<a class="social-button rt-social-button-6" href="<?php echo $gantry->get('social-button-6-link'); ?>" target="<?php echo $gantry->get('social-target'); ?>">
				<span class="<?php echo $gantry->get('social-button-6-icon'); ?>"></span>
				<?php if ($gantry->get('social-button-6-text') != "") :?>
					<span class="social-button-text"><?php echo $gantry->get('social-button-6-text'); ?></span>
				<?php endif; ?>
			</a>
			<?php endif; ?>

			<div class="clear"></div>
		</div>
		<?php
		return ob_get_clean();
	}
}