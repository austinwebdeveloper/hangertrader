<?php
/**
* @version   $Id: comingsoon.php 24174 2014-11-20 15:38:43Z arifin $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
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
class GantryFeatureComingSoon extends GantryFeature {
    var $_feature_name = 'comingsoon';

    function isEnabled(){
    	if ($this->get('enabled')) {
        	return true;
        }
    }
    
    function isInPosition($position) {
        return false;
    }
    function isOrderable(){
        return true;
    }
    
    function init() {
        global $gantry;
        $input   = JFactory::getApplication()->input;
        $user    = JFactory::getUser();
        $isAdmin = $user->get('isRoot');

        if ($input->getString('tmpl')!=='comingsoon') {
            if (!$isAdmin) {
                header("Location: ".$gantry->baseUrl."?tmpl=comingsoon");
            }
        }
    }
}
