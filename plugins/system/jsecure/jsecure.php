<?php 
/**
 * jSecure Authentication plugin for Joomla!
 * jSecure Authentication extention prevents access to administration (back end)
 * login page without appropriate access key.
 *
 * @author      $Author: Ajay Lulia $
 * @copyright   Joomla Service Provider - 2014
 * @package     jSecure3.0.3
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     $Id: jsecure.php  $
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
error_reporting(E_ALL & ~E_STRICT);
jimport('joomla.plugin.plugin');

$lang = JFactory::getLanguage();

if($lang->getName()=="English (United Kingdom)")
{
	JPlugin::loadLanguage('plg_system_jsecure');
}

require_once('jsecure/jsecure.class.php');

$basepath     = JPATH_ADMINISTRATOR .'/components/com_jsecure';
$configFile	  = $basepath.'/params.php';
				
require_once($configFile);		

class plgSystemJSecure extends JPlugin {
	 private static $_configuration;
	
	function plgSystemCanonicalization(& $subject, $config) {
		
		parent :: __construct($subject, $config);
	}
function onUserLogout() {
	
	 self::$_configuration = 1;
	 echo self::$_configuration;
	 $session    = JFactory::getSession();
	 $this->params->logout = true;
	}
   function onUserLogin($user, $options = array())
   {
	  $JSecureConfig = new JSecureConfig();
	 	$publish  = $JSecureConfig->publish;
		if(!$publish)
		{			
			return true;
		}
	  if($JSecureConfig->login_control)
	   {
        $res = $this->check_user_login($user['username']);
		if(count($res)>0)
			{
			  $app= JFactory::getApplication();
			  $message = JText::_("Username: ".$user['username']." is already logged in from another site.You can't login again.");
              $link = JURI::root()."administrator/index.php?option=com_login";
			  $app->redirect($link,$message,'error');
		   }
	   }
   }
	function onAfterDispatch() {
		
	    $session    = JFactory::getSession();
		$logout = self::$_configuration;
		$app           = JFactory::getApplication();
		
		if ($app->isAdmin()) 
		{
			$extension = JRequest::getVar('extension', '');
			$com = JRequest::getVar('option', '');
			
            //$protected = jsecure::checkComponentprotect($com,$extension);
			$pro = new jsecure();
			$protected = $pro->checkComponentprotect($com,$extension);

		$config        = new JConfig();
		$JSecureConfig = new JSecureConfig();
		$app           = JFactory::getApplication();
		$path          = '';
		$path         .= $JSecureConfig->options == 1 ? JURI::root().$JSecureConfig->custom_path : JURI::root();
		$jsecure 	   =  new jsecure();
		$publish       = $JSecureConfig->publish;
		
		if(!$publish){			
			return true;
		}


		$session    = JFactory::getSession();
		$checkedKey = $session->get('jSecureAuthentication');

		if(!empty($checkedKey)){			
			return true;
		}
		
		$submit       = JRequest::getVar('submit', '');
		$passkey      = $JSecureConfig->key;

		if($submit == 'submit'){			
			$resultFormAction = jsecure::formAction($JSecureConfig);
			
			if(!empty($resultFormAction)){
				$session->set('jSecureAuthentication', 1);
				$link = JURI::root()."administrator/index.php?option=com_login";
				$app->redirect($link);
			} else {
				$app->redirect($path);
			}
		}
		
		$resultBloackIPs = jsecure::checkIps($JSecureConfig);
		
		if(!$resultBloackIPs){
			$app->redirect($path);
		}
		
		$task        = $JSecureConfig->passkeytype;

		switch($task){
			case 'form':
				jsecure::displayForm();
			exit;
			break;

			case 'url':
			default:
				$session    = JFactory::getSession();
				$resultUrlKey = jsecure::checkUrlKey($JSecureConfig);
				if(!empty($resultUrlKey)){
					 $session->set('jSecureAuthentication', 1);
					 return true;
				} else {
					$app->redirect($path);
				}
			break;
		}

	  }
	  else
	  {
		/* starts meta tag control for front side */
		$JSecureConfig = new JSecureConfig();
		$document = JFactory::getDocument();
		$publish       = $JSecureConfig->publish;
		if(!$publish)
		{			
			return true;
		}
   
        if($JSecureConfig->metatagcontrol)
		{
           // Set global info in callback function
           $global_info['sitename'] = $app->getCfg('sitename');
           $document_info['title'] = $document->getTitle();
           $document_info['description'] = $document->getDescription();
           $document_info['keywords'] = $document->getMetaData('keywords');
           $document_info['author'] = $document->getMetaData('author');
		   $document_info['rights'] = $document->getMetaData('rights');
           $document_info['generator'] = $document->getGenerator();
            
		   $customgenerator = $document->getMetaData('generator');
			if($JSecureConfig->metatag_generator)
			{
               $document->setGenerator(str_replace('"', '&quot;', $JSecureConfig->metatag_generator)); 
			}
			if($JSecureConfig->metatag_keywords)
			{
               $document->setMetaData('keywords', str_replace('"', '&quot;', $JSecureConfig->metatag_keywords)); 
			}
			if($JSecureConfig->metatag_description)
			{
				 $document->setDescription(str_replace('"', '&quot;',$JSecureConfig->metatag_description));
           	}
			if($JSecureConfig->metatag_rights)
			{
			   $document->setMetaData('rights', str_replace('"', '&quot;', $JSecureConfig->metatag_rights));
            }
	
		}
		else
		{
           return;
		}
	  }
	}
  function check_user_login($username)
  {
    $db = JFactory::getDBO(); 
	$query = "SELECT * from #__session WHERE username='".$username."' AND userid!=0 ";
	$db->setQuery($query);
	$rows = $db->loadObjectList();
	return $rows;
  }

}
?>