<?php
if (!defined('_VALID_MOS') && !defined('_JEXEC'))
  die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
*
* @package  RealEstateManager
* @copyright 2012 Andrey Kvasnevskiy-OrdaSoft(akbet@mail.ru); Rob de Cleen(rob@decleen.com);
* Homepage: http://www.ordasoft.com
* @version: 3.8 Pro
*
*
*/
/**
* Legacy function, use <jdoc:include type="module" /> instead
*
* @deprecated        As of version 1.5
*/
if (!defined('DS'))
  define('DS', DIRECTORY_SEPARATOR);

if (!function_exists('mosLoadModule')) {

  function mosLoadModule($name, $style = - 1) {
    ?><jdoc:include type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" /><?php
  }

}
if (!isset($GLOBALS['realestatemanager_configuration'])) {
  require_once (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS
   . 'com_realestatemanager' . DS . 'realestatemanager.class.conf.php' );
  $GLOBALS['realestatemanager_configuration'] = 
  isset($realestatemanager_configuration) ? $realestatemanager_configuration : null;
}
/**
* Legacy function, using <jdoc:include type="modules" /> instead
*
* @deprecated  As of version 1.5
*/
//if (!function_exists('clearDate')) {
//
//    function clearDate($date) {
//        $date = preg_replace('/%/', '', $date);
//        return $date;
//    }
//
//}
if (!function_exists('mosMail')) {
  function mosMail($from, $fromname, $recipient, $subject, $body, $mode = 0, $cc = NULL, 
    $bcc = NULL, $attachment = NULL, $replyto = NULL, $replytoname = NULL) {
    $mailer = JFactory::getMailer();

    $mailer->setSender(array($from, $fromname));
    $mailer->setSubject($subject);
    $mailer->setBody($body);

      // Are we sending the email as HTML?
    if ($mode) {
      $mailer->isHTML(true);
    } 

    if (!is_array($recipient)) {
      $recipient = explode(',', $recipient);   
    }

    $mailer->addRecipient($recipient);
    $mailer->addCC($cc);
    $mailer->addBCC($bcc);
    $mailer->addAttachment($attachment);

      // Take care of reply email addresses
    if (is_array($replyto)) {
      $numReplyTo = count($replyto);
      for ($i=0; $i < $numReplyTo; $i++){
        $mailer->addReplyTo(array($replyto[$i], $replytoname[$i]));
      }
    } elseif (isset($replyto)) {
      $mailer->addReplyTo(array($replyto, $replytoname));
    }
    return  $mailer->Send();
  }
}

if (!function_exists('mosLoadAdminModules')) {

  function mosLoadAdminModules($position = 'left', $style = 0) {
      // Select the module chrome function
    if (is_numeric($style)) {
      switch ($style) {
        case 2:
        $style = 'xhtml';
        break;
        case 0:
        default:
        $style = 'raw';
        break;
      }
    }
    ?><jdoc:include type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>" /><?php
  }

}
/**
* Legacy function, using <jdoc:include type="module" /> instead
*
* @deprecated  As of version 1.5
*/
if (!function_exists('mosLoadAdminModule')) {

  function mosLoadAdminModule($name, $style = 0) {
    ?><jdoc:include type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" /><?php
  }

}
/**
* Legacy function, always use {@link JRequest::getVar()} instead
*
* @deprecated  As of version 1.5
*/
if (!function_exists('mosStripslashes')) {

  function mosStripslashes(&$value) {
    $ret = '';
    if (is_string($value)) {
      $ret = stripslashes($value);
    } else {
      if (is_array($value)) {
        $ret = array();
        foreach ($value as $key => $val)
          $ret[$key] = mosStripslashes($val);
      } else
      $ret = $value;
    }
    return $ret;
  }

}
if (!function_exists("formatMoney")) {

  function formatMoney($number, $fractional = false, $pattern = ".") {
    if(preg_match("/\d/", $pattern)){

      $msg = "Your separator: $pattern - incorrect, you can not use numbers, to split price" ;
      echo '<script>alert("'.$msg.'");</script>';
      $pattern = ".";      
    }
    if ($fractional) {
      $number = sprintf('%.2f', $number);
    }
    if ($pattern == ".")
      $number = str_replace(".", ",", $number);
    while (true) {
      $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1' . $pattern . '$2', $number);
          //echo $replaced."<br>";
      if ($replaced != $number) {
        $number = $replaced;
      } else {
        break;
      }
    }
      // $number = preg_replace('/\^/', $number, $pattern);
    return $number;
  }

}
/**
* Legacy function, use {@link JFolder::files()} or {@link JFolder::folders()} instead
*
* @deprecated  As of version 1.5
*/
if (!function_exists('mosReadDirectory')) {

  function mosReadDirectory($path, $filter = '.', $recurse = false, $fullpath = false) {
    $arr = array(null);
      // Get the files and folders
    jimport('joomla.filesystem.folder');
    $files = JFolder::files($path, $filter, $recurse, $fullpath);
    $folders = JFolder::folders($path, $filter, $recurse, $fullpath);
      // Merge files and folders into one array
    $arr = array();
    if (is_array($files))
      $arr = $files;
    if (is_array($folders))
      $arr = array_merge($arr, $folders);
      // Sort them all
    asort($arr);
    return $arr;
  }

}
/**
* Legacy function, use {@link JApplication::redirect() JApplication->redirect()} instead
*
* @deprecated  As of version 1.5
*/
if (!function_exists('date_to_data_ms')){
  function date_to_data_ms($data_string){             // 2014-01-25 covetr to date in ms

    global $database;

//        $date = data_transform_rem($data_string);

/*        $query = "SELECT UNIX_TIMESTAMP('$data_string')"; 
      $database->setQuery($query);
      $rent_ms = $database->loadResult(); 
      
      return $rent_ms;*/
      if($data_string){
       $rent_mas = explode('-', $data_string);
       $month=$rent_mas[1];
       $day=$rent_mas[2];
       $year=$rent_mas[0];
       $rent_ms = mktime ( 0 ,0, 0, $month , $day , $year);
       return $rent_ms;
     }else{
      exit;
    }
  }
}
if (!function_exists('mosRedirect')) {

  function mosRedirect($url, $msg = '') {
      $mainframe = JFactory::getApplication(); // for J 1.6
      $mainframe->redirect($url, $msg);
    }

  }
