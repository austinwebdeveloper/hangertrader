<?php
/**
 * @version   $Id: styledeclaration.php 24336 2014-11-28 18:33:35Z arifin $
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

class GantryFeatureStyleDeclaration extends GantryFeature {
    var $_feature_name = 'styledeclaration';

    function isEnabled() {
		global $gantry;
        $menu_enabled = $this->get('enabled');

        if (1 == (int)$menu_enabled) return true;
        return false;
    }

	function init() {
        global $gantry, $ie_ver;
        $browser = $gantry->browser;

        if ($gantry->browser->name == 'ie' && $gantry->browser->shortversion == 8) $ie_ver = 'ie8';

        // Logo
    	$css = $this->buildLogo();

        // Less Variables
    	$lessVariables = array(
            'accent-color1'                 => $gantry->get('accent-color1',                '#0085F6'),
            'accent-color2'                 => $gantry->get('accent-color2',                '#34A1FC'),

            'pagesurround-overlay'          => $gantry->get('pagesurround-overlay',         'light'),

            'header-textcolor'              => $gantry->get('header-textcolor',             '#FFFFFF'),
            'header-background'             => $gantry->get('header-background',            '#0085F6'),
            'header-type'                   => $gantry->get('header-type',                  'normal'),

            'slideshow-textcolor'           => $gantry->get('slideshow-textcolor',          '#FFFFFF'),
            'slideshow-overlaybackground'   => $gantry->get('slideshow-overlaybackground',  '#090D0F'),
            'slideshow-overlayopacity'      => $gantry->get('slideshow-overlayopacity',     '0.35'),

            'top-textcolor'                 => $gantry->get('top-textcolor',                '#686868'),
            'top-background'                => $gantry->get('top-background',               '#FFFFFF'),

            'showcase-textcolor'            => $gantry->get('showcase-textcolor',           '#BFC3C5'),
            'showcase-background'           => $gantry->get('showcase-background',          '#504B52'),

            'fullstrip-textcolor'           => $gantry->get('fullstrip-textcolor',          '#686868'),
            'fullstrip-background'          => $gantry->get('fullstrip-background',         '#FFFFFF'),

            'firstfullwidth-textcolor'      => $gantry->get('firstfullwidth-textcolor',     '#686868'),
            'firstfullwidth-background'     => $gantry->get('firstfullwidth-background',    '#FFFFFF'),

            'breadcrumb-textcolor'          => $gantry->get('breadcrumb-textcolor',         '#686868'),
            'breadcrumb-background'         => $gantry->get('breadcrumb-background',        '#FFFFFF'),

            'feature-textcolor'             => $gantry->get('feature-textcolor',            '#9D9A9E'),
            'feature-background'            => $gantry->get('feature-background',           '#3A353C'),

            'utility-textcolor'             => $gantry->get('utility-textcolor',            '#9D9A9E'),
            'utility-background'            => $gantry->get('utility-background',           '#3A353C'),

            'secondfullwidth-textcolor'     => $gantry->get('secondfullwidth-textcolor',    '#FFFFFF'),
            'secondfullwidth-background'    => $gantry->get('secondfullwidth-background',   '#3A353C'),
            'secondfullwidth-type'          => $gantry->get('secondfullwidth-type',         'preset1'),

            'maintop-textcolor'             => $gantry->get('maintop-textcolor',            '#686868'),
            'maintop-background'            => $gantry->get('maintop-background',           '#FFFFFF'),

            'expandedtop-textcolor'         => $gantry->get('expandedtop-textcolor',        '#686868'),
            'expandedtop-background'        => $gantry->get('expandedtop-background',       '#FFFFFF'),

            'mainbody-overlay'              => $gantry->get('mainbody-overlay',             'light'),

            'expandedbottom-textcolor'      => $gantry->get('expandedbottom-textcolor',     '#686868'),
            'expandedbottom-background'     => $gantry->get('expandedbottom-background',    '#FFFFFF'),

            'mainbottom-textcolor'          => $gantry->get('mainbottom-textcolor',         '#686868'),
            'mainbottom-background'         => $gantry->get('mainbottom-background',        '#FFFFFF'),

            'thirdfullwidth-textcolor'      => $gantry->get('thirdfullwidth-textcolor',     '#686868'),
            'thirdfullwidth-background'     => $gantry->get('thirdfullwidth-background',    '#FFFFFF'),

            'extension-textcolor'           => $gantry->get('extension-textcolor',          '#090D0F'),
            'extension-background'          => $gantry->get('extension-background',         '#FFFFFF'),
            'extension-type'                => $gantry->get('extension-type',               'preset1'),

            'bottom-textcolor'              => $gantry->get('bottom-textcolor',             '#686868'),
            'bottom-background'             => $gantry->get('bottom-background',            '#090D0F'),

            'footer-textcolor'              => $gantry->get('footer-textcolor',             '#686868'),
            'footer-background'             => $gantry->get('footer-background',            '#090D0F'),

            'copyright-textcolor'           => $gantry->get('copyright-textcolor',          '#686868'),
            'copyright-background'          => $gantry->get('copyright-background',         '#090D0F')
    	);

        // Section Background Images
        $positions  = array('secondfullwidth-customsecondfullwidth-image', 'extension-customextension-image');
        $source     = "";

        foreach ($positions as $position) {
            $data = $gantry->get($position, false) ? json_decode(str_replace("'", '"', $gantry->get($position))) : false;
            if ($data) $source = $data->path;
            if (!preg_match('/^\//', $source)) $source = JURI::root(true).'/'.$source;
            $lessVariables[$position] = $data ? 'url(' . $source . ')' : 'none';
        }

        $gantry->addInlineStyle($css);

       	$gantry->addLess('global.less', 'master.css', 7, $lessVariables);

	    $this->_disableRokBoxForiPhone();

        if ($gantry->get('layout-mode')=="responsive") {
            $gantry->addLess('mediaqueries.less');
            $gantry->addLess('grid-flexbox-responsive.less');
            $gantry->addLess('menu-dropdown-direction.less');
        }

        // Scrolling Header
        if ($gantry->get('header-type')=="scroll") {
            $gantry->addScript('scrolling-header-fullpage.js');
        }

        // unassigned menu items pages.
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        if (!$menu->getActive()) {
            $gantry->addInlineStyle(".header-type-scroll #rt-header {opacity: 1; visibility: visible; position: relative;}");
        }

        if ($gantry->get('layout-mode')=="960fixed")   $gantry->addLess('960fixed.less');
        if ($gantry->get('layout-mode')=="1200fixed")  $gantry->addLess('1200fixed.less');

        // RTL
        if ($gantry->get('rtl-enabled')) $gantry->addLess('rtl.less', 'rtl.css', 8, $lessVariables);

        // Demo Styling
        if ($gantry->get('demo')) $gantry->addLess('demo.less', 'demo.css', 9, $lessVariables);

        // Third Party (k2)
        if ($gantry->get('k2')) $gantry->addLess('thirdparty-k2.less', 'thirdparty-k2.css', 10, $lessVariables);

        // Chart Script
        if ($gantry->get('chart-enabled')) $gantry->addScript('chart.js');

        // Animate
        if ($gantry->get('animate')){
            $gantry->addLess('animate.less');
            // WOW JS
            if ($ie_ver != 'ie8') {
                $gantry->addScript('wow.js');
                $gantry->addScript('wow-init.js');
            }
        }
	}

    function buildLogo(){
		global $gantry;

        if ($gantry->get('logo-type')!="custom") return "";

        $source = $width = $height = "";

        $logo = str_replace("&quot;", '"', str_replace("'", '"', $gantry->get('logo-custom-image')));
        $data = json_decode($logo);

        if (!$data){
            if (strlen($logo)) $source = $logo;
            else return "";
        } else {
            $source = $data->path;
        }

        if (substr($gantry->baseUrl, 0, strlen($gantry->baseUrl)) == substr($source, 0, strlen($gantry->baseUrl))){
            $file = JPATH_ROOT . '/' . substr($source, strlen($gantry->baseUrl));
        } else {
            $file = JPATH_ROOT . '/' . $source;
        }

        if (isset($data->width) && isset($data->height)){
            $width = $data->width;
            $height = $data->height;
        } else {
            $size = @getimagesize($file);
            $width = $size[0];
            $height = $size[1];
        }

        if (!preg_match('/^\//', $source))
        {
            $source = JURI::root(true).'/'.$source;
        }

        $source = str_replace(' ', '%20', $source);

        $output = "";
        $output .= "#rt-logo {background: url(".$source.") 50% 0 no-repeat !important;}"."\n";
        $output .= "#rt-logo {width: ".$width."px;height: ".$height."px;}"."\n";

        $file = preg_replace('/\//i', DIRECTORY_SEPARATOR, $file);

        return (file_exists($file)) ?$output : '';
    }

	function _disableRokBoxForiPhone() {
		global $gantry;

		if ($gantry->browser->platform == 'iphone' || $gantry->browser->platform == 'android') {
			$gantry->addInlineScript("window.addEvent('domready', function() {\$\$('a[rel^=rokbox]').removeEvents('click');});");
		}
	}
}