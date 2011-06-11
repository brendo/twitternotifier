	<?php
	
	require_once TOOLKIT . '/class.administrationpage.php';
	
	class contentExtensionTwitterNotifierAccounts extends AdministrationPage
	{
		protected $_prefix = null;
		protected $_handle = '';
		protected $_status = '';
		protected $_driver = null;
		protected $_uri = null;
		protected $_id = null;
		protected $_account = null;
		protected $_pagination = null;
		protected $_table_column = 'account';
		protected $_table_columns = array();
		protected $_table_direction = 'asc';
	
		public function __construct(&$parent)
		{
			parent::__construct($parent);
	
			$this->_uri = URL . '/symphony/extension/twitternotifier';
			$this->_driver = Symphony::ExtensionManager()->create('twitternotifier');
		}
		
		public function build($context)
		{
			//var_dump($context);
			
			if(isset($context[0]))
			{
				switch($context[0])
				{
					case 'edit':
						$this->__prepareEdit($context);
						break;
					
					case 'new':
						$this->__prepareEdit($context);
						break;
				}
			}
			parent::build($context);
		}
		
		public function __viewNew()
		{
			$this->__viewEdit();
		}
		
		public function __prepareEdit($context)
		{
			session_start();
			
			$id = $context[1];
			
			$this->_account = Symphony::Database()->fetch("
				SELECT * from `tbl_authors_twitter_accounts` WHERE `id` = {$id}
			");
			
			/**
			 * Create the basic session variables if they don't exist
			 * This means the authentication process hasn't started yet.
			 */
			if(!isset($_SESSION['twitter_notifier']) || empty($_SESSION['twitter_notifier']))
			{
				$_SESSION['twitter_notifier'] = array(
					'consumer_key'			=> $this->_driver->get('consumer_key'),
					'consumer_secret'		=> $this->_driver->get('consumer_secret'),
					'oauth_callback'		=> $this->_driver->get('oauth_callback'),
					'oauth_token'			=> "",
					'oauth_token_secret'	=> "",
					'oauth_verifier'		=> "",
					'twitter_user_id'		=> "",
					'twitter_screen_name'	=> ""
				);
			}
		}
		
		public function __viewEdit($context)
		{
			$this->setPageType('form');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Twitter Accounts'), __('Symphony'))));
			$this->appendSubheading(__('Add a Twitter Account'));
			
			if($this->_account || $_POST['fields'])
			{
				$fields = $this->_account[0];
				$fields['sections'] = explode(',', $fields['sections']);
				
				if($_POST['fields'])
				{
					foreach($_POST['fields'] as $key => $value)
					{
						if(!$value || empty($value)){
							$errors[$key] = true;
						}
						else
						{
							$fields[$key] = $value;
						}
					}
				}
			}
			else
			{
				$fields = null;
			}
			
			if($_POST)
			{
				if(false == trim($_POST['fields']['account']))
				{
					$errors['account'] = 'You need to specify your !';
				}
	
				if(false == trim($_POST['fields']['password']))
				{
					$errors['password'] = 'Gotta have your password to access your Twitter account!';
				}
	
				if(false == trim($_POST['fields']['url']))
				{
					$errors['url'] = 'Need a url to point to your new content!';
				}
	
				if(false == preg_match('#\{entry_id\}#i', $_POST['fields']['url']))
				{
					$errors['url'] = 'You need to use {entry_id} to identify your matched content!';
				}
	
				if(false == trim($_POST['fields']['path']))
				{
					$errors['path'] = 'How am I supposed to find my way to your content without a path?';
				}
	
				if(false == $errors)
				{
					$this->_Parent->Database->insert($_POST['fields'], "{$this->_prefix}twitter_accounts");
	
					redirect(URL . '/symphony/extension/twitternotifier/accounts/');
				}
			}
	
			//var_dump($this->_account, $fields);die;
			
	
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Account Details'));

			if($this->_account['id'])
			{
				$fieldset->appendChild(Widget::Input("fields[id]", $fields['id'], 'hidden'));
			}
			
			/**
			 * Twitter Account Name
			 */
			$label = Widget::Label(__('Account'));
			$label->appendChild(Widget::Input('fields[account]', General::sanitize($fields['account'])));
			if(isset($errors['account']))
			{
				$label = Widget::wrapFormElementWithError($label, __('A Twitter account name is required.'));
			}
			$fieldset->appendChild($label);
	
			/**
			 * Twitter Account Password
			 */
			$label = Widget::Label(__('Password'));
			$label->appendChild(Widget::Input('fields[password]', General::sanitize($fields['password'])));
			if(isset($errors['password']))
			{
				$label = Widget::wrapFormElementWithError($label, __('A Twitter account password is required.'));
			}
			$fieldset->appendChild($label);
	
			$this->Form->appendChild($fieldset);
			
			
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Posting Details'));
	
			/**
			 * Symphony Sections for notifications
			 *
			 * Need to figure out how to select the current sections, and how to do a multiselect
			 */
			$label = Widget::Label(__('Sections'));
	
			$SectionManager = new SectionManager($this->_Parent);
	
			$options = array();
	
			foreach($SectionManager->fetch(NULL, 'ASC', 'sortorder') as $section)
			{
				$options[] = array($section->get('id'), $section->get('id'), $section->get('name'));
			}
	
			$label->appendChild(Widget::Select('fields[sections]', $options, array('id' => 'section')));
	
	
			$p = new XMLElement('p', __('Select the section to monitor.'));
			$p->setAttribute('class', 'help');
	
			if(isset($errors['sections']))
			{
				$label = Widget::wrapFormElementWithError($label, $this->_errors['account']);
			}
	
			$fieldset->appendChild($label);
			$fieldset->appendChild($p);
	
	
			$label = Widget::Label('Path');
			$label->appendChild(Widget::Input('fields[path]', General::sanitize($fields['path'])));
	
			$p = new XMLElement('p', __('Use XPath here to find the identifier you use to access your content. <br />Example: //root/entry/name[@handle]'));
			$p->setAttribute('class', 'help');
	
			if(isset($errors['path']))
			{
				$label = Widget::wrapFormElementWithError($label, $errors['path']);
			}
	
			$fieldset->appendChild($label);
			$fieldset->appendChild($p);
	
	
			$label = Widget::Label('Url');
			$label->appendChild(Widget::Input('fields[url]', General::sanitize($fields['url'])));
	
			$p = new XMLElement('p', __('Enter the full URL to access your section content. Use <strong>{entry_id}</strong> where you would place the unique identifier for your content.<br />Example: http://www.thedrunkenepic.com/articles/<strong>{entry_id}</strong>/'));
			$p->setAttribute('class', 'help');
	
			if(isset($errors['url']))
			{
				$label = Widget::wrapFormElementWithError($label, $errors['url']);
			}
	
	
			$fieldset->appendChild($label);
			$fieldset->appendChild($p);
	
			$this->Form->appendChild($fieldset);
	
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input('action[save]', __('Add Twitter Account'), 'submit', array('accesskey' => 's')));
	
			$this->Form->appendChild($div);
	
			return true;
		}
		public function __actionIndex()
		{
			$checked = (
				(isset($_POST['items']) && is_array($_POST['items']))
					? array_keys($_POST['items'])
					: null
			);
	
			if(is_array($checked) && !empty($checked))
			{
				switch ($_POST['with-selected'])
				{
					case 'delete':
						foreach ($checked as $id)
						{
							Symphony::Database()->query("
								DELETE FROM `tbl_authors_twitter_accounts` WHERE `id` = {$id}
							");
						}
	
						redirect("{$this->_uri}/accounts/");
						break;
					
					case 'status':
						foreach($checked as $id)
						{
							$account = Symphony::Database()->fetch("
								SELECT `status` FROM `tbl_authors_twitter_accounts` WHERE `id` = {$id}");
							$status = ($account[0]['status'] == 'Active') ? 'Inactive' : 'Active';
							Symphony::Database()->query("
								UPDATE `tbl_authors_twitter_accounts` SET `status` = '{$status}' WHERE `id` = {$id}
							");
						}
				}
			}
		}

		public function __viewIndex()
		{
			// List the table columns
			$this->_table_columns = array(
				'account'	=> array(__('Account'), true),
				'author'	=> array(__('Author'), true),
				'sections'	=> array(__('Sections'), true),
				'last-sent'	=> array(__('Last Sent'), true),
				'status'	=> array(__('Status'), true)
			);
			
			// Begin pagination
			if(isset($_GET['sort']) && $_GET['sort'] && $this->_table_columns[$_GET['sort']][1])
			{
				$this->_table_columns = $_GET['sort'];
			}
			
			if (isset($_GET['order']) && $_GET['order'] == 'desc') {
				$this->_table_direction = 'desc';
			}

			$this->_pagination = (object)array(
				'page'		=> (
					isset($_GET['pg']) && $_GET['pg'] > 1
						? $_GET['pg']
						: 1
				),
				'length'	=> Symphony::Engine()->Configuration->get('pagination_maximum_rows', 'symphony')
			);

			$accounts = Symphony::Database()->fetch("
				SELECT
					id,
					account,
					sections,
					authors,
					date_last_sent,
					status
				FROM tbl_authors_twitter_accounts
			");

			// Calculate pagination:
			$this->_pagination->start = max(1, (($page - 1) * 17));
			$this->_pagination->end = (
				$this->_pagination->start == 1
				? $this->_pagination->length
				: $start + count($this->_importers)
			);
			$this->_pagination->total = count($accounts);
			$this->_pagination->pages = ceil(
				$this->_pagination->total / $this->_pagination->length
			);			


			$this->setPageType('table');
			$this->setTitle(__('Symphony') . ' &ndash; ' . __('Twitter Accounts'));
			
			$this->appendSubheading(__('Twitter Accounts'), Widget::Anchor(
				__('Create New'), "{$this->_uri}/accounts/new/",
				__('Add a Twitter Account'), 'create button'
			));
	
			$thead = array();
			$tbody = array();
			$sections = array();
			$authors = array();
			
			// Columns with sorting
			foreach($this->_table_columns as $column => $values)
			{
				if($values[1])
				{
					if($column == $this->_table_column)
					{
						if($this->_table_direction == 'desc')
						{
							$direction = 'asc';
							$label = 'ascending';
						}
						else
						{
							$direction = 'desc';
							$label = 'descending';
						}
					}
					else
					{
						$direction = 'asc';
						$label = 'ascending';
					}
					
					$link = $this->generateLink(array(
						'sort'	=> $column,
						'order'	=> $direction
					));

					$anchor = Widget::Anchor(
						$values[0], $link,
						__("Sort by {$label} " . strtolower($values[0]))
					);
					
					if ($column == $this->_table_column) {
						$anchor->setAttribute('class', 'active');
					}
					
					$thead[] = array($anchor, 'col');
				}
				else
				{
					$thead[] = array($values[0], 'col');
				}
			}
			
			$SectionManager = new SectionManager($this->_Parent);
	
			foreach($SectionManager->fetch(NULL, 'ASC', 'sortorder') as $section)
			{
				$sections[$section->get('id')] = $section->get('name');
			}
			
			$AuthorManager = new AuthorManager($this->_Parent);
			
			foreach($AuthorManager->fetch() as $author)
			{
				$authors[$author->get('id')] = $author->get('first_name')." ".$author->get('last_name');
			}
		
			if(!is_array($accounts) || empty($accounts))
			{
				$tbody = array(
					Widget::TableRow(array(
						Widget::TableData(
							__('None Found.'), 'inactive', null, count($thead)
						)
					))
				);
			}
			else
			{
				foreach($accounts as $account)
				{
					// Column 1
					$col_account = Widget::TableData(Widget::Anchor(
						$account['account'],
						"{$this->_uri}/accounts/edit/{$account['id']}/"
					));
					$col_account->appendChild(Widget::Input(
						"items[{$account['id']}]",
						null, 'checkbox'
					));
					
					// Column 2
					$col_author = Widget::TableData($authors[$account['authors']]);
					
					// Column 3
					$col_date = Widget::TableData(DateTimeObj::get(
						__SYM_DATETIME_FORMAT__, strtotime($account['date_last_sent'])
					));
					
					
					$account_sections = '';
					$section_ids = (is_array($account['sections'])) ? explode(',',$account('sections')): array($account['sections']);
					foreach($section_ids as $section_id)
					{
						$account_sections .= $sections[$section_id].', ';
					}
					$col_sections = Widget::TableData(trim($account_sections,", "));
					
					$col_status = Widget::TableData($account['status']);
	
					$tbody[] = Widget::TableRow(
						array(
							$col_account,
							$col_author,
							$col_sections,
							$col_date,
							$col_status
						),
						null
					);
				}
			}
	
			$table = Widget::Table
			(
				Widget::TableHead($thead), null,
				Widget::TableBody($tbody)
			);
			$table->setAttribute('class', 'selectable');
	
			$this->Form->appendChild($table);
	
			$actions = new XMLElement('div');
			$actions->setAttribute('class', 'actions');
	
			$options = array(
				array(null, false, 'With Selected...'),
				array('delete', false, 'Delete', 'confirm'),
				array('status', false, 'Change Status')
			);
	
			$actions->appendChild(Widget::Select('with-selected', $options));
			$actions->appendChild(Widget::Input('action[apply]', 'Apply', 'submit'));
	
			$this->Form->appendChild($actions);
	
			// Pagination:
			if ($this->_pagination->pages > 1) {
				$ul = new XMLElement('ul');
				$ul->setAttribute('class', 'page');
				
				// First:
				$li = new XMLElement('li');
				$li->setValue(__('First'));
				
				if ($this->_pagination->page > 1) {
					$li->setValue(
						Widget::Anchor(__('First'), $this->generateLink(array(
							'pg' => 1
						)))->generate()
					);
				}
				
				$ul->appendChild($li);
				
				// Previous:
				$li = new XMLElement('li');
				$li->setValue(__('&larr; Previous'));
				
				if ($this->_pagination->page > 1) {
					$li->setValue(
						Widget::Anchor(__('&larr; Previous'), $this->generateLink(array(
							'pg' => $this->_pagination->page - 1
						)))->generate()
					);
				}
				
				$ul->appendChild($li);
				
				// Summary:
				$li = new XMLElement('li', __('Page %s of %s', array(
					$this->_pagination->page,
					max($this->_pagination->page, $this->_pagination->pages)
				)));
				$li->setAttribute('title', __('Viewing %s - %s of %s entries', array(
					$this->_pagination->start,
					$this->_pagination->end,
					$this->_pagination->total
				)));
				$ul->appendChild($li);
				
				// Next:
				$li = new XMLElement('li');
				$li->setValue(__('Next &rarr;'));
				
				if ($this->_pagination->page < $this->_pagination->pages) {
					$li->setValue(
						Widget::Anchor(__('Next &rarr;'), $this->generateLink(array(
							'pg' => $this->_pagination->page + 1
						)))->generate()
					);
				}
				
				$ul->appendChild($li);
				
				// Last:
				$li = new XMLElement('li');
				$li->setValue(__('Last'));
				
				if ($this->_pagination->page < $this->_pagination->pages) {
					$li->setValue(
						Widget::Anchor(__('Last'), $this->generateLink(array(
							'pg' => $this->_pagination->pages
						)))->generate()
					);
				}
				
				$ul->appendChild($li);
				$this->Form->appendChild($ul);
			}
		}
		public function generateLink($values) {
			$values = array_merge(array(
				'pg'	=> $this->_pagination->page,
				'sort'	=> $this->_table_column,
				'order'	=> $this->_table_direction
			), $values);
			
			$count = 0;
			$link = Symphony::Engine()->getCurrentPageURL();
			
			foreach ($values as $key => $value) {
				if ($count++ == 0) {
					$link .= '?';
				}
				
				else {
					$link .= '&amp;';
				}
				
				$link .= "{$key}={$value}";
			}
			
			return $link;
		}
	
	}
	
?>