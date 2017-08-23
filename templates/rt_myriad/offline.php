<?php
/**
* @version   $Id: offline.php 24322 2014-11-28 16:38:32Z arifin $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load and Inititialize Gantry Class
global $gantry;
require_once(dirname(__FILE__) . '/lib/gantry/gantry.php');
$gantry->init();

// Load Mootools
JHTML::_('behavior.framework', true);

$doc = JFactory::getDocument();
$app = JFactory::getApplication();

// Less Variables
$lessVariables = array(
    'accent-color1'                 => $gantry->get('accent-color1',                '#0085F6'),
    'accent-color2'                 => $gantry->get('accent-color2',                '#34A1FC'),

    'header-textcolor'              => $gantry->get('header-textcolor',             '#FFFFFF'),
    'header-background'             => $gantry->get('header-background',            '#0085F6'),

    'slideshow-textcolor'           => $gantry->get('slideshow-textcolor',          '#FFFFFF'),
    'slideshow-overlaybackground'   => $gantry->get('slideshow-overlaybackground',  '#090D0F'),
    'slideshow-overlayopacity'      => $gantry->get('slideshow-overlayopacity',     '0.35'),

    'showcase-textcolor'            => $gantry->get('showcase-textcolor',           '#686868'),
    'showcase-background'           => $gantry->get('showcase-background',          '#FFFFFF'),

    'firstfullwidth-textcolor'      => $gantry->get('firstfullwidth-textcolor',     '#686868'),
    'firstfullwidth-background'     => $gantry->get('firstfullwidth-background',    '#FFFFFF'),

    'feature-textcolor'             => $gantry->get('feature-textcolor',            '#9D9A9E'),
    'feature-background'            => $gantry->get('feature-background',           '#3A353C'),

    'utility-textcolor'             => $gantry->get('utility-textcolor',            '#9D9A9E'),
    'utility-background'            => $gantry->get('utility-background',           '#3A353C'),

    'secondfullwidth-textcolor'     => $gantry->get('secondfullwidth-textcolor',    '#FFFFFF'),
    'secondfullwidth-background'    => $gantry->get('secondfullwidth-background',   '#3A353C'),
    'secondfullwidth-type'          => $gantry->get('secondfullwidth-type',         'preset1'),

    'maintop-textcolor'             => $gantry->get('maintop-textcolor',            '#686868'),
    'maintop-background'            => $gantry->get('maintop-background',           '#FFFFFF'),

    'mainbody-overlay'              => $gantry->get('mainbody-overlay',             'light'),

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

$gantry->addStyle('grid-responsive.css', 5);
$gantry->addLess('bootstrap.less', 'bootstrap.css', 6);
$gantry->addLess('offline.less', 'offline.css', 4, $lessVariables);

// Scripts
if ($gantry->browser->name == 'ie'){
	if ($gantry->browser->shortversion == 8){
		$gantry->addScript('html5shim.js');
		$gantry->addScript('placeholder-ie.js');
	}
	if ($gantry->browser->shortversion == 9){
		$gantry->addInlineScript("if (typeof RokMediaQueries !== 'undefined') window.addEvent('domready', function(){ RokMediaQueries._fireEvent(RokMediaQueries.getQuery()); });");
		$gantry->addScript('placeholder-ie.js');
	}
}
if ($gantry->get('layout-mode', 'responsive') == 'responsive') $gantry->addScript('rokmediaqueries.js');

ob_start();
?>
<body id="rt-offline" <?php echo $gantry->displayBodyTag(); ?>>
	<div id="rt-page-surround">
		<section id="rt-showcase-surround">
			<div id="rt-showcase">
				<div class="rt-container">
					<div class="rt-flex-container">
						<div class="rt-offline-body">
							<div class="rt-logo-block rt-offline-logo">
							    <a id="rt-logo" href="<?php echo $gantry->baseUrl; ?>"></a>
							</div>

							<?php
								$msgs = $app->getMessageQueue();
							?>
							<?php if (sizeof($msgs) > 0) : ?>
								<div class="rt-container">
									<jdoc:include type="message" />
									<div class="clear"></div>
								</div>
							<?php endif; ?>

							<div class="rt-offline-title rt-big-title rt-center">
								<div class="module-title">
									<h2 class="title"><?php echo JText::_("RT_OFFLINE_TITLE"); ?></h2>
								</div>
							</div>

							<?php if ($app->getCfg('display_offline_message', 1) == 1 && str_replace(' ', '', $app->getCfg('offline_message')) != ''): ?>
								<p class="rt-offline-message">
									<?php echo $app->getCfg('offline_message'); ?>
								</p>
									<?php elseif ($app->getCfg('display_offline_message', 1) == 2 && str_replace(' ', '', JText::_('JOFFLINE_MESSAGE')) != ''): ?>
								<p class="rt-offline-message">
									<?php echo JText::_('JOFFLINE_MESSAGE'); ?>
								</p>
							<?php  endif; ?>

							<?php if ($app->getCfg('offline_image')) : ?>
							<img src="<?php echo $app->getCfg('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->getCfg('sitename')); ?>" />
							<?php endif; ?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</section>

		<section id="rt-neck-surround">
			<div id="rt-feature">
				<div class="rt-container">
					<div class="rt-flex-container">
						<?php if ($gantry->get('email-subscription-enabled')) : ?>
							<section id="rt-subscription-form">
								<p class="rt-subscription-title">
									<?php echo JText::_("RT_OFFLINE_SUBSCRIPTION_TITLE"); ?>
								</p>
								<form class="rt-offline-form" action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open('http://feedburner.google.com/fb/a/mailverify?uri=<?php echo $gantry->get('feedburner-uri'); ?>', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true">
									<input type="text" placeholder="<?php echo JText::_('RT_EMAIL') ?>" class="inputbox" name="email">
									<input type="hidden" value="<?php echo $gantry->get('feedburner-uri'); ?>" name="uri"/>
									<input type="hidden" name="loc" value="en_US"/>
									<input type="submit" name="Submit" class="button" value="<?php echo JText::_('RT_SUBSCRIBE') ?>" />
								</form>
								<div class="clear"></div>
							</section>
						<?php endif; ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</section>

		<section id="rt-extension-surround">
			<div id="rt-extension">
				<div class="rt-container">
					<div class="rt-flex-container">
						<div id="rt-authorized-form">
							<h2 class="rt-authorized-form-title"><?php echo JText::_("AUTHORIZED_LOGIN"); ?></h2>

							<p class="rt-authorized-login-message">
								<?php echo JText::_("RT_OFFLINE_LOGIN_MESSAGE"); ?>
							</p>

							<?php
						        $user    = JFactory::getUser();
						        $isAdmin = $user->get('isRoot');
							?>
							<?php if (!$isAdmin): ?>
								<form class="rt-authorized-login-form" action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="rt-form-login">
									<input name="username" id="username" class="inputbox" type="text" placeholder="<?php echo JText::_('JGLOBAL_USERNAME') ?>" />
									<input type="password" name="password" class="inputbox" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
									<input type="hidden" name="remember" class="inputbox" value="yes" id="remember" />
									<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
									<input type="hidden" name="option" value="com_users" />
									<input type="hidden" name="task" value="user.login" />
									<input type="hidden" name="return" value="<?php echo base64_encode(JURI::base()) ?>" />
									<?php echo JHtml::_('form.token'); ?>
								</form>
							<?php endif; ?>
							<?php if ($isAdmin): ?>
								<form class="rt-authorized-login-form" action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="rt-form-login">
									<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGOUT'); ?>" />
									<input type="hidden" name="option" value="com_users" />
									<input type="hidden" name="task" value="user.logout" />
									<input type="hidden" name="return" value="<?php echo $return; ?>" />
									<?php echo JHtml::_('form.token'); ?>
								</form>
							<?php endif; ?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</section>

		<footer id="rt-footer-surround">
				<div id="rt-copyright">
					<div class="rt-container">
						<div class="rt-flex-container">
							<?php echo $gantry->displayModules('copyright','standard','standard'); ?>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</footer>
	</div>
</body>
</html>
<?php

$body = ob_get_clean();
$gantry->finalize();

require_once(JPATH_LIBRARIES.'/joomla/document/html/renderer/head.php');
$header_renderer = new JDocumentRendererHead($doc);
$header_contents = $header_renderer->render(null);
ob_start();
?>
<!doctype html>
<html xml:lang="<?php echo $gantry->language; ?>" lang="<?php echo $gantry->language;?>" >
<head>
	<?php echo $header_contents; ?>
	<?php if ($gantry->get('layout-mode') == '960fixed') : ?>
	<meta name="viewport" content="width=960px">
	<?php elseif ($gantry->get('layout-mode') == '1200fixed') : ?>
	<meta name="viewport" content="width=1200px">
	<?php else : ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php endif; ?>
</head>
<?php
$header = ob_get_clean();
echo $header.$body;