/**
* Legacy function, use {@link JArrayHelper::getValue()} instead
*
* @deprecated  As of version 1.5
*/
if (!function_exists('mosGetParam')) {

  function mosGetParam(&$arr, $name, $def = null, $mask = 0) {
      // Static input filters for specific settings
    static $noHtmlFilter = null;
    static $safeHtmlFilter = null;
    $var = JArrayHelper::getValue($arr, $name, $def, '');
      // If the no trim flag is not set, trim the variable
    if (!($mask & 1) && is_string($var))
      $var = trim($var);
      // Now we handle input filtering
    if ($mask & 2) {
          // If the allow html flag is set, apply a safe html filter to the variable
      if (is_null($safeHtmlFilter))
        $safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
      $var = $safeHtmlFilter->clean($var, 'none');
    } elseif ($mask & 4) {
          // If the allow raw flag is set, do not modify the variable
      $var = $var;
    } else {
          // Since no allow flags were set, we will apply the most strict filter to the variable
      if (is_null($noHtmlFilter)) {
        $noHtmlFilter = JFilterInput::getInstance(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */
        );
      }
      $var = $noHtmlFilter->clean($var, 'none');
    }
    return $var;
  }

}
/**
* Legacy function, use {@link JEditor::save()} or {@link JEditor::getContent()} instead
*
* @deprecated  As of version 1.5
*/
if (!function_exists('getEditorContents')) {

  function getEditorContents($editorArea, $hiddenField) {
    jimport('joomla.html.editor');
    $editor = JFactory::getEditor();
    echo $editor->save($hiddenField);
  }

}
/**
* Legacy function, use {@link JFilterOutput::objectHTMLSafe()} instead
*
* @deprecated  As of version 1.5
*/
if (!function_exists('mosMakeHtmlSafe')) {

  function mosMakeHtmlSafe(&$mixed, $quote_style = ENT_QUOTES, $exclude_keys = '') {
    JFilterOutput::objectHTMLSafe($mixed, $quote_style, $exclude_keys);
  }

}
/**
* Legacy utility function to provide ToolTips
*
* @deprecated As of version 1.5
*/
if (!function_exists('mosToolTip')) {

  function mosToolTip($tooltip, $title = '', $width = '', $image = 'tooltip.png', $text = '', $href = '', $link = 1) {
      // Initialize the toolips if required
    static $init;
    if (!$init) {
      JHTML::_('behavior.tooltip');
      $init = true;
    }
    return JHTML::_('tooltip', $tooltip, $title, $image, $text, $href, $link);
  }

}
/**
* Legacy function to replaces &amp; with & for xhtml compliance
*
* @deprecated  As of version 1.5
*/
if (!function_exists('mosTreeRecurseREM')) {

  function mosTreeRecurseREM($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1) {
    if (@$children[$id] && $level <= $maxlevel) {
      $parent_id = $id;
      foreach ($children[$id] as $v) {
        $id = $v->id;
        if ($type) {
          $pre = 'L ';
          $spacer = '.      ';
        } else {
          $pre = '- ';
          $spacer = '  ';
        }
        if ($v->parent == 0)
          $txt = $v->name;
        else
          $txt = $pre . $v->name;
        $pt = $v->parent;
        $list[$id] = $v;
        $list[$id]->treename = "$indent$txt";
        $list[$id]->children = count(@$children[$id]);
        $list[$id]->all_fields_in_list = count(@$children[$parent_id]);
        $list = mosTreeRecurseREM($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
      }
    }
    return $list;
  }

}
if (!function_exists('getGroupsByUser')) {

  function getGroupsByUser($uid, $recurse) {
    if (version_compare(JVERSION, "1.6.0", "lt")) {

    } else if (version_compare(JVERSION, "1.6.0", "ge")) {
      $database = JFactory::getDBO();
          // Custom algorythm
      $usergroups = array();
      if ($recurse == 'RECURSE') {
              // [1]: Recurse getting the usergroups
        $id_group = array();
        $q1 = "SELECT group_id FROM `#__user_usergroup_map` WHERE user_id={$uid}";
        $database->setQuery($q1);
        $rows1 = $database->loadObjectList();
        foreach ($rows1 as $v)
          $id_group[] = $v->group_id;
        for ($k = 0; $k < count($id_group); $k++) {
          $q = "SELECT g2.id FROM `#__usergroups` g1 "
          . " LEFT JOIN `#__usergroups` g2 ON g1.lft > g2.lft AND g1.lft < g2.rgt "
          . " WHERE g1.id={$id_group[$k]} ORDER BY g2.lft";
          $database->setQuery($q);
          $rows = $database->loadObjectList();
          foreach ($rows as $r)
            $usergroups[] = $r->id;
        }
        $usergroups = array_unique($usergroups);
      }
          // [2]: Non-Recurse getting usergroups
      $q = "SELECT * FROM #__user_usergroup_map WHERE user_id = {$uid}";
      $database->setQuery($q);
      $rows = $database->loadObjectList();
      foreach ($rows as $k => $v)
        $usergroups[] = $rows[$k]->group_id;
          // If user is unregistered, Joomla contains it into standard group (Public by default).
          // So, groupId for anonymous users is 1 (by default).
          // But custom algorythm doesnt do this: if user is not autorised, he will NOT connected to any group.
          // And groupId will be 0.
      if (count($rows) == 0)
        $usergroups[] = - 2;
      return $usergroups;
    } else {
      echo "Sanity test. Error version check!";
      exit;
    }
  }

}

if (!function_exists('getWhereUsergroupsCondition')) {

  function getWhereUsergroupsCondition($table_alias) {
    $my = JFactory::getUser();
    if (isset($my->id) AND $my->id != 0)
      $usergroups_sh = getGroupsByUser($my->id, '');
    else
      $usergroups_sh = array();
    $usergroups_sh[] = - 2;
    $s = '';
    for ($i = 0; $i < count($usergroups_sh); $i++) {
      $g = $usergroups_sh[$i];
      $s.= " $table_alias.params LIKE '%,{$g}' or $table_alias.params = '{$g}' or " . 
      "$table_alias.params LIKE '{$g},%' or $table_alias.params LIKE '%,{$g},%' ";
      if (($i + 1) < count($usergroups_sh))
        $s.= ' or ';
    }
    return $s;
  }

}
if (!function_exists('addSubMenuRealEstate')) {

  function addSubMenuRealEstate($vName) {
    if (!defined('_HEADER_NUMBER')) loadConstRem();
    JSubMenuHelper::addEntry(JText::_(_HEADER_NUMBER),
     'index.php?option=com_realestatemanager', $vName == 'Houses');
    JSubMenuHelper::addEntry(JText::_(_CATEGORIES_NAME),
     'index.php?option=com_realestatemanager&section=categories', $vName == 'Categories');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_LABEL_REVIEWS),
     'index.php?option=com_realestatemanager&task=manage_review', $vName == 'Reviews');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_ADMIN_RENT_REQUESTS),
     'index.php?option=com_realestatemanager&task=rent_requests', $vName == 'Rent Requests');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_ADMIN_RENT_HISTORY),
     'index.php?option=com_realestatemanager&task=users_rent_history', $vName == 'Users Booking History');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_ADMIN_SALE_MANAGER_MENU),
     'index.php?option=com_realestatemanager&task=buying_requests', $vName == 'Sale Manager');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_ADMIN_IMPORT_EXPORT),
     'index.php?option=com_realestatemanager&task=show_import_export', $vName == 'Import/Export');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_LABEL_FEATURED_MANAGER_FEATURE_MANAGER),
     'index.php?option=com_realestatemanager&section=featured_manager', $vName == 'Features Manager');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_LABEL_LANGUAGE_MENU),
     'index.php?option=com_realestatemanager&section=language_manager', $vName == 'Language Manager');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_ADMIN_LABEL_SETTINGS),
     'index.php?option=com_realestatemanager&task=config', $vName == 'Settings');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_ADMIN_ABOUT_ORDERS),
     'index.php?option=com_realestatemanager&task=orders', $vName == 'Orders');
    JSubMenuHelper::addEntry(JText::_(_REALESTATE_MANAGER_ADMIN_ABOUT_ABOUT),
     'index.php?option=com_realestatemanager&task=about', $vName == 'About');
  }

}


if (!function_exists('loadConstRem')) {
  function loadConstRem() {
    global $database, $mosConfig_absolute_path;
    $is_exception = false;
    $database->setQuery("SELECT * FROM #__rem_languages");
    $langs = $database->loadObjectList();
    $component_path = JPath::clean($mosConfig_absolute_path . '/components/com_realestatemanager/lang/');
    $component_constans = array();
    if (is_dir($component_path) && ($component_constans =
      JFolder::files($component_path, '^[^_]*\.php$', false, true))) {
        //check and add constants file in DB
      foreach ($component_constans as $i => $file) {
        $file_name = pathinfo($file);
        $file_name = $file_name['filename'];
        if ($file_name === 'constant') {
          require($mosConfig_absolute_path . "/components/com_realestatemanager/lang/$file_name.php");
          foreach ( $constMas as $mas ) {
            $database->setQuery(
              "INSERT IGNORE INTO #__rem_const (const, sys_type) VALUES ('".
              $mas["const"]."','".$mas["value_const"]."')");
            $database->query();
          }
        }
      }                
        //check and add new text files in DB
      $flag1=true;
      print_r("<b>These constants exit in Languages files but not exist in file constants:</b><br><br>");
      foreach ($component_constans as $i => $file) {
        $file_name = pathinfo($file);
        $file_name = $file_name['filename'];
        $LangLocal = '';
        if ($file_name != 'constant') {
          require($mosConfig_absolute_path . "/components/com_realestatemanager/lang/$file_name.php");
          try {
            $database->setQuery("INSERT IGNORE INTO #__rem_languages (lang_code,title) VALUES ('"
              . $LangLocal['lang_code'] . "','" . $LangLocal['title'] . "')");
            $database->query();
            $database->setQuery("SELECT id FROM #__rem_languages " .
             " WHERE lang_code = '" . $LangLocal['lang_code'] . "' AND title='".$LangLocal['title']."'");
            $idLang = $database->loadResult();
            foreach ($constLang as $item) { 
                //if(!isset($item['value_const'])) var_dump($item);
              $database->setQuery("SELECT id FROM #__rem_const WHERE const = '" . $item['const'] . "'");
              $idConst = $database->loadResult();
              if(!array_key_exists ( 'value_const'  , $item ) || !$idConst){
               print_r($item['const']." not exist in file <b>'constant'</b> for this language:  <b>"
                 . $LangLocal['title']."</b>.");
               $flag1 = false;
             } else {
              $database->setQuery(
               "INSERT IGNORE INTO #__rem_const_languages (fk_constid,fk_languagesid,value_const) "
               . " VALUES ($idConst, $idLang, " . $database->quote($item['value_const']) . ")");
              $database->query();
            }
          }
        } catch (Exception $e) {
          $is_exception = true;
              //echo 'Send exception, please write to admin for language check: ',  $e->getMessage(), "\n";
        }
      }
    }
    if($flag1){
      print_r("<br /><p style='color:green;'><b>Everything is [ OK ]</b></p><br />");
    }
    else{
      print_r("<br><b style='color:red;'>This constants not loaded.!</b><br><br>");
    }

        //if text constant missing recover they in DB
    if (!defined('_HEADER_NUMBER')) {
      $query = "SELECT c.const, cl.value_const ";
      $query .= "FROM #__rem_const_languages as cl ";
      $query .= "LEFT JOIN #__rem_languages AS l ON cl.fk_languagesid=l.id ";
      $query .= "LEFT JOIN #__rem_const AS c ON cl.fk_constid=c.id ";
      $query .= "WHERE l.lang_code = 'en-GB'";
      $database->setQuery($query);
      $langConst = $database->loadObjectList();
      foreach ($langConst as $item) {
        if(!defined($item->const)){
          defined($item->const) or define($item->const, $item->value_const);
        }
      }
    }
  }

    //if some language file missing recover it
  $component_path = JPath::clean($mosConfig_absolute_path . '/components/com_realestatemanager/lang/');
  $component_constans = array();
  if (is_dir($component_path) && ($component_constans = JFolder::files($component_path, '^[^_]*\.php$', false, true))) {
    foreach ($component_constans as $i => $file) {
      $isLang = 0;
      $file_name = pathinfo($file);
      $file_name = $file_name['filename'];
      if ($file_name != 'constant') {
        require($mosConfig_absolute_path . "/components/com_realestatemanager/lang/$file_name.php");
          //$fileMas[] = $LangLocal;
        $fileMas[] = $LangLocal['title']; 
      }
    }
  }

  $database->setQuery("SELECT title FROM #__rem_languages");
  if (version_compare(JVERSION, '3.0', 'lt')) {
    $langs = $database->loadResultArray();
  } else {
    $langs = $database->loadColumn();
  }
  if (count($langs) > count($fileMas)) {
    $results = array_diff($langs, $fileMas);
    foreach ($results as $result) {
      $database->setQuery("SELECT lang_code FROM #__rem_languages WHERE title = '$result'");
      $lang_code = $database->loadResult();
      $langfile = "<?php if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) "
      . "die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );";
      $langfile .= "\n\n/**\n*\n* @package  RealestateManager\n"
      . "* @copyright 2012 Andrey Kvasnevskiy-OrdaSoft(akbet@mail.ru); Rob de Cleen(rob@decleen.com);\n"
      . "* Homepage: http://www.ordasoft.com\n* @version: 3.0 Pro\n*\n* */\n\n";
      $langfile .= "\$LangLocal = array('lang_code'=>'$lang_code', 'title'=>'$result');\n\n";
      $langfile .= "\$constLang = array();\n\n";

      $query = "SELECT c.const, cl.value_const ";
      $query .= "FROM #__rem_const_languages as cl ";
      $query .= "LEFT JOIN #__rem_languages AS l ON cl.fk_languagesid=l.id ";
      $query .= "LEFT JOIN #__rem_const AS c ON cl.fk_constid=c.id ";
      $query .= "WHERE l.title = '$result'";

      $database->setQuery($query);
      $constlanguages = $database->loadObjectList();

      foreach ($constlanguages as $constlanguage) {
        $langfile .= "\$constLang[] = array('const'=>'" . $constlanguage->const
        . "', 'value_const'=>'" . $database->quote($constlanguage->value_const) . "');\n";
      }

        // Write out new initialization file
      $fd = fopen($mosConfig_absolute_path . "/components/com_realestatemanager/lang/$result.php", "w")
      or die("Cannot create language file.");
      fwrite($fd, $langfile);
      fclose($fd);
    }
  }
} 
}

