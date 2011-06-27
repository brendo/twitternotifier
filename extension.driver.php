<?php

require_once TOOLKIT . '/class.sectionmanager.php';
require_once TOOLKIT . '/class.entrymanager.php';
require_once TOOLKIT . '/class.authormanager.php';

class Extension_TwitterNotifier extends Extension
{
	public function about()
	{
		return array
		(
			'name'         => 'Twitter Notifier',
			'version'      => '0.1alpha',
			'release-date' => '2011-06-05',
			'author'       => array(
				'name'    => 'John Porter',
				'website' => 'http://designermonkey.co.uk/',
				'email'   => 'contact@designermonkey.co.uk'
			),
			'description' => 'Notify Twitter when you create an entry.'
		);
	}

	public function uninstall()
	{
		return Symphony::Database()->query("
			DROP TABLE `tbl_authors_twitter_accounts`
		");
	}

	public function install()
	{
		return Symphony::Database()->query("
			CREATE TABLE IF NOT EXISTS `tbl_authors_twitter_accounts` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`account` varchar(100) NOT NULL,
				`oauth_token` text,
				`oauth_secret` text,
				`sections` int(10) unsigned NOT NULL,
				`authors` int(10) unsigned NOT NULL,
				`url` varchar(250) NOT NULL,
				`date_last_sent` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
				`path` varchar(250) NOT NULL,
				`status` varchar(250) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM;
		");
	}

	public function getSubscribedDelegates()
	{
		return array(
			array(
				'page'		=> '/publish/new/',
				'delegate'	=> 'EntryPostCreate',
				'callback'	=> 'sendTwitterNotification'
			),
			array(
				'page' 		=> '/backend/',
				'delegate'	=> 'InitaliseAdminPageHead',
				'callback'	=> 'initialiseAdminPageHead'
			),
			array(
				'page'		=> '/system/preferences/',
				'delegate' 	=> 'AddCustomPreferenceFieldsets',
				'callback' 	=> 'appendToPreferences'
			),
			array(
				'page' => '/system/preferences/',
				'delegate' => 'Save',
				'callback' => 'savePreferences'
			),
			array(
				'page' => '/system/preferences/success/',
				'delegate' => 'Save',
				'callback' => 'savePreferences'
			)
		);
	}

	public function fetchNavigation()
	{
		return array(
			array(
				'location' => 'System',
				'name' => 'Twitter Accounts',
				'link' => '/accounts/'
				)
		);
	}

	public function initialiseAdminPageHead($context)
	{
			$page = $context['parent']->Page;
			if($page instanceof contentExtensionTwitterNotifierAccounts){
				$page->addScriptToHead(URL . '/extensions/twitternotifier/assets/jquery.oauthpopup.js', null, false);
			}
	}

	public function getConsumerKey()
	{
		return Symphony::Configuration()->get('consumer-key', 'twitter-notifier');
	}

	public function getConsumerSecret()
	{
		return Symphony::Configuration()->get('consumer-secret', 'twitter-notifier');
	}

	private function _checkConsumerDetails()
	{
		if(Symphony::Configuration()->get('consumer-secret', 'twitter-notifier') && Symphony::Configuration()->get('consumer-key', 'twitter-notifier'))
		{
			return true;
		}
		return false;
	}

	public function savePreferences($context)
	{
		if(empty($context['settings']['twitter-notifier']['consumer-key']) || empty($context['settings']['twitter-notifier']['consumer-secret']))
		{
			if($this->_checkConsumerDetails() == true && $context['settings']['twitter-notifier']['remove-details'] == 'yes')
			{
				$context['settings']['twitter-notifier']['consumer-key'] = $this->getConsumerKey();
				$context['settings']['twitter-notifier']['consumer-secret'] = $this->getConsumerSecret();
			}
		}
	}

	public function appendToPreferences($context)
	{
		$fieldset = new XMLElement('fieldset');
		$fieldset->setAttribute('class', 'settings');
		$fieldset->appendChild(new XMLElement('legend', 'Twitter Notifier'));

		$status = new XMLElement('div');

		if($this->_checkConsumerDetails() == true)
		{
			$status->appendChild(new XMLElement('p', 'Your Consumer details are saved in the preferences, to change them, please re-enter them here.'));
		}
		else
		{
			// Add info
			$fieldset->appendChild(new XMLElement('p','An <a href="http://dev.twitter.com/login" title="Login at Twitter Developers site">application will need registering</a> with a Twitter account to get the details required here.'));
			$status->setAttribute('class', 'invalid');
			$p = new XMLElement('p', 'Your Consumer details are not saved in the preferences enter them to save.');
			$p->setAttribute('style', 'padding-top:0.75em');
			$status->appendChild($p);
		}
		$fieldset->appendChild($status);

		$div = new XMLElement('div');
		$div->setAttribute('class','group');

		// Add Consumer Key field
		$label = Widget::Label('Consumer Key');
		$label->appendChild(Widget::Input('settings[twitter-notifier][consumer-key]', null, 'password'));
		$div->appendChild($label);
		// Add Consumer Secret field
		$label = Widget::Label('Consumer Secret');
		$label->appendChild(Widget::Input('settings[twitter-notifier][consumer-secret]', null, 'password'));
		$div->appendChild($label);
		$fieldset->appendChild($div);
		// Add sub help
		$fieldset->appendChild(new XMLElement('p','Your Consumer details are required to allow Author accounts access to notify Twitter.', array('class' => 'help')));

		// Need to build the checkbox for removing settings.

		$context['wrapper']->appendChild($fieldset);
	}


	/**
	 * Sends Twitter Notification
	 * NOT COMPLETED YET!!
	 *
	 */
	public function sendTwitterNotification($context)
	{
		$xpath  = new DOMXPath($this->getEntry($context['entry']->get('id')));

		$accounts = Symphony::Database()->fetch("
			SELECT
				id,
				account,
				password,
				url,
				path
			FROM tbl_twitter_accounts
			WHERE section = " . (int) $context['section']->_data['id'] ."
		");

		foreach($accounts as $account)
		{
 			$Results = $xpath->query($account['path']);

			if($identifier = trim($results->item(0)->nodeValue))
			{


				/* $url_to_shorten = str_replace('{entry_id}', $identifier, $account['url']);

				// Shorten the URL to the new entry:

				$ch = curl_init("http://is.gd/api.php?longurl={$url_to_shorten}");

				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_NOBODY, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

				$link_to_entry = curl_exec($ch);
				curl_close($ch);
				*/

				$url_to_shorten = str_replace('{entry_id}', $identifier, $account['url']);

				// We use our own URL-Shortener

				// settings for byspd.de

					$byspd_user = 'bayernspd';
					$byspd_pass = 'CXek&94(L';
				// 	$keyword = $_REQUEST['key'];		// optional keyword
					$format = 'simple';			// output format: 'json', 'xml' or 'simple'
					$api_url = "http://byspd.de/yourls-api.php";


				// Init the CURL session
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $api_url);
					curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
					curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
					curl_setopt($ch, CURLOPT_POSTFIELDS, array(     // Data to POST
						'url'      => $url_to_shorten,
				//		'keyword'  => $keyword,
						'format'   => $format,
						'action'   => 'shorturl',
						'username' => $byspd_user,
						'password' => $byspd_pass
					));


				/*
				// Shorten the URL to the new entry:
				$ch = curl_init("http://is.gd/api.php?longurl={$url_to_shorten}");

				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_NOBODY, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				*/

				$link_to_entry = curl_exec($ch);
				curl_close($ch);


				// Notify Twitter:

				$headers = array
				(
					'Expect: ',
					'X-Twitter-Client: Twitter Notifier',
					'X-Twitter-Client-Version: 1.0.0 Alpha',
					'X-Twitter-Client-URL: http://www.thedrunkenepic.com/'
				);

				$message = 'Neuer Beitrag: ' . $this->_Parent->Configuration->get('sitename', 'general') .': '. $url_to_shorten .'' ;

				$url = 'http://twitter.com/statuses/update.xml?status=' . urlencode(stripslashes(urldecode($message)));

				$ch = curl_init($url);

				curl_setopt($ch, CURLOPT_POSTFIELDS, array('source' => 'Symphony CMS'));
				curl_setopt($ch, CURLOPT_USERPWD, "{$account['account']}:{$account['password']}");
				curl_setopt($ch, CURLOPT_VERBOSE, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($ch, CURLOPT_POST, 1);

//				curl_setopt($ch, CURLOPT_POSTFIELDS, array('source' => 'Symphony CMS'));
//				curl_setopt($ch, CURLOPT_USERPWD, "{$account['account']}:{$account['password']}");
//				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//				curl_setopt($ch, CURLOPT_VERBOSE, true);
//				curl_setopt($ch, CURLOPT_NOBODY, false);
//				curl_setopt($ch, CURLOPT_HEADER, false);
//				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$response = curl_exec($ch);
				curl_close($ch);

				$this->_Parent->Database->query("UPDATE {$prefix}twitter_accounts SET date_last_sent = NOW() WHERE id = {$account['id']}");
			}
		}

		return true;
	}

	protected function getEntry($id)
	{
		$entry = new XMLElement('entry');
		$this->getEntryData($id, $entry);

		$xml = new DOMDocument();
		$xml->loadXML($entry->generate(true));

		return $Dom;
	}

	protected function getEntryData($id, $xml)
	{
		$EntryManager = new EntryManager($this->_Parent);
		$EntryManager->setFetchSorting('id', 'ASC');

		$entries = $EntryManager->fetch($id);
		$entry = @$entries[0];

		$xml->setAttribute('id', $id);

		foreach($entry->fetchAllAssociatedEntryCounts() as $section => $count)
		{
			$handle = Symphony::Database()->fetchVar('handle', 0, "SELECT handle FROM tbl_sections WHERE id = '{$section}' LIMIT 1");

			$xml->setAttribute($handle, (string) $count);
		}

		foreach($entry->getData() as $field_id => $values)
		{
			$field =& $EntryManager->fieldManager->fetch($field_id);
			$field->appendFormattedElement($xml, $values, false, null);
		}
	}
}