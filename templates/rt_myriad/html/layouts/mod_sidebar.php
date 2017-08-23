<?php
/**
 * @version   $Id: mod_sidebar.php 24409 2014-12-01 08:52:13Z arifin $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.gantrylayout');

/**
 *
 * @package    gantry
 * @subpackage html.layouts
 */
class GantryLayoutMod_Sidebar extends GantryLayout {
    var $render_params = array(
        'contents'       =>  null,
        'position'      =>  null,
        'gridCount'     =>  null,
        'pushPull'      =>  ''
    );
    function render($params = array()){
        /** @var $gantry Gantry */
		global $gantry;

        $rparams = $this-> _getParams($params);
        ob_start();
    // XHTML LAYOUT
?>
        <div class="rt-sidebar-wrapper rt-grid-<?php echo $rparams->gridCount;?> <?php echo $rparams->pushPull; ?>">
            <div class="rt-sidebar-block">
                <div id="rt-<?php echo $rparams->position; ?>">
                    <?php echo $rparams->contents; ?>
                </div>
            </div>
        </div>

<?php
        return ob_get_clean();
    }
}