if (!function_exists('language_check')) {
  function language_check($component_db_name = 'rem' ) {
    global $database;          
    $database->setQuery("SELECT * FROM #__".$component_db_name."_languages");
    $langIds = $database->loadObjectList();

    $flag2=true;
    print_r("<br /><b>These constants exit in file constants but not exist in Languages files:</b><br />");
    foreach ($langIds as $langId){
      $query = " SELECT  lc.*  FROM    #__".$component_db_name."_const as lc ";
      $query .= " WHERE  NOT EXISTS ";
      $query .= " ( SELECT  l1.*  FROM #__".$component_db_name."_const_languages as l1 ";
      $query .= " WHERE lc.id = l1.`fk_constid` and l1.fk_languagesid = ".$langId->id.") ";
      $database->setQuery($query);
      $badLangConsts = $database->loadObjectList();
      if($badLangConsts){
        $flag2 = false;
        print_r("<br />Languages: ".$langId->title."<br />");
        print_r($badLangConsts);
        echo "<br><br>";
      }
    } 
    if($flag2)  
      print_r("<br /><p style='color:green;'><b>Everything is [ OK ]</b></p><br /><br />");  
  }
}

if (!function_exists('remove_langs')) {
  function remove_langs($component_db_name = 'rem' ) {
   global $database;


   $query = " TRUNCATE TABLE #__".$component_db_name."_languages; ";
   $database->setQuery($query);
   $database->query();

   $query = " TRUNCATE TABLE #__".$component_db_name."_const; ";
   $database->setQuery($query);
   $database->query();

   $query = " TRUNCATE TABLE #__".$component_db_name."_const_languages ;";
   $database->setQuery($query);
   $database->query();

 }
}

