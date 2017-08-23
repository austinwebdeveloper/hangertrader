<?php
/**
* Copyright (C) 2015  freakedout (www.freakedout.de)
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

defined('_JEXEC') or die('Restricted Access');

if (!class_exists('joomlamailerMCAPI')) {
    require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/libraries/MCAPI.class.php');
}

class PlgUserJoomlamailer extends JPlugin {

    private static $MC = null;
    private static $oldEmail;
    protected $app;
    protected $db;
    protected $api;
    protected $debug;
    protected $listId;
	protected $autoloadLanguage = true;

	public function __construct(&$subject, $config) {
        if (!class_exists('joomlamailerMCAPI')) {
            return;
        }

		parent::__construct($subject, $config);
		JFormHelper::addFieldPath(__DIR__ . '/fields');

        $this->app = JFactory::getApplication();
        $this->db = JFactory::getDBO();
        $this->api = $this->getApiInstance();
        $this->debug = JFactory::getConfig()->get('debug');
        $this->listId = $this->params->get('listid');
	}

    public function onAfterRender() {
        // "dirty hack" to make tabs appear in backend user profile
        $option = $this->app->input->getCmd('option');
        $view = $this->app->input->getCmd('view');
        if ($this->app->isAdmin() && $option == 'com_users' && $view == 'user') {
            $body = JResponse::getBody();
            $script = '<script>!function($){
                $(document).ready(function(){
                    $("a[href=#joomlamailer_merges]").text("Newsletter Fields");
                    $("a[href=#joomlamailer_groupings]").text("Newsletter Groups");
                });
            }(jQuery);</script>';
            $body = preg_replace('#(</body>)#i', $script . '$1', $body);
            JResponse::setBody($body);
        }
    }

	public function onContentPrepareData($context, $data) {
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile'))) {
			return true;
		}

		if (is_object($data)) {
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->joomlamailer) && $userId > 0) {
                $user = JFactory::getUser($userId);

                // check if user is subscribed
                $query = $this->db->getQuery(true)
                    ->select(1)
                    ->from($this->db->qn('#__joomailermailchimpintegration'))
                    ->where($this->db->qn('userid') . ' = ' . $this->db->q($userId))
                    ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->listId));
                try {
                    $isSubscribed = ($this->db->setQuery($query)->loadResult() ? 1 : 0);
                } catch (Exception $e) {
                    $isSubscribed = false;
                }

                $data->joomlamailer['subscribe'] = $isSubscribed;
                if (!JHtml::isRegistered('users.subscribe')) {
                    JHtml::register('users.subscribe', array(__CLASS__, 'subscribe'));
                }

                if (!$isSubscribed) {
                    return;
                }

                $userData = $this->api->listMemberInfo($this->listId, $user->email);
                if (isset($userData['merges']) && count($userData['merges'])) {
                    foreach ($userData['merges'] as $key => $value) {
                        $data->joomlamailer_merges[$key] = $value;
                    }
                }
                if (isset($userData['merges']['GROUPINGS']) && count($userData['merges']['GROUPINGS'])) {
                    foreach ($userData['merges']['GROUPINGS'] as $group) {
                        $data->joomlamailer_groupings[$group['id']] = $group['groups'];
                    }
                }

                if (!JHtml::isRegistered('users.birthday')) {
                    JHtml::register('users.birthday', array(__CLASS__, 'birthday'));
                }
                if (!JHtml::isRegistered('users.address')) {
                    JHtml::register('users.address', array(__CLASS__, 'address'));
                }
			}
		}

		return true;
	}

    public static function subscribe($value) {
        return JText::_(($value ? 'JYES' : 'JNO'));
    }

    public static function birthday($value) {
        jimport('joomla.plugin.helper');
        $plugin = JPluginHelper::getPlugin('user', 'joomlamailer');
        $pluginParams = new JRegistry($plugin->params);
        $dateFormat = $pluginParams->get('dateFormat');
        if ($dateFormat == 'DD/MM') {
            $value = explode('/', $value);
            $value = array_reverse($value);
            $value = implode('/', $value);
        }

        return $value;
    }

    public static function address($value) {
        return (is_array($value) ? implode(', ', $value) : '-');
    }

	/**
	 * adds additional fields to the user editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data) {
		if (!($form instanceof JForm)) {
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

        if (!$this->listId) {
            if ($this->debug) {
                $this->_subject->setError('No list selected in joomlamailer user plugin config!');
                return false;
            }

            return;
        }

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration'))) {
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__ . '/profiles');
		$form->loadFile('profile', false);

        $mergeFields = $this->api->listMergeVars($this->listId);
        if (is_array($mergeFields) && count($mergeFields)) {
            $mergeFieldsConfig = $this->params->get('fields', array());

            $elements = array();
            foreach ($mergeFields as $field) {
                if (in_array($field['tag'], array('EMAIL', 'FNAME', 'LNAME', 'SIGNUPAPI'))
                    || !in_array($field['tag'], $mergeFieldsConfig) || !$field['show'] || !$field['public']) {
                    continue;
                }

                $attr = $options = '';
                switch ($field['field_type']) {
                    case 'url':
                        $type = 'url';
                        break;
                    case 'birthday':
                        $type = 'birthday';
                        $attr = 'format="' . $this->params->get('dateFormat') . '"';
                        $field['helptext'] = $this->params->get('dateFormat');
                        break;
                    case 'address':
                        $type = 'address';
                        break;
                    case 'phone':
                        $type = 'tel';
                        break;
                    case 'number':
                        $type = 'number';
                        break;
                    case 'radio':
                        $type = 'radio';
                        if (isset($field['choices'])) {
                            foreach ($field['choices'] as $choice) {
                                $options .= '<option value="' . $choice . '">' . $choice . '</option>';
                            }
                        }
                        break;
                    case 'dropdown':
                        $type = 'list';
                        if (isset($field['choices'])) {
                            foreach ($field['choices'] as $choice) {
                                $options .= '<option value="' . $choice . '">' . $choice . '</option>';
                            }
                        }
                        $attr = 'multiple="false"';
                        break;
                    default:
                        $type = 'text';
                }
                $elements[] = new SimpleXMLElement('<fieldset name="joomlamailer_merges">
                    <field name="' . $field['tag'] . '"
                        type="' . $type . '"
                        label="' . $field['name'] . '"
                        description="' . $field['helptext'] . '"
                        class=""
                        size=""
                        required="' . $field['req'] . '" ' .
                        $attr . '>' .
                        $options . '
                        </field>
                </fieldset>');
            }
            if (count($elements)) {
                $form->setFields($elements, 'joomlamailer_merges');
            }
        }

        $interests = $this->api->listInterestGroupings($this->listId);
        if (is_array($interests) && count($interests)) {
            $interestsConfig = $this->params->get('interests', array());

            $elements = array();
            foreach ($interests as $interest) {
                if (!in_array($interest['id'], $interestsConfig)) {
                    continue;
                }
                $attr = '';
                switch ($interest['form_field']) {
                    case 'dropdown':
                        $type = 'list';
                        break;
                    case 'radio':
                        $type = 'radio';
                        break;
                    case 'checkboxes':
                        $type = 'checkbox';
                        $attr = 'multiple="true"';
                        break;
                }

                $options = '';
                foreach ($interest['groups'] as $group) {
                    $options .= '<option value="' . $group['name'] . '">' . $group['name'] . '</option>';
                }

                $elements[] = new SimpleXMLElement('<fieldset name="joomlamailer_groupings">
                    <field name="' . $interest['id'] . ($interest['form_field'] == 'checkbox' ? '][' : '') . '"
                        type="' . $type . '"
                        label="' . $interest['name'] . '" ' .
                        $attr . '>' .
                        $options . '
                        </field>
                </fieldset>');
            }
            if (count($elements)) {
                $form->setFields($elements, 'joomlamailer_groupings');
            }
        }

		return true;
	}

    /**
     * Method is called before user data is stored in the database
     *
     * @param   array    $user   Holds the old user data.
     * @param   boolean  $isnew  True if a new user is stored.
     * @param   array    $data   Holds the new user data.
     *
     * @return    boolean
     */
    public function onUserBeforeSave($oldUser, $isNew, $newUser) {
        self::$oldEmail = $oldUser['email'];
    }

	/**
	 * saves user profile data
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $success true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return bool
	 */
	public function onUserAfterSave($data, $isNew, $success, $error) {
        if (!$this->listId || $success !== true) {
            return;
        }

        $option = $this->app->input->getCmd('option');
        $task = $this->app->input->getCmd('task');

        $userId = Joomla\Utilities\ArrayHelper::getValue($data, 'id', 0, 'int');
        $user = JFactory::getUser($userId);
        $email = $data['email'];

        //file_put_contents('register' . microtime(true) . '.txt', print_r($data, true) . "\n" . print_r($isNew, true)
        //  . "\n" . print_r($option, true) . "\n" . print_r($task, true) . "\n" . print_r($_POST, true));

        if (($option == 'com_users' && $task == 'activate') || ($option == 'com_comprofiler' && $task == 'confirm')
            || ($option == 'com_community' && $task == 'activate' && $data['activation'] == '')) {
            $query = $this->db->getQuery(true)
                ->select($this->db->qn(array('fname', 'lname', 'email', 'groupings', 'merges')))
                ->from($this->db->qn('#__joomailermailchimpintegration_signup'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
            try {
                $res = $this->db->setQuery($query)->loadObject();
            } catch (Exception $e) {}
            if (!$res) {
                return;
            }

            // create hidden signup date merge var if it doesn't exist
            $createSignupdateMerge = true;
            $mergeVarsApi = $this->api->listMergeVars($this->listId);
            foreach ($mergeVarsApi as $mv) {
                if ($mv['tag'] == 'SIGNUPAPI') {
                    $createSignupdateMerge = false;
                    break;
                }
            }
            if ($createSignupdateMerge){
                $this->api->listMergeVarAdd($this->listId, 'SIGNUPAPI', 'date added (API)',
                    array('field_type' => 'date', 'public' => false));
            }

            // build API data object
            $mergeVars = json_decode($res->merges, true);
            $groupings = json_decode($res->groupings, true);

            $mergeVars = array_merge(array(
                'FNAME' => $res->fname,
                'LNAME' => $res->lname,
                'INTERESTS' => '',
                'GROUPINGS' => $groupings,
                'OPTINIP' => $this->getIpAddress(),
                'SIGNUPAPI' => date('Y-m-d')
            ), $mergeVars);

            //Subscribe the user
            $emailType = '';
            $doubleOptin = $updateExisting = $replaceInterests = $sendWelcome = false;

            $this->api->listSubscribe($this->listId, $email, $mergeVars, $emailType, $doubleOptin, $updateExisting,
                $replaceInterests, $sendWelcome);

            $query = $this->db->getQuery(true)
                ->delete($this->db->qn('#__joomailermailchimpintegration_signup'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email));
            try {
                $this->db->setQuery($query)->execute();
            } catch (Exception $e) {}

            // 211 = List_InvalidOption; 215 = List_NotSubscribed
            if ($this->api->errorCode && !in_array($this->api->errorCode, array(211, 215))) {
                $this->_subject->setError("Unable to subscribe to the newsletter list!\n\tCode=" . $this->api->errorCode
                    . "\n\tMsg=" . $this->api->errorMessage . "\n");
                return false;
            } else {
                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__joomailermailchimpintegration'))
                    ->set($this->db->qn('userid') . ' = ' . $this->db->q($userId))
                    ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                    ->set($this->db->qn('listid') . ' = ' . $this->db->q($this->listId));
                try {
                    $this->db->setQuery($query)->execute();
                } catch (Exception $e) {}
            }

            return;
        }

        // process registration / profile form
        if ($option == 'com_community') {
            if (!$isNew || $task != 'registerUpdateProfile') {
                return;
            }
            $query = $this->db->getQuery(true)
                ->select($this->db->qn('id'))
                ->from($this->db->qn('#__community_fields'))
                ->where($this->db->qn('fieldcode') . ' = ' . $this->db->q('newsletter'));
            try {
                $fieldId = $this->db->setQuery($query)->loadResult();
            } catch (Exception $e) {}
            if (!$fieldId) {
                return;
            }
            $subscribe = ($this->app->input->getCmd('field' . $fieldId, false) ? 1 : 0);
            $name = $data['name'];
        } else  if ($option == 'com_comprofiler') {
            $subscribe = $this->app->input->getInt('cb_newsletter', false);
            $name = $this->app->input->getString('name');
        } else  if ($option == 'com_virtuemart') {
            $subscribe = $this->app->input->getInt('newsletter', false);
            $name = $this->app->input->getString('name');
        } else {
            $jform = $this->app->input->get('jform', array());
            if (!isset($jform['joomlamailer'])) {
                return;
            }
            $subscribe = $jform['joomlamailer']['subscribe'];
            $name = $jform['name'];
        }

        $name = explode(' ', $name);
        $fname = $name[0];
        $lname = '';
        if (count($name) > 1) {
            unset($name[0]);
            $lname = implode(' ', $name);
        }

        // Check if the user is already activated and is subscribed
        $isSubscribed = false;
        if (!$user->activation && $user->email && self::$oldEmail) {
            $userlists = $this->api->listsForEmail(self::$oldEmail);
            if ($userlists && in_array($this->listId, $userlists)) {
                $isSubscribed = true;
            }
        }

        // User wishes to subscribe/update interests
        if ($subscribe == 1) {
            // Get merge vars from API
            $mergeFields = $this->api->listMergeVars($this->listId);
            $mergeFieldsConfig = $this->params->get('fields');
            // Get interest groupings from API
            $interests = $this->api->listInterestGroupings($this->listId);
            $interestsConfig = $this->params->get('interests');

            $merges = $groupings = array();

            if ($option == 'com_users') {
                if ($mergeFields && $mergeFieldsConfig) {
                    foreach ($mergeFields as $field) {
                        if (in_array($field['tag'], array('EMAIL', 'FNAME', 'LNAME', 'SIGNUPAPI'))) {
                            continue;
                        }
                        $value = @$jform['joomlamailer_merges'][$field['tag']];
                        if ($value) {
                            if ($field['field_type'] == 'birthday') {
                                $value = $value['month'] . '/' . $value['day'];
                            }
                            $merges[$field['tag']] = $value;
                        }
                    }
                }

                if ($interests && $interestsConfig) {
                    foreach ($interests as $interest) {
                        if (!in_array($interest['id'], $interestsConfig)) {
                            continue;
                        }
                        $postData = @$jform['joomlamailer_groupings'][$interest['id']];
                        if ($postData) {
                            $groups = array();
                            if (is_array($postData)) {
                                foreach ($postData as $selected) {
                                    foreach ($interest['groups'] as $group) {
                                        if ($selected == $group['bit']) {
                                            $groups[] = $group['name'];
                                            continue 2;
                                        }
                                    }
                                }
                            } else {
                                $groups[] = $postData;
                            }

                            $groupings[$interest['name']] = array(
                                'name'   => $interest['name'],
                                'id'     => $interest['id'],
                                'groups' => implode(',', $groups)
                            );
                        }
                    }
                }
            } else if (in_array($option, array('com_comprofiler', 'com_community', 'com_virtuemart'))) {
                // Get custom fields
                $query = $this->db->getQuery(true)
                    ->select($this->db->qn(array('dbfield', 'grouping_id', 'type', 'framework'), array('dbfield', 'gid', 'type', 'framework')))
                    ->from($this->db->qn('#__joomailermailchimpintegration_custom_fields'))
                    ->where($this->db->qn('listID') . ' = ' . $this->db->q($this->listId));
                $this->db->setQuery($query);
                $customFields = $this->db->loadAssocList();

                if ($customFields) {
                    // loop over merge vars
                    if ($mergeFields) {
                        foreach ($mergeFields as $f) {
                            foreach ($customFields as $cf) {
                                if ($cf['type'] == 'field') {
                                    if($f['tag'] == strtoupper($cf['gid'])) {
                                        if (($option == 'com_comprofiler' && $cf['framework'] == 'CB')
                                            || ($option == 'com_virtuemart' && $cf['framework'] == 'VM')) {
                                            if ($f['field_type'] == 'date') {
                                                if ($option == 'com_virtuemart') {
                                                    $valDay = $this->app->input->getString('birthday_selector_day');
                                                    $valMonth = $this->app->input->getString('birthday_selector_month');
                                                    $valYear = $this->app->input->getString('birthday_selector_year');
                                                    $val = $valMonth . '/' . $valDay . '/' . $valYear;
                                                } else {
                                                    $val = $this->app->input->getString($cf['dbfield']);
                                                }
                                                $merges[$f['tag']] = substr($val, 3, 2) . '-' . substr($val, 0, 2) .
                                                    '-' . substr($val, 6, 4);
                                            } else {
                                                $val = $this->app->input->getString($cf['dbfield']);
                                                $merges[$f['tag']] = $val;
                                            }
                                        } else {
                                            if ($this->app->input->getString('field' . $cf['dbfield'], 0)) {
                                                $val = $this->app->input->getString('field' . $cf['dbfield']);
                                                if ($f['field_type'] == 'date') {
                                                    $merges[$f['tag']] = $val[2] . '-' . $val[1] . '-' . $val[0];
                                                } else {
                                                    $merges[$f['tag']] = $val;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // loop over groupings
                    if ($interests) {
                        foreach ($interests as $i) {
                            foreach ($customFields as $cf) {
                                if ($cf['type'] == 'group') {
                                    if ($i['id'] == $cf['gid']) {
                                        $groups = array();
                                        if (($option == 'com_comprofiler' && $cf['framework'] == 'CB')
                                            || ($option == 'com_virtuemart' && $cf['framework'] == 'VM')){
                                            $field = $this->app->input->getString($cf['dbfield']);
                                        } else {
                                            if ($this->app->input->getString('field' . $cf['dbfield'], 0)) {
                                                $field = $this->app->input->getString('field' . $cf['dbfield']);
                                            }
                                        }
                                        if (isset($field) && is_array($field)) {
                                            foreach ($field as $g) {
                                                foreach ($i['groups'] as $gg) {
                                                    if ($g == $gg['name']) {
                                                        $groups[] = $gg['name'];
                                                    }
                                                }
                                            }
                                        } else {
                                            foreach ($i['groups'] as $gg) {
                                                if (isset($field) && $field == $gg['name']) {
                                                    $groups[] = $gg['name'];
                                                }
                                            }
                                        }

                                        $groupings[$i['name']] = array(
                                            'name'   => $i['name'],
                                            'id'     => $i['id'],
                                            'groups' => implode(',', $groups)
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }


            // If this is a new user then just store details now and subscribe the user later at activation
            if ($user->activation || $data['activation']) {
                $query = $this->db->getQuery(true)
                    ->insert($this->db->qn('#__joomailermailchimpintegration_signup'))
                    ->set(array(
                        $this->db->qn('fname') . ' = ' . $this->db->q($fname),
                        $this->db->qn('lname') . ' = ' . $this->db->q($lname),
                        $this->db->qn('email') . ' = ' . $this->db->q($email),
                        $this->db->qn('merges') . ' = ' . $this->db->q(json_encode($merges)),
                        $this->db->qn('groupings') . ' = ' . $this->db->q(json_encode($groupings))
                    ));
                try {
                    $this->db->setQuery($query)->execute();
                } catch (Exception $e) {}

            } else if ($task != 'saveregisters') {
                $mergeVars = array(
                    'FNAME' => $fname,
                    'LNAME' => $lname,
                    'INTERESTS' => '',
                    'GROUPINGS' => $groupings
                );

                //Get the users ip address unless the admin is saving his profile in backend
                if ($this->app->isSite()) {
                    $mergeVars['OPTINIP'] = $this->getIpAddress();
                }

                $mergeVars = array_merge($mergeVars, $merges);
                $emailType = '';
                $doubleOptin = $updateExisting = $replaceInterests = $sendWelcome = false;

                if ($isSubscribed === false) {
                    // subscribe the user
                    $this->api->listSubscribe($this->listId, $email, $mergeVars, $emailType, $doubleOptin,
                        $updateExisting, $replaceInterests, $sendWelcome);
                    $query = $this->db->getQuery(true)
                        ->insert($this->db->qn('#__joomailermailchimpintegration'))
                        ->set(array(
                            $this->db->qn('userid') . ' = ' . $this->db->q($user->id),
                            $this->db->qn('email') . ' = ' . $this->db->q($email),
                            $this->db->qn('listid') . ' = ' . $this->db->q($this->listId)
                        ));
                    try {
                        $this->db->setQuery($query)->execute();
                    } catch (Exception $e) {}
                } else {
                    // update the users subscription
                    if ($email != self::$oldEmail) {
                        // update local database entry
                        $query = $this->db->getQuery(true)
                            ->update($this->db->qn('#__joomailermailchimpintegration'))
                            ->set($this->db->qn('email') . ' = ' . $this->db->q($email))
                            ->where($this->db->qn('email') . ' = ' . $this->db->q(self::$oldEmail))
                            ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->listId));
                        try {
                            $this->db->setQuery($query)->execute();
                        } catch (Exception $e) {}

                        // add new email address to merge vars array
                        $mergeVars['EMAIL'] = $email;
                    }

                    $this->api->listUpdateMember($this->listId, self::$oldEmail, $mergeVars, '', true);
                }
            }

        // user wishes to unsubscribe
        } else if (!$subscribe && $isSubscribed) {
            $this->api->listUnsubscribe($this->listId, $email, false, false, false);
            // remove local database entry
            $query = $this->db->getQuery(true)
                ->delete($this->db->qn('#__joomailermailchimpintegration'))
                ->where($this->db->qn('email') . ' = ' . $this->db->q($email))
                ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->listId));
            try {
                $this->db->setQuery($query)->execute();
            } catch (Exception $e) {}
        }

        if ($this->api->errorCode && in_array($this->api->errorCode, array(211, 215)) === false) {
            $this->_subject->setError("Unable to load listSubscribe()!\n\tCode=" . $this->api->errorCode
                . "\n\tMsg=" . $this->api->errorMessage . "\n");
            return false;
        }

		return true;
	}

    // unsubscribe the user when his account is deleted and if this option is set in the plugin config
    public function onUserAfterDelete($user, $success, $msg) {
        $userId = JArrayHelper::getValue($user, 'id', 0, 'int');
        $unsubscribe = $this->params->get('unsubscribe', 0);

        if (!$success || !$userId || !$this->listId || !$unsubscribe) {
            return;
        }

        // unsubscribe the user
        $this->api->listUnsubscribe($this->listId, $user['email'], false, false, false);

        $this->db = JFactory::getDBO();
        $query = $this->db->getQuery(true)
            ->delete($this->db->qn('#__joomailermailchimpintegration'))
            ->where($this->db->qn('email') . ' = ' . $this->db->q($user['email']))
            ->where($this->db->qn('listid') . ' = ' . $this->db->q($this->listId));
        try {
            $this->db->setQuery($query)->execute();
        } catch (Exception $e) {}
    }

    private function getApiInstance() {
        if (!PlgUserJoomlamailer::$MC) {
            $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
            $MCapi = $params->get('params.MCapi');
            PlgUserJoomlamailer::$MC = new joomlamailerMCAPI($MCapi);
        }

        return PlgUserJoomlamailer::$MC;
    }

    private function getIpAddress() {
        $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return '';
    }
}
