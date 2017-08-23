<?php
/**
* @copyright	Copyright (C) 2013 Jsn Project company. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @package		Easy Profile
* website		www.easy-profile.com
* Technical Support : Forum -	http://www.easy-profile.com/support.html
*/

defined('_JEXEC') or die;

class PlgUserJsn_Users extends JPlugin
{
	private $config=null;

	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		// Include Field Class & Field Model
		foreach (glob(JPATH_ADMINISTRATOR . '/components/com_jsn/helpers/fields/*.php') as $filename) {
			require_once $filename;
		}
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR.'/components/com_jsn/models/fields');
		JFormHelper::addRulePath(JPATH_ADMINISTRATOR.'/components/com_jsn/models/rule');
		
		// Load Config
		$this->config = JComponentHelper::getParams('com_jsn');
	}

	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->firstname) and $userId > 0)
			{	
				$db = JFactory::getDbo();
				
				// Load the profile data from the database.
				static $user_cache=null;
				if(!isset($user_cache[$userId]))
				{
					$query = $db->getQuery(true);
					$query->select('a.*')->from('#__jsn_users AS a')->where('a.id = '.$userId);
					$db->setQuery( $query );
					$user_cache[$userId] = $db->loadObject();
				}
				$user=$user_cache[$userId];
				
				if(empty($user)) $user=new stdClass();
				
				if(!isset($user->firstname)) $user->firstname=$data->name;
				
				static $fields_cache=null;
				if($fields_cache==null)
				{
					$query = $db->getQuery(true);
					$query->select('a.*')->from('#__jsn_fields AS a')->where('a.level = 2')->where('a.published = 1')/*->order($db->escape('a.lft') . ' ASC')*/;
					$db->setQuery( $query );
					$fields_cache = $db->loadObjectList();
				}
				$fields=$fields_cache;
					
				// Privacy
				if(isset($user->privacy))
				{
					if(is_string($user->privacy)) $user->privacy=json_decode($user->privacy);
					if(count($user->privacy)) foreach($user->privacy as $key => $value)
					{
						$data->$key=$value;
					}
				}

				foreach($fields as $field)
				{
					// Load Field Registry
					$registry = new JRegistry;
					$registry->loadString($field->params);
					$field->params = $registry;
					
					// Privacy
					$privacy_name='privacy_'.$field->alias;
					if($field->params->get('privacy',0))
					{
						if(!isset($data->$privacy_name)) $data->$privacy_name=$field->params->get('privacy_default',0);
					}
					else
					{
						if(isset($data->$privacy_name)) unset($data->$privacy_name);
					}
					
					// Field Class
					$class='Jsn'.ucfirst($field->type).'FieldHelper';
					if(class_exists($class)) $class::loadData($field, $user, $data);

					// Register Function to Profile Display
					if (!JHtml::isRegistered('jsn.'.$field->type))
					{
						JHtml::register('jsn.'.$field->type, array($class, $field->type));
					}

				}
				if (!JHtml::isRegistered('jsn.email'))
				{
					JHtml::register('jsn.email', array('JsnUsermailFieldHelper', 'usermail'));
				}
				
				// Social Connect
				if(isset($user->facebook_id)) $data->facebook_id=$user->facebook_id;
				if(isset($user->twitter_id)) $data->twitter_id=$user->twitter_id;
				if(isset($user->google_id)) $data->google_id=$user->google_id;
				if(isset($user->linkedin_id)) $data->linkedin_id=$user->linkedin_id;
			}
		}
		return true;
	}

	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// Include Language for com_users
		$lang = JFactory::getLanguage();
		$lang->load('com_jsn');

		require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');

		// Access
		if(isset($data->id))  $id=JFactory::getApplication()->getUserState('com_users.edit.profile.id',$data->id);
		else $id=JFactory::getApplication()->getUserState('com_users.edit.profile.id',JFactory::getApplication()->input->get('id',null));
		
		$user=JsnHelper::getUser($id);
		$access=$user->getAuthorisedViewLevels();
		
		$db=JFactory::getDbo();
		
		// Social Integration
		global $JSNSOCIAL;
		if($JSNSOCIAL && !$user->guest)
		{
			$db->setQuery("SELECT friend_id FROM #__jsnsocial_friends WHERE user_id = ".$user->id);
			$friends = $db->loadColumn();
			if(empty($friends)) $friends=array(0);
		}
		
		// Visitor
		$userVisitor=JFactory::getUser();
		
		// Access to view field and groups
		$accessVisitor=$userVisitor->getAuthorisedViewLevels();
		
		// Privacy
		if($userVisitor->id==$user->id) $privacy=99;
		elseif($userVisitor->id && !$JSNSOCIAL) $privacy=1;
		elseif($userVisitor->id && $JSNSOCIAL && in_array($userVisitor->id,$friends)) $privacy=1;
		else $privacy=0;
		
		// Privacy Skip for Admin
		if($userVisitor->authorise('core.edit', 'com_users')) $privacy=99;
		
		// Set fields query depend on view
		if($name=='com_users.profile' && JFactory::getApplication()->input->get('layout')!='edit' && JFactory::getApplication()->input->get('task','')=='') $where="profile = 1";
		elseif($name=='com_users.profile' && (JFactory::getApplication()->input->get('layout')=='edit' || JFactory::getApplication()->input->get('task','')!='')) {
			$session = JFactory::getSession();
			$original_id=$session->get('jsn_original_id',0);
			if($this->config->get('admin_frontend', 0) && ($original_id!=$user->id || $user->authorise('core.edit', 'com_users')))
			{
				if(JFactory::getApplication()->input->get('task','')=='') JFactory::getApplication()->enqueueMessage(JText::_('COM_JSN_ADMIN_FRONTEND'),'info');
				$where=false;
			}
			else{
				$where="edit = 1";
			}
		}
		elseif($name=='com_users.registration') $where="register = 1";
		else $where=false;

		

		// Reset Form
		if(JFactory::getApplication()->isSite()){
			$form->reset(true);
		}
		else {
			$form->removeField('name');
			$form->removeField('email');
			$form->removeField('username');
			$form->removeField('password');
			$form->removeField('password2');
		}

		// Compile XML
		$xml='<form>'."\n";
		$query = $db->getQuery(true);
		$query->select('a.*')->from('#__jsn_fields AS a')->where('a.level = 1')->where('a.published = 1')->order($db->escape('a.lft') . ' ASC');//->where('a.alias <> ' . $db->quote('root'));
		$db->setQuery( $query );
		$fieldgroups = $db->loadObjectList();
		$first_fieldgroup=true;
		foreach($fieldgroups as $fieldgroup)
		{
			if( in_array($fieldgroup->access, $access) && ($privacy==99 || in_array($fieldgroup->accessview, $accessVisitor)))
			{
				$query = $db->getQuery(true);
				$query->select('a.*')->from('#__jsn_fields AS a')->where('a.level = 2')->where('a.published = 1')->where('a.parent_id = '.$fieldgroup->id)->order($db->escape('a.lft') . ' ASC');//->where('a.alias <> ' . $db->quote('root'));
				if($where) $query->where($where);
				$db->setQuery( $query );
				$fields = $db->loadObjectList('id');
				$fieldsXml='';
				foreach($fields as $field)
				{
					// Load Options
					$registry = new JRegistry;
					$registry->loadString($field->params);
					$field->params = $registry;
					
					// Hide on Edit Option
					//if(JFactory::getApplication()->isSite() && $field->params->get('hideonedit',0) && JFactory::getApplication()->input->get('layout')=='edit') continue;
					
					// Privacy Default
					$privacy_enable=$field->params->get('privacy',0);
					$privacy_name='privacy_'.$field->alias;
					if(!isset($data->$privacy_name) && is_object($data)) $data->$privacy_name=$field->params->get('privacy_default',0);
					
					if( in_array($field->access, $access) && ($privacy==99 || in_array($field->accessview, $accessVisitor)) && (JFactory::getApplication()->isAdmin() || !$privacy_enable || $data->$privacy_name<=$privacy/*!in_array($field->alias, $privacyProtected)*/) )
					{
						// Get Xml
						$class='Jsn'.ucfirst($field->type).'FieldHelper';
						if(class_exists($class)) {
							// Privacy Xml
							if((JFactory::getApplication()->input->get('task','')!='' || JFactory::getApplication()->input->get('view')=='registration' || JFactory::getApplication()->input->get('layout')=='edit') && !in_array($field->alias,array('password','registereddate','lastvisitdate')) && $privacy_enable) 
								if($this->config->get('profileACL',2)!=0) $fieldsXml.='
								<field
									name="privacy_'.$field->alias.'"
									type="privacy"
									default="'.$field->params->get('privacy_default',0).'"
									id="privacy_'.$field->alias.'"
									class="privacy"
									label=""
								></field>
								
								';
							if(JFactory::getApplication()->isAdmin() && !$field->core) $fieldsXml.=str_replace(array('required="true"','requiredfile="true"'),array('required="false"','requiredfile="false"'),$class::getXml($field));
							else $fieldsXml.=$class::getXml($field);
						}
						$lastFielgroup='jsn_'.$fieldgroup->alias;
					}
				}
				if($fieldsXml!='')
				{
					$xml.="\n\t".'<fieldset name="jsn_'.$fieldgroup->alias.'" label="'.JsnHelper::xmlentities(JText::_($fieldgroup->title)).'" >'.($name=='com_users.registration' && ($this->config->get('tabs', 1) || $first_fieldgroup) ? '<field name="spacer_'.$fieldgroup->alias.'" type="spacer" class="text" label="COM_USERS_REGISTER_REQUIRED" />' : '' ).$fieldsXml."\n\t".($fieldgroup->core ? JsnCoreFieldHelper::getXml() : '' ).'</fieldset>'."\n";
					$first_fieldgroup=false;
				}
			}
		}
		
		// Remove Default value in Edit Profile
		if($name!='com_users.registration' || JFactory::getApplication()->input->get('task','')!=''/* && JFactory::getApplication()->input->get('layout')!='edit' && JFactory::getApplication()->input->get('task','')==''*/)
		{
			$xml=str_replace('default="','removed_default="',$xml);
		}

		// TwoFactor XML code, not in fieldset
		if($name=='com_users.profile') $xml.='<field name="twofactor" type="hidden" default="none" />';

		// Close XML
		$xml.="\n".'</form>';
		
		// Load XML
		$xml=new SimpleXMLElement($xml);
		$form->load($xml);
		
		// Remove Required for Conditional
		if(JFactory::getApplication()->input->get('jform',null,'array'))
		{
			require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
			$dataCheck=(object) array_merge((array) $user,JFactory::getApplication()->input->get('jform',array(),'array'));
			$excludeRequired=JsnHelper::excludeFromProfile($dataCheck,true);
			foreach($excludeRequired as $field){
				$form->setFieldAttribute($field,'required',false);
				$form->setFieldAttribute($field,'requiredfile',false);
			}
		}
		

		// Adjust Params to see after other fields
		if($userVisitor->id==$user->id && JFactory::getApplication()->isSite() && JComponentHelper::getParams('com_users')->get('frontend_userparams',1) && $name!='com_users.registration')
		{
			$form->loadFile(JPATH_SITE . '/components/com_users/models/forms/frontend.xml');
		}

		// Move Captcha to last fieldset
		if($name=='com_users.registration')
		{
			$form->load('<form><fieldset name="'.$lastFielgroup.'"><field
				name="captcha"
				type="captcha"
				label="COM_USERS_CAPTCHA_LABEL"
				description="COM_USERS_CAPTCHA_DESC"
				validate="captcha"
			/></fieldset></form>');
		}
		
		// Site Language on Registration
		if($name=='com_users.registration' && JComponentHelper::getParams('com_users')->get('site_language',0))
		{
			$form->loadFile(JPATH_SITE . '/components/com_users/models/forms/sitelang.xml');
		}

		// Remove joomla core field from profile
		if($name=='com_users.profile' && JFactory::getApplication()->input->get('layout')!='edit' && JFactory::getApplication()->input->get('task','')=='')
		{
			if(JFactory::getApplication()->input->get('option')=='com_users') $form->removeField('username'); // username is included in summary
			$form->removeField('password1');
			$form->removeField('password2');
			$form->removeField('email2');
		}

		// Remove username field if logintype is MAIL
		$logintype=$this->config->get('logintype', 'USERNAME');
		if($logintype=='MAIL'){
			$form->setFieldAttribute('username', 'type','hidden');
			if(JFactory::getApplication()->input->get('view')=='registration' || JFactory::getApplication()->isAdmin())
			{
				$form->setFieldAttribute('username', 'default', md5(uniqid(rand(), true)));
			}
		}

		// Set Default Value in Registration
		if($name=='com_users.registration')
		{
			foreach(JFactory::getApplication()->input->get->getArray() as $key => $value)
			{
				$form->setFieldAttribute($key,'default',$value);
			}
		}

		return true;
	}

	/*private $addToTriggerEmail='';
	private $addToTriggerUsername='';
	private $addToTriggerPassword='';
	private $addToTriggerName='';
	public function onUserBeforeSave($user, $isnew, $data)
	{
		$this->addToTriggerEmail=$user['email'];
		$this->addToTriggerUsername=$user['username'];
		$this->addToTriggerPassword=$user['password'];
		$this->addToTriggerName=$user['name'];
		return true;
	}*/

	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = JArrayHelper::getValue($data, 'id', 0, 'int');
		
		if($this->config->get('logintype', 'USERNAME')=='MAIL' && JFactory::getApplication()->input->get('task')=='registration.activate')
		{
			$user = JFactory::getUser($userId);
			if(isset($user->tmp_username))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->update("#__users");
				$query->set($db->quoteName('username').' = '.$db->quote($user->tmp_username));
				$query->where('id = '. $userId);
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		if ($userId && $result && isset($data['firstname']))
		{
			try
			{
				$db = JFactory::getDbo();

				// Compile new Format Name
				$logintype=$this->config->get('logintype', 'USERNAME');
				if( $isNew && $logintype=='MAIL' )
				{
					if(JFactory::getApplication()->isSite()) $data['username']=$data['email1'];
					else $data['username']=$data['email'];
				}
				// Format Name
				$namestyle=$this->config->get('namestyle', 'FIRSTNAME_LASTNAME');
				switch($namestyle){
					case 'FIRSTNAME_LASTNAME':
						$name=trim(trim($data['firstname']).' '.trim($data['lastname']));
					break;
					case 'FIRSTNAME_SECONDNAME_LASTNAME':
						$name=trim(trim(trim($data['firstname']).' '.trim($data['secondname'])).' '.trim($data['lastname']));
					break;
					case 'FIRSTNAME':
						$name=trim($data['firstname']);
					break;
				}
				
				// Write new Format Name
				$query = $db->getQuery(true);
				$query->update("#__users");
				$query->set($db->quoteName('name').' = '.$db->quote($name));
				if( $isNew && $logintype=='MAIL' ){
					$username=JApplication::stringURLSafe( $name );
					if( ! trim($username,'-_ ') ){
						$username='user_'.date('YmdHis');
					}
					$queryCheckUsername = $db->getQuery(true);
					$queryCheckUsername->select('a.email')->from('#__users AS a')->where('a.username = '.$db->quote($username));
					$db->setQuery( $queryCheckUsername );
					if($userCheckUsername=$db->loadResult()){
						$username=$username.'_'.rand(0,2000);
					}
					$query->set($db->quoteName('username').' = '.$db->quote($username));
				}
				$query->where('id = '. $userId);
				$db->setQuery($query);
				$db->execute();

				// Check if user exist
				$query = $db->getQuery(true);
				$query->select('a.id')->from('#__jsn_users AS a')->where('a.id = '.$userId);
				$db->setQuery( $query );
				$isUpdate = $db->loadObjectList();
				
				// Load Fields
				require_once(JPATH_SITE.'/components/com_jsn/helpers/helper.php');
				$query = $db->getQuery(true);
				$query->select('a.*')->from('#__jsn_fields AS a')->where('a.level = 2')->where('a.published = 1')->order($db->escape('a.lft') . ' ASC');
				//if(JFactory::getApplication()->isSite() && JFactory::getApplication()->input->get('option')=='com_users' && (JFactory::getApplication()->input->get('task')=='profile.save' || JFactory::getApplication()->input->get('task')=='save')) $query->where('a.edit = 1');
				$db->setQuery( $query );
				$fields = $db->loadObjectList();
				$storeData=array();
				$jsnUser=JsnHelper::getUser($userId);
				$no_edit_fields=array();

				$session = JFactory::getSession();
				$original_id=$session->get('jsn_original_id',0);
				if($this->config->get('admin_frontend', 0) && ($original_id!=$jsnUser->id || $jsnUser->authorise('core.edit', 'com_users')))
					$allow_no_edit_fields=true;
				else
					$allow_no_edit_fields=false;

				foreach($fields as $field)
				{
					// Load Field Registry
					$registry = new JRegistry;
					$registry->loadString($field->params);
					$field->params = $registry;
					
					$class='Jsn'.ucfirst($field->type).'FieldHelper';
					if(class_exists($class)) $class::storeData($field, $data, $storeData);
					
					// Not edit fields filled only for conditions
					if(JFactory::getApplication()->isSite() && JFactory::getApplication()->input->get('option')=='com_users' && (JFactory::getApplication()->input->get('task')=='profile.save' || JFactory::getApplication()->input->get('task')=='save') && $field->edit==0 && $allow_no_edit_fields==false)
					{
						$alias=$field->alias;
						$no_edit_fields[]=$alias;
						$storeData[$alias]=$jsnUser->$alias;
						if(is_array($storeData[$alias])) $storeData[$alias]=json_encode($storeData[$alias]);
					}
				}
				
				// Check if columns exist
				$query = $db->getQuery(true);
				$query = "SHOW COLUMNS FROM #__jsn_users";
				$db->setQuery( $query );
				$result=$db->loadObjectList();
				$columns=array();
				foreach($result as $column)
				{
					$columns[]=$column->Field;
				}
				foreach($storeData as $key=>$value)
				{
					if(!in_array($key,$columns))
					{
						unset($storeData[$key]);
						if(JFactory::getApplication()->isAdmin())
						{
							JFactory::getApplication()->enqueueMessage('Error on save "'.$key.'" field, try to recreate a field!','warning');
						}
					}
				}
				
				// Privacy Field
				$data['privacy']=array();
				foreach($data as $key => $value)
				{
					if(substr($key,0,8)=='privacy_') $data['privacy'][$key]=$value;
				}
				$storeData['privacy']=json_encode($data['privacy']);
				
				// Social Connect
				if(isset($data['facebook_id'])) $storeData['facebook_id']=$data['facebook_id'];
				if(isset($data['twitter_id'])) $storeData['twitter_id']=$data['twitter_id'];
				if(isset($data['google_id'])) $storeData['google_id']=$data['google_id'];
				if(isset($data['linkedin_id'])) $storeData['linkedin_id']=$data['linkedin_id'];
				
				// Reset field Hidden By Condition
				$conditions_check=(object)$storeData;
				foreach($conditions_check as $key=>$val)
				{
					if(!is_null(json_decode($conditions_check->$key))) $conditions_check->$key=json_decode($conditions_check->$key);
				}
				$conditions_check->id=$userId;
				$removedByConditions=JsnHelper::excludeFromProfile($conditions_check,true);
				foreach($removedByConditions as $field){
					if(isset($storeData[$field])) $storeData[$field]='';
				}
				
				// Unset fields not in edit form

				foreach($no_edit_fields as $field){
					if(isset($storeData[$field])) unset($storeData[$field]);
				}
				
				// Trigger Profile and Field Update
				JPluginHelper::importPlugin('jsn');
				
				$changed=array();
				$storeData['email']=$data[ 'email' ];
				$storeData['username']=$data[ 'username' ];
				$storeData['password']=$data[ 'password' ];
				$storeData['name']=$data[ 'name' ];
				//$jsnUser->email=$this->addToTriggerEmail;
				//$jsnUser->username=$this->addToTriggerUsername;
				//$jsnUser->password=$this->addToTriggerPassword;
				//$jsnUser->name=$this->addToTriggerName;
				$dispatcher	= JEventDispatcher::getInstance();
				foreach($storeData as $key=>$value)
				{
					if($key!='privacy'){
						if(isset($jsnUser->$key) && is_array($jsnUser->$key)) $value=json_decode($value);
						if((isset($jsnUser->$key) && $jsnUser->$key!=$value) || !isset($jsnUser->$key)){
							$changed[]=$key;
							$dispatcher->trigger('triggerField'.ucfirst(str_replace('-','_',$key)).'Update',array($jsnUser,&$storeData,$changed,$isNew));
						}
					}
				}
				if(count($changed))
				{
					$dispatcher->trigger('triggerProfileUpdate',array($jsnUser,&$storeData,$changed,$isNew));
				}
				unset($storeData['email']);
				unset($storeData['username']);
				unset($storeData['password']);
				unset($storeData['name']);
				
				
				// Write Jsn User
				if(count($isUpdate))
				{
					// Update User
					$query = $db->getQuery(true);
					$query->update("#__jsn_users");
					foreach($storeData as $key => $value)
					{
						$query->set($db->quoteName($key).' = '.$db->quote($value));
					}
					$query->where('id = '. $userId);
					$db->setQuery($query);
					$db->execute();
				}
				else{
					// New User
					$fields=array();
					$values=array();
					foreach($storeData as $key => $value)
					{
						$fields[]=$db->quoteName($key);
						$values[]=$db->quote($value);
					}
					
					$query="INSERT INTO #__jsn_users(id,".implode(', ',$fields).") VALUES(".$userId.", ".implode(', ',$values).")";
					$db->setQuery($query);
					$db->execute();
					
				}
				
				//Update Session
				$session = JFactory::getSession();
				if($userId==$session->get('user')->id){
					$session->set('user', new JUser($userId));
				}
				
				//Redirect on Profile Page
				if(!$isNew) {
					$profileMenu=JFactory::getApplication()->getMenu()->getItems('link','index.php?option=com_jsn&view=profile',true);
					if(isset($profileMenu->id)) $Itemid=$profileMenu->id;
					else $Itemid='';
					JFactory::getApplication()->setUserState('com_users.edit.profile.redirect', JRoute::_('index.php?option=com_jsn&Itemid='.$Itemid.'&view=profile&id='. (int) $userId,false));
				}
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}

	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__jsn_users WHERE id = ' . $userId
				);

				$db->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
		return true;
	}
	
	public function onUserLogin($user, $options = array())
	{
		if(JFactory::getApplication()->isAdmin()) return true;
		
		// Retrieve User
		$instance = JUser::getInstance();
		$id = (int) JUserHelper::getUserId($user['username']);
		if ($id)
		{
			$instance->load($id);
		}
		else 
		{
			return true;
		}
		
		// Redirect on First Login
		if($this->config->get('firstLoginUrl') && $instance->lastvisitDate=='0000-00-00 00:00:00')
		{
			$router = JFactory::getApplication()->getRouter();
			if ($router->getMode() == JROUTER_MODE_SEF)
			{
				JFactory::getApplication()->setUserState('users.login.form.return', 'index.php?Itemid='.$this->config->get('firstLoginUrl') );
			}
			else {
				JFactory::getApplication()->setUserState('users.login.form.return', JFactory::getApplication()->getMenu()->getItem($this->config->get('firstLoginUrl'))->link.'&Itemid='.$this->config->get('firstLoginUrl') );
			}
			return true;
		}
		// Redirect on Login
		if($this->config->get('loginUrl')/* && !(JFactory::getApplication()->getUserState('users.login.form.return'))*/)
		{
			$router = JFactory::getApplication()->getRouter();
			if ($router->getMode() == JROUTER_MODE_SEF)
			{
				JFactory::getApplication()->setUserState('users.login.form.return', 'index.php?Itemid='.$this->config->get('loginUrl') );
			}
			else {
				JFactory::getApplication()->setUserState('users.login.form.return', JFactory::getApplication()->getMenu()->getItem($this->config->get('loginUrl'))->link.'&Itemid='.$this->config->get('loginUrl') );
			}
			return true;
		}
		return true;
	}
	
}
