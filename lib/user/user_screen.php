<?php

class pz_user_screen {

	public $user;	
	
	function __construct($user) 
	{
		$this->user = $user;
	}
	
	
	public function getTableView($p = array())
	{
		$edit_link = pz::url($p["mediaview"],$p["controll"],$p["function"],array("user_id"=>$this->user->getId(),"mode"=>"edit_user"));
		$del_link = pz::url($p["mediaview"],$p["controll"],$p["function"],array("user_id"=>$this->user->getId(),"mode"=>"delete_user"));
		
		$return = '
              <tr class="user-'.$this->user->getId().' user-screen-tableview">
                <td class="img1"><img src="'.$this->user->getInlineImage().'" width="40" height="40" alt="" /></td>';
                
		if(pz::getUser()->isAdmin()) {
        	$return .= '<td><a href="javascript:pz_loadPage(\'user_form\',\''.$edit_link.'\')"><span class="title">'.$this->user->getName().'</span></a></td>';
		}else
		{
        	$return .= '<td><span class="title">'.$this->user->getName().'</span></td>';
		}

		if($this->user->isActive())  
			$return .= '<td><span class="status status-1">'.pz_i18n::msg("yes").'</span></td>';
		else 
			$return .= '<td><span class="status status-2">'.pz_i18n::msg("no").'</span></td>';

		if($this->user->isAdmin())  
			$return .= '<td><span class="status status-1">'.pz_i18n::msg("yes").'</span></td>';
		else 
			$return .= '<td><span class="status status-2">'.pz_i18n::msg("no").'</span></td>';


		if(pz::getUser()->isAdmin())
		{
		
      if($this->user->hasPerm('webdav')) {
        $return .= '<td><span class="status status-1">'.pz_i18n::msg("yes").'</span></td>';
      }else {
        $return .= '<td><span class="status status-2">'.pz_i18n::msg("no").'</span></td>';
      }

      if($this->user->hasPerm('carddav')) {
        $return .= '<td><span class="status status-1">'.pz_i18n::msg("yes").'</span></td>';
      }else {
        $return .= '<td><span class="status status-2">'.pz_i18n::msg("no").'</span></td>';
      }
		  
      if($this->user->hasPerm('projectsadmin')) {
        $return .= '<td><span class="status status-1">'.pz_i18n::msg("yes").'</span></td>';
      }else {
        $return .= '<td><span class="status status-2">'.pz_i18n::msg("no").'</span></td>';
      }
		
		  $last_login = "-";
      if($this->user->getValue("last_login") != "")
      {
  		  $d = DateTime::createFromFormat('Y-m-d H:i:s', $this->user->getValue("last_login"), pz::getDateTimeZone());
        $last_login = ' '.strftime(pz_i18n::msg("show_datetime_normal"),pz_user::getDateTime($d)->format("U")).'';
      }
      
      /*
      $created = "-";
      if($this->user->getValue("created") != "")
      {
		    $d = DateTime::createFromFormat('Y-m-d H:i:s', $this->user->getValue("created"), pz::getDateTimeZone());
        $created = ' '.strftime(pz_i18n::msg("show_datetime_normal"),pz_user::getDateTime($d)->format("U")).'';
		  }
		  */
		
		  $return .= '<td>'.$last_login.'</td>'; // last_login
		  // $return .= '<td>'.$created.'</td>'; // created
		
			if(pz::getUser()->getId() != $this->user->getId()) {
        		$return .= '<td><a class="bt2" href="javascript:pz_loadPage(\'users_list\',\''.$del_link.'\')"><span class="title">'.pz_i18n::msg("delete").'</span></a></td>';
			}else
			{
	        	$return .= '<td><span class="title"></span></td>';
			}
		}        
        
		return $return;
		
	}
	
	
	static function getTableListView($users, $p = array())
	{
		
		$paginate_screen = new pz_paginate_screen($users);
		// $paginate_screen->setListAmount(1);
		$paginate = $paginate_screen->getPlainView($p);
		
		$list = "";
		foreach($paginate_screen->getCurrentElements() as $user) {
			$ps = new pz_user_screen($user);
			$list .= $ps->getTableView($p);
		}
		
		$paginate_loader = $paginate_screen->setPaginateLoader($p, '#users_list');

		if($paginate_screen->isScrollPage()) {
		  		$content = '
          <table class="users tbl1">
          '.$list.'
          </table>
          '.$paginate_loader;
        return $content;
		}
		
		$content = $paginate.'
          <table class="users tbl1">
          <thead><tr>
              <th></th>
              <th>'.pz_i18n::msg("username").'</th>
              <th>'.pz_i18n::msg("active").'</th>
              <th>'.pz_i18n::msg("admin").'</th>
              ';
		if(pz::getUser()->isAdmin()) {
			$content .= '
              <th>'.pz_i18n::msg("webdav").'</th>
              <th>'.pz_i18n::msg("carddav").'</th>
              <th>'.pz_i18n::msg("projectsadmin").'</th>
              <th>'.pz_i18n::msg("last_login").'</th>
              <th>'.pz_i18n::msg("functions").'</th>
				';
		}
		
    $content .= '
      </tr></thead>
      <tbody>
        '.$list.'
      </tbody>
      </table>'.$paginate_loader;
				
		if(isset($p["info"])) {
			$content = $p["info"].$content;
		}
		
		$f = new pz_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="users_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';

	}
	
