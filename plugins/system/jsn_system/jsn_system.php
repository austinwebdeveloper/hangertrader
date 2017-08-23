<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;

class plgSystemJsn_System extends JPlugin
{
	public function onAfterRoute()
	{
		$app=JFactory::getApplication();
		
		// Load Config
		$config = JComponentHelper::getParams('com_jsn');
		
		// Load Language for modules
		if($app->isAdmin() && (JFactory::getApplication()->input->get('option')=='com_modules' || JFactory::getApplication()->input->get('option')=='com_advancedmodules')) // Added advanced module configuration
		{
			$lang = JFactory::getLanguage();
			$lang->load('com_jsn');
		}
		
		if($config->get('logintype', 'USERNAME')=='MAIL')
		{
			// Reset Password with Email config
			if(JFactory::getApplication()->input->get('task')=='reset.confirm')
			{
				$input = JFactory::getApplication()->input;
				$form=$input->post->get('jform', array(), 'array');
				if(isset($form['username'])){
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->select('a.username')->from('#__users as a')->where('email = '.$db->quote($form['username']));
					$db->setQuery( $query );
					if($username=$db->loadResult()){
						$form['username']=$username;
						$input->post->set('jform', $form);
						JFactory::getApplication()->input->set('jform', $form);
					}
				}
			}
			// Register with Email config (Bug username in email)
			if(JFactory::getApplication()->input->get('task')=='registration.register' || ($app->isAdmin() && JFactory::getApplication()->input->get('option')=='com_users' && JFactory::getApplication()->input->get('layout')=='edit' && JFactory::getApplication()->input->get('id',-1)==0 ))
			{
				$input = JFactory::getApplication()->input;
				$form=$input->post->get('jform', array(), 'array');
				if($app->isSite()) $form['username']=$form['email1'];
				else  $form['username']=$form['email'];
				$input->post->set('jform', $form);
				JFactory::getApplication()->input->set('jform', $form);
			}
			if(JFactory::getApplication()->input->get('task')=='registration.activate')
			{
				$db = JFactory::getDbo();
				$token=JFactory::getApplication()->input->get('token',false);
				if($token){
					// Get the user id based on the token.
					$query = $db->getQuery(true);
					$query->select($db->quoteName('id'))
						->from($db->quoteName('#__users'))
						->where($db->quoteName('activation') . ' = ' . $db->quote($token))
						->where($db->quoteName('block') . ' = ' . 1)
						->where($db->quoteName('lastvisitDate') . ' = ' . $db->quote($db->getNullDate()));
					$db->setQuery($query);
					try
					{
						$userId = (int) $db->loadResult();
						$user = JFactory::getUser($userId);
						$com_users_config = JComponentHelper::getParams('com_users');
						if($com_users_config->get('useractivation',1)!=2 || ($com_users_config->get('useractivation',1)==2 && $user->getParam('activate',0)))
						{
							$user->tmp_username=$user->username;
							$user->username=$user->email;
						}
					}
					catch (RuntimeException $e)
					{
						$this->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);
					}
				}
			}
		}
		