// Updated on June 25, 2011
// accessgroupid - array which contains accepted user groups for this item
// usersgroupid - groupId of the user
// For anonymous user Uid = 0 and Gid = 0
if (!function_exists('checkAccess_REM')) {

  function checkAccess_REM($accessgroupid, $recurse, $usersgroupid, $acl) {
    if (!is_array($usersgroupid)) {
      $usersgroupid = explode(',', $usersgroupid);
    }
      //parse usergroups
    $tempArr = array();
    $tempArr = explode(',', $accessgroupid);
    for ($i = 0; $i < count($tempArr); $i++) {
      if (((!is_array($usersgroupid) && $tempArr[$i] == $usersgroupid) OR
       (is_array($usersgroupid) && in_array($tempArr[$i], $usersgroupid))) || $tempArr[$i] == - 2) {
              //allow access
        return true;
    } else {
      if ($recurse == 'RECURSE') {
        if (is_array($usersgroupid)) {
          foreach ($usersgroupid as $j)
            if (in_array($j, $tempArr))
              return 1;
          } else {
            if (in_array($usersgroupid, $tempArr))
              return 1;
          }
        }
      }
      } // end for
      //deny access
      return 0;
    }

  }
  if (!function_exists('editorArea')) {

    function editorArea($name, $content, $hiddenField, $width, $height, $col, $row, $option = true) {
      jimport('joomla.html.editor');
      $editor = JFactory::getEditor();
      echo $editor->display($hiddenField, $content, $width, $height, $col, $row, $option);
    }

  }

  if(!function_exists('protectInjection')){
    function protectInjection($element, $def = '', $filter = "STRING",$bypass_get=false){
      global $database;
      if(!$bypass_get){
        $value = JFactory::getApplication()->input->get($element, $def, $filter);
                // $value = $element;
      }else{
        $value = $element;
      }

      if(empty($value)) return $value; 


      if(is_array($value)){
        $start_array = $value;
      }else {
        $hash_string_start = md5($value);
      } 

      $value = str_ireplace(array("/*","*/","select", "insert", "update", "drop", "delete", "alter"), "", $value);

      if(is_array($value)){
        $end_array = $value;
      }else {
        $hash_string_end = md5($value);
      } 

      if((!is_array($value) && $hash_string_start != $hash_string_end)
        ||
        is_array($value) && count(array_diff($start_array, $end_array)))
      {
        return protectInjection($value, $def , $filter ,true);
      }

      return $database->quote($value);
    }

  }

  if(!function_exists('protectInjectionWithoutQuote')){
    function protectInjectionWithoutQuote($element, $def = '', $filter = "STRING",$bypass_get=false){
      global $database;
      if(!$bypass_get){
        $value = JFactory::getApplication()->input->get($element, $def, $filter);
                // $value = $element;
      }else{
        $value = $element;
      }

      if(empty($value)) return $value; 


      if(is_array($value)){
        $start_array = $value;
      }else {
        $hash_string_start = md5($value);
      } 

      $value = str_ireplace(array("/*","*/","select", "insert", "update", "drop", "delete", "alter"), "", $value);

      if(is_array($value)){
        $end_array = $value;
      }else {
        $hash_string_end = md5($value);
      } 

      if((!is_array($value) && $hash_string_start != $hash_string_end)
        ||
        is_array($value) && count(array_diff($start_array, $end_array)))
      {
        return protectInjectionWithoutQuote($value, $def , $filter ,true);
      }

      return $database->escape($value);
    }

  }



  if (!class_exists('getLayoutPath')) {

    class getLayoutPath {

      static function getLayoutPathCom($components, $type, $layout = 'default') {
        $template = JFactory::getApplication()->getTemplate();
        $defaultLayout = $layout;

        if (strpos($layout, ':') !== false) {
              // Get the template and file name from the string
          $temp = explode(':', $layout);
          $template = ($temp[0] == '_') ? $template : $temp[0];
          $layout = $temp[1];
          $defaultLayout = ($temp[1]) ? $temp[1] : 'default';
        }

          // Build the template and base path for the layout
        $tPath = JPATH_THEMES . '/' . $template . '/html/' . $components . '/' . $type . '/' . $layout . '.php';
        $cPath = JPATH_BASE . '/components/' . $components . '/views/' . $type . '/tmpl/' . $layout . '.php';
        $dPath = JPATH_BASE . '/components/' . $components . '/views/' . $type . '/tmpl/default.php';
          // If the template has a layout override use it
        if (file_exists($tPath)) {
          return $tPath;
        } else if (file_exists($cPath)) {
          return $cPath;
        } else if (file_exists($dPath)) {
          return $dPath;
        } else {
          echo "Bad layout path, please write to admin";
          exit;
        }
      }

    }

  }

  if (!function_exists('getLayoutsRem')) {

    function getLayoutsRem($components, $type) {
      global $database;
      $database = JFactory::getDBO();
          // get current template on frontend
      $template = '';
      $query = "SELECT template FROM #__template_styles WHERE client_id=0 AND home=1"; 
      $database->setQuery($query);
      $template = $database->loadResult();

          // Build the template and base path for the layout
      $tPath = JPATH_SITE . '/templates/' . $template . '/html/' . $components . '/' . $type . '/';
      $cPath = JPATH_SITE . '/components/' . $components . '/views/' . $type . '/tmpl/';

      $layouts1 = array();
      $layouts3 = array();

      if (is_dir($tPath) && ($layouts1 = JFolder::files($tPath, '^[^_]*\.php$', false, true))) {

        foreach ($layouts1 as $i => $file) {
          $select_file_name = pathinfo($file);
          $select_file_name = $select_file_name['filename'];
          $layouts3[] = $select_file_name;
        }
      } 
      $layouts2 = array();
      $layouts4 = array();

      if (is_dir($cPath) && ($layouts2 = JFolder::files($cPath, '^[^_]*\.php$', false, true))) {

        foreach ($layouts2 as $i => $file) {
          $select_file_name = pathinfo($file);
          $select_file_name = $select_file_name['filename'];
          $layouts4[] =  $select_file_name;
        }
      } 
      $layouts = array_merge($layouts3,$layouts4);
      $layouts = array_unique($layouts);
      return $layouts;  
    }
  }

  if(!function_exists('transforDateFromPhpToJquery')){
    function transforDateFromPhpToJquery(){
      global $realestatemanager_configuration;
      $DateToFormat = str_replace("d",'dd',(str_replace("m",'mm',(str_replace("Y",'yy',(
        str_replace('%','',$realestatemanager_configuration['date_format'])))))));
      return $DateToFormat;
    }
  }

  if (!function_exists('data_transform_rem')) {

    function data_transform_rem($date, $date_format = "from") {
      global $realestatemanager_configuration, $database;     
      
      if (strstr($date, "00:00:00") OR strlen($date) < 11) {
        $format = $realestatemanager_configuration['date_format'];
        $formatForDateFormat = 'Y-m-d';
      } else {
        $format = $realestatemanager_configuration['date_format']. " "
        . $realestatemanager_configuration['datetime_format'];
        $formatForDateFormat = 'Y-m-d H:i:s';
      }

      $formatForCreateObjDate = str_replace("%","",$format); 
      
      if(function_exists('date_format')){

        $dateObject = date_create_from_format($formatForCreateObjDate, $date);

        if($dateObject){
          $date = date_format($dateObject, $formatForDateFormat);
        }else{
          $dateObject = date_create_from_format($formatForDateFormat, $date);
          if($dateObject){
            $date = date_format($dateObject, $formatForDateFormat);
          }  
        }  
      }else{
        $query = "SELECT STR_TO_DATE('$date','$format')"; 
        $database->setQuery($query);
        $normaDat = $database->loadResult(); 

        if(strlen($normaDat) > 0){
          $date = $normaDat;
        }           
      }
      return $date;
      
    }

  } 


  if(!function_exists('checkRentDayNightREM')){


    function checkRentDayNightREM ($from, $until, $rent_from, $rent_until, $realestatemanager_configuration){

      if(isset($realestatemanager_configuration) && $realestatemanager_configuration['special_price']['show']){

        if (( $rent_from >= $from &&
          $rent_from <= $until) || ($rent_from <= $from && 
            $rent_until >= $until) || ( 
              $rent_until >= $from && $rent_until <= $until))
        {
          return 'Sorry, this item not is available from " '. $from .' " until " '. $until . '"';
        }
      }else{

        if($rent_from === $rent_until){
          return 'Sorry, not one night, not selected'; 
        }

        if($rent_from < $until && $rent_until > $from){
          return 'Sorry, this item not is available from " '. $from .' " until " '. $until . '"';                       
        }                              
      }
    }
  }
  if(!function_exists('createRentTable')){
    function createRentTable($rentTerm, $massage, $typeMessage){
      global $realestatemanager_configuration;
      if($typeMessage === 'error'){
        echo '<div id ="message-here" style ="color: red; font-size: 18px;" >'.$massage.'</div>';
      }else{
        echo '<div id ="message-here" style ="color: gray; font-size: 18px;" >'.$massage.'</div>';            
      }   

      echo '<div id ="SpecialPriseBlock">';
      echo '<table class="adminlist_04" width ="100%" align ="center">';
      echo '<tr>';
      echo '<th class="title" align ="center" width ="25%">'.
      _REALESTATE_MANAGER_RENT_PRICE_PER_DAY.'</th>';
      echo '<th class="title" align ="center" width ="25%">'.
      _REALESTATE_MANAGER_FROM.'</th>';
      echo '<th class="title" align ="center" width ="25%" >'
      ._REALESTATE_MANAGER_TO.'</th>';
      echo '<th class="title" >'._REALESTATE_MANAGER_LABEL_REVIEW_COMMENT.'</th>';
      echo '<th class="title" align ="center" width ="25%">'.
      _REALESTATE_MANAGER_LABEL_CALENDAR_SELECT_DELETE.'</th>';
      echo '</tr>';

      for ($i = 0; $i < count($rentTerm); $i++) {  
        $DateToFormat = str_replace("D",'d',(str_replace("M",'m',(str_replace('%','',
          $realestatemanager_configuration['date_format'])))));
        $date_from = new DateTime($rentTerm[$i]->price_from);
        $date_to = new DateTime($rentTerm[$i]->price_to);

        echo '<tr>';
        echo '<td align ="center">'.$rentTerm[$i]->special_price.'</td>';
        echo '<td align ="center">'.date_format($date_from, $DateToFormat).'</td>';
        echo '<td align ="center">'.date_format($date_to, $DateToFormat).'</td>';
        echo '<td>'.$rentTerm[$i]->comment_price.'</td>';
        echo '<td align ="center"><input type="checkbox" name="del_rent_sal[]" value="'
        .$rentTerm[$i]->id.'"</td>';

        echo '</tr>';
      }   
      echo '</table>';
      echo '<p>';
      

      echo '<p>';             
      echo '</div>' ;

      exit;
    } 
  }


  if(!function_exists('rentPriceREM')){


    function rentPriceREM($bid,$rent_from,$rent_until,$special_price,
      $comment_price,$currency_spacial_price){


      global $database, $realestatemanager_configuration;
      $rent_from_transf = data_transform_rem($rent_from);
      $rent_until_transf = data_transform_rem($rent_until);
      
      if($bid==''){
        $rentTerm = array();
        createRentTable($rentTerm, 'Please save or apply this item first','error');
        return;
      }

      $query = "SELECT * FROM #__rem_rent_sal where fk_houseid = " . $bid;
      $database->setQuery($query);
      $rentTerm = $database->loadObjectList();    


      if($special_price==''){
        createRentTable($rentTerm, 'You need fill Price','error');
      }
      if($rent_from==''){
        createRentTable($rentTerm, 'You need fill Check In','error');
      }
      if($rent_until==''){
        createRentTable($rentTerm, 'You need fill Check Out','error');
      }
      if($rent_from_transf >$rent_until_transf){
        createRentTable($rentTerm, 'Incorrect Check Out','error');
      }

      
      foreach ($rentTerm as $oneTerm){
        $returnMessage = checkRentDayNightREM (($oneTerm->price_from),($oneTerm->price_to),
         $rent_from_transf, $rent_until_transf, $realestatemanager_configuration);

        if(strlen($returnMessage) > 0){
          createRentTable($rentTerm, $returnMessage, 'error');
        }   
      }                                        

      $sql = "INSERT INTO #__rem_rent_sal (fk_houseid, price_from, price_to,
      special_price, priceunit, comment_price) VALUES (" . $bid . ", '" .
      $rent_from_transf . "', '" . $rent_until_transf . "', '" .
      $special_price . "','" . $currency_spacial_price . "','" . 
      $comment_price . "')";             
      $database->setQuery($sql);
      $database->query(); 

      $query = "SELECT * FROM #__rem_rent_sal where fk_houseid = " . $bid;
      $database->setQuery($query);
      $rentTerm = $database->loadObjectList();
      
      createRentTable($rentTerm, 'Add special price on data: from "'.
        $rent_from.'" to "'.$rent_until.'"','');
    }
  }


  if(!function_exists('calculatePriceREM')){

    function calculatePriceREM ($hid,$rent_from,$rent_until,$realestatemanager_configuration,$database,$week){  
      $week = intval($week);
      $date_from = $rent_from;

      $date_until = $rent_until;

      $rent_from = data_transform_rem($rent_from);


      $rent_until = data_transform_rem($rent_until);


      if($rent_from >$rent_until){
        echo '0';exit;
      }
      if($realestatemanager_configuration['special_price']['show']){
        $query = "SELECT * FROM #__rem_rent_sal WHERE fk_houseid = ".$hid .
        " AND (price_from <= ('" .$rent_until. "') AND price_to >= ('" .$rent_from. "'))";   
      }else{
        $query = "SELECT * FROM #__rem_rent_sal WHERE fk_houseid = ".$hid .
        " AND (price_from < ('" .$rent_until. "') AND price_to > ('" .$rent_from. "'))";         
      }
      $database->setQuery($query);
      $data_for_price = $database->loadObjectList(); 
      
      $zapros = "SELECT price, priceunit FROM #__rem_houses WHERE id=" . $hid . ";";
      $database->setQuery($zapros);
      $item_rem = $database->loadObjectList(); 
      $rent_from_ms = date_to_data_ms($rent_from); 
      $rent_to_ms = date_to_data_ms($rent_until);
      

      if($realestatemanager_configuration['special_price']['show']){                      
        $rent_to_ms = $rent_to_ms + (60*60*24);           
      }
      
      $count_day = (($rent_to_ms - $rent_from_ms)/60/60/24);
      $count_day = round($count_day);
      $array_day_between_to_from[0]=$rent_from; 

      for($i = 1; $i < $count_day; $i++){
        $array_day_between_to_from[]=date('Y-m-d',$rent_from_ms + (60*60*24)*($i));
      } 

      $count_day_spashal_price = 0;                 
      $comment_rent_price = '';
      $count_spashal_price = 0;
      
      foreach ($data_for_price as $one_period){
        $from = $one_period->price_from;
        $to = $one_period->price_to; 

        for ($day = 0; $day < $count_day; $day++){ 
          $currentday = ($array_day_between_to_from[$day]);

          if(isset($realestatemanager_configuration)
           && $realestatemanager_configuration['special_price']['show']){
            if (($currentday >= $from) && ($currentday <= $to)){   
              $count_day_spashal_price++;   
              $count_spashal_price += $one_period->special_price;
            }                     
          }else{
            if (($currentday >= $from) && ($currentday < $to)){   
              $count_day_spashal_price++;   
              $count_spashal_price += $one_period->special_price;
            }                      
          } 
        }        
      }

      $count_day_not_sp_price = $count_day - $count_day_spashal_price;
      $sum_price_not_sp_price =  $count_day_not_sp_price * $item_rem[0]->price;
      $sum_price = $sum_price_not_sp_price + $count_spashal_price;

      if ($week == 1)
      {
        $sum_price = $sum_price/6;
      }
      elseif ($week == 2)
      {
        $sum_price = 2*$sum_price/13;
      }
      else {
        $sum_price = 3*$sum_price/20;
      }

      //modification par Skorweb
      $returnArr[0]=$sum_price; 
      $returnArr[1]=$item_rem[0]->priceunit; 
      $returnArr[2]=$comment_rent_price;
      
      return $returnArr;
    }
  }


  if(!function_exists('getCountHouseForSingleUserREM')){


    function getCountHouseForSingleUserREM($my,$database,$realestatemanager_configuration){


      $user_group = userGID_REM($my->id);         
      $user_group_mas = explode(',', $user_group);
      $max_count_house = 0;

      foreach ($user_group_mas as $value) {            
        $count_house_for_single_group =
        $realestatemanager_configuration['user_manager_rem'][$value]['count_homes'];

        if($count_house_for_single_group>$max_count_house){
          $max_count_house = $count_house_for_single_group;
        }            
      }
      
      $count_house_for_single_group = $max_count_house;  
      $database->setQuery("SELECT COUNT('houseid') as `count_homes` " .
       "FROM #__rem_houses WHERE owner_id= '" . $my->id. "'AND published='1'" );
      $house_single_user = $database->loadObject();
      $count_house_single_user = $house_single_user->count_homes;
      $returnarray = array();
      $returnarray[0] = $count_house_single_user;
      $returnarray[1] = $count_house_for_single_group;
      return $returnarray;      
    }
  }

  if(!function_exists('getHTMLPayPalRM')){

    function getHTMLPayPalRM($realestate,$plugin_name_select){
      global $database;
      if(!getPublicPlugin()){
        echo "<div class='alert alert-error'>" . _REALESTATE_MANAGER_RENT_INSTALL_PAYPAL . "</div>";
      }else{
        $dispatcher = JDispatcher::getInstance();
        $plugin = JPluginHelper::importPlugin( 'payment',$plugin_name_select);
        $query = "SELECT profile_value FROM #__user_profiles "
        ."\n WHERE profile_key = 'profile.paypal_email' AND user_id = ".$realestate->owner_id;
        $database->setQuery($query);
        $paypal_email = $database->loadResult();
        $data = array('vtitle' => $realestate->htitle, 'price' => $realestate->price, 'currency_code' => $realestate->priceunit,'owner_paypal_email' => $paypal_email);
        $html = $dispatcher->trigger('getHTMLPayPal', array($data));
        echo $html[0];
      }
    }
  }

  if(!function_exists('getPublicPlugin')){

    function getPublicPlugin(){
     $db = JFactory::getDBO();
     $condtion = array(0 => '\'payment\'');
     $condtionatype = join(',',$condtion);
     if(JVERSION >= '1.6.0')
     {
       $query = "SELECT extension_id as id,name,element,enabled as published
                   FROM #__extensions
                   WHERE folder in ($condtionatype) AND enabled=1";
                 }
                 else
                 {
                   $query = "SELECT id,name,element,published
                   FROM #__plugins
                   WHERE folder in ($condtionatype) AND published=1";
                 }
                 $db->setQuery($query);
                 $gatewayplugin = $db->loadobjectList();

                 $retr = count($gatewayplugin);
                 if($retr>0){
                   $ret_string = "";
                   for($i=0;$i<$retr;$i++){                                           
                     $ret_string .= "<option value='".$gatewayplugin[$i]->name."'>"
                     .$gatewayplugin[$i]->name."</option>";
                   }
                   return $ret_string;
                 } 
                 else{
                   return false;
                 }

               }

             }
             if(!function_exists('saveAssociateCayegoriesREM')){

              function saveAssociateCayegoriesREM($post, $database){

                $currentId = $post['id'];
                if($currentId){
                  $i = 1;
                  $assocArray = array();
                  $assocCategoryId = array();

                  while(isset($post['associate_category'.$i])){
                    $langAssoc = $post['associate_category_lang'.$i];
                    $valAssoc = $post['associate_category'.$i];
                    $assocArray[$langAssoc] = $valAssoc;
                    if($valAssoc){
                      $assocCategoryId[] = $valAssoc;  
                    }

                    $i++;
                  }
                  $currentId = $post['id'];
                  $currentLang = $post['language'];
                  $assocArray[$currentLang] = $currentId;
                  $assocStr = serialize($assocArray);

                  $query = "select `associate_category` from #__rem_main_categories where `id` = ".$currentId."";
                  $database->setQuery($query);
                  $oldAssociate = $database->loadResult(); 
                  $oldAssociateArray = unserialize($oldAssociate);

                  if($oldAssociateArray){
                    foreach ($oldAssociateArray as $key => $value) {
                      if($value && !isset($assocCategoryId[$value])){
                        $assocCategoryId[] = $value;                    
                      }
                    }    
                  }

                  if(!isset($assocCategoryId[$currentId])){
                    $assocCategoryId[] = $currentId;                    
                  }

                  $idToChange = implode(',' , $assocCategoryId);

                  if(count($idToChange) && !empty($idToChange)){  
                    $query = "UPDATE #__rem_main_categories SET `associate_category`='"
                    .$assocStr."' where `id` in (".$idToChange.")";
                    $database->setQuery($query);
                    $database->query();       
                  }       
                }  
              }
            }

            if(!function_exists('getAssociateHousesLang')){

              function getAssociateHousesLang($hoseIds){

                global $database;   
                $query = "select associate_house from #__rem_houses where id = ".$hoseIds.
                " and associate_house is not null";     
                $database->setQuery($query);
                $houseAssociateHouse = $database->loadResult();             
                if (!empty($houseAssociateHouse)){
                  $houseLangIds = unserialize($houseAssociateHouse);
                  return $houseLangIds;
                }
              }   
            }

            if(!function_exists('getAssociateHouses')){

              function getAssociateHouses($hoseIds){

                global $database;

                $one = array();

                $query = "select associate_house from #__rem_houses where id = ".$hoseIds.
                " and associate_house is not null";

                $database->setQuery($query);
                $houseAssociateHouse = $database->loadResult(); 


                if (!empty($houseAssociateHouse)){
                  $hoseIds = unserialize($houseAssociateHouse);

                  foreach($hoseIds as $oneHouse){
                    if($oneHouse != 0){
                      $one[] = $oneHouse;         
                    }
                  } 

                  $bids = implode(',', $one);
                  return $bids;
                }
              }   
            }

            if(!function_exists('getAssociateDiff')){

              function getAssociateDiff($assocArray1,$assocArray2){

               global $database;

               $diff_ids = array();

               $diff = array_diff($assocArray1,$assocArray2);                  
               foreach($diff as $key => $value){
                if($value != 0){
                  $diff_ids[] = $value;                    
                }
              }               

              return $diff_ids ;
            }   
          }

          if(!function_exists('getAssociateOld')){
            function getAssociateOld(){
              global $database;
              $id_check = JRequest::getVar('id', "");
              $query = "select `associate_house` from #__rem_houses where `id` = ".$id_check."";             
              $database->setQuery($query);
              $oldAssociate = $database->loadResult();           
              $oldAssoc_func = unserialize($oldAssociate);
              return $oldAssoc_func;
            }   
          }

          if(!function_exists('ClearAssociateDiff')){

            function ClearAssociateDiff(){

              global $database;
              $old_ids_assoc=array();
              $new_ids_assoc=array();
              $id_check = JRequest::getVar('id', ""); 
              $language_post = JRequest::getVar('language', "");     
              $oldAssociateArray = getAssociateOld();
              $i = 1;
              $assocArray = array();
              while(count(JRequest::getVar("associate_house".$i))){           
               $langAssoc = JRequest::getVar("associate_house_lang".$i);                
               $valAssoc = JRequest::getVar("language_associate_house".$i);              
               $assocArray[$langAssoc] = $valAssoc;                       
               $i++;
             }             
             $assocArray[$language_post] = $id_check;
             if(!empty($oldAssociateArray) && !empty($assocArray))
              $old_ids_assoc = getAssociateDiff($oldAssociateArray,$assocArray);
            if(count($old_ids_assoc)>0)
            {   
              foreach($old_ids_assoc as $key => $value) {             
                $diff_assoc2 = getAssociateHouses($value);    
                if(!empty($diff_assoc2)){
                  $ids_assoc_diff2 = explode(',', $diff_assoc2);
                  foreach ($ids_assoc_diff2 as $key2 => $value2){
                    if(!in_array($value2,$old_ids_assoc)){
                      $assoc_lang = getAssociateHousesLang($value);
                      foreach ($assoc_lang as $key3 => $value3){
                        if($value3 == $value2){
                          $assoc_lang[$key3] = 0;                    
                        } 
                      }
                      $houseLangIds = serialize($assoc_lang);
                      $query = "UPDATE #__rem_houses SET `associate_house`='".$houseLangIds.
                      "' where `id` = ".$value."";
                      $database->setQuery($query);
                      $database->query();                        
                    }
                  }
                }                   
              }
            }  
            if(!empty($oldAssociateArray) && !empty($assocArray))
              $new_ids_assoc = getAssociateDiff($assocArray,$oldAssociateArray);
            if(count($new_ids_assoc)>0)
            {   
              foreach($new_ids_assoc as $key => $value) {            
                $diff_assoc2 = getAssociateHouses($value);  
                if(!empty($diff_assoc2)){
                  $ids_assoc_diff2 = explode(',', $diff_assoc2);
                  foreach ($ids_assoc_diff2 as $key2 => $value2){
                    if($value2 == $value || $value2 == 0 ) continue;
                    $assoc_lang = getAssociateHousesLang($value2);
                    foreach ($assoc_lang as $key3 => $value3){
                      if($value3 == $value){
                        $assoc_lang[$key3] = 0;                    
                      }
                    }                       
                    $houseLangIds = serialize($assoc_lang);
                    $query = "UPDATE #__rem_houses SET `associate_house`='".$houseLangIds.
                    "' where `id` = ".$value2."";
                    $database->setQuery($query);
                    $database->query(); 
                  }
                }                   
              }
            }  
          }   
        }

        if(!function_exists('edit_house_associate')){

          function edit_house_associate($house,$call_from){
            global $my, $database;

            $associateArray = array();
            $userid = $my->id;

            $query = "SELECT lang_code FROM `#__languages` ";
            $database->setQuery($query);
            $allLanguages =  $database->loadColumn(); 
    //bch
            if($call_from=='backend')
            {
              $query = "SELECT id,language,htitle FROM `#__rem_houses`";
            }
            else
            {
              $query = "SELECT id,language,htitle FROM `#__rem_houses` WHERE owner_id = " . $userid . "";
            }



            $database->setQuery($query);
            $allhouse =  $database->loadObjectlist(); 

            $query = "select associate_house from #__rem_houses where id =".$house->id;
            $database->setQuery($query);
            $houseAssociateHouse =  $database->loadResult(); 

            if(!empty($houseAssociateHouse)){
              $houseAssociateHouse = unserialize($houseAssociateHouse);
            }else{
              $houseAssociateHouse = array();
            }

            $i=0;
            foreach ($allLanguages as $oneLang) {
              $i++;
              $associate_house = array();
              $associate_house[] = mosHtml::makeOption(0, 'select'); 

              foreach($allhouse as $oneHouse){
                if($oneLang == $oneHouse->language && $oneHouse->id != $house->id){
                  $associate_house[] = mosHtml::makeOption(($oneHouse->id), $oneHouse->htitle);
                } 
              } 

              if($house->language != $oneLang){

                if(isset($houseAssociateHouse[$oneLang]) && 
                  $houseAssociateHouse[$oneLang] !== $house->id ){
                  $associateArray[$oneLang]['assocId'] = $houseAssociateHouse[$oneLang];
              }else{
                $associateArray[$oneLang]['assocId'] = 0;
              }

              $associate_house_list = mosHTML :: selectList($associate_house, 
                'language_associate_house'.$i,
                'class="inputbox" size="1"', 'value', 'text', 
                $associateArray[$oneLang]['assocId']); 

            }else{
              $associate_house_list = null;
            }

            $associateArray[$oneLang]['list'] = $associate_house_list;

            if(isset($houseAssociateHouse[$oneLang]) && 
              $houseAssociateHouse[$oneLang] !== $house->id ){
              $associateArray[$oneLang]['assocId'] = $houseAssociateHouse[$oneLang];
          }else{
            $associateArray[$oneLang]['assocId'] = 0;
          }
        }    
        return $associateArray;
      }    
    }


    if(!function_exists('save_house_associate')){
      function save_house_associate(){
        global  $database;
        $id_check = JRequest::getVar('id', "");
        $id_true = JRequest::getVar('idtrue', "");
        $language_post = JRequest::getVar('language', "");
        if($id_check){
          if(empty($id_true)){
      //----------get new values (what house we choose for chaque language) --------------------------//
            $i = 1;
            $assocArray = array();
            $assocHouseId = array();
            while(count(JRequest::getVar("associate_house".$i))){           
              $langAssoc = JRequest::getVar("associate_house_lang".$i, '');                
              $valAssoc = JRequest::getVar("language_associate_house".$i,'');
              if($valAssoc == '' ) {
                $i++;
                continue;
              }
              $assocArray[$langAssoc] = $valAssoc;              
              if($valAssoc){
            $assocHouseId[] = $valAssoc;  //----Array of new house_ids                   
          }            
          $i++;
        }
        if(count($assocArray) > 0 ) {
          $assocArray[$language_post] = $id_check;            
          $assocStr = serialize($assocArray);
      //-----------slect associate with old values------------------------------------------//
          $oldAssociateArray = getAssociateOld();
        //----------------------------------------------------------------//
          if(!isset($assocHouseId[$id_check])){        
            $assocHouseId[] = $id_check;                           
          }
          if($assocArray && $oldAssociateArray){
            ksort($assocArray);
            ksort($oldAssociateArray);
          }
          if($assocArray !== $oldAssociateArray){   //-----------compare old and new values--

        //---------set null for houses that are not more in associates----------------//
            ClearAssociateDiff();

        //---------set new associates for houses that are choosed----------------//
        //--ids of new houses  where we set new values for column associate_house
            $idToChange = implode(',' , $assocHouseId); 
            if(count($idToChange) && !empty($idToChange)){  
              $query = "select * from #__rem_rent where `fk_houseid` in (".$idToChange.
              ") and `rent_return` is NULL";
              $database->setQuery($query);
              $CheckAssociate = $database->loadObjectList(); 
              if(!empty($CheckAssociate))
              {
                echo "<script> alert('"._REALESTATE_MANAGER_MUST_RETURN_HOUSES_FROM_RENT
                ."'); window.history.go(-1); </script>";
                exit;
              }
              $query = "UPDATE #__rem_houses SET `associate_house`='".$assocStr.
              "' where `id` in (".$idToChange.")";
              $database->setQuery($query);
              $database->query();            
            }else{
              $query = "UPDATE #__rem_houses SET `associate_house`= null where `id` = ".$id_check."";
              $database->setQuery($query);
              $database->query();     
            }
          }
        }
      }
    } 
  }
}