	public function getProjectPermTableListView($p, $projects)
	{
	  $content = '';
	
	  $p["layer"] = 'userperms_list';
	
		$paginate_screen = new pz_paginate_screen($projects);
		$paginate = $paginate_screen->getPlainView($p);
		
		$list = "";
		foreach($paginate_screen->getCurrentElements() as $project) {
			$list .= $this->getProjectPermTableView($p, $project);
		}
		
		$paginate_loader = $paginate_screen->setPaginateLoader($p, '#userperms_list');

		if($paginate_screen->isScrollPage()) {
		  $content = '
        <table class="userperms tbl1">
        <thead><tr>
              <th></th>
              <th>'.pz_i18n::msg("project_name").'</th>
              <th>'.pz_i18n::msg("emails").'</th>
              <th>'.pz_i18n::msg("calendar_events").'</th>
              <th>'.pz_i18n::msg("calendar_jobs").'</th>
              <th>'.pz_i18n::msg("calendar_caldav").'</th>
              <th>'.pz_i18n::msg("calendar_jobs_caldav").'</th>
              <th>'.pz_i18n::msg("files").'</th>
          </tr></thead>
        <tbody>
          '.$list.'
        </tbody>
        </table>'.$paginate_loader;
		  return $content;
		}
		
		
		$content = $paginate.'
          <table class="userperms tbl1">
          <thead><tr>
              <th></th>
              <th>'.pz_i18n::msg("project_name").'</th>
              <th>'.pz_i18n::msg("emails").'</th>
              <th>'.pz_i18n::msg("calendar_events").'</th>
              <th>'.pz_i18n::msg("calendar_jobs").'</th>
              <th>'.pz_i18n::msg("calendar_caldav").'</th>
              <th>'.pz_i18n::msg("calendar_jobs_caldav").'</th>
              <th>'.pz_i18n::msg("files").'</th>
          </tr></thead>
          <tbody>
            '.$list.'
          </tbody>
          </table>'.$paginate_loader;
		
		if(isset($p["info"])) {
			$content = $p["info"].$content;
		}
		
		$f = new pz_fragment();
		$f->setVar('title', $p["title"], false);
		$f->setVar('content', $content , false);
		return '<div id="userperms_list" class="design2col">'.$f->parse('pz_screen_list.tpl').'</div>';
	
	}
	