		if($app->isSite())
		{
			// ---- Edit Profiles from Frontend
			$user=JFactory::getUser();
			
			$session = JFactory::getSession();
			$original_id=$session->get('jsn_original_id',0);
			if(empty($original_id)){
				$session->set('jsn_original_id', (int) $user->id );
				$original_id=$user->id;
			}
			if($user->authorise('core.edit', 'com_users') && JFactory::getApplication()->input->get('option','')=='com_users' && JFactory::getApplication()->input->get('layout','')=='edit' && JFactory::getApplication()->input->get('user_id',0) > 0)
			{
				// Check Super User
				$editUser=JFactory::getUser(JFactory::getApplication()->input->get('user_id',0));
				if(in_array(8,$editUser->groups) && !in_array(8,$user->groups))
				{
					$lang = JFactory::getLanguage();
					$lang->load('com_jsn');
					$app->enqueueMessage(JText::_('COM_JSN_NOCHANGEADMIN'),'error');
					$app->redirect(JRoute::_('index.php?option=com_jsn&view=profile&id='.JFactory::getApplication()->input->get('user_id',0),false));
				}
				// Change edit id
				$app->setUserState('com_users.edit.profile.id', (int) JFactory::getApplication()->input->get('user_id') );
			}
			else if($user->authorise('core.edit', 'com_users') && JFactory::getApplication()->input->get('option','')=='com_users' && JFactory::getApplication()->input->get('task','')=='profile.save')
			{
				// Check Super User
				$editUser=JFactory::getUser(JFactory::getApplication()->input->get('user_id',0));
				if(in_array(8,$editUser->groups) && !in_array(8,$user->groups))
				{
					$lang = JFactory::getLanguage();
					$lang->load('com_jsn');
					$app->enqueueMessage(JText::_('COM_JSN_NOCHANGEADMIN'),'error');
					$app->redirect(JRoute::_('index.php?option=com_jsn&view=profile',false));
				}
				// Change user e editid
				$input = JFactory::getApplication()->input;
				$form=$input->post->get('jform', array(), 'array');
				if(isset($form['id']) && $form['id']!=$user->id)
				{
					$user->id=$form['id'];
					JSession::getFormToken();
					$input->post->set(JSession::getFormToken(),1);
					$app->setUserState('com_users.edit.profile.id', (int) $form['id'] );
				}
			}
			else if($user->id!=$original_id)
			{
				// Restore User
				$app->setUserState('com_users.edit.profile.id', (int) $original_id );
				$session->set('user', new JUser($original_id));
				// Redirect
				$menu = $app->getMenu();
				$profileMenu=$menu->getItems('link','index.php?option=com_jsn&view=profile',true);
				if(isset($profileMenu->id))
				{
					$app->redirect(JRoute::_('index.php?option=com_jsn&id='.$user->id.'&view=profile&Itemid='.$profileMenu->id,false));
				}
				else{
					$app->redirect(JRoute::_('index.php?option=com_jsn&id='.$user->id.'&view=profile&Itemid='.$menu->getDefault()->id,false));
				}
			}
			else 
			{
				$app->setUserState('com_users.edit.profile.id', (int) $original_id );
			}
			
			// ---- Load JSN Plugins
			JPluginHelper::importPlugin('jsn');

			// ---- Redirect
			if(JFactory::getApplication()->input->get('option')=='com_users' && JFactory::getApplication()->input->get('view')=='profile' && JFactory::getApplication()->input->get('layout')!='edit' && JFactory::getApplication()->input->get('task','')=='')
			{
				$profileMenu=JFactory::getApplication()->getMenu()->getItems('link','index.php?option=com_jsn&view=profile',true);
				if(isset($profileMenu->id)) $Itemid=$profileMenu->id;
				else $Itemid='';
				$app->redirect(JRoute::_('index.php?option=com_jsn&view=profile&Itemid='.$Itemid,false));
			}
			
			// ---- Cookie problem when access to other profiles
			$user->set('cookieLogin','');
			
			// ---- Check if required fields are not empty
			$checkRequired=$config->get('forcerequired',0);
			if($user->id && $checkRequired && !(JFactory::getApplication()->input->get('option','')=='com_jsn' && JFactory::getApplication()->input->get('view','')=='opField'))
			{
				require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
				$user=JsnHelper::getUser();
				
				$excludeFromCheck=JsnHelper::excludeFromProfile($user);
				$access=$user->getAuthorisedViewLevels();
				$db=JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('a.alias')->from('#__jsn_fields AS a')->where('a.level = 2')->where('a.published = 1')->where('a.required = 1')->where('a.edit = 1')->where('a.access IN ('.implode(',',$access).')');
				$db->setQuery( $query );
				$requiredFields = $db->loadColumn();
				$required=true;
				foreach($requiredFields as $requiredField)
				{
					$formFormatRequiredField='jform['.$requiredField.']';
					if(!in_array($formFormatRequiredField,$excludeFromCheck) && (!isset($user->$requiredField) || $user->$requiredField=='')) $required=false;
				}
				if(!$required)
				{
					if(JFactory::getApplication()->input->get('option')=='com_users' && JFactory::getApplication()->input->get('view')=='profile' && JFactory::getApplication()->input->get('layout')=='edit')
					{
						$lang = JFactory::getLanguage();
						$lang->load('com_jsn');
						if(JFactory::getApplication()->input->get('task')!='profile.save' && JFactory::getApplication()->input->get('task')!='save') $app->enqueueMessage(JText::_('COM_JSN_COMPLETEREGISTRATION'),'warning');
					}
					else{
						if(JFactory::getApplication()->input->get('task')!='profile.save' && JFactory::getApplication()->input->get('task')!='save' && JFactory::getApplication()->input->get('task')!='user.logout') $app->redirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit',false));
					}
				
				}
			}
			if( $config->get('layout', 1) && JFactory::getApplication()->input->get('option')=='com_users' && ( ( JFactory::getApplication()->input->get('view')=='profile' && JFactory::getApplication()->input->get('layout')=='edit' ) || (JFactory::getApplication()->input->get('view')=='registration' && JFactory::getApplication()->input->get('layout','')=='') ) )
			{
				$active=JFactory::getApplication()->getMenu()->getActive();
				if(empty($active)) {
					$active=JFactory::getApplication()->getMenu()->getDefault();
					$active->home=0;
				}
				$active->query['layout']='easyprofile';
				JFactory::getApplication()->getMenu()->setActive($active->id);
			}
		}
	}
	
	function onAfterRender()
    {
		$app = JFactory::getApplication();
		if (!$app->isAdmin()){
			$config = JComponentHelper::getParams('com_jsn');
			$page=JResponse::getBody();
			$page=str_replace('{socialconnect_icon}', '<div class="socialconnect icon"></div>', $page);
			$page=str_replace('{socialconnect}', '<div class="socialconnect"></div>', $page);
			require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
			$user=JsnHelper::getUser();
			if(!$user->guest)
			{
				$output=array();
				$output[]='<div class="socialconnect_unlink">';
				if(!empty($user->facebook_id)) $output[]='<div><a class="zocial facebook" href="index.php?option=com_jsn&amp;view=facebook&task=unset">'.JText::_('COM_JSN_UNLINK').'</a></div>';
				if(!empty($user->twitter_id)) $output[]='<div><a class="zocial twitter" href="index.php?option=com_jsn&amp;view=twitter&task=unset">'.JText::_('COM_JSN_UNLINK').'</a></div>';
				if(!empty($user->google_id)) $output[]='<div><a class="zocial googleplus" href="index.php?option=com_jsn&amp;view=google&task=unset">'.JText::_('COM_JSN_UNLINK').'</a></div>';
				if(!empty($user->linkedin_id)) $output[]='<div><a class="zocial linkedin" href="index.php?option=com_jsn&amp;view=linkedin&task=unset">'.JText::_('COM_JSN_UNLINK').'</a></div>';
				$output[]='</div>';
				$page=str_replace('{socialconnect_unlink}', implode('', $output), $page);
			}
			else $page=str_replace('{socialconnect_unlink}', '', $page);
			
			JResponse::setBody($page);
		}
	}
	
	public function onBeforeCompileHead()
	{
		$app=JFactory::getApplication();

		// Load Config
		$config = JComponentHelper::getParams('com_jsn');

		// Javascript for Tabs
		if(!$app->isAdmin() && JFactory::getApplication()->input->get('option')=='com_users' && $config->get('tabs',1))
		{
			// Tabs
			$doc = JFactory::getDocument();
			$script='
			var jsn_prev_button="'.JText::_('JPREV').'";
			var jsn_next_button="'.JText::_('JNEXT').'";
			';
			$doc->addScriptDeclaration( $script );
			$doc->addScript(JURI::root().'components/com_jsn/assets/js/tabs.js');
		}
		// Javascript for com_users
		if(JFactory::getApplication()->input->get('option')=='com_users' || (JFactory::getApplication()->input->get('option')=='com_admin' && JFactory::getApplication()->input->get('view')=='profile'))
		{
			JHtml::_('jquery.framework');
			$doc = JFactory::getDocument();
			//$doc->addStylesheet(JURI::root().'components/com_jsn/assets/css/style.css');
			$doc->addScript(JURI::root().'components/com_jsn/assets/js/privacy.js');
			$doc->addScript(JURI::root().'components/com_jsn/assets/js/name.js');
		}
		
		// Javascript for Condition (com_users and userlist search form)
		if((JFactory::getApplication()->input->get('option','')=='com_users' && JFactory::getApplication()->input->get('layout','')=='edit') || (JFactory::getApplication()->input->get('option','')=='com_users' && JFactory::getApplication()->input->get('layout','')=='' && JFactory::getApplication()->input->get('view','')=='registration') || (JFactory::getApplication()->input->get('option','')=='com_jsn' && JFactory::getApplication()->input->get('view','')=='list') || (JFactory::getApplication()->input->get('option')=='com_admin' && JFactory::getApplication()->input->get('view')=='profile'))
		{
			JHtml::_('jquery.framework');
			$doc = JFactory::getDocument();
			$db=JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.*')->from('#__jsn_fields AS a')->where('a.level = 2')->where('a.published = 1')->order($db->escape('a.lft') . ' ASC');
			$db->setQuery( $query );
			$fields = $db->loadObjectList('alias');
			$script='jQuery(document).ready(function($){
				var hideFieldset=$("#member-profile fieldset.hide,#member-registration fieldset.hide").length;
				$(".spacer").closest(".control-group,.form-group").addClass("spacer-container");
				function checkFieldset(){
					$("#member-profile fieldset:not(.radio ):not(.checkboxes ),#member-registration fieldset:not(.radio ):not(.checkboxes )").each(function(){
						if($(this).find(".control-group.hide,.form-group.hide").length==$(this).find(".control-group:not(.spacer-container),.form-group:not(.spacer-container)").length){
							$(this).hide().addClass("hide");
							
						}
						else {
							$(this).show().removeClass("hide");
							
						}
						
					});'
				.(!$app->isAdmin() && $config->get('tabs',1) ? '	if($("#member-profile fieldset.hide,#member-registration fieldset.hide").length!=hideFieldset){
						hideFieldset=$("#member-profile fieldset.hide,#member-registration fieldset.hide").length;
						tabs($);
					}' : '')
				.'}
				';
			foreach($fields as $field)
			{
				// Load Options
				$registry = new JRegistry;
				$registry->loadString($field->params);
				$field->params = $registry->toArray();
				// Check User Access and add field hidden in form
				require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
				$user=JsnHelper::getUser();
				$access=$user->getAuthorisedViewLevels();
				$skip=array(/*'password','email',*/'registerdate','lastvisitdate');
				if(!$app->isAdmin() && ((JFactory::getApplication()->input->get('option')=='com_users' && JFactory::getApplication()->input->get('layout')=='edit' && $field->edit==0) || (JFactory::getApplication()->input->get('option')=='com_users' && JFactory::getApplication()->input->get('view')=='registration' && JFactory::getApplication()->input->get('task','')=='' && $field->register==0)) && in_array($field->access,$access) && !in_array($field->alias,$skip) && $field->type!='delimeter')
				{
					$alias=$field->alias;
					$userValue=(isset($user->$alias) ? $user->$alias : '');
					if(is_array($userValue)) $userValue=implode(',',$userValue);
					$script.='if(!$("#jform_'.str_replace('-','_',$field->alias).'").length) $("form#member-registration,form#member-profile").after("<input type=\"hidden\" id=\"jform_'.str_replace('-','_',$field->alias).'\" value=\"'.str_replace(array("\n","\r"), '',$userValue).'\" />");';
					$field->type="hidden";
				}
				// Conditions
				$condition_suffix=array('','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19');
				foreach($condition_suffix as $suffix)
				{
					if(empty($field->params['condition_hide'.$suffix])) $field->params['condition_hide'.$suffix]=array();
					if(isset($field->params['condition_operator'.$suffix]) && $field->params['condition_operator'.$suffix]!=0 && count($field->params['condition_hide'.$suffix])>0){
						if(isset($field->params['condition_action'.$suffix]) && $field->params['condition_action'.$suffix]=='show')
						{
							$actionShowFields=true;
						}
						else
						{
							$actionShowFields=false;
						}
						switch($field->params['condition_operator'.$suffix])
						{
							case 1:
								$operator='==';
								$operator_post='';
								if($actionShowFields) $not='!';
								else $not='';
							break;
							case 2:
								$operator='>';
								$operator_post='';
								if($actionShowFields) $not='!';
								else $not='';
							break;
							case 3:
								$operator='<';
								$operator_post='';
								if($actionShowFields) $not='!';
								else $not='';
							break;
							case 4:
								$operator='.indexOf';
								$operator_post='!=-1';
								if($actionShowFields) $not='!';
								else $not='';
							break;
							case 5:
								$operator='!=';
								$operator_post='';
								if($actionShowFields) $not='!';
								else $not='';
							break;
							case 6:
								$operator='.indexOf';
								$operator_post='!=-1';
								if($actionShowFields) $not='';
								else $not='!';
							break;
						}
						// Field to Show/Hide
						$fieldsToHide='';
						foreach($field->params['condition_hide'.$suffix] as $fieldToHide)
						{
							$fieldsToHide.='#jform_'.str_replace('-','_',$fieldToHide).',';
							$fieldsToHide.='#jform_privacy_'.str_replace('-','_',$fieldToHide).',';
						}
						$fieldsToHide=trim($fieldsToHide,',');
						// Field to Check
						if($field->params['condition_field'.$suffix]=='_custom') $valueToCheck='var valToCheck="'.$field->params['condition_custom'.$suffix].'";';
						else $valueToCheck='
							var valToCheck=$("#jform_'.str_replace('-','_',$field->params['condition_field'.$suffix]).'").val();
							$("#jform_'.str_replace('-','_',$field->params['condition_field'.$suffix]).' input:checked").each(function(){
								if(valToCheck=="") valToCheck=$(this).val();
								else valToCheck=valToCheck+","+$(this).val();
							});
						';
						// Field to Bind
						if($field->params['condition_field'.$suffix]=='_custom') $fieldToBind='#jform_'.str_replace('-','_',$field->alias);
						else $fieldToBind='#jform_'.str_replace('-','_',$field->alias).',#jform_'.str_replace('-','_',$field->params['condition_field'.$suffix]);
						// Code
						$scriptval='
							var val=$("#jform_'.str_replace('-','_',$field->alias).'").val();
						';
						if($field->type=='radiolist')
							$scriptval='
								var val=$("#jform_'.str_replace('-','_',$field->alias).' input:checked").val();
							';
						if($field->type=='checkboxlist')
							$scriptval='
								var val="";
								$("#jform_'.str_replace('-','_',$field->alias).' input:checked").each(function(){
									if(val=="") val=$(this).val();
									else val=val+","+$(this).val();
								});
							';
						$script.='
							'.$scriptval.'
							if(val==null || val==undefined) val="";
							'.$valueToCheck.'
							if(valToCheck==null || valToCheck==undefined) valToCheck="";
							if($("#jform_'.str_replace('-','_',$field->alias).'").length) if('.$not.'(val'.$operator.'(valToCheck)'.$operator_post.'))
								$("'.$fieldsToHide.'").each(function(){
									if($(this).is(".required") || $(this).is("[aria-required=\'true\']") || $(this).is("[required=\'required\']")){
										$(this).addClass("norequired").removeClass("required").removeAttr("required").attr("aria-required","false");
										$(this).find("input[type!=\'checkbox\']").addClass("norequired").removeClass("required").removeAttr("required").attr("aria-required","false");
									}
									$(this).closest(".control-group,.form-group").hide().addClass("hide");
								});
							else
								$("'.$fieldsToHide.'").each(function(){
									if($(this).is(".norequired")){
										$(this).addClass("required").removeClass("norequired").attr("required","required").attr("aria-required","true");
										$(this).find("input[type!=\'checkbox\']").addClass("required").removeClass("norequired").attr("required","required").attr("aria-required","true");
									}
									$(this).closest(".control-group,.form-group").show().removeClass("hide");
								});
							checkFieldset();
							$("'.$fieldToBind.'").bind("change keyup",function(){
								'.$scriptval.'
								if(val==null || val==undefined) val="";
								'.$valueToCheck.'
								if(valToCheck==null || valToCheck==undefined) valToCheck="";
								if('.$not.'(val'.$operator.'(valToCheck)'.$operator_post.'))
									$("'.$fieldsToHide.'").each(function(){
										if($(this).is(".required") || $(this).is("[aria-required=\'true\']") || $(this).is("[required=\'required\']")){
											$(this).addClass("norequired").removeClass("required").removeAttr("required").attr("aria-required","false");
											$(this).find("input[type!=\'checkbox\']").addClass("norequired").removeClass("required").removeAttr("required").attr("aria-required","false");
										}
										$(this).closest(".control-group,.form-group").slideUp(function(){$(this).addClass("hide");checkFieldset();});
									});
								else
									$("'.$fieldsToHide.'").each(function(){
										if($(this).is(".norequired")){
											$(this).addClass("required").removeClass("norequired").attr("required","required").attr("aria-required","true");
											$(this).find("input[type!=\'checkbox\']").addClass("required").removeClass("norequired").attr("required","required").attr("aria-required","true");
										}
										$(this).closest(".control-group,.form-group").slideDown().removeClass("hide");
										checkFieldset();
									});
								
							});';
					}
				}
			}
			$script.='});';
			$doc->addScriptDeclaration( $script );
		}
		
		// Javascript Options Page (Backend)
		if($app->isAdmin() && JFactory::getApplication()->input->get('option')=='com_config' && JFactory::getApplication()->input->get('component')=='com_jsn' )
		{
			$script='jQuery(document).ready(function($){
				$("#jform_layout0, #jform_layout1").change(function(){
					if( $("#jform_layout1").is(":checked") ) {
						$("#jform_layout_width").closest(".control-group").show();
						$("#jform_layout_maxwidth").closest(".control-group").show();
						$("#jform_layout_form").closest(".control-group").show();
					}
					else {
						$("#jform_layout_width").closest(".control-group").hide();
						$("#jform_layout_maxwidth").closest(".control-group").hide();
						$("#jform_layout_form").closest(".control-group").hide();
					}
					$("#jform_layout_width0").change();
				});
				$("#jform_layout_width0, #jform_layout_width1").change(function(){
					if( $("#jform_layout_width1").is(":checked") ) {
						$("#jform_layout_maxwidth").closest(".control-group").show();
					}
					else {
						$("#jform_layout_maxwidth").closest(".control-group").hide();
					}
				});
				$("#jform_layout0").change();
				$("#jform_layout_width0").change();
			});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( $script );
		}

		// Add Bootstrap CSS
		if(!$app->isAdmin() && (JFactory::getApplication()->input->get('option')=='com_users' || JFactory::getApplication()->input->get('option')=='com_jsn') && $config->get('bootstrap',0))
		{
			$doc = JFactory::getDocument();
			$doc->addStylesheet(JURI::root().'media/jui/css/bootstrap.min.css');
			$dir = $doc->direction;
			if($dir=='rtl')
			{
				$doc->addStylesheet(JURI::root().'media/jui/css/bootstrap-rtl.css');
			}
		}

		// Add Bootstrap Icons
		if(!$app->isAdmin() && (JFactory::getApplication()->input->get('option')=='com_users' || JFactory::getApplication()->input->get('option')=='com_jsn') && $config->get('bootstrap_icons',0))
		{
			$doc = JFactory::getDocument();
			$doc->addStylesheet(JURI::root().'media/jui/css/icomoon.css');
		}
		
		// Add Javascript Bootstrap on profile page
		if(JFactory::getApplication()->input->get('option')=='com_jsn' && JFactory::getApplication()->input->get('view','profile')=='profile')
		{
			JHtml::_('bootstrap.framework');
		}

		// Add Style to Site (Components, Modules and Plugins)
		if($app->isSite() || (JFactory::getApplication()->input->get('option')=='com_users' || (JFactory::getApplication()->input->get('option')=='com_admin' && JFactory::getApplication()->input->get('view')=='profile')))
		{
			$doc = JFactory::getDocument();
			$doc->addStylesheet(JURI::root().'components/com_jsn/assets/css/style.css');
			$dir = $doc->direction;
			if($dir=='rtl')
			{
				$doc->addStylesheet(JURI::root().'components/com_jsn/assets/css/style-rtl.css');
			}
		}
	}
}