if(!function_exists('available_dates')){
  function available_dates($house_id){
    global $database,$realestatemanager_configuration;
    $date_NA='';
    $query = "SELECT rent_from, rent_until FROM #__rem_rent WHERE fk_houseid='".$house_id.
    "' AND rent_return is null";
    $database->setQuery($query);
    $calenDate = $database->loadObjectList();
    // create a massiv of all dates when houses are in rent and then is used for 
    // make dates unavailable in calendar for rent 
    foreach($calenDate as $calenDate){     
      $not_av_from = $calenDate->rent_from;
      $not_av_until = $calenDate->rent_until;
      $not_av_from_begin = new DateTime( $not_av_from);
      $not_av_until_end = new DateTime( $not_av_until);
      if($realestatemanager_configuration['special_price']['show']){
        $not_av_until_end = $not_av_until_end->modify( '+1 day' ); 
      }
      // else{
      //   $not_av_from_begin = $not_av_from_begin->modify( '+1 day' );
      // }
      $interval = new DateInterval('P1D');
      $daterange = new DatePeriod($not_av_from_begin, $interval, $not_av_until_end);
      foreach($daterange as $datess){
        $date_NA[] = $datess->format("Y-m-d");
        $date_NA[] = $datess->format("d-m-Y");
      }
    }               
    return $date_NA;
  }   
}
////////////////////////////STORE video/track functions START\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if (!function_exists('storeTrack')) {
  function storeTrack(&$house) {
    global $realestatemanager_configuration, $mosConfig_absolute_path;
    for ($i = 1;isset($_FILES['new_upload_track' . $i]) 
      || array_key_exists('new_upload_track_url' . $i, $_POST);$i++) {
      $track_name = '';
    if (isset($_FILES['new_upload_track' . $i]) && $_FILES['new_upload_track' . $i]['name'] != "") {
          //storing e-Document
      $track = JRequest::getVar('new_upload_track' . $i, '', 'files');
      $ext = pathinfo($track['name'], PATHINFO_EXTENSION);
      $allowed_exts = explode(",", $realestatemanager_configuration['allowed_exts_track']);
      $ext = strtolower($ext);
      if (!in_array($ext, $allowed_exts)) {
        echo "<script> alert(' File ext. not allowed to upload! - " . $ext .
        "'); window.history.go(-1); </script>\n";
        exit();
      }
      $code = guid();
      $track_name = $code . '_' . filter($track['name']);
      if (intval($track['error']) > 0 && intval($track['error']) < 4) {
        echo "<script> alert('" . _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD_ERROR . " - " .
        $track_name . "'); window.history.go(-1); </script>\n";
        exit();
      } else if (intval($track['error']) != 4) {
        $track_new = $mosConfig_absolute_path . $realestatemanager_configuration['tracks']['location'] . $track_name;
        if (!move_uploaded_file($track['tmp_name'], $track_new)) {
          echo "<script> alert('" . _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD_ERROR . " - " .
          $track_name . "'); window.history.go(-1); </script>\n";
          exit();
        }
      }
    }
    if (array_key_exists('new_upload_track_kind' . $i, $_POST) 
      && $_POST['new_upload_track_kind' . $i] != "") {
      $uploadTrackKind = JRequest::getVar('new_upload_track_kind' . $i, '', 'post');
    $uploadTrackKind = strip_tags(trim($uploadTrackKind));
  }
  if (array_key_exists('new_upload_track_scrlang' . $i, $_POST) 
    && $_POST['new_upload_track_scrlang' . $i] != "") {
    $uploadTrackScrlang = JRequest::getVar('new_upload_track_scrlang' . $i, '', 'post');
  $uploadTrackScrlang = strip_tags(trim($uploadTrackScrlang));
}
if (array_key_exists('new_upload_track_label' . $i, $_POST) 
  && $_POST['new_upload_track_label' . $i] != "") {
  $uploadTrackLabel = JRequest::getVar('new_upload_track_label' . $i, '', 'post');
$uploadTrackLabel = strip_tags(trim($uploadTrackLabel));
}
if (array_key_exists('new_upload_track_url' . $i, $_POST) && $_POST['new_upload_track_url' . $i] != "") {
  $uploadTrackURL = JRequest::getVar('new_upload_track_url' . $i, '', 'post');
  $uploadTrackURL = strip_tags(trim($uploadTrackURL));
  if (empty($track_name) && !empty($uploadTrackURL))
    saveTracks($house->id, $uploadTrackURL, $uploadTrackKind, $uploadTrackScrlang, $uploadTrackLabel);          
}
if (!empty($track_name)) 
  saveTracks($house->id, $track_name, $uploadTrackKind, $uploadTrackScrlang, $uploadTrackLabel);
}
}
}