	public function getProjectPermTableView($p = array(),$project)
	{
		
		if(!($projectuser = pz_projectuser::get($this->user,$project)))
			return "";

		$project_user_screen = new pz_projectuser_screen($projectuser);

    $td = array();
    $td[] = '<td class="img1"><img src="'.$project->getInlineImage().'" width="40" height="40" alt="" /></td>';
		$td[] = '<td><span class="title">'.$project->getName().'</span></td>';

    $status = 2;
		if($project->hasEmails() == 1) { $status = $projectuser->hasEmails() ? $status = 1 : $status = 0; }
	  $td[] = $project_user_screen->getPermTableCellView("emails", $status);

    $status = 2;
	  if ($project->hasCalendarEvents() == 1) { $status = $projectuser->hasCalendarEvents() ? $status = 1 : $status = 0; }
	  $td[] = $project_user_screen->getPermTableCellView("calendar_events", $status);

    $status = 2;
    if ($project->hasCalendarJobs() == 1) { $status = $projectuser->hasCalendarJobs() ? $status = 1 : $status = 0; }
    $td[] = $project_user_screen->getPermTableCellView("calendar_jobs", $status);

    $status = 2;
	  if ($project->hasCalendarEvents() == 1) { $status = $projectuser->hasCalDAVEvents() ? $status = 1 : $status = 0; }
	  $td[] = $project_user_screen->getPermTableCellView("caldav_events", $status);

    $status = 2;
    if ($project->hasCalendarJobs() == 1) { $status = $projectuser->hasCalDAVJobs() ? $status = 1 : $status = 0; }
    $td[] = $project_user_screen->getPermTableCellView("caldav_jobs", $status);

    $status = 2;
    if ($project->hasFiles() == 1) { $status = $projectuser->hasFiles() ? $status = 1 : $status = 0; }
    $td[] = $project_user_screen->getPermTableCellView("files", $status);
		
    $return = '<tr>'.implode("",$td).'</tr>';
		
		return $return;
	}
	
	static function getSearchForm($p = array())
	{
		
		$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("search_for_users").'</h1>
          </div>
        </header>';
		
		$xform = new rex_xform;
		$xform->setObjectparams("real_field_names",TRUE);
		$xform->setObjectparams("form_showformafterupdate", TRUE);
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('users_list','users_search_form','".pz::url($p["mediaview"],$p["controll"],$p["function"])."')");
		$xform->setObjectparams("form_id", "users_search_form");
		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl', 'runtime'));
		$xform->setValueField("text",array("search_name",pz_i18n::msg("name")));
		$xform->setValueField("submit",array('submit',pz_i18n::msg('search'), '', 'search'));
		$xform->setValueField("hidden",array("mode","list"));
		$searchform = $xform->getForm();
		
		$return = '<div id="users_search" class="design1col xform-search">'.$header.$searchform.'</div>';
		
		return $return;
		
	}
	
	public function getBlockView($p = array())
	{
		// $edit_link = pz::url("screen","tools","users",array("user_id"=>$this->user->getId(),"mode"=>"edit_user"));

    /*
		if($this->user->isAdmin())  
			$return .= '<td><span class="status status-1">'.pz_i18n::msg("yes").'</span></td>';
		else 
			$return .= '<td><span class="status status-2">'.pz_i18n::msg("no").'</span></td>';

		if($this->user->isActive())  
			$return .= '<td><span class="status status-1">'.pz_i18n::msg("yes").'</span></td>';
		else 
			$return .= '<td><span class="status status-2">'.pz_i18n::msg("no").'</span></td>';
    */

    $info = "";
		if(pz::getUser()->isAdmin())
		{
		  $last_login = "-";
      if($this->user->getValue("last_login") != "")
      {
  		  $d = DateTime::createFromFormat('Y-m-d H:i:s', $this->user->getValue("last_login"), pz::getDateTimeZone());
        $last_login = ' '.strftime(pz_i18n::msg("show_datetime_normal"),pz_user::getDateTime($d)->format("U")).'';
      }
      		
      $created = "-";
      if($this->user->getValue("created") != "")
      {
		    $d = DateTime::createFromFormat('Y-m-d H:i:s', $this->user->getValue("created"), pz::getDateTimeZone());
        $created = ' '.strftime(pz_i18n::msg("show_datetime_normal"),pz_user::getDateTime($d)->format("U")).'';
		  }

      $info = $last_login.''.$created.''; // created
		
		}

		$return = '
		        <article class="user block image">
		          <header>
		            <a class="detail" href="">
		              <figure><img src="'.$this->user->getInlineImage().'" width="40" height="40" /></figure>
		              <hgroup class="data">
		                <h2 class="hl7 piped">
		                  <span class="name">'.$this->user->getName().'</span>
		                  <span class="info">'.$this->user->getId().'</span>
		                </h2>
		                <span class="">'.$info.'</span>
		              </hgroup>
		            </a>
		          </header>
		        </article>
    ';



        
		return $return;
		
	}
	
		
	public function getApiView($p) 
	{
		$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("api_key").'</h1>
          </div>
        </header>';
	
		$return = $header;
	
		$return .= '<div class="xform">'.pz_i18n::msg('api_info',$this->user->getAPIKey()).'</div>';
	
		$return = '<div id="api_form"><div id="api_view" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
	
	}
	
	
	// ---------------------------------------- FORM VIEWS
	
