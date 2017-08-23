<?php
/**
 * @version   $Id: copyright.php 24174 2014-11-20 15:38:43Z arifin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2013 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');
/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureCopyright extends GantryFeature
{
	var $_feature_name = 'copyright';

	function render($position)
	{
		ob_start();
		?>
	<div class="rt-copyright-content rt-block">
		<?php echo $this->get('text'); ?>
	</div>
	<?php
		return ob_get_clean();
	}
}