if (!function_exists('checkMimeType')) {
  function checkMimeType($ext) {
    global $database;
    $database->setQuery("SELECT mime_type FROM #__rem_mime_types WHERE mime_ext=".$database->quote($ext));
    $type = $database->loadResult();
    if(!$type)
      $type = 'unknown';
    return $type;
  }
}

if (!function_exists('filter')) {
  function filter($value) {
    $value = str_replace(array("/", "|", "\\", "?", ":", ";", "*", "#", "%", "$", "+", "=", ";", " "), "_", $value);
    return $value;
  }
}

if (!function_exists('guid')) {
  function guid() {
    if (function_exists('com_create_guid')) {
      return com_create_guid();
    } else {
      mt_srand((double)microtime() * 10000); //optional for php 4.2.0 and up.
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45); // "-"
      $uuid = //chr(123)// "{"
      substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
      //.chr(125);// "}"
      return $uuid;
    }
  }
}

jimport( 'joomla.filesystem.file' );
if(!function_exists('rem_createImage')){
  function rem_createImage($imgSrc, $imgDest, $width, $height, $crop = true, $quality = 90) {
    if (JFile::exists($imgDest)) {
      $info = getimagesize($imgDest, $imageinfo);
      if (($info[0] == $width) && ($info[1] == $height)) {
        return;
      }
    }
    if (JFile::exists($imgSrc)) {

      $info = getimagesize($imgSrc, $imageinfo);
      $sWidth = $info[0];
      $sHeight = $info[1];
      rem_resize_img($imgSrc, $imgDest, $width, $height, $crop, $quality);
    }
  }
}
if(!function_exists('rem_resize_img')){
  function rem_resize_img($imgSrc, $imgDest, $tmp_width, $tmp_height, $crop = true, $quality = 90) {
   global $mosConfig_absolute_path;

   $info = getimagesize($imgSrc, $imageinfo);
   $sWidth = $info[0];
   $sHeight = $info[1];

   if($sWidth == 0 || $tmp_width == 0 ) {
    $logPath  = $mosConfig_absolute_path . "/administrator/components/com_realestatemanager/my_log.log";
    file_put_contents($logPath, "  rem_resize_img zero ".$imgSrc."::".$sWidth ."::" . $tmp_width."\n",  FILE_APPEND );

    $imgSrc = $mosConfig_absolute_path."/components/com_realestatemanager/photos/"."no-img_eng_big.gif";
    $info = getimagesize($imgSrc, $imageinfo);
    $sWidth = $info[0];
    $sHeight = $info[1];
  }

  if ($sHeight / $sWidth > $tmp_height / $tmp_width) {
    $width = $sWidth;
    $height = round(($tmp_height * $sWidth) / $tmp_width);
    $sx = 0;
    $sy = round(($sHeight - $height) / 3);
  }
  else {
    $height = $sHeight;
    $width = round(($sHeight * $tmp_width) / $tmp_height);
    $sx = round(($sWidth - $width) / 2);
    $sy = 0;
  }

  if (!$crop) {
    $sx = 0;
    $sy = 0;
    $width = $sWidth;
    $height = $sHeight;
  }

  $ext = str_replace('image/', '', $info['mime']);
  $imageCreateFunc = rem_getImageCreateFunction($ext);
  $imageSaveFunc = rem_getImageSaveFunction($ext);

  $sImage = $imageCreateFunc($imgSrc);
  $dImage = imagecreatetruecolor($tmp_width, $tmp_height);

        // Make transparent
  if ($ext == 'png') {
    imagealphablending($dImage, false);
    imagesavealpha($dImage,true);
    $transparent = imagecolorallocatealpha($dImage, 255, 255, 255, 127);
    imagefilledrectangle($dImage, 0, 0, $tmp_width, $tmp_height, $transparent);
  }

  imagecopyresampled($dImage, $sImage, 0, 0, $sx, $sy, $tmp_width, $tmp_height, $width, $height);

  if ($ext == 'png') {
    $imageSaveFunc($dImage, $imgDest, 9);
  }
  else if ($ext == 'gif') {
    $imageSaveFunc($dImage, $imgDest, $quality);
  }
  else {
    $imageSaveFunc($dImage, $imgDest, $quality);
  }
}
}
if(!function_exists('rem_getImageCreateFunction')){
  function rem_getImageCreateFunction($type) {
    switch ($type) {
      case 'jpeg':
      case 'jpg':
      $imageCreateFunc = 'imagecreatefromjpeg';
      break;

      case 'png':
      $imageCreateFunc = 'imagecreatefrompng';
      break;

      case 'bmp':
      $imageCreateFunc = 'imagecreatefrombmp';
      break;

      case 'gif':
      $imageCreateFunc = 'imagecreatefromgif';
      break;

      case 'vnd.wap.wbmp':
      $imageCreateFunc = 'imagecreatefromwbmp';
      break;

      case 'xbm':
      $imageCreateFunc = 'imagecreatefromxbm';
      break;

      default:
      $imageCreateFunc = 'imagecreatefromjpeg';
    }

    return $imageCreateFunc;
  }
}
if(!function_exists('rem_getImageSaveFunction')){
  function rem_getImageSaveFunction($type) {
    switch ($type) {
      case 'jpeg':
      $imageSaveFunc = 'imagejpeg';
      break;

      case 'png':
      $imageSaveFunc = 'imagepng';
      break;

      case 'bmp':
      $imageSaveFunc = 'imagebmp';
      break;

      case 'gif':
      $imageSaveFunc = 'imagegif';
      break;

      case 'vnd.wap.wbmp':
      $imageSaveFunc = 'imagewbmp';
      break;

      case 'xbm':
      $imageSaveFunc = 'imagexbm';
      break;

      default:
      $imageSaveFunc = 'imagejpeg';
    }

    return $imageSaveFunc;
  }
}