	static function getAddForm($p = array()) 
	{
		$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("user_add").'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form','user_add_form','".pz::url($p["mediaview"],$p["controll"],$p["function"],array("mode"=>'add_user'))."')");
		$xform->setObjectparams("form_id", "user_add_form");

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		$xform->setValueField("text",array("name",pz_i18n::msg("name")));
			$xform->setValidateField("empty",array("name",pz_i18n::msg("error_name_empty")));
		$xform->setValueField("text",array("login",pz_i18n::msg("login")));
			$xform->setValidateField("empty",array("login",pz_i18n::msg("error_login_empty")));
			$xform->setValidateField("unique",array("login",pz_i18n::msg("error_login_unique")));
		$xform->setValueField("text",array("password",pz_i18n::msg("password")));

		$xform->setValueField("text",array("email",pz_i18n::msg("email")));
			$xform->setValidateField("empty",array("email",pz_i18n::msg("error_email_empty")));
			$xform->setValidateField("unique",array("email",pz_i18n::msg("error_email_unique")));

		$xform->setValueField("checkbox",array("status",pz_i18n::msg("active"),"","0"));
		$xform->setValueField("checkbox",array("admin",pz_i18n::msg("admin").' ('.pz_i18n::msg("admin_info").')',"","0"));

    $xform->setValueField("datestamp",array("created","mysql","","0","1"));
    $xform->setValueField("datestamp",array("updated","mysql","","0","0"));

    $xform->setValueField("checkbox",array("webdav",pz_i18n::msg("webdav").' ('.pz_i18n::msg("webdav_info").')',"","0","no_db"));
		$xform->setValueField("checkbox",array("carddav",pz_i18n::msg("carddav").' ('.pz_i18n::msg("carddav_info").')',"","0","no_db"));
		$xform->setValueField("checkbox",array("projectsadmin",pz_i18n::msg("projectsadmin").' ('.pz_i18n::msg("projectsadmin_info").')',"","0","no_db"));

		$xform->setValueField("textarea",array("comment",pz_i18n::msg("user_comment")));

		$xform->setActionField("db",array('pz_user'));

		$return = $xform->getForm();


		if($xform->getObjectparams("actions_executed")) 
		{
			$user_id = $xform->getObjectparams("main_id");
			if($user = pz_user::get($user_id)) {
				
				$webdav = $xform->objparams["value_pool"]["email"]["webdav"];
				if($webdav == 1) $user->addPerm('webdav');
				else $user->removePerm('webdav');

				$carddav = $xform->objparams["value_pool"]["email"]["carddav"];
				if($carddav == 1) $user->addPerm('carddav');
				else $user->removePerm('carddav');

				$projectsadmin = $xform->objparams["value_pool"]["email"]["projectsadmin"];
				if($projectsadmin == 1) $user->addPerm('projectsadmin');
				else $user->removePerm('projectsadmin');
			
				$user->savePerm();
				
  			if($xform->objparams["value_pool"]["email"]["password"] != "") {
          $user->passwordHash($xform->objparams["value_pool"]["email"]["password"]);
        }
        $user->update();

				$user->create();
				
			}
			$return = $header.'<p class="xform-info">'.pz_i18n::msg("user_added").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('users_list','users_search_form',pz::url($p["mediaview"],$p["controll"],$p["function"],array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form"><div id="user_add" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}
	
	
	public function getEditForm($p = array()) 
	{

    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("user_edit").': '.$this->user->getName().'</h1>
          </div>
        </header>';

		$xform = new rex_xform;
		// $xform->setDebug(TRUE);

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("main_id",$this->user->getId());
		$xform->setObjectparams("main_where",'id='.$this->user->getId());
		$xform->setObjectparams('getdata',true);

		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form','user_edit_form','".pz::url($p["mediaview"],$p["controll"],$p["function"],array("mode"=>'edit_user'))."')");
		$xform->setObjectparams("form_id", "user_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setHiddenField("user_id",$this->user->getId());

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		$xform->setValueField("text",array("name",pz_i18n::msg("name")));
		$xform->setValidateField("empty",array("name",pz_i18n::msg("error_name_empty")));

		$xform->setValueField("text",array("login",pz_i18n::msg("login")));
		$xform->setValidateField("empty",array("login",pz_i18n::msg("error_login_empty")));
		$xform->setValidateField("unique",array("login",pz_i18n::msg("error_login_unique")));

		$xform->setValueField("password",array("password",pz_i18n::msg("password"),"","no_db"));
				
		$xform->setValueField("text",array("email",pz_i18n::msg("email")));
			$xform->setValidateField("empty",array("email",pz_i18n::msg("error_email_empty")));
			$xform->setValidateField("unique",array("email",pz_i18n::msg("error_email_unique")));

		if($this->user->getId() != pz::getUser()->getId())
		{
		}
		$xform->setValueField("checkbox",array("status",pz_i18n::msg("active"),"","0"));
		$xform->setValueField("checkbox",array("admin",pz_i18n::msg("admin").' ('.pz_i18n::msg("admin_info").')',"","0"));
		
    $xform->setValueField("datestamp",array("updated","mysql","","0","0"));

    $xform->setValueField("checkbox",array("webdav",pz_i18n::msg("webdav").' ('.pz_i18n::msg("webdav_info").')',"",$this->user->hasPerm("webdav"),"no_db"));
		$xform->setValueField("checkbox",array("carddav",pz_i18n::msg("carddav").' ('.pz_i18n::msg("carddav_info").')',"",$this->user->hasPerm("carddav"),"no_db"));
		$xform->setValueField("checkbox",array("projectsadmin",pz_i18n::msg("projectsadmin").' ('.pz_i18n::msg("projectsadmin_info").')',"",$this->user->hasPerm("projectsadmin"),"no_db"));

		$xform->setValueField("textarea",array("comment",pz_i18n::msg("user_comment")));

		$xform->setActionField("db",array('pz_user','id='.$this->user->getId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			
			$webdav = $xform->objparams["value_pool"]["email"]["webdav"];
			if($webdav == 1) $this->user->addPerm('webdav');
			else $this->user->removePerm('webdav');

			$carddav = $xform->objparams["value_pool"]["email"]["carddav"];
			if($carddav == 1) $this->user->addPerm('carddav');
			else $this->user->removePerm('carddav');

			$projectsadmin = $xform->objparams["value_pool"]["email"]["projectsadmin"];
			if($projectsadmin == 1) $this->user->addPerm('projectsadmin');
			else $this->user->removePerm('projectsadmin');
		
			$this->user->savePerm();
			
			$this->user = pz_user::get($this->user->getId(),TRUE);
			if($xform->objparams["value_pool"]["email"]["password"] != "") {
        $this->user->passwordHash($xform->objparams["value_pool"]["email"]["password"]);
      }
      
      $this->user->update();
			
			$return = $header.'<p class="xform-info">'.pz_i18n::msg("user_updated").'</p>'.$return;
			$return .= pz_screen::getJSLoadFormPage('users_list','users_search_form',pz::url($p["mediaview"],$p["controll"],$p["function"],array("mode"=>'list')));
			
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form"><div id="user_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}

	
	
	public function getMyEditForm($p = array()) 
	{

    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("profile_edit").'</h1>
          </div>
        </header>';

		$xform = new rex_xform;

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("main_id",$this->user->getId());
		$xform->setObjectparams("main_where",'id='.$this->user->getId());
		$xform->setObjectparams('getdata',true);

		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form','user_edit_form','".pz::url($p["mediaview"],$p["controll"],$p["function"],array("mode"=>'edit_user'))."')");
		$xform->setObjectparams("form_id", "user_edit_form");
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));
		$xform->setValueField("text",array("name",pz_i18n::msg("name")));
		$xform->setValidateField("empty",array("name",pz_i18n::msg("error_name_empty")));

		$xform->setValueField("text",array("login",pz_i18n::msg("login")));
		$xform->setValidateField("empty",array("login",pz_i18n::msg("error_login_empty")));
		$xform->setValidateField("unique",array("login",pz_i18n::msg("error_login_unique")));

		$xform->setValueField("text",array("email",pz_i18n::msg("email")));
			$xform->setValidateField("empty",array("email",pz_i18n::msg("error_email_empty")));
			$xform->setValidateField("unique",array("email",pz_i18n::msg("error_email_unique")));

		$xform->setValueField("pz_select_screen",array("account_id",pz_i18n::msg("default_email_account"),pz::getUser()->getEmailaccountsAsString(),"","",0));
		
		$startpages = array();
		$startpages[] = array('id'=>'projects','label'=>pz_i18n::msg("projects"));
		$startpages[] = array('id'=>'emails','label'=>pz_i18n::msg("emails"));
		$startpages[] = array('id'=>'calendars','label'=>pz_i18n::msg("calendars"));
		
		$xform->setValueField("pz_select_screen",array("startpage",pz_i18n::msg("default_startpage_account"),$startpages,"no_db",$this->user->getConfig('startpage'),0));
		
    $xform->setValueField("datestamp",array("updated","mysql","","0","0"));

    $xform->setActionField("db",array('pz_user','id='.$this->user->getId()));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			$this->user = pz_user::get($this->user->getId(),TRUE);
			$this->user->setConfig('startpage',$xform->objparams["value_pool"]["email"]["startpage"]);
			$this->user->saveConfig();
			$this->user->update();
			$return = $header.'<p class="xform-info">'.pz_i18n::msg("user_updated").'</p>'.$return;
			// $return .= pz_screen::getJSLoadFormPage('users_list','users_search_form',pz::url('screen','tools','users',array("mode"=>'list')));
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form"><div id="user_edit" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}
	
	
	public function getMyPasswordEditForm($p = array()) 
	{

    	$header = '
        <header>
          <div class="header">
            <h1 class="hl1">'.pz_i18n::msg("profile_edit_password").'</h1>
          </div>
        </header>';

		$xform = new rex_xform;

		$xform->setObjectparams("main_table",'pz_user');
		$xform->setObjectparams("main_id",$this->user->getId());
		$xform->setObjectparams("main_where",'id='.$this->user->getId());
		$xform->setObjectparams('getdata',true);

		$xform->setObjectparams("form_action", "javascript:pz_loadFormPage('user_form_2','user_edit_password_form','".pz::url($p["mediaview"],$p["controll"],$p["function"],array("mode"=>'edit_password'))."')");
		$xform->setObjectparams("form_id", "user_edit_password_form");
		$xform->setObjectparams('form_showformafterupdate',1);

		$xform->setValueField('objparams',array('fragment', 'pz_screen_xform.tpl'));

		$xform->setValueField("password",array("password",pz_i18n::msg("password"),"","no_db"));
		$xform->setValueField("password",array("password_2",pz_i18n::msg("password_reenter"),"","no_db"));

		$xform->setValidateField("empty",array("password",pz_i18n::msg("error_password_empty")));

		$xform->setValidateField("compare",array("password","password_2",pz_i18n::msg("error_passwords_different")));

		$return = $xform->getForm();

		if($xform->getObjectparams("actions_executed")) 
		{
			$this->user = pz_user::get($this->user->getId(),TRUE); // refresh data
			
			if($xform->objparams["value_pool"]["email"]["password"] != "") {
        $this->user->passwordHash($xform->objparams["value_pool"]["email"]["password"]);
      }
			
			$this->user->update();
			// $this->user->hashPassword();
			$return = $header.'<p class="xform-info">'.pz_i18n::msg("user_password_updated").'</p>'.$return;
		}else
		{
			$return = $header.$return;	
		}
		$return = '<div id="user_form_2"><div id="user_edit_password" class="design1col xform-edit">'.$return.'</div></div>';

		return $return;	
		
	}
	
	
}