/**
 * Saves the record on an edit form submit
 * @param database A database connector object
 */
if (!function_exists('rem_picture_thumbnail')) {
  function rem_picture_thumbnail($file, $high_original, $width_original) {
    global $mosConfig_absolute_path, $realestatemanager_configuration;
    $params3 = $realestatemanager_configuration['thumb_param']['show'];
    $uploaddir = $mosConfig_absolute_path . '/components/com_realestatemanager/photos/';
      //file name and extention
    if(!file_exists($mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file))
      $file = 'no-img_eng_big.gif';
      $file_inf = pathinfo($file);
      $file_type = '.' . $file_inf['extension'];
      $file_name = basename($file, $file_type);
      $index = '';
      // Setting the resize parameters
      list($width, $height) = getimagesize($mosConfig_absolute_path .
       '/components/com_realestatemanager/photos/' . $file);

      $size = "_" . $high_original . "_" . $width_original;
      
      if (file_exists($mosConfig_absolute_path . '/components/com_realestatemanager/photos/'
       . $file_name . $size . $index . $file_type)) {
        return $file_name . $size . $index . $file_type;
    } else {
      if ($width < $height) {
        if ($height > $high_original) {
          $k = $height / $high_original;
        } else if ($width > $width_original) {
          $k = $width / $width_original;
        }
        else
          $k = 1;
      } else {
        if ($width > $width_original) {
          $k = $width / $width_original;
        } else if ($height > $high_original) {
          $k = $height / $high_original;
        }
        else
          $k = 1;
      }
      $w_ = $width / $k;
      $h_ = $height / $k;
    }

    if($params3 == 1){ 
     $index = "_2_";
     $CreateNewImage = rem_createImage($uploaddir.$file, $uploaddir.$file_name . $size
      . $index . $file_type, $high_original, $width_original);
     return $file_name . $size . $index . $file_type;
   }
      // Creating the Canvas
   $tn = imagecreatetruecolor($w_, $h_);
   $index = "_1_";
   switch (strtolower($file_type)) {
    case '.png':
    $source = imagecreatefrompng($mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file);
    $file = imagecopyresampled($tn, $source, 0, 0, 0, 0, $w_, $h_, $width, $height);
    imagepng($tn, $mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file_name . $size . $index . $file_type);
    break;
    case '.jpg':
    $source = imagecreatefromjpeg($mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file);
    $file = imagecopyresampled($tn, $source, 0, 0, 0, 0, $w_, $h_, $width, $height);
    imagejpeg($tn, $mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file_name . $size . $index . $file_type);
    break;
    case '.jpeg':
    $source = imagecreatefromjpeg($mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file);
    $file = imagecopyresampled($tn, $source, 0, 0, 0, 0, $w_, $h_, $width, $height);
    imagejpeg($tn, $mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file_name . $size . $index . $file_type);

    break;
    case '.gif':
    $source = imagecreatefromgif($mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file);
    $file = imagecopyresampled($tn, $source, 0, 0, 0, 0, $w_, $h_, $width, $height);
    imagegif($tn, $mosConfig_absolute_path .
     '/components/com_realestatemanager/photos/' . $file_name . $size . $index . $file_type);
    break;
    default:
    echo 'not support';
    return;
  }

  return $file_name . $size . $index . $file_type;
}
}

if (!function_exists('storeVideo')) {
  function storeVideo(&$house) {
    global $realestatemanager_configuration, $mosConfig_absolute_path;
    for ($i = 1;isset($_FILES['new_upload_video' . $i]) 
      || array_key_exists('new_upload_video_url' . $i, $_POST) 
      || array_key_exists('new_upload_video_youtube_code' . $i, $_POST);$i++) {
      $video_name = '';
    if (isset($_FILES['new_upload_video' . $i]) && $_FILES['new_upload_video' . $i]['name'] != "") {
          //storing e-Document
      $video = JRequest::getVar('new_upload_video' . $i, '', 'files');
      $ext = pathinfo($video['name'], PATHINFO_EXTENSION);
      $allowed_exts = explode(",", $realestatemanager_configuration['allowed_exts_video']);
      $ext = strtolower($ext);

      if (!in_array($ext, $allowed_exts)) {
        echo "<script> alert(' File ext. not allowed to upload! - " . $ext.
        "'); window.history.go(-1); </script>\n";
        exit();
      }

      $type = checkMimeType($ext);

      if (stripos($type, $video['type'])===false) {
        echo "<script> alert(' File ext. not allowed to upload! - " . $ext.
        "'); window.history.go(-1); </script>\n";
        exit();
      }
      $code = guid();
      $video_name = $code . '_' . filter($video['name']);
      if (intval($video['error']) > 0 && intval($video['error']) < 4) {
        echo "<script> alert('" . _REALESTATE_MANAGER_LABEL_VIDEO_UPLOAD_ERROR . " - " .
        $video_name . "'); window.history.go(-1); </script>\n";
        exit();
      } else if (intval($video['error']) != 4) {
        $video_new = $mosConfig_absolute_path . $realestatemanager_configuration['videos']['location']  . $video_name;
        if (!move_uploaded_file($video['tmp_name'], $video_new)) {
          echo "<script> alert('" . _REALESTATE_MANAGER_LABEL_VIDEO_UPLOAD_ERROR . " - " .
          $video_name . "'); window.history.go(-1); </script>\n";
          exit();
        }
        saveVideos($video_name, $house->id, $type);
      }
    }
    if (array_key_exists('new_upload_video_url' . $i, $_POST) && $_POST['new_upload_video_url' . $i] != "") {
      $uploadVideoURL = JRequest::getVar('new_upload_video_url' . $i, '', 'post');
      $uploadVideoURL = strip_tags(trim($uploadVideoURL));
      $end = explode(".", $uploadVideoURL);
      $ext = end($end);
      $type = checkMimeType($ext);
      if(empty($video_name) && !empty($uploadVideoURL))
        saveVideos($uploadVideoURL, $house->id, $type);
    }
    if (array_key_exists('new_upload_video_youtube_code' . $i, $_POST) 
      && $_POST['new_upload_video_youtube_code' . $i] != "") {
      $uploadVideoYoutubeCode = JRequest::getVar('new_upload_video_youtube_code' . $i, '', 'post');
    $uploadVideoYoutubeCode = strip_tags(trim($uploadVideoYoutubeCode));
    saveYouTubeCode($uploadVideoYoutubeCode, $house->id);
  }
}
}
}

if (!function_exists('saveTracks')) {
  function saveTracks($h_id, $src, $uploadTrackKind, $uploadTrackScrlang, $uploadTrackLabel) {
    global $database,$realestatemanager_configuration, $mosConfig_absolute_path;
    if ($src != "" && !strstr($src, "http")) {
      $query = "INSERT INTO #__rem_track_source (fk_house_id,src,kind,scrlang,label)".
      "\n VALUE ($h_id,
        '" . $realestatemanager_configuration['tracks']['location'].$src . "',
        '" . $uploadTrackKind . "',
        '" . $uploadTrackScrlang . "',
        '" . $uploadTrackLabel . "')";
      }else{
        $query ="INSERT INTO #__rem_track_source (fk_house_id,src,kind,scrlang,label)".
        "\n VALUE ($h_id,
          '" . $src."',
          '" . $uploadTrackKind . "',
          '" . $uploadTrackScrlang . "',
          '" . $uploadTrackLabel . "')";
        }
        $database->setQuery($query);
        $database->query();
      }
    }

    if (!function_exists('saveVideos')) {
      function saveVideos($src, $h_id, $type) {
        global $database,$realestatemanager_configuration, $mosConfig_absolute_path;
        if ($src != "" && strstr($src, "http")) {
          $query = "INSERT INTO #__rem_video_source(fk_house_id, src, type)".
          "\n VALUE($h_id,'" . $src . "', '" . $type . "')";
        }else{
          $query = "INSERT INTO #__rem_video_source(fk_house_id,src,type)".
          "\n VALUE($h_id,
            '".$realestatemanager_configuration['videos']['location'].$src."',
            '".$type."')";
          }
          $database->setQuery($query);
          $database->query();
        }
      }

      if (!function_exists('saveYouTubeCode')) {
        function saveYouTubeCode($youtube_code, $h_id) {
          global $database;
      $database->setQuery("SELECT id FROM #__rem_video_source 
        WHERE youtube != '' 
        AND fk_house_id = $h_id");
      $database->query();
      $youtubeId = $database->LoadResult();
      if ($youtube_code != '' && !empty($youtubeId)) {
        $query = "UPDATE #__rem_video_source".
        "\n SET youtube = '" . $youtube_code . "'".
        "\n WHERE id = $youtubeId";
      } else {
        $query = "INSERT INTO #__rem_video_source (fk_house_id,youtube)". 
        "\n VALUE($h_id,'" . $youtube_code . "')";
      }
      $database->setQuery($query);
      $database->query();
    }
  }


////////////////////////////STORE video/track functions END\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

///////////////////////////DELETE video/track functions START\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
  if (!function_exists('deleteTracks')) {
    function deleteTracks($h_id) {
      global $database, $mosConfig_absolute_path, $mosConfig_live_site, $realestatemanager_configuration;
      $database->setQuery("SELECT id FROM #__rem_track_source where fk_house_id = $h_id;");
      $tdiles_id = $database->loadColumn();
      $deleteTr_id = array();
      foreach($tdiles_id as $key => $value) {
        if (isset($_POST['track_option_del' . $value])) {
          array_push($deleteTr_id, JRequest::getVar('track_option_del' . $value, '', 'post'));
        }
      }
      if ($deleteTr_id) {
        $del_tid = implode(',', $deleteTr_id);
        $sql = "SELECT src FROM #__rem_track_source WHERE id IN (" .$del_tid . ")";
        $database->setQuery($sql);
        $tracks = $database->loadColumn();
        if ($tracks) {
          foreach($tracks as $name) {
            if (substr($name, 0, 4) != "http") unlink($mosConfig_absolute_path . $name);
          }
        }
        $sql = "DELETE FROM #__rem_track_source WHERE (id IN (" . $del_tid . ")) 
        AND (fk_house_id = $h_id)";
        $database->setQuery($sql);
        $database->query();
      }
    }
  }

  if (!function_exists('deleteVideos')) {
    function deleteVideos($h_id) {
      global $database, $mosConfig_absolute_path, $mosConfig_live_site, $realestatemanager_configuration;
      $database->setQuery("SELECT id FROM #__rem_video_source where fk_house_id = $h_id;");
      $vdiles_id = $database->loadColumn();
      $deleteVid_id = array();
      foreach($vdiles_id as $key => $value) {
        if (isset($_POST['video_option_del' . $value])) {
          array_push($deleteVid_id, JRequest::getVar('video_option_del' . $value, '', 'post'));
        }
      }
      $database->setQuery("SELECT id FROM #__rem_video_source where fk_house_id = $h_id AND youtube IS NOT NULL;");
      $youtubeid = $database->loadResult();
      if (!empty($youtubeid)) {
        if (isset($_POST['youtube_option_del' . $youtubeid])) {
          $y_t_id = mosGetParam($_REQUEST, 'youtube_option_del' . $youtubeid, '');
        $sql = "DELETE FROM #__rem_video_source 
        WHERE id = $y_t_id 
        AND fk_house_id=$h_id";
        $database->setQuery($sql);
        $database->query();
      }
    }
    if ($deleteVid_id) {
      $del_id = implode(',', $deleteVid_id);
      $sql = "SELECT src FROM #__rem_video_source WHERE id IN (". $del_id . ")";
      $database->setQuery($sql);
      $videos = $database->loadColumn();
      if ($videos) {
        foreach($videos as $name) {
          if (substr($name, 0, 4) != "http" && file_exists($mosConfig_absolute_path . $name)) 
            unlink($mosConfig_absolute_path . $name);
        }
      }
      $sql = "DELETE FROM #__rem_video_source 
      WHERE (id IN (" . $del_id . ")) 
      AND (fk_house_id=$h_id)";
      $database->setQuery($sql);
      $database->query();
    }
  }
}
///////////////////////////DELETE video/track fucntions END\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if(!function_exists('return_bytes')){
  function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // Модификатор 'G' доступен, начиная с PHP 5.1.0
      case 'g':
      $val *= 1024;
      case 'm':
      $val *= 1024;
      case 'k':
      $val *= 1024;
    }
    return $val;
  } 
}

if(!function_exists('getAvilableRM')){
  function getAvilableRM ($calenDate,$month,$year,$realestatemanager_configuration,$day){
    global $flag3;
    if(strlen($month) == 1){
      $month = '0'.$month ;
    }
    if(strlen($day) == 1){
      $day = '0'.$day ;                     
    }
    $toDay = $day+1;
    if(strlen($toDay) == 1){
      $toDay = '0'.$toDay ;
    }
    $cheackDataFrom = $year.'-'.$month.'-'.$day;
    $cheackDataTo = $year.'-'.$month.'-'.$toDay;
    foreach ($calenDate as $oneTerm){
      $from=explode(' ',$oneTerm->rent_from);
      $until=explode(' ',$oneTerm->rent_until);
      if($cheackDataFrom >= $oneTerm->rent_until)continue;
      $resultmsg = checkRentDayNightREM (($oneTerm->rent_from),($oneTerm->rent_until), $cheackDataFrom, $cheackDataTo, $realestatemanager_configuration);       
      if($cheackDataTo <= date('Y-m-d') && strlen($resultmsg) > 1){
        if(!$realestatemanager_configuration['special_price']['show'] 
          && ($cheackDataFrom == $until[0] || $cheackDataFrom == $from[0] )){
          if($flag3){
            $flag3 = false;
            return 'calendar_day_gone_not_avaible_night_end';
          }else{
            $flag3 = true;
            return 'calendar_day_gone_not_avaible_night_start';
          }
        }
        return 'calendar_day_gone_not_avaible';
      } 
      if(strlen($resultmsg) > 1){
        if(!$realestatemanager_configuration['special_price']['show'] 
          && ($cheackDataFrom == $until[0] || $cheackDataFrom == $from[0] )){
          if($flag3){
            $flag3 = false;
            return 'calendar_not_available_night_end';
          }else{
            $flag3 = true;
            return 'calendar_not_available_night_start';
          }  
        }
        return 'calendar_not_available';
      }
      if($cheackDataTo <= date('Y-m-d')){
        return 'calendar_day_gone_avaible';
      }
    }  
    if(isset($cheackDataTo) && $cheackDataTo <= date('Y-m-d')){
      return 'calendar_day_gone_avaible';
    } 
    return 'calendar_available';
  }
}
if(!function_exists('com_house_categoryTreeList')){
  function com_house_categoryTreeList($id, $action, $is_new, &$options = array(), $catid='', $lang='') {
    global $database;
    if($lang){
      $list = com_house_categoryArray($lang);
    } else {
      $list = com_house_categoryArray();
    }        
    $cat = new mainRealEstateCategories($database);        
    $cat->load($id);

    $this_treename = '';
    $childs_ids = Array();
    foreach ($list as $item) {
      if ($item->id == $cat->id || array_key_exists($item->parent_id, $childs_ids))
        $childs_ids[$item->id] = $item->id;
    }

    foreach ($list as $item) {
      if ($this_treename) {
        if ($item->id != $cat->id
          && strpos($item->treename, $this_treename) === false
          && array_key_exists($item->id, $childs_ids) === false) {
          $options[] = mosHTML::makeOption($item->id, $item->treename);
        }
      } else {
        if ($item->id != $cat->id) {
          $options[] = mosHTML::makeOption($item->id, $item->treename);
        } else {
          $this_treename = "$item->treename/";
        }
      }
    }

    $parent = null;
    $parent = mosHTML::selectList($options, 'catid', 'class="inputbox" size="1" style="width: 115px"', 'value', 'text', $catid);
    return $parent;
  }
}

if(!function_exists('com_house_categoryArray')){
  function com_house_categoryArray($lang='') {
    global $database, $my, $acl;
        // get a list of the menu items
    $query = "SELECT c.*, c.parent_id AS parent, c.params AS access"
    . "\n FROM #__rem_main_categories c"
    . "\n WHERE section='com_realestatemanager'"
    . "\n AND published <> 0 ";
    if($lang){
      $query .=  " AND ($lang)"
      . "\n ORDER BY ordering";
    } else {
      $query .= " ORDER BY ordering";
    }
    $database->setQuery($query);
    $items = $database->loadObjectList();
    foreach ($items as $r => $cat_item) {
      if (!checkAccess_REM($cat_item->access, 'NORECURSE', userGID_REM($my->id), $acl)) {
            //if have not access then remove item from search
        unset($items[$r]);
      }
    } 
    $items = array_values($items);
        // establish the hierarchy of the menu
    $children = array();
        // first pass - collect children
    foreach ($items as $v) {
      $pt = $v->parent;
      $list = @$children[$pt] ? $children[$pt] : array();
      array_push($list, $v);
      $children[$pt] = $list;
    }
        // second pass - get an indent list of the items
    $array = mosTreeRecurseREM(0, '', array(), $children);

    return $array;
  }
}
if (!function_exists('checkJavaScriptIncludedRE')) {
  function checkJavaScriptIncludedRE($name) {

    $doc = JFactory::getDocument();

    foreach($doc->_scripts as $script_path=>$value){
      if(strpos( $script_path, $name ) !== false ) return true ;
    }
    return false;
  }
}