<?php
if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
defined("DS") OR define("DS", DIRECTORY_SEPARATOR);
/**
 *
 * @package  RealEstateManager
 * @copyright 2012 Andrey Kvasnevskiy-OrdaSoft(akbet@mail.ru); Rob de Cleen(rob@decleen.com)
 * Homepage: http://www.ordasoft.com
 * @version: 3.9 Pro
 *
 * */
$mosConfig_absolute_path = $GLOBALS['mosConfig_absolute_path'] = JPATH_SITE;
global $mosConfig_lang, $user_configuration; // for 1.6
$mainframe = JFactory::getApplication(); // for 1.6
$GLOBALS['mainframe'] = $mainframe;

if (get_magic_quotes_gpc()) {

    function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }

    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

jimport('joomla.html.pagination');
require_once($mosConfig_absolute_path . "/components/com_realestatemanager/compat.joomla1.5.php");
if (version_compare(JVERSION, "3.0.0", "lt"))
    include_once($mosConfig_absolute_path . '/libraries/joomla/application/pathway.php'); // for 1.6
include_once($mosConfig_absolute_path .
   '/components/com_realestatemanager/realestatemanager.main.categories.class.php');
jimport('joomla.application.pathway');
jimport('joomla.html.pagination');
jimport('joomla.filesystem.folder');

$database = JFactory::getDBO();

require_once $mosConfig_absolute_path . 
"/administrator/components/com_realestatemanager/language.php";
require_once($mosConfig_absolute_path . 
  "/components/com_realestatemanager/captcha.php");
/** load the html drawing class */
require_once ($mosConfig_absolute_path .
   "/components/com_realestatemanager/realestatemanager.class.rent.php");
require_once ($mosConfig_absolute_path .
 "/components/com_realestatemanager/realestatemanager.html.php"); // for 1.6
require_once ($mosConfig_absolute_path .
 "/components/com_realestatemanager/realestatemanager.class.php"); // for 1.6
require_once ($mosConfig_absolute_path .
   "/components/com_realestatemanager/realestatemanager.class.rent_request.php");
require_once ($mosConfig_absolute_path .
   "/components/com_realestatemanager/realestatemanager.class.buying_request.php");
require_once ($mosConfig_absolute_path .
   "/components/com_realestatemanager/realestatemanager.class.rent.php");
require_once ($mosConfig_absolute_path .
   "/components/com_realestatemanager/realestatemanager.class.review.php");
require_once ($mosConfig_absolute_path .
   "/administrator/components/com_realestatemanager/realestatemanager.class.others.php");
//added 2012_06_05 that's because it doesn't work with enabled plugin System-Legacy, so if it works, let it work :)
require_once($mosConfig_absolute_path .
   "/components/com_realestatemanager/functions.php");
require_once($mosConfig_absolute_path .
   "/components/com_realestatemanager/includes/menu.php");

require_once ($mosConfig_absolute_path .
   "/administrator/components/com_realestatemanager/realestatemanager.class.impexp.php");

//added 2012_06_05 that's because it doesn't work with enabled plugin System-Legacy, so if it works, let it work :)
if (!array_key_exists('realestatemanager_configuration', $GLOBALS)) {
    require_once ($mosConfig_absolute_path .
       "/administrator/components/com_realestatemanager/realestatemanager.class.conf.php");
    $GLOBALS['realestatemanager_configuration'] = $realestatemanager_configuration;
} else
global $realestatemanager_configuration;

if (!isset($option))
    $GLOBALS['option'] = $option = mosGetParam($_REQUEST, 'option', 'com_realestatemanager');
else
    $GLOBALS['option'] = $option;

if (isset($option) && $option == "com_simplemembership") {
    if (!array_key_exists('user_configuration2', $GLOBALS)) {
        require_once (JPATH_SITE . DS . 'administrator' . DS . 'components' . DS .
           'com_simplemembership' . DS . 'admin.simplemembership.class.conf.php');
        $GLOBALS['user_configuration2'] = $user_configuration;
    } else {
        global $user_configuration;
    }
}

//remove_langs();exit;
$my = JFactory::getUser();
$acl = JFactory::getACL();
$GLOBALS['my'] = $my;
$GLOBALS['acl'] = $acl;
$id = intval(protectInjectionWithoutQuote('id', 0));
$catid = intval(mosGetParam($_REQUEST,'catid', 0));
$bids = mosGetParam($_REQUEST, 'bid', array(0));
$Itemid = protectInjectionWithoutQuote('Itemid', 0);
$printItem = trim(mosGetParam($_REQUEST, 'printItem', ""));
$doc = JFactory::getDocument(); // for 1.6
$GLOBALS['doc'] = $doc; // for 1.6
$GLOBALS['op'] = $doc; // for 1.6
$doc->setTitle(_REALESTATE_MANAGER_TITLE); // for 1.6

if (!isset($GLOBALS['Itemid']))
    $GLOBALS['Itemid'] = JRequest::getInt('Itemid');
if (!isset($GLOBALS['Itemid']))
    $GLOBALS['Itemid'] = $Itemid = intval(protectInjectionWithoutQuote('Itemid', 0));

// paginations
$intro = $realestatemanager_configuration['page']['items']; // page length

if ($intro) {
    $paginations = 1;
    $limit = intval(protectInjectionWithoutQuote('limit', $intro));
    $GLOBALS['limit'] = $limit;
    $limitstart = intval(protectInjectionWithoutQuote('limitstart', 0));
    $GLOBALS['limitstart'] = $limitstart;
    $total = 0;
    $LIMIT = 'LIMIT ' . $limitstart . ',' . $limit;
} else {
    $paginations = 0;
    $LIMIT = '';
}

$session = JFactory::getSession();
$session->set("array", $paginations);

if (!isset($task))
    $GLOBALS['task'] = $task = mosGetParam($_REQUEST, 'task', '');
else {
    $GLOBALS['task'] = $task;
}

if (isset($_REQUEST['view']))
    $view = protectInjectionWithoutQuote('view', '');


if ((!isset($task) OR $task == '') AND isset($view))
    $GLOBALS['task'] = $task = $view;

// if ((!isset($task) OR $task == '') AND (protectInjectionWithoutQuote('start', '') != '')){    
//     $GLOBALS['task'] = $task = protectInjectionWithoutQuote('start', '');
// }

if ( (!isset($task) OR $task == '' ) && (!isset($view) OR $view == '') ){
    $app = new JSite();
    $menu = $app->getMenu() ;
    $item  = $menu->getActive();
    if( isset($item) ) $GLOBALS['task'] = $task = $item->query['view'];
}

if (isset($_REQUEST['submit']) && $_REQUEST['submit'] == "[ Rent Request ]")
    $task = "rent_request";

if ($realestatemanager_configuration['debug'] == '1') {
    echo "Task: " . $task . "<br />";
    print_r($_REQUEST);
    echo "<hr /><br />";
}

$bid = mosGetParam($_REQUEST, 'bid', array(0));
// -
if(isset($_REQUEST["bid"]) AND isset ($_REQUEST["rent_from"]) AND isset($_REQUEST["rent_until"])){

    $bid_ajax_rent = $_REQUEST["bid"];
    $rent_from = $_REQUEST["rent_from"];
    $rent_until = $_REQUEST["rent_until"];
    $week = $_REQUEST["week"];
    
    if(isset($_REQUEST["special_price"])){
     $special_price = $_REQUEST["special_price"]; 
 }
 if(isset($_REQUEST["currency_spacial_price"])){
     $currency_spacial_price = $_REQUEST["currency_spacial_price"];
 }  

 if(isset($_REQUEST["comment_price"])){
    $comment_price = $_REQUEST["comment_price"];
} else {
    $comment_price = '';
}
}
// print_r($task);exit;
switch ($task) {
    case 'paypal':
    PHP_realestatemanager::paypal();
    break;
    case 'ajax_rent_calcualete':        
    PHP_realestatemanager::ajax_rent_calcualete($bid_ajax_rent,$rent_from,$rent_until,$week);
    break;
    case 'ajax_update_check_payment':        
    PHP_realestatemanager::ajax_update_check_payment($order_id);
    break;
    case 'secret_image':
    PHP_realestatemanager::secretImage();
    break;

    case 'show_search_house':
    if (version_compare(JVERSION, '3.0', 'ge')) {
        $menu = new JTableMenu($database);
        $menu->load($GLOBALS['Itemid']);
        $params = new JRegistry;
        $params->loadString($menu->params);
    } else {
        $menu = new mosMenu($database);
        $menu->load($GLOBALS['Itemid']);
        $params = new mosParameters($menu->params);
    }
    $layout = $params->get('showsearchhouselayout', '');
    PHP_realestatemanager::showSearchHouses($option, $catid, $option, $layout);
    break;
    case 'show_search':
    PHP_realestatemanager::showSearchHouses($option, $catid, $option);
    break;

    case 'search':
    PHP_realestatemanager::searchHouses($option, $catid, $option, $languagelocale);
    break;

    case 'all_houses':
    if (version_compare(JVERSION, '3.0', 'ge')) {
        $menu = new JTableMenu($database);
        $menu->load($GLOBALS['Itemid']);
        $params = new JRegistry;
        $params->loadString($menu->params);
    } else {
        $menu = new mosMenu($database);
        $menu->load($GLOBALS['Itemid']);
        $params = new mosParameters($menu->params);
    }
    $layout = $params->get('allhouselayout', '');
    if ($layout == '')
        $layout = 'default';
    PHP_realestatemanager::ShowAllHouses($layout, $printItem);
    break;
    case 'view_house':
    case 'view':
    if (version_compare(JVERSION, '3.0', 'ge')) {
        $menu = new JTableMenu($database);
        $menu->load($GLOBALS['Itemid']);
        $params = new JRegistry;
        $params->loadString($menu->params);
    } else {
        $menu = new mosMenu($database);
        $menu->load($GLOBALS['Itemid']);
        $params = new mosParameters($menu->params);
    }
    $layout = $params->get('viewhouselayout', '');

    if ($layout == '' && isset($catid) && $catid != 0) { 
        $query = "SELECT params2 FROM #__rem_main_categories WHERE id =" . $catid;
        $database->setQuery($query);
        $params2 = $database->loadResult();
        $object_params = unserialize($params2);
        if (isset($object_params->view_house))
            $layout = $object_params->view_house;
    }
    if ($id) {

        $query = "SELECT id FROM #__rem_houses WHERE id =" . $id;
        $database->setQuery($query);
        $id_tmp = $database->loadObjectList();
        if( !isset( $id_tmp[0] ) ){ 
            echo"<br /><br /><h1 style='text-align:center'>" . _REALESTATE_MANAGER_LABEL_SEARCH_NOTHING_FOUND . "</h1>";
            return;
        }

        $query = "SELECT idcat AS catid FROM #__rem_categories WHERE iditem=" . $id;
        $database->setQuery($query);
        $catid = $database->loadObjectList();

        $logPath  = $mosConfig_absolute_path . "/administrator/components/com_realestatemanager/my_log.log";
        if( !isset( $catid[0] ) ) file_put_contents($logPath, " Get category ".$id."::".$task."  ".time()."  \n",  FILE_APPEND );

            $catid = $catid[0]->catid;
            PHP_realestatemanager::showItemREM($option, $id, $catid, $printItem, $layout);
        } else {
            if (version_compare(JVERSION, '3.0', 'ge')) {
                $menu = new JTableMenu($database);
                $menu->load($Itemid);
                $params = new JRegistry;
                $params->loadString($menu->params);
            } else {
                $menu = new mosMenu($database);
                $menu->load($GLOBALS['Itemid']);
                $params = new mosParameters($menu->params);
            }
            if (version_compare(JVERSION, "1.6.0", "lt")) {
                $id = $params->get('house');
            } else if (version_compare(JVERSION, "1.6.0", "ge") ) {
                $view_house_id = ''; // for 1.6 
                $view_house_id = $params->get('house');
                if ($view_house_id > 0) {
                    $id = $view_house_id;
                }
            }
            $query = "SELECT idcat AS catid FROM #__rem_categories WHERE iditem=" . $id;
            $database->setQuery($query);
            $catid = $database->loadObject();
            if(isset($catid))
                $catid = $catid->catid;

            PHP_realestatemanager::showItemREM($option, $id, $catid, $printItem, $layout);
        }
        break;
        case 'review_house':
        case 'review':
        PHP_realestatemanager::reviewHouse($option);
        break;

        case 'alone_category':
        case 'showCategory':
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $menu = new JTableMenu($database);
            $menu->load($Itemid);
            $params = new JRegistry;
            $params->loadString($menu->params);
        } else {
            $menu = new mosMenu($database);
            $menu->load($GLOBALS['Itemid']);
            $params = new mosParameters($menu->params);
        }

        $layout = $params->get('categorylayout', '');
        

        if ($layout == '' && isset($catid) && $catid != 0) {
            $query = "SELECT params2 FROM #__rem_main_categories WHERE id =" . $catid;
            $database->setQuery($query);
            $params2 = $database->loadResult();
            $object_params = unserialize($params2);
            if (isset($object_params->alone_category))
                $layout = $object_params->alone_category;
        }
        if ($catid) {
            PHP_realestatemanager::showCategory($catid, $printItem, $option, $layout, $languagelocale);
        } else {
            $menu = new mosMenu($database);
            $menu->load($GLOBALS['Itemid']);
            $params = new mosParameters($menu->params);
            if (version_compare(JVERSION, "1.6.0", "lt")) {
                $catid = $params->get('catid');
            } else if (version_compare(JVERSION, "1.6.0", "ge")) {
                $single_category_id = ''; // for 1.6
                $single_category_id = $params->get('single_category');
                if ($single_category_id > 0)
                    $catid = $single_category_id;
            }
            PHP_realestatemanager::showCategory($catid, $printItem, $option, $layout, $languagelocale);
        }
        break;

        case "rets_link_import":
        PHP_realestatemanager::rets_link_import($option);
        break;

    // case "link_import":
    // PHP_realestatemanager::link_import($option);
    // break;

    // case "update_map":
    // PHP_realestatemanager::updateMap($option);
    // break;

        case "link_import":
        PHP_realestatemanager::link_import($option);
        break;

        case "update_map":

        PHP_realestatemanager::updateMap($option);
        break;



        case 'rent_request':

        PHP_realestatemanager::showRentRequest($option, $bids);
        break;

        case 'rent_requests':
        PHP_realestatemanager::rent_requests($option, $bids);
        break;

        case 'rent':
        if (protectInjectionWithoutQuote('save') == 1)
            PHP_realestatemanager::saveRent($option, $bid);
        else
            PHP_realestatemanager::rent($option, $bid);
        break;

        case 'rent_return':
        if (protectInjectionWithoutQuote('save') == 1)
            PHP_realestatemanager::saveRent_return($option, $bid); else
        PHP_realestatemanager::rent_return($option, $bid);
        break;

        case "edit_rent":
        case "edit_rent_houses":
        if (mosGetParam($_POST, 'save') == 1) {
            if (count($bid) > 1) {
                echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_ONE_ITEM_ALERT .
                "'); window.history.go(-1); </script>\n";
                exit;
            }
            PHP_realestatemanager::saveRent($option, $bid, "edit_rent",false);
        } else
        PHP_realestatemanager::edit_rent($option, $bid);
        break;

        case 'accept_rent_requests':
        PHP_realestatemanager::accept_rent_requests($option, $bids);
        break;

        case 'decline_rent_requests':
        PHP_realestatemanager::decline_rent_requests($option, $bids);
        break;

        case 'buying_requests':
        PHP_realestatemanager::buying_requests($option, $bids);
        break;

        case 'accept_buying_requests':
        PHP_realestatemanager::accept_buying_requests($option, $bids);
        break;

        case 'decline_buying_requests':
        PHP_realestatemanager::decline_buying_requests($option, $bids);
        break;

        case 'rent_history':
        PHP_realestatemanager::rent_history($option);
        break;

        case 'save_rent_request':
        PHP_realestatemanager::saveRentRequest($option, $bids);
        break;

        case 'buying_request':

        PHP_realestatemanager::saveBuyingRequest($option, $bids);
        break;

        case 'mdownload':
        PHP_realestatemanager::mydownload($id);
        break;

        case 'downitsf':
        PHP_realestatemanager::downloaditself($id);
        break;

        case 'add_house' :
        case 'show_add' :
        PHP_realestatemanager::editHouse($option, 0);
        break;

        case 'edit_house':
        PHP_realestatemanager::editHouse($option, $id);
        break;

        case 'save_add' :
        PHP_realestatemanager::saveHouse($option, $id);
        break;

        case 'my_houses':
        case 'edit_my_houses':
        PHP_realestatemanager::editMyHouses($option);
        break;

        case 'show_rss_categories':
        PHP_realestatemanager::listRssCategories($languagelocale);
        break;

        case 'owners_list':
        case 'ownerslist':
        PHP_realestatemanager::ownersList($option);
        break;

        case 'owner_houses':
        case 'view_user_houses':
        case 'showownerhouses':
        PHP_realestatemanager::viewUserHouses($option, $languagelocale);
        break;

        case 'show_my_houses':
        case 'showmyhouses':
        PHP_realestatemanager::viewUserHouses($option, $languagelocale);
        break;
        
        case 'rent_before_end_notify':
        PHP_realestatemanager::rentBeforeEndNotify($option);
        break;

        case 'publish_house':
        PHP_realestatemanager::publishHouse();
        break;

        case 'unpublish_house':
        PHP_realestatemanager::unpublishHouse();
        break;

        case 'delete_house':
        PHP_realestatemanager::deleteHouse();
        break;

        case "ajax_rent_price":             
        rentPriceREM($bid_ajax_rent,$rent_from,$rent_until,$special_price,$comment_price,$currency_spacial_price);
        break;
        case 'all_categories':
        if (version_compare(JVERSION, '2.5', 'ge')) {
            $menu = new JTableMenu($database);
            $menu->load($GLOBALS['Itemid']);
            $params = new JRegistry;
            $params->loadString($menu->params);
        } else {
            $menu = new mosMenu($database);
            $menu->load($GLOBALS['Itemid']);
            $params = new mosParameters($menu->params);
        }
        $layout = $params->get('allcategorylayout', '');
        if ($layout == '')
            $layout = "default";
        PHP_realestatemanager::listCategories($catid, $layout, $languagelocale);
        break;

        case 'add_to_wishlist':
        PHP_realestatemanager::addHouseToWishlist();
        break;
        case 'remove_from_wishlist':
        PHP_realestatemanager::removeHouseFromWishlist();
        break;
        case 'wishlist':
        PHP_realestatemanager::showWishlist($option, $task);
        break;

        default:

        if (version_compare(JVERSION, '3.0', 'ge')) {
            $menu = new JTableMenu($database);
            $menu->load($GLOBALS['Itemid']);
            $params = new JRegistry;
            $params->loadString($menu->params);
        } else {
            $menu = new mosMenu($database);
            $menu->load($GLOBALS['Itemid']);
            $params = new mosParameters($menu->params);
        }
        $layout = $params->get('allhouselayout', '');
        if ($layout == '')
            $layout = 'default';
        PHP_realestatemanager::ShowAllHouses($layout, $printItem);
        break;
    }


    class PHP_realestatemanager {

        static function mylenStr($str, $lenght) {
            if (strlen($str) > $lenght) {
                $str = substr($str, 0, $lenght);
                $str = substr($str, 0, strrpos($str, " "));
            }
            return $str;
        }

        static function addTitleAndMetaTags($idHouse = 0) {
            global $database, $doc, $mainframe, $Itemid;

            $view = JREQUEST::getCmd('view', null);
            $catid = JREQUEST::getInt('catid', null);
            $id = JREQUEST::getInt('id', null);
            $lang = JREQUEST::getString('lang', null);
            $title = array();
            $sitename = htmlspecialchars($mainframe->getCfg('sitename'));

            if (isset($view)) {
                $view = str_replace("_", " ", $view);
                $view = ucfirst($view);
                $title[] = $view;
            }

            $s = getWhereUsergroupsCondition('c');

            if (!isset($catid)) {

            // Parameters
                if (version_compare(JVERSION, '3.0', 'ge')) {
                    $menu = new JTableMenu($database);
                    $menu->load($Itemid);
                    $params = new JRegistry;
                    $params->loadString($menu->params);
                } else {
                    $menu = new mosMenu($database);
                    $menu->load($Itemid);
                    $params = new mosParameters($menu->params);
                }
                if (version_compare(JVERSION, "1.6.0", "lt")) {
                    $catid = $params->get('catid');
                } else if (version_compare(JVERSION, "1.6.0", "ge")) {
                $single_category_id = ''; // for 1.6 
                $single_category_id = $params->get('single_category');
                if ($single_category_id > 0)
                    $catid = $single_category_id;
            }
        }

        //To get name of category
        if (isset($catid)) {
            $query = "SELECT  c.name, c.title, c.id AS catid, c.parent_id
                    FROM #__rem_main_categories AS c
                    WHERE ($s) AND c.id = " . intval($catid);
                    $database->setQuery($query);
                    $row = null;
                    $row = $database->loadObject();
                    if (isset($row)) {
                        $cattitle = array();
                        if ($row->title != '') {
                    $cattitle[] = $row->title; //$row->name
                } else {
                    $cattitle[] = $row->name;
                }               
                while (isset($row) && $row->parent_id > 0) {
                    $query = "SELECT  name, title, c.id AS catid, parent_id 
                        FROM #__rem_main_categories AS c
                        WHERE ($s) AND c.id = " . intval($row->parent_id);
                        $database->setQuery($query);
                        $row = $database->loadObject();
                        if (isset($row)) { 
                            if ($row->title == '' && $row->name != '') {
                            $cattitle[] = $row->name; //$row->name
                        } else {
                            $cattitle[] = $row->title; //$row->name
                        }
                    } 
                }
                $title = array_merge($title, array_reverse($cattitle));
            }
        }

        //To get Name of the houses
        if (isset($id)) {
            $query = "SELECT h.htitle, c.id AS catid 
                    FROM #__rem_houses AS h
                    LEFT JOIN #__rem_categories AS hc ON h.id=hc.iditem
                    LEFT JOIN #__rem_main_categories AS c ON c.id=hc.idcat 
                    WHERE ({$s}) AND h.id=" . intval($id) . "
                    GROUP BY h.id";
                    $database->setQuery($query);
                    $row = null;
                    $row = $database->loadObject();
                    if (isset($row)) {
                        $idtitle = array();
                        $idtitle[] = $row->htitle;
                        $title = array_merge($title, $idtitle);
                    }
                }

                if (empty($title) && $idHouse != 0) {
                    $query = "SELECT h.htitle 
                    FROM #__rem_houses AS h
                    WHERE  h.id=" . $idHouse;
                    $database->setQuery($query);
                    $row = null;
                    $row = $database->loadObject();
                    if (isset($row)) {
                        $idtitle = array();
                        $idtitle[] = $row->htitle;
                        $title = array_merge($title, $idtitle);
                    }
                }

                $tagtitle = "";
                for ($i = 0; $i < count($title); $i++) {
                    $tagtitle = trim($tagtitle) . " | " . trim($title[$i]);
                }
                /*******************************************/
                $app = JFactory::getApplication();

                if ($app->getParams()->get('page_title') !='') $rem = $app->getParams()->get('page_title');
                else $rem = $app->getMenu()->getActive()->title;
                /*******************************************/
        // $rem = $menu->getActive()->title; //"RealEstate Manager ";
        //To set Title
                $title_tag = PHP_realestatemanager::mylenStr($rem . $tagtitle, 75);
        //To set meta Description
                $metadata_description_tag = PHP_realestatemanager::mylenStr($rem . $tagtitle, 200);
        //To set meta KeywordsTag
                $metadata_keywords_tag = PHP_realestatemanager::mylenStr($rem . $tagtitle, 250);
                $doc->setTitle($title_tag);
                $doc->setMetaData('description', $metadata_description_tag);
                $doc->setMetaData('keywords', $metadata_keywords_tag);
            }

            static function output_file($file, $name, $mime_type = '') {
        /*
          This function takes a path to a file to output ($file),
          the filename that the browser will see ($name) and
          the MIME type of the file ($mime_type, optional).
          If you want to do something on download abort/finish,
          register_shutdown_function('function_name');
         */
          if (!is_readable($file))
            die('File not found or inaccessible!');
        $size = filesize($file);
        $name = rawurldecode($name);

        /* Figure out the MIME type (if not specified) */
        $known_mime_types = array(
            "pdf" => "application/pdf",
            "txt" => "text/plain",
            "html" => "text/html",
            "htm" => "text/html",
            "exe" => "application/octet-stream",
            "zip" => "application/zip",
            "doc" => "application/msword",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "gif" => "image/gif",
            "png" => "image/png",
            "jpeg" => "image/jpg",
            "jpg" => "image/jpg",
            "php" => "text/plain"
        );

        if ($mime_type == '') {
            $file_extension = strtolower(substr(strrchr($file, "."), 1));
            if (array_key_exists($file_extension, $known_mime_types)) {
                $mime_type = $known_mime_types[$file_extension];
            } else
            $mime_type = "application/force-download";
        };

        $name = str_replace(" ", "", $name);
        ob_end_clean(); //turn off output buffering to decrease cpu usage
        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression'))
            ini_set('zlib.output_compression', 'Off');

        header('Content-Type: application/force-download');
        header("Content-Disposition: inline; filename=$name");
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');

        /* The three lines below basically make the download non-cacheable */
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // multipart-download and download resuming support
        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
            list($range) = explode(",", $range, 2);
            list($range, $range_end) = explode("-", $range);
            $range = intval($range);
            if (!$range_end)
                $range_end = $size - 1; else
            $range_end = intval($range_end);
            $new_length = $range_end - $range + 1;
            header("HTTP/1.1 206 Partial Content");
            header("Content-Length: $new_length");
        } else {
            $new_length = $size;
            header("Content-Length: " . $size);
        }

        $chunksize = 1 * (1024 * 1024); //you may want to change this
        $bytes_send = 0;
        if ($file = fopen($file, 'r')) {
            if (isset($_SERVER['HTTP_RANGE']))
                fseek($file, $range);
            while (!feof($file) && (!connection_aborted()) && ($bytes_send < $new_length)) {
                $buffer = fread($file, $chunksize);
                print($buffer); // is also possible
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($file);
        } else
        die('Error - can not open file.');
        die();
    }

    static function mydownload($id) {
        global $realestatemanager_configuration;
        global $mosConfig_absolute_path;

        $session = JFactory::getSession();
        $pas = $session->get("ssmid", "default");
        $sid_1 = $session->getId();

        if (!($session->get("ssmid", "default")) || $pas == "" || $pas != $sid_1 || $_COOKIE['ssd'] != $sid_1 ||
            !array_key_exists("HTTP_REFERER", $_SERVER) || $_SERVER["HTTP_REFERER"] == "" ||
            strpos($_SERVER["HTTP_REFERER"], $_SERVER['SERVER_NAME']) === false) {
            echo '<H3 align="center">Link failure</H3>';
        exit;
    }
    if ($realestatemanager_configuration['license']['show']) {
        $fd = fopen($mosConfig_absolute_path . "/components/com_realestatemanager/mylicense.php", "w")
        or die("Config license file is failure");
        fwrite($fd, _REALESTATE_MANAGER_ADMIN_CONFIG_LICENSE_TEXT);
        fclose($fd);
        HTML_realestatemanager :: displayLicense($id);
    } else
    PHP_realestatemanager::downloaditself($id);
}

static function downloaditself($idt) {
    global $database, $my, $realestatemanager_configuration, $mosConfig_absolute_path;

    $session = JFactory::getSession();
    $pas = $session->get("ssmid", "default");
    $sid_1 = $session->getId();

    if (!($session->get("ssmid", "default")) || $pas == "" || $pas != $sid_1 ||
        $_COOKIE['ssd'] != $sid_1 || !array_key_exists("HTTP_REFERER", $_SERVER) ||
        $_SERVER["HTTP_REFERER"] == "" ||
        strpos($_SERVER["HTTP_REFERER"], $_SERVER['SERVER_NAME']) === false) {
        echo '<H3 align="center">Link failure</H3>';
    exit;
}
$session->set("ssmid", "default");

if (array_key_exists("id", $_POST))
    $id = intval($_POST['id']); else
$id = $idt;

$query = "SELECT * from #__rem_houses where id = " . $id;
$database->setQuery($query);
$house = $database->loadObjectList();

if (strpos($_SERVER["HTTP_REFERER"], $_SERVER['SERVER_NAME']) !== false) {
    $name = explode('/', $house[0]->edok_link);
    $file_path = $mosConfig_absolute_path .
    $realestatemanager_configuration['edocs']['location'] . $name[count($name) - 1];
    set_time_limit(0);
    PHP_realestatemanager::output_file($file_path, $name[count($name) - 1]);
    exit;
} else {
    header("Cache-control: private");
    header('Pragma: private');
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("HTTP/1.1 301 Moved Permanently");
    header('Content-Type: application/force-download');
    header("Location: " . $house[0]->edok_link);
    exit;
}
}

static function saveRentRequest($option, $bids) {  
    global $mainframe, $database, $my, $acl, $realestatemanager_configuration, $mosConfig_mailfrom, $Itemid;
    $pathway = sefRelToAbs('index.php?option=' . $option . '&amp;task=rent_request&amp;Itemid=' . $Itemid);

    $transform_from = data_transform_rem($_POST['rent_from']);
    $transform_until = data_transform_rem($_POST['rent_until']);
        //Modification par Skorweb//
    $week=$_POST['week'];
    $acompte=0.30;
    $data = JFactory::getDBO();

    PHP_realestatemanager::addTitleAndMetaTags();

    $jinput = JFactory::getApplication()->input;
    if(isset($_POST['user_email']) && $_POST['user_email'] != '') {
        $email = $jinput->getString('user_email');
        $houseId = $jinput->get('houseid');
        $name = $jinput->getString('user_name');
            $calculated_price = JRequest::getVar('calculated_price');///with currency//akosha
            $calculated_price = $calculated_price*$acompte.' EUR';
            $sql = "SELECT u.id as userID, u.email, u.name  FROM #__users AS u  WHERE u.email =".
            $data->Quote($email);
            $database->setQuery($sql);
            $result = $database->loadObjectList();

            if($result == '0' || $result == null) {
                $name = $name;
                $email = $email;
                $user = '';
            } else {
                $email = $result[0]->email;
                $user = $result[0]->userID;
                $name = $result[0]->name;
            }
            $_REQUEST['userId'] = $user;
            $_REQUEST['id'] = $houseId;
            $_REQUEST['name_bayer'] = $name;
            $calculated_price = JRequest::getVar('calculated_price');
            $calculated_price =  $calculated_price*$acompte.' EUR';
            $sql = "SELECT htitle FROM #__rem_houses WHERE id='".$houseId."'";
            $database->setQuery($sql);
            $htitle = $database->loadResult();
            $sql = "INSERT INTO  `#__rem_orders`(fk_user_id, status, name,email, fk_house_id,fk_houses_htitle,order_calculated_price, order_date)
            VALUES ('".$user."', 'En attente de paiement', '".$name."', ".$database->Quote($email).",
            '".$houseId."', '".$htitle."', '".$calculated_price."',now())";
            $database->setQuery($sql);
            $database->query();
            $orderId = $database->insertid();
            $text = "Rent request<br>(From:".JRequest::getVar('rent_from')
            ." - To: ".JRequest::getVar('rent_until').")";
            $sql = "INSERT INTO `#__rem_orders_details`(fk_order_id,fk_user_id,email,fk_houses_htitle,name,status,order_date,
            fk_house_id,txn_type,order_calculated_price)
            VALUES ('".$orderId."','".$user."',". $database->Quote($email) .",
            '".$htitle."','".$name."','En attente de paiement',now(),
            '".$houseId."','".$text."','".$calculated_price."')";
            $database->setQuery($sql);
            $database->query();
            $_REQUEST['orderID'] =$orderId;
        }
        

        $path_way = $mainframe->getPathway();
        $path_way->addItem(_REALESTATE_MANAGER_LABEL_TITLE_RENT_REQUEST, $pathway);
        // --

        if (!($realestatemanager_configuration['rentstatus']['show']) 
          || !checkAccess_REM($realestatemanager_configuration['rentrequest']['registrationlevel'], 
              'NORECURSE', userGID_REM($my->id), $acl)) {
            echo _REALESTATE_MANAGER_NOT_AUTHORIZED;
        return;
    }

    $help = array();

    $rent_request = new mosRealEstateManager_rent_request($database);
    $post = JRequest::get('post');
    if (!$rent_request->bind($post)) {
        echo "<script> alert('" . $rent_request->getError() . "'); window.history.go(-1); </script>\n";
        exit;
    }

        //*********************   begin compare to key   ***************************
    $session = JFactory::getSession();
    $password = $session->get('captcha_keystring', 'default');

    if (array_key_exists('keyguest', $_POST) && ($_POST['keyguest'] != $password) && (userGID_REM($my->id) <= 0)) {
        mosRedirect("index.php?option=com_realestatemanager&task=view&catid=" . intval($_POST["catid"]) . "&id=" .
            intval($_POST["fk_houseid"]) . "&Itemid=$Itemid&title=" . $_POST['title'] . "&comment=" .
            $_POST['comment'] . "&rating=" . $_POST['rating'], "You typed bad characters from picture!");
        exit;
    }
        //**********************   end compare to key   *****************************

    $date_format = $realestatemanager_configuration['date_format'];
    if(phpversion() >= '5.3.0') {
        $date_format = str_replace('%', '', $date_format);
        $d_from = DateTime::createFromFormat($date_format, $post['rent_from']);
        $d_until = DateTime::createFromFormat($date_format, $post['rent_until']);
        if ($d_from === FALSE or $d_until === FALSE) {
            echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_BAD_DATE_ALERT .
            "'); window.history.go(-1); </script>\n";
            exit;
        }
        $rent_request->rent_from = $d_from->format('Y-m-d');
        $rent_request->rent_until = $d_until->format('Y-m-d');

    } else {
        $rent_request->rent_from = data_transform_rem($post['rent_from'],'to');
        $rent_request->rent_until = data_transform_rem($post['rent_until'],'to');
    }

    $rent_request->user_email = ($rent_request->user_email);
    $rent_request->rent_request = date("Y-m-d H:i:s");
    $rent_request->fk_houseid = intval($_REQUEST["houseid"]);

    if ($rent_request->rent_from > $rent_request->rent_until) {
        echo "<script> alert('" . $rent_request->rent_from . " is more than " . $rent_request->rent_until .
        "'); window.history.go(-1); </script>\n";
        exit;
    }
    $query = "SELECT * FROM #__rem_houses where id= " . $rent_request->fk_houseid;
    $data->setQuery($query);
    $houseid = $data->loadObject();

    $rent_from = substr($rent_request->rent_from, 0, 10);
    $rent_until = substr($rent_request->rent_until, 0, 10);

    if ($my->id != 0)
        $rent_request->fk_userid = $my->id;
    if (!$rent_request->check()) {
        echo "<script> alert('" . $rent_request->getError() . "'); window.history.go(-1); </script>\n";
        exit;
    }

    if (!$rent_request->store()) {
        echo "<script> alert('" . $rent_request->getError() . "'); window.history.go(-1); </script>\n";
        exit;
    }   

    $time_difference = calculatePriceREM($houseid->id,$rent_from,$rent_until,
      $realestatemanager_configuration,$database,$week);    

    $rent_request->checkin();
    array_push($help, $rent_request);

    $currentcat = new stdClass();

        // Parameters
    $menu = new mosMenu($database);
    $menu->load($Itemid);
    $params = new mosParameters($menu->params);
    $menu_name = set_header_name_rem($menu, $Itemid);
    $params->def('header', $menu_name);
    $params->def('pageclass_sfx', '');
    $params->def('back_button', $mainframe->getCfg('back_button'));
        // --

    $currentcat->descrip = _REALESTATE_MANAGER_LABEL_RENT_REQUEST_THANKS;
    $currentcat->img = "./components/com_realestatemanager/images/rem_logo.png";
    $currentcat->header = $params->get('header');

        // used to show table rows in alternating colours
    $tabclass = array('sectiontableentry1', 'sectiontableentry2');

    if ($realestatemanager_configuration['rentrequest_email']['show']) {
        $params->def('show_email', 1);
        if (checkAccess_REM($realestatemanager_configuration['rentrequest_email']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl)) {
            $params->def('show_input_email', 1);
    }
}

if ($realestatemanager_configuration['paypal_buy_status']['show']) {
    $params->def('paypal_buy_status', 1);
    if (checkAccess_REM($realestatemanager_configuration['paypal_buy']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('paypal_buy_status_rl', 1);
}
}

if ($params->get('show_input_email')) {
    if (trim($realestatemanager_configuration['rentrequest_email']['address']) != "") {
        $mail_to = explode(",", $realestatemanager_configuration['rentrequest_email']['address']);
        $userid = $my->id;
                //select user (added rent request)
        $zapros = "SELECT name, email FROM #__users WHERE id=" . $userid . ";";
        $database->setQuery($zapros);
        $item_user = $database->loadObjectList();
        echo $database->getErrorMsg();

        $zapros = "SELECT r.`id`, r.`houseid`, r.`htitle`, r.`owneremail` " .
        " FROM #__rem_houses AS r WHERE r.`id`='" . $rent_request->fk_houseid . "';";
        $database->setQuery($zapros);
        $item_house = $database->loadObjectList();
        echo $database->getErrorMsg();
        if (trim($item_house[0]->owneremail) != '')
            $mail_to[] = $item_house[0]->owneremail;

        if (count($mail_to) > 0)
            $username = (isset($item_user[0]->name)) ? $item_user[0]->name : _REALESTATE_MANAGER_LABEL_ANONYMOUS;
        $message = str_replace("{username}", $username, _REALESTATE_MANAGER_EMAIL_NOTIFICATION_RENT_REQUEST);
        $message = str_replace("{hid_value}", $item_house[0]->houseid, $message);
        $message = str_replace("{user_name}", $rent_request->user_name, $message);
        $message = str_replace("{user_email}", $rent_request->user_email, $message);
        $message = str_replace("{user_mailing}", $rent_request->user_mailing, $message);
        $message = str_replace("{rent_from}", $rent_request->rent_from, $message);
        $message = str_replace("{rent_until}", $rent_request->rent_until, $message);
        $message = str_replace("{house_title}", $item_house[0]->htitle, $message);
        if ($userid == 0) {
            mosMail($mosConfig_mailfrom, _REALESTATE_MANAGER_LABEL_ANONYMOUS, $mail_to,
               _REALESTATE_MANAGER_NEW_RENT_REQUEST_ADDED, $message, true);
        } else {
            mosMail($mosConfig_mailfrom, $item_user[0]->name, $mail_to,
               _REALESTATE_MANAGER_NEW_RENT_REQUEST_ADDED, $message, true);
        }
    }
}
        //********************   end add send mail for admin   ****************
$backlink = JRoute::_($_SERVER['HTTP_REFERER']);
HTML_realestatemanager :: showRentRequestThanks($params, $backlink, $currentcat, $houseid, $time_difference);
}

static function saveBuyingRequest($option, $bids) {
    global $mainframe, $database, $my, $Itemid, $acl;
    global $realestatemanager_configuration, $mosConfig_mailfrom;
// echo __FILE__.":  ".__LINE__."<br />";
    $acompte = 0.30;
    $jinput = JFactory::getApplication()->input;
    if(isset($_POST['customer_email']) && $_POST['customer_email'] != '') {
        $email = $jinput->getString('customer_email');
        $bId = $jinput->get('bid', 0, 'ARRAY');
        $name = $jinput->getString('customer_name');
        $time_difference = null;
        $sql = "SELECT u.id as userID, u.email, u.name  FROM `#__users` AS u  WHERE u.email ='". $email."'";
        $database->setQuery($sql);
        $result = $database->loadObjectList();
        if($result == '0' || $result == null) {
            $name = $name;
            $email = $email;
            $user = '';
        } else {
            $email = $result[0]->email;
            $user = $result[0]->userID;
            $name = $result[0]->name;
        }
        $_REQUEST['userId'] = $user;
        $_REQUEST['user_email'] = $email;
        $_REQUEST['name_bayer'] = $name;
        $_REQUEST['id'] = $houseId = $bId[0];

        if($realestatemanager_configuration['special_price']['show']){
            $rent_from = data_transform_rem(date('Y-m-d'));
            $rent_until = data_transform_rem(date('Y-m-d'));
            $query = "SELECT special_price as price,priceunit FROM `#__rem_rent_sal` WHERE fk_houseid = ".$houseId .
            " AND (price_from <= ('" .$rent_until. "') AND price_to >= ('" .$rent_from. "'))";
            $database->setQuery($query);
            $res = $database->loadObjectList();
            if($res){
                $time_difference = array();
                $time_difference['0'] = $res['0']->price;
                $time_difference['1'] = $res['0']->priceunit;
                $sql = "SELECT htitle FROM #__rem_houses WHERE id='".$houseId."'";
                $database->setQuery($sql);
                $htitle = $database->loadResult();
            }else{
                $sql = "SELECT price,priceunit,htitle FROM #__rem_houses WHERE id='".$houseId."'";
                $database->setQuery($sql);
                $res = $database->loadObjectList();
                $htitle = $res[0]->htitle;
            }
        }else{
            $sql = "SELECT price,priceunit,htitle FROM #__rem_houses WHERE id='".$houseId."'";
            $database->setQuery($sql);
            $res = $database->loadObjectList();
            $htitle = $res[0]->htitle;
        }
        $calculated_price = $res['0']->price*$acompte.' '.$res['0']->priceunit;
        $sql = "INSERT INTO  `#__rem_orders`(fk_user_id, status, name,email, fk_house_id,fk_houses_htitle,order_calculated_price, order_date)
        VALUES ('".$user."', 'En attente de paiement', '".$name."', ".$database->Quote($email).",'".$houseId."', '".$htitle."', '".$calculated_price."',now())";
        $database->setQuery($sql);
        $database->query();
        $orderId = $database->insertid();
        $sql = "INSERT INTO `#__rem_orders_details`(fk_order_id,fk_user_id,email,
        fk_houses_htitle,name,status,order_date,
        fk_house_id,txn_type,order_calculated_price)
        VALUES (".$orderId.",'".$user."',". $database->Quote($email) .",
        '".$htitle."','".$name."','En attente de paiement',now(),
        '".$houseId."','Buy request','".$calculated_price."')";
        $database->setQuery($sql);
        $database->query();
        $_REQUEST['orderID'] =$orderId;
        }// order in #__rem_orders

        if (!($realestatemanager_configuration['buystatus']['show']) ||
            !checkAccess_REM($realestatemanager_configuration['buyrequest']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl)) {
            echo _REALESTATE_MANAGER_NOT_AUTHORIZED;
        return;
    }

    $buying_request = new mosRealEstateManager_buying_request($database);

    $post = JRequest::get('post');
    if (!$buying_request->bind($post)) {
        echo $buying_request->getError();
        exit;
    }

        //*********************   begin compare to key   ***************************
    $session = JFactory::getSession();
    $password = $session->get('captcha_keystring', 'default');

    if (array_key_exists('keyguest', $_POST) && ($_POST['keyguest'] != $password) && (userGID_REM($my->id) <= 0)) {
        mosRedirect("index.php?option=com_realestatemanager&task=view&catid=" . intval($_POST["catid"]) . "&id=" .
            intval($_POST["fk_houseid"]) . "&Itemid=$Itemid&title=" . $_POST['title'] . "&comment=" .
            $_POST['comment'] . "&rating=" . $_POST['rating'], "You typed bad characters from picture!");
        exit;
    }
        //**********************   end compare to key   *****************************


    $buying_request->customer_email = ($buying_request->customer_email);
    $buying_request->buying_request = date("Y-m-d H:i:s");
    $buying_request->fk_houseid = $bids[0];
    if (!$buying_request->store())
        echo "error:" . $buying_request->getError();
    $currentcat = new stdClass();

        // Parameters
    $menu = new JTableMenu($database);
    $menu->load($Itemid);
    $params = new mosParameters($menu->params);
    $menu_name = set_header_name_rem($menu, $Itemid);
    $params->def('header', $menu_name);
    $params->def('pageclass_sfx', '');
    $params->def('back_button', $mainframe->getCfg('back_button'));

    $currentcat->descrip = _REALESTATE_MANAGER_LABEL_BUYING_REQUEST_THANKS;

        // page image
    $currentcat->img = "./components/com_realestatemanager/images/rem_logo.png";
    $currentcat->header = $params->get('header');

        //sending notification
    if (($realestatemanager_configuration['buyingrequest_email']['show'])) {
        $params->def('show_email', 1);
        if (checkAccess_REM($realestatemanager_configuration['buyingrequest_email']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl))
            $params->def('show_input_email', 1);
    }

    if ($realestatemanager_configuration['paypal_buy_status_sale']['show']) {
        $params->def('paypal_buy_status', 2);
        if (checkAccess_REM($realestatemanager_configuration['paypal_buy_sale']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl)) {
            $params->def('paypal_buy_status_rl', 2);
    }
}

if ($params->get('show_input_email')) {
    $mail_to = array();
    if (trim($realestatemanager_configuration['buyingrequest_email']['address']) != "")
        $mail_to = explode(",", $realestatemanager_configuration['buyingrequest_email']['address']);

    $userid = $my->id;
            //select user (added rent request)
    $zapros = "SELECT name, email FROM #__users WHERE id=" . $userid . ";";
    $database->setQuery($zapros);
    $item_user = $database->loadObjectList();
    echo $database->getErrorMsg();

    for ($i = 0; $i < count($bids); $i++) {
        $zapros = "SELECT `id`, `houseid`, `htitle`,`owneremail` FROM #__rem_houses WHERE `id`='" . $bids[$i] . "';";
        $database->setQuery($zapros);
        $item_house = $database->loadObjectList();
        echo $database->getErrorMsg();

        if (trim($item_house[0]->owneremail) != '')
            $mail_to[] = $item_house[0]->owneremail;
        if (count($mail_to) > 0) {
            $username = (isset($item_user[0]->name)) ? $item_user[0]->name : _REALESTATE_MANAGER_LABEL_ANONYMOUS;
            $message = str_replace("{username}", $username, _REALESTATE_MANAGER_EMAIL_NOTIFICATION_BUYING_REQUEST);
            $message = str_replace("{customer_name}", $buying_request->customer_name, $message);
            $message = str_replace("{customer_email}", $buying_request->customer_email, $message);
            $message = str_replace("{customer_phone}", $buying_request->customer_phone, $message);
            $message = str_replace("{customer_comment}", $buying_request->customer_comment, $message);
            $message = str_replace("{hid_value}", $item_house[0]->houseid, $message);
            $message = str_replace("{house_title}", $item_house[0]->htitle, $message);
            if ($userid == 0) {
                mosMail($mosConfig_mailfrom, _REALESTATE_MANAGER_LABEL_ANONYMOUS, $mail_to,
                   _REALESTATE_MANAGER_BUYING_REQUEST_ADDED, $message, true);
            } else {
                mosMail($mosConfig_mailfrom, $item_user[0]->name, $mail_to,
                   _REALESTATE_MANAGER_BUYING_REQUEST_ADDED, $message, true);
            }
        }
    }
}
$query = "SELECT * FROM #__rem_houses where id= " . $buying_request->fk_houseid;
$database->setQuery($query);
$houseid = $database->loadObject();
$backlink = JRoute::_($_SERVER['HTTP_REFERER']);


HTML_realestatemanager :: showRentRequestThanks($params, $backlink, $currentcat, $houseid);
}

static function showRentRequest($option, $bid) {  exit;
    global $mainframe, $database, $my, $Itemid, $acl, $realestatemanager_configuration;

    $pathway = sefRelToAbs('index.php?option=' . $option . '&amp;task=rent_request&amp;Itemid=' . $Itemid);

        // for 1.6
    $path_way = $mainframe->getPathway();
    $path_way->addItem(_REALESTATE_MANAGER_LABEL_TITLE_RENT_REQUEST, $pathway);
        // --

    if (!($realestatemanager_configuration['rentstatus']['show']) ||
       !checkAccess_REM($realestatemanager_configuration['rentrequest']['registrationlevel'],
          'NORECURSE', userGID_REM($my->id), $acl)) {
        echo _REALESTATE_MANAGER_NOT_AUTHORIZED;
    return;
}

$bids = implode(',', $bid);

        // getting all houses for this category
$query = "SELECT * FROM #__rem_houses"
. "\nWHERE `id` IN (" . $bids . ") ORDER BY `catid`, `ordering`";
$database->setQuery($query);
$houses = $database->loadObjectList();

$currentcat = new stdClass();

        // Parameters
$menu = new mosMenu($database);
$menu->load($Itemid);
$params = new mosParameters($menu->params);
$menu_name = set_header_name_rem($menu, $Itemid);
$params->def('header', $menu_name);
        // --

$params->def('pageclass_sfx', '');
$params->def('show_rentstatus', 1);
$params->def('show_rentrequest', 1);
$params->def('rent_save', 1);
$params->def('back_button', $mainframe->getCfg('back_button'));

        // page description
$currentcat->descrip = _REALESTATE_MANAGER_DESC_RENT;

        // page image
$currentcat->img = null;
$currentcat->header = $params->get('header');
        // used to show table rows in alternating colours
$tabclass = array('sectiontableentry1', 'sectiontableentry2');

HTML_realestatemanager::showRentRequest($houses, $currentcat, $params, $tabclass,
   $catid, $sub_categories, false, $option);
}

    /**
     * comments for registered users
     */
    static function reviewHouse() {
        global $mainframe, $database, $my, $Itemid, $acl, $realestatemanager_configuration,
        $mosConfig_absolute_path, $catid;
        global $mosConfig_mailfrom, $session, $option;

        if (!($realestatemanager_configuration['reviews']['show']) ||
           !checkAccess_REM($realestatemanager_configuration['reviews']['registrationlevel'],
              'NORECURSE', userGID_REM($my->id), $acl)) {
            echo _REALESTATE_MANAGER_NOT_AUTHORIZED;
        return;
    }
    $review = new mosRealEstateManager_review($database);
        //************publish_on_review begin
    if ($realestatemanager_configuration['publish_on_review']['show']) {
        if (checkAccess_REM($realestatemanager_configuration['publish_on_review']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl)) {
            $review->published = 1;
    }
    else
        $review->published = 0;
}
else
    $review->published = 0;


        //************publish on add end

$review->date = date("Y-m-d H:i:s");
$review->getReviewFrom($my->id);

        //*********************   begin compare to key   ***************************
$session = JFactory::getSession();
$password = $session->get('captcha_keystring', 'default');

if (array_key_exists('keyguest', $_POST) && ($_POST['keyguest'] != $password) && (userGID_REM($my->id) <= 0)) {
    mosRedirect("index.php?option=com_realestatemanager&task=view&catid=" . intval($_POST["catid"]) . "&id=" .
        intval($_POST["fk_houseid"]) . "&Itemid=$Itemid&title=" . $_POST['title'] . "&comment=" .
        $_POST['comment'] . "&rating=" . $_POST['rating'], "You typed bad characters from picture!");
    exit;
}
        //**********************   end compare to key   *****************************
$post = JRequest::get('post');
if (!$review->bind($post)) {
    echo "<script> alert('" . $house->getError() . "'); window.history.go(-1); </script>\n";
    exit;
}
$review->rating = $_POST['rating'];
if (version_compare(JVERSION, "3.0", "ge"))
    $review->rating *= 2;
if (!$review->check()) {
    echo "<script> alert('" . $house->getError() . "'); window.history.go(-1); </script>\n";
    exit;
}
if (!$review->store()) {
    echo "<script> alert('" . $house->getError() . "'); window.history.go(-1); </script>\n";
    exit;
}

        //***************   begin add send mail for admin   ******************

$menu = new mosMenu($database);
$menu->load($Itemid);
$params = new mosParameters($menu->params);

if (($realestatemanager_configuration['review_added_email']['show']) &&
   trim($realestatemanager_configuration['review_email']['address']) != "") {

    $params->def('show_email', 1);
if (checkAccess_REM($realestatemanager_configuration['review_added_email']['registrationlevel'],
   'NORECURSE', userGID_REM($my->id), $acl)) {
    $params->def('show_input_email', 1);
}
}


if ($params->get('show_input_email')) {
    $mail_to = explode(",", $realestatemanager_configuration['review_email']['address']);

            //select house title
    $zapros = "SELECT htitle FROM #__rem_houses WHERE houseid = '" . intval($_POST['fk_houseid']) . "';";
    $database->setQuery($zapros);
    $house_title = $database->loadObjectList();
    echo $database->getErrorMsg();

    $rating = (($item_review[0]->rating) / 2);

    $username = (isset($review->user_name)) ? $review->user_name : _REALESTATE_MANAGER_LABEL_ANONYMOUS;
    $message = str_replace("{username}", $username, _REALESTATE_MANAGER_EMAIL_NOTIFICATION_REVIEW);
    $message = str_replace("{house_title}", $house_title[0]->htitle, $message);
    $message = str_replace("{title}", $review->title, $message);
    $message = str_replace("{rating}", $rating, $message);
    $message = str_replace("{comment}", $review->comment, $message);

    mosMail($mosConfig_mailfrom, $username, $mail_to, _REALESTATE_MANAGER_NEW_REVIEW_ADDED, $message, true);
}
        //********************   end add send mail for admin ************
        //showing the original entries
mosRedirect("index.php?option=" . $option . "&task=view_house&catid=" . intval($_POST['catid'])
   . "&id=$review->fk_houseid&Itemid=$Itemid");
}

static function link_import($option) {
    global $database, $my;

    $catid = '';
    $retVal = mosRealEstateManagerImportExport :: importHousesXMLFromTREB($catid);
            //HTML_realestatemanager:: showImportResult($retVal, $option);
            //break;
    echo "Import Success";
    exit;
     //***********************   end add for XML format   *****************************************
    
}


static function rets_link_import($option) {
    global $database, $my;

    $catid = '';
    $retVal = mosRealEstateManagerImportExport :: importHousesXMLFromRETS($catid);
            //HTML_realestatemanager:: showImportResult($retVal, $option);
            //break;
    echo "Import Success";
    exit;
     //***********************   end add for XML format   *****************************************
    
}


static function updateMap($option){
    global $database,$option;
    global $mosConfig_absolute_path, $mosConfig_live_site, $realestatemanager_configuration;

    $logPath  = $mosConfig_absolute_path . "/administrator/components/com_realestatemanager/my_log.log";

        //exit if now import or map update is going
    if (file_exists($logPath)) {
      clearstatcache();
      $ret = time() - filemtime($logPath) ;
      if($ret < 600){
       echo "updateMap  exit, file accessed : " . $ret ."seconds ago <br />";
       file_put_contents($logPath, "updateMap exit, file accessed : " . $ret ."seconds ago  \n\n",  FILE_APPEND );
       exit ;

   }
} 


file_put_contents($logPath, "   updateMap start  ".time()." \n",  FILE_APPEND );


$api_key = $realestatemanager_configuration['api_key'];

// SELECT count(*)
//  FROM zm8wo_rem_houses AS h 
//  WHERE h.hlatitude = '' AND h.hlongitude = ''

$query = "SELECT h.id,h.hlocation,h.hcountry,h.hregion,h.hcity "
. "\n FROM #__rem_houses AS h"
. "\n WHERE h.hlatitude = '' AND h.hlongitude = '' "
. "\n ORDER BY h.id ASC LIMIT 1000";
$database->setQuery($query);
$datas = $database->loadObjectList();

    if (!file_exists($logPath)) exit; //remove file and break import
    file_put_contents($logPath," updateMap 2 " . count($datas) . "  ".time()." \n\n",  FILE_APPEND );

    foreach ($datas as $data) {

      $address="";
      $address .= ($data->hcountry != "null")? $data->hcountry : "";
      $address .= ($data->hregion != "null")? ", ". $data->hregion : "";
      $address .= ($data->hcity != "null")? ", ". $data->hcity : "";
      $address .= ($data->hlocation != "null")? ", " . $data->hlocation : "";
      //$address = str_replace(" ", "+", $address); 
      $address = urlencode($address);
      $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$api_key";//http:
      $response = file_get_contents($url);
      $json = json_decode($response,TRUE);

      print_r($response);
echo "updateMap:".$address." \n"; flush();   if( ob_get_level() > 0 ) ob_flush(); //fix error: Maximum execution time   
    if (!file_exists($logPath)) exit; //remove file and break import
    file_put_contents($logPath," updateMap 3 " . $address . "::".$json['status'] ."  ".time()." \n\n",  FILE_APPEND );


    if( (isset($json['error_message']) && $json['error_message'] == "You have exceeded your daily request quota for this API." )
        || $json['status'] == "OVER_QUERY_LIMIT" ) return ;

      if(isset($json['error_message']) || $json['status'] == "ZERO_RESULTS" ) {

          if( $json['status'] = "ZERO_RESULTS") {
              $house_class = new mosRealEstateManager($database);
              $house_class->load($data->id);
              $house_class->hlatitude = "ZERO_RESULTS";
              if (!$house_class->check() || !$house_class->store()) {
                  echo $house_class->getError();
              }
              unset($house_class);
          }

          continue ;
      }
      $lat = $json['results'][0]['geometry']['location']['lat']; 
      $lng = $json['results'][0]['geometry']['location']['lng']; 
      

      $house_class = new mosRealEstateManager($database);
      $house_class->load($data->id);
      $house_class->hlatitude = $lat;
      $house_class->hlongitude = $lng;
      $house_class->map_zoom = '14';
      if (!$house_class->check() || !$house_class->store()) {
          echo $house_class->getError();
      }
      file_put_contents($logPath," updateMap 4 " . $address . "::".$data->id ."  ".time()." \n\n",  FILE_APPEND );

      unset($house_class);
  }

  mosRedirect("index.php?option=com_realestatemanager&option=".$option);
}



static function constructPathway($cat) {
    global $mainframe, $database, $option, $Itemid, $mosConfig_absolute_path;

    $app = JFactory::getApplication();
    $path_way = $app->getPathway();

    $query = "SELECT * FROM #__rem_main_categories WHERE section = 'com_realestatemanager' AND published = 1";
    $database->setQuery($query);
    $rows = $database->loadObjectlist('id');
    if ($cat != NULL)
            $pid = $cat->id;  //need check
        $pathway = array();
        $pathway_name = array();
        while ($pid != 0) {
            $cat = @$rows[$pid];
            $pathway[] = sefRelToAbs('index.php?option=' . $option .
               '&task=showCategory&catid=' . @$cat->id . '&Itemid=' . $Itemid);
            $pathway_name[] = @$cat->title;
            $pid = @$cat->parent_id;
        }
        $pathway = array_reverse($pathway);
        $pathway_name = array_reverse($pathway_name);

        for ($i = 0, $n = count($pathway); $i < $n; $i++) {
            $path_way->addItem($pathway_name[$i], $pathway[$i]);
        }
    }

    //get current user groups
    static function getUserGroups() {
        $my = JFactory::getUser();
        $acl = JFactory::getACL();
        $usergroups = $acl->get_group_parents($my->gid, 'ARO', 'NORECURSE');
        if ($usergroups)
            $usergroups = ',' . implode(',', $usergroups); else
        $usergroups = '';
        return '-2,' . $my->gid . $usergroups;
    }

    static function showCategory($catid, $printItem, $option, $layout, $languagelocale) {
        global $mainframe, $database, $acl, $my, $langContent;
        global $mosConfig_shownoauth, $mosConfig_live_site, $mosConfig_absolute_path;
        global $cur_template, $Itemid, $realestatemanager_configuration;
        global $mosConfig_list_limit, $limit, $total, $limitstart;

        PHP_realestatemanager::addTitleAndMetaTags();
        //getting the current category informations
        $database->setQuery("SELECT * FROM #__rem_main_categories WHERE id='" . intval($catid) . "'");
        $category = $database->loadObjectList();
        if (isset($category[0]))
            $category = $category[0];
        else {
            echo _REALESTATE_MANAGER_ERROR_ACCESS_PAGE;
            return;
        }

        if ($category->params == '')  $category->params = '-2';
        
        if (!checkAccess_REM($category->params, 'NORECURSE', userGID_REM($my->id), $acl)) {
            echo _REALESTATE_MANAGER_ERROR_ACCESS_PAGE;
            return;
        }
        //sorting

        $item_session = JFactory::getSession();
        $sort_arr = $item_session->get('rem_housesort', '');
        if (is_array($sort_arr)) {
            $tmp1 = mosGetParam($_POST, 'order_direction');
            if ($tmp1 != '') {
                $sort_arr['order_direction'] = $tmp1;
            }
            $tmp1 = mosGetParam($_POST, 'order_field');
            //$tmp1= $database->Quote($tmp1);
            if ($tmp1 != '') {
                $sort_arr['order_field'] = $tmp1;
            }
            $item_session->set('rem_housesort', $sort_arr);
        } else {
            $sort_arr = array();
            $sort_arr['order_field'] = 'htitle';
            $sort_arr['order_direction'] = 'asc';
            $item_session->set('rem_housesort', $sort_arr);
        }
        if ($sort_arr['order_field'] == "price")
            $sort_string = "CAST( " . $sort_arr['order_field'] . " AS SIGNED)" . " " . $sort_arr['order_direction'];
        else
            $sort_string = $sort_arr['order_field'] . " " . $sort_arr['order_direction'];



        if (isset($langContent)) {

            $lang = $langContent;
            // $query = "SELECT lang_code FROM #__languages WHERE sef = '$lang'";
            // $database->setQuery($query);
            // $lang = $database->loadResult();
            $lang = " and ( h.language = '$lang' or h.language like 'all' or h.language like '' "
            . " or h.language like '*' or h.language is null) "
            . " AND ( c.language = '$lang' or c.language like 'all' or c.language like '' or "
            . " c.language like '*' or c.language is null) ";
        } else {
            $lang = "";
        }
        $s = getWhereUsergroupsCondition('c');



        $query = "SELECT COUNT(DISTINCT h.id)
        \nFROM #__rem_houses AS h"
        . "\nLEFT JOIN #__rem_categories AS hc ON hc.iditem=h.id"
        . "\nLEFT JOIN #__rem_main_categories AS c ON c.id=hc.idcat"
        . "\nWHERE c.id = '$catid' AND h.published='1' $lang AND h.approved='1' AND c.published='1'
        AND ($s)";


        //getting groups of user
        $s = getWhereUsergroupsCondition('c');

        $database->setQuery($query);
        $total = $database->loadResult();


        $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6
        // getting all houses for this category

        $query = "SELECT h.*,hc.idcat AS catid,hc.idcat AS idcat, c.title as category_title "
        . "\nFROM #__rem_houses AS h "
        . "\nLEFT JOIN #__rem_categories AS hc ON hc.iditem=h.id "
        . "\nLEFT JOIN #__rem_main_categories AS c ON c.id=hc.idcat "
        . "\nWHERE hc.idcat = '" . $catid . "' AND h.published='1' "
        . "\n    AND c.published='1'  $lang AND ($s)"
        . "\nGROUP BY h.id"
        . "\nORDER BY " . $sort_string
        . "\nLIMIT $pageNav->limitstart,$pageNav->limit;";


        $database->setQuery($query);
        $houses = $database->loadObjectList();

        // For show all houses from subcategories which are included in main category use this request 
        //(just comment request to not display subcategory houses)

        // $query = "SELECT id FROM #__rem_main_categories WHERE parent_id = '" . $catid . "'";
        // $database->setQuery($query);
        // $if_parent = $database->loadColumn();
        // if(!empty($if_parent)){
        //     foreach($if_parent as $parent_cat){
        //         $query = "SELECT h.*,hc.idcat AS catid,hc.idcat AS idcat, c.title as category_title "
        //                 . "\nFROM #__rem_houses AS h "
        //                 . "\nLEFT JOIN #__rem_categories AS hc ON hc.iditem=h.id "
        //                 . "\nLEFT JOIN #__rem_main_categories AS c ON c.id=hc.idcat "
        //                 . "\nWHERE hc.idcat = '" . $parent_cat . "' AND h.published='1' "
        //                 . "\n AND c.published='1'  $lang AND ($s)"
        //                 . "\nGROUP BY h.id"
        //                 . "\nORDER BY " . $sort_string
        //                 . "\nLIMIT $pageNav->limitstart,$pageNav->limit;";
        //         $database->setQuery($query);
        //         $child_houses = $database->loadObjectList();
        //         $houses = array_merge($child_houses,$houses);
        //         $query = "SELECT id FROM #__rem_main_categories WHERE parent_id = '" . $parent_cat . "'";
        //         $database->setQuery($query);
        //         $if_parent2 = $database->loadColumn();
        //         foreach($if_parent2 as $child_id){
        //             $query = "SELECT h.*,hc.idcat AS catid,hc.idcat AS idcat, c.title as category_title "
        //                     . "\nFROM #__rem_houses AS h "
        //                     . "\nLEFT JOIN #__rem_categories AS hc ON hc.iditem=h.id "
        //                     . "\nLEFT JOIN #__rem_main_categories AS c ON c.id=hc.idcat "
        //                     . "\nWHERE hc.idcat = '" . $child_id . "' AND h.published='1' "
        //                     . "\n AND c.published='1'  $lang AND ($s)"
        //                     . "\nGROUP BY h.id"
        //                     . "\nORDER BY " . $sort_string
        //                     . "\nLIMIT $pageNav->limitstart,$pageNav->limit;";
        //             $database->setQuery($query);
        //             $child_houses = $database->loadObjectList();
        //             $houses = array_merge($child_houses,$houses);
        //         }
        //     }
        // }


        $query = "SELECT h.*,c.id, c.parent_id, c.title, c.image,COUNT(hc.iditem) as houses,
        '1' as display" .
        " \n FROM  #__rem_main_categories as c " .
        " \n LEFT JOIN #__rem_categories AS hc ON hc.idcat=c.id " .
        " \n LEFT JOIN #__rem_houses AS h ON h.id=hc.iditem " .
        "  \n WHERE c.section='com_realestatemanager'  $lang "
        . " AND c.published=1 AND ({$s})
        \n GROUP BY c.id
        \n ORDER BY c.parent_id DESC, c.ordering ";

        $database->setQuery($query);
        $cat_all = $database->loadObjectList();
        
        foreach ($cat_all as $k1 => $cat_item1) {            
            $query = "SELECT COUNT(hc.iditem) as houses" .
            "\n FROM  #__rem_main_categories as c " .
            "\n LEFT JOIN #__rem_categories AS hc ON hc.idcat=c.id " .
            "\n LEFT JOIN #__rem_houses AS h ON h.id=hc.iditem " .
            "\n WHERE c.section='com_realestatemanager' AND c.published=1  $lang
            \n AND ( h.published || isnull(h.published) ) AND ( h.approved || isnull(h.approved )) AND ({$s})
            \n AND c.id = " . $cat_all[$k1]->id . "    
            \n GROUP BY c.id";              

            $database->setQuery($query);

            $houses_count = $database->loadObjectList();
            if($houses_count)
                $cat_all[$k1]->houses = $houses_count[0]->houses;
            else
                $cat_all[$k1]->houses = 0;                    
        }

        $currentcat = new stdClass();

        // Parameters
        $menu = new JTableMenu($database); //for 1.6
        $menu->load($Itemid);

        $menu_name = set_header_name_rem($menu, $Itemid);

        $params = new mosParameters($menu->params);
        $params->def('rss_show', $realestatemanager_configuration['rss']['show']);
        $params->def('show_category', 1);
       // $params->def('header', $menu_name); // for 1.6
        $params->def('pageclass_sfx', '');
        $params->def('category_name', $category->title);
        // add wishlist markers ------------------------------------------
        $query = "SELECT fk_houseid FROM `#__rem_users_wishlist` " . 
        "WHERE fk_userid =" . $my->id;
        $database->setQuery($query);
        $result = $database->loadColumn();
        $params->def('wishlist', $result);
        //-----------------------------------------------------------------
        if ($layout==''){
            $layout = ($params->get('allhouselayout'));
        }
        if(JRequest::getVar('module') == 'mod_realestatemanager_featured_pro_j3'){
            $layout = 'default';
        }
        PHP_realestatemanager::constructPathway($category);

        // wish list
        if (($realestatemanager_configuration['wishlist']['show'])) {
            if (checkAccess_REM($realestatemanager_configuration['wishlist']['registrationlevel'],
               'RECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_add_to_wishlist', 1);
        }
    }      
//***************   begin show search_option    *********************
    if ($realestatemanager_configuration['search_option']['show']) {
        $params->def('search_option', 1);
        if (checkAccess_REM($realestatemanager_configuration['search_option']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl)) {
            $params->def('search_option_registrationlevel', 1);
    }
}
//**************   end show search_option     ******************************

if (($realestatemanager_configuration['rentstatus']['show'])) {
    if (checkAccess_REM($realestatemanager_configuration['rentrequest']['registrationlevel'],
       'RECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_rentstatus', 1);
    $params->def('show_rentrequest', 1);
}
}

if ($realestatemanager_configuration['housestatus']['show']) {
    $params->def('show_housestatus', 1);
    if (checkAccess_REM($realestatemanager_configuration['houserequest']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_houserequest', 1);
}
}
if ($realestatemanager_configuration['price']['show']) {
    $params->def('show_pricestatus', 1);
    if (checkAccess_REM($realestatemanager_configuration['price']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_pricerequest', 1);
}
}

        //*********   begin add for  Manager print pdf: button 'print PDF'    *******
if (($realestatemanager_configuration['print_pdf']['show'])) {
    $params->def('show_print_pdf', 1);
    if (checkAccess_REM($realestatemanager_configuration['print_pdf']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_print_pdf', 1);
}
}
        //*************************   end add for  Manager print pdf: button 'print PDF'    **************/
        //*************************   begin add for  Manager print view: button 'print VIEW'    **********/
if ($realestatemanager_configuration['print_view']['show']) {
    $params->def('show_print_view', 1);
    if (checkAccess_REM($realestatemanager_configuration['print_view']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_print_view', 1);
}
}
        //*************************   end add for  Manager print view: button 'print VIEW'    *******/
        //*************************   begin add for  Manager mail to: button 'mail to'    ***********/
if ($realestatemanager_configuration['mail_to']['show']) {
    $params->def('show_mail_to', 1);
    if (checkAccess_REM($realestatemanager_configuration['mail_to']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_mail_to', 1);
}
}
        //*************************   end add for  Manager mail to: button 'mail to'    ************/
        //*****   begin add for Manager Add house: button 'Add a house'
if ($realestatemanager_configuration['add_house']['show']) {
    $params->def('show_add_house', 1);
    if (checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_add_house', 1);
}
}
        //*********   end add for Manager Add house: button 'Add a house'   */
       //***************   begin show search_option    *********************/
if ($realestatemanager_configuration['search_option']['show']) {
    $params->def('search_option', 1);
    if (checkAccess_REM($realestatemanager_configuration['search_option']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('search_option_registrationlevel', 1);
}
}
        //**************   end show search_option     ******************************
$params->def('sort_arr_order_direction', $sort_arr['order_direction']);
$params->def('sort_arr_order_field', $sort_arr['order_field']);

        //add for show in category picture
if ($realestatemanager_configuration['cat_pic']['show'])
    $params->def('show_cat_pic', 1);

$params->def('show_rating', 1);
$params->def('hits', 1);
$params->def('back_button', $mainframe->getCfg('back_button'));

$currentcat->descrip = $category->description;


$params->def('show_rating', 1);
$params->def('hits', 1);
$params->def('back_button', $mainframe->getCfg('back_button'));

$currentcat->descrip = $category->description;
        // page image
$currentcat->img = null;
$path = $mosConfig_live_site . '/images/stories/';

$currentcat->header = $params->get('header');
$currentcat->header = ((trim($currentcat->header)) ? $currentcat->header . ":" : "") . $category->title;
$currentcat->img = null;


        // used to show table rows in alternating colours
$tabclass = array('sectiontableentry1', 'sectiontableentry2');

$params->def('minifotohigh', $realestatemanager_configuration['foto']['high']);
$params->def('minifotowidth', $realestatemanager_configuration['foto']['width']);

foreach ($houses as $house) {
    if ($house->language != '*') {
        $query = "SELECT sef FROM #__languages WHERE lang_code = '$house->language'";
        $database->setQuery($query);
        $house->language = $database->loadResult();
    }
}

$params->def('singlecategory01', "{loadposition com_realestatemanager_single_category_01,xhtml}");
$params->def('singlecategory02', "{loadposition com_realestatemanager_single_category_02,xhtml}");
$params->def('singlecategory03', "{loadposition com_realestatemanager_single_category_03,xhtml}");
$params->def('singlecategory04', "{loadposition com_realestatemanager_single_category_04,xhtml}");
$params->def('singlecategory05', "{loadposition com_realestatemanager_single_category_05,xhtml}");
$params->def('singlecategory06', "{loadposition com_realestatemanager_single_category_06,xhtml}");
$params->def('singlecategory07', "{loadposition com_realestatemanager_single_category_07,xhtml}");
$params->def('singlecategory08', "{loadposition com_realestatemanager_single_category_08,xhtml}");
$params->def('singlecategory09', "{loadposition com_realestatemanager_single_category_09,xhtml}");
$params->def('singlecategory10', "{loadposition com_realestatemanager_single_category_10,xhtml}");
$params->def('singlecategory11', "{loadposition com_realestatemanager_single_category_11,xhtml}");
$params->def('typeLayout', 'alone_category');

if (empty($houses)) {
    HTML_realestatemanager::displayHouses_empty($houses, $currentcat, $params,
     $tabclass, $catid, $cat_all, $pageNav,PHP_realestatemanager::is_exist_subcategory_houses($catid), $option);
} else {
  switch ($printItem) {
    case 'pdf':
    HTML_realestatemanager::displayHousesPdf($houses, $currentcat,
     $params, $tabclass, $catid, $cat_all, $pageNav);
    break;

    case 'print':
    HTML_realestatemanager::displayHousesPrint($houses, $currentcat,
     $params, $tabclass, $catid, $cat_all, $pageNav);
    break;

    default:
    HTML_realestatemanager::displayHouses($houses, $currentcat, $params,
       $tabclass, $catid, $cat_all, $pageNav,PHP_realestatemanager::is_exist_subcategory_houses($catid), $option, $layout);
    break;
}
}
}

static function showItemREM($option, $id, $catid, $printItem, $layout) {
    global $mainframe, $database, $my, $acl, $option;
    global $mosConfig_shownoauth, $mosConfig_live_site, $mosConfig_absolute_path;
    global $cur_template, $Itemid, $realestatemanager_configuration;

    PHP_realestatemanager::addTitleAndMetaTags($id);

    $database->setQuery("SELECT id FROM #__rem_houses where id=$id ");
    if (version_compare(JVERSION, "3.0.0", "lt"))
        $trueid = $database->loadResultArray();
    else
        $trueid = $database->loadColumn();
    if (!in_array(intval($id), $trueid)) {
        echo _REALESTATE_MANAGER_ERROR_ACCESS_PAGE;
        return;
    }
        //add to path category name
        //getting the current category informations
    $query = "SELECT * FROM #__rem_main_categories WHERE id='" . intval($catid) . "'";

    $database->setQuery($query);
    $category = $database->loadObjectList();

    if (isset($category[0]))
        $category = $category[0];
    else {
        echo _REALESTATE_MANAGER_ERROR_ACCESS_PAGE;
        return;
    }

        //Record the hit
    $sql = "UPDATE #__rem_houses SET hits = hits + 1 WHERE id = " . $id . "";
    $database->setQuery($sql);
    $database->query();

    $sql2 = "UPDATE #__rem_houses SET featured_clicks = featured_clicks - 1 "
    . " WHERE featured_clicks > 0 and id = " . $id . "";
    $database->setQuery($sql2);
    $database->query();

    $sql3 = "UPDATE #__rem_houses SET featured_shows = featured_shows - 1 "
    . " WHERE featured_shows > 0 and id = " . $id . "";
    $database->setQuery($sql3);
    $database->query();

        //load the house
    $house = new mosRealEstateManager($database);
    $house->load($id);
    $house->setOwnerName();
    $access = $house->getAccess_REM();

        // for breadcrumbs 
    PHP_realestatemanager::constructPathway($category);
    $path_way = $mainframe->getPathway();
    $path_way->addItem(substr($house->htitle, 0, 32) . "");

    $selectstring = "SELECT a.* FROM #__rem_houses AS a";
    $database->setQuery($selectstring);
    $rows = $database->loadObjectList();
    $date = date(time());
    foreach ($rows as $row) {
        $check = strtotime($row->checked_out_time);
        $remain = 7200 - ($date - $check);
        if (($remain <= 0) && ($row->checked_out != 0)) {
            $database->setQuery("UPDATE #__rem_houses SET checked_out=0,checked_out_time=0");
            $database->query();
        }
    }

    if (!checkAccess_REM($access, 'RECURSE', userGID_REM($my->id), $acl)) {
        echo _REALESTATE_MANAGER_ERROR_ACCESS_PAGE;
        return;
    }
    if ($house->owneremail != $my->email) {
        if ($house->published == 0) {
            echo _REALESTATE_MANAGER_ERROR_HOUSE_NOT_PUBLISHED;
            return;
        }
        if ($house->approved == 0) {
            echo _REALESTATE_MANAGER_ERROR_HOUSE_NOT_APPROVED;
            return;
        }
    }
        // $path_way->addItem(substr($house->htitle, 0, 32) . "");
        /////////////////////////////////////////////////////////////////////////////////////
        //Select list for listing type
    $listing_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
    $listing_type[1] = _REALESTATE_MANAGER_OPTION_FOR_RENT;
    $listing_type[2] = _REALESTATE_MANAGER_OPTION_FOR_SALE;

        //Select list for listing status
    $listing_status[_REALESTATE_MANAGER_OPTION_SELECT] = 0;
    $listing_status1 = explode(',', _REALESTATE_MANAGER_OPTION_LISTING_STATUS);
    $i = 1;
    foreach ($listing_status1 as $listing_status2) {
        $listing_status[$listing_status2] = $i;
        $i++;
    }

        //Select list for property type
    $property_type[_REALESTATE_MANAGER_OPTION_SELECT] = 0;
    $property_type1 = explode(',', _REALESTATE_MANAGER_OPTION_PROPERTY_TYPE);
    $i = 1;
    foreach ($property_type1 as $property_type2) {
        $property_type[$property_type2] = $i;
        $i++;
    }


        ////////////////////////////////////////////////////////////
        //$app = JFactory::getApplication();
        //$menu1 = $app->getMenu();
        //if ( $menu1->getItem($Itemid) )
        //$menu_name = $menu1->getItem($Itemid)->title ;
        //else $menu_name = '';
        // --
        // Parameters
        $menu = new JTableMenu($database); // for 1.6
        // Parameters
        $menu = new mosMenu($database);
        $menu->load($Itemid);

        $menu_name = set_header_name_rem($menu, $Itemid);

        $params = new mosParameters($menu->params);
        $params->def('header', $menu_name); //for 1.6
        $params->def('pageclass_sfx', '');
        if (!isset($my->id)) { //for 1.6
            $my->id = 0;
        }
        // add wishlist markers ------------------------------------------
        $query = "SELECT fk_houseid FROM `#__rem_users_wishlist` " . 
        "WHERE fk_userid =" . $my->id;
        $database->setQuery($query);
        $result = $database->loadColumn();
        $params->def('wishlist', $result);
        //-----------------------------------------------------------------
        // wish list
        if (($realestatemanager_configuration['wishlist']['show'])) {
            if (checkAccess_REM($realestatemanager_configuration['wishlist']['registrationlevel'],
               'RECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_add_to_wishlist', 1);
        }
    }
        //*******   begin add for  Manager print pdf: button 'print PDF'    ***********
    if ($realestatemanager_configuration['print_pdf']['show']) {
        $params->def('show_print_pdf', 1);
        if (checkAccess_REM($realestatemanager_configuration['print_pdf']['registrationlevel'], 
          'NORECURSE', userGID_REM($my->id), $acl)) {
            $params->def('show_input_print_pdf', 1);
    }
}
        //****   end add for  Manager print pdf: button 'print PDF'    *************
        //****   begin add for  Manager print view: button 'print VIEW'   **********
if ($realestatemanager_configuration['print_view']['show']) {
    $params->def('show_print_view', 1);
    if (checkAccess_REM($realestatemanager_configuration['print_view']['registrationlevel'], 
      'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_print_view', 1);
}
}
        //****   end add for  Manager print view: button 'print VIEW'    ********
        //****   begin add for  Manager mail to: button 'mail to'    *************
if ($realestatemanager_configuration['mail_to']['show']) {
    $params->def('show_mail_to', 1);
    if (checkAccess_REM($realestatemanager_configuration['mail_to']['registrationlevel'], 
      'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_mail_to', 1);
}
}

if ($realestatemanager_configuration['calendar']['show']) {
    $params->def('calendar_show', 1);
    if (checkAccess_REM($realestatemanager_configuration['calendarlist']['registrationlevel'],
     'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('calendarlist_show', 1);
}
}

        //***  end add for  Manager mail to: button 'mail to'    **********

if ($realestatemanager_configuration['rentstatus']['show']) {
    $params->def('show_rentstatus', 1);
    if (checkAccess_REM($realestatemanager_configuration['rentrequest']['registrationlevel'], 
      'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_rentrequest', 1);
}
}

if ($realestatemanager_configuration['buystatus']['show']) {
    $params->def('show_buystatus', 1);
    if (checkAccess_REM($realestatemanager_configuration['buyrequest']['registrationlevel'], 
      'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_buyrequest', 1);
}
}

if ($realestatemanager_configuration['reviews']['show']) {
    $params->def('show_reviews', 1);
    if (checkAccess_REM($realestatemanager_configuration['reviews']['registrationlevel'],
     'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_inputreviews', 1);
}
}

if ($realestatemanager_configuration['edocs']['show']) {
    $params->def('show_edocstatus', 1);
    if (checkAccess_REM($realestatemanager_configuration['edocs']['registrationlevel'],
     'NORECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_edocsrequest', 1); //+18.01
                //+18.01
        }
    }

    if ($realestatemanager_configuration['price']['show']) {
        $params->def('show_pricestatus', 1);
        if (checkAccess_REM($realestatemanager_configuration['price']['registrationlevel'], 
          'NORECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_pricerequest', 1); //+18.01
        }
    }

    if ($realestatemanager_configuration['sale_separator']) {
        $params->def('show_sale_separator', 1);
    }

        //************   begin show 'location and reviews tab'   ***************
    if (($realestatemanager_configuration['location_tab']['show'])) {
        $params->def('show_location', 1);
        if (checkAccess_REM($realestatemanager_configuration['location_tab']['registrationlevel'],
         'NORECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_locationtab_registrationlevel', 1); //+18.01
        }
    }

        //************   begin show 'location and reviews tab'   ***************
    if (($realestatemanager_configuration['street_view']['show'])) {
        $params->def('street_view', 1);
        if (checkAccess_REM($realestatemanager_configuration['street_view']['registrationlevel'],
         'NORECURSE', userGID_REM($my->id), $acl)) {
                $params->def('street_view_registrationlevel', 1); //+18.01
        }
    }

    if (($realestatemanager_configuration['reviews_tab']['show'])) {
        $params->def('show_reviews_tab', 1);
        if (checkAccess_REM($realestatemanager_configuration['reviews_tab']['registrationlevel'], 
           'NORECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_reviewstab_registrationlevel', 1); //+18.01
        }
    }
        //************   end show 'location and reviews tab'   ***************
        //************   begin show 'contacts'   ***************************
    if (($realestatemanager_configuration['contacts']['show'])) {
        $params->def('show_contacts_line', 1);
        $i = checkAccess_REM($realestatemanager_configuration['contacts']['registrationlevel'],
         'NORECURSE', userGID_REM($my->id), $acl);
        if ($i) {
                $params->def('show_contacts_registrationlevel', 1); //+18.01
            }
        }

        if (($realestatemanager_configuration['owner']['show'])) {
            $params->def('show_owner_line', 1);
            $i = checkAccess_REM($realestatemanager_configuration['owner']['registrationlevel'], 
              'NORECURSE', userGID_REM($my->id), $acl);
            if ($i) {
                $params->def('show_owner_registrationlevel', 1); //+18.01
            }
        }

        if (($realestatemanager_configuration['captcha_option']['show'])) {
            $params->def('captcha_option', 1);
            $i = checkAccess_REM($realestatemanager_configuration['captcha_option']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i) {
                $params->def('captcha_option_registrationlevel', 1); //+18.01
            }
        }

        if (($realestatemanager_configuration['captcha_option_booking']['show'])) {
            $params->def('captcha_option_booking', 1);
            $i = checkAccess_REM($realestatemanager_configuration['captcha_option_booking']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i) {
                $params->def('captcha_option_booking_registrationlevel', 1); //+18.01
            }
        }

        if (($realestatemanager_configuration['captcha_option_sendmessage']['show'])) {
            $params->def('captcha_option_sendmessage', 1);
            $i = checkAccess_REM($realestatemanager_configuration['captcha_option_sendmessage']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i) {
                $params->def('captcha_option_sendmessage_registrationlevel', 1); //+18.01
            }
        }

        if (($realestatemanager_configuration['calendar']['show'])) {
            $params->def('calendar_option', 1);
            $i = checkAccess_REM($realestatemanager_configuration['calendarlist']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i) {
                $params->def('calendar_option_registrationlevel', 1); //+18.01
            }
        }

        $params->def('pageclass_sfx', '');
        $params->def('item_description', 1);
        $params->def('show_edoc', $realestatemanager_configuration['edocs']['show']);
        $params->def('back_button', $mainframe->getCfg('back_button'));


        // page header
        $currentcat = new stdClass();
        $currentcat->header = $params->get('header');
        $currentcat->header = ((trim($currentcat->header)) ? $currentcat->header . ":" : "") . $house->htitle;
        $currentcat->img = null;


        $query = "select main_img from #__rem_photos WHERE fk_houseid='$house->id' order by img_ordering,id";
        $database->setQuery($query);
        $house_photos = $database->loadObjectList();
        // show the house

        $query = "SELECT f.* ";
        $query .= "FROM #__rem_feature as f ";
        $query .= "LEFT JOIN #__rem_feature_houses as fv ON f.id = fv.fk_featureid ";
        $query .= "WHERE f.published = 1 and fv.fk_houseid = $id ";
        $query .= "ORDER BY f.categories";
        $database->setQuery($query);
        $house_feature = $database->loadObjectList();

        /**********************************************/
        $currencyArr = array();
        $currentCurrency='';
        $currencys = explode(';', $realestatemanager_configuration['currency']);
        foreach ($currencys as $oneCurency) {
            $oneCurrArr = explode('=', $oneCurency);
            if(!empty($oneCurrArr[0]) && !empty($oneCurrArr[1])){
             $currencyArr[$oneCurrArr[0]] = $oneCurrArr[1]; 
             if($house->priceunit == $oneCurrArr[0]){
                 $currentCurrency = $oneCurrArr[1];
             }
         }
     }
     if($currentCurrency){
        foreach ($currencyArr as $key=>$value) {
            $currencys_price[$key] = round($value / $currentCurrency * $house->price, 2);

        }
    }else{
        if($house->owner_id == $my->id){
            JError::raiseWarning( 100, _REALESTATE_MANAGER_CURRENCY_ERROR);
        }
    }

    /**********************************************/

    $params->def('view01', "{loadposition com_realestatemanager_view_house_01,xhtml}");
    $params->def('view02', "{loadposition com_realestatemanager_view_house_02,xhtml}");
    $params->def('view03', "{loadposition com_realestatemanager_view_house_03,xhtml}");
    $params->def('viewdescription', "{loadposition com_realestatemanager_view_house_description,xhtml}");
    $params->def('view04', "{loadposition com_realestatemanager_view_house_04,xhtml}");
    $params->def('view05', "{loadposition com_realestatemanager_view_house_05,xhtml}");
    $params->def('view06', "{loadposition com_realestatemanager_view_house_06,xhtml}");
    $params->def('view07', "{loadposition com_realestatemanager_view_house_07,xhtml}");
    $params->def('similaires', "{loadposition com_realestatemanager_similaires,xhtml}");
        //////////////start select video/tracks
        $query = "SELECT src,type,youtube FROM #__rem_video_source AS r
                LEFT JOIN  #__rem_houses AS h ON r.fk_house_id=h.id
                WHERE r.fk_house_id =" . $house->id;
                $database->setQuery($query);
                $videos = $database->loadObjectList();
        $query = "SELECT src,kind,scrlang,label FROM #__rem_track_source AS t
                LEFT JOIN  #__rem_houses AS h ON t.fk_house_id = h.id
                WHERE t.fk_house_id = " . $house->id;
                $database->setQuery($query);
                $tracks = $database->loadObjectList();
        /////////////////////end

                switch ($printItem) {
                    case 'pdf': HTML_realestatemanager::displayHouseMainPdf($house, $tabclass,
                       $params, $currentcat, $ratinglist, $house_photos);
                    break;

                    case 'print': HTML_realestatemanager::displayHouseMainprint($house,
                       $tabclass, $params, $currentcat, $ratinglist, $house_photos);
                    break;

                    default: HTML_realestatemanager::displayHouse($house, $tabclass,
                       $params, $currentcat, $ratinglist, $house_photos,$videos,$tracks, $id, $catid,
                       $option, $house_feature, $currencys_price, $layout);
                    break;
                }
            }

            static function getMonth($month) {

                switch ($month) {
                    case 1:
                    $smonth = JText::_('JANUARY');
                    break;
                    case 2:
                    $smonth = JText::_('FEBRUARY');
                    break;
                    case 3:
                    $smonth = JText::_('MARCH');
                    break;
                    case 4:
                    $smonth = JText::_('APRIL');
                    break;
                    case 5:
                    $smonth = JText::_('MAY');
                    break;
                    case 6:
                    $smonth = JText::_('JUNE');
                    break;
                    case 7:
                    $smonth = JText::_('JULY');
                    break;
                    case 8:
                    $smonth = JText::_('AUGUST');
                    break;
                    case 9:
                    $smonth = JText::_('SEPTEMBER');
                    break;
                    case 10:
                    $smonth = JText::_('OCTOBER');
                    break;
                    case 11:
                    $smonth = JText::_('NOVEMBER');
                    break;
                    case 12:
                    $smonth = JText::_('DECEMBER');
                    break;
                }

                return $smonth;
            }

            static function showSearchHouses($options, $catid, $option, $layout = "default") {
                global $mainframe, $database, $my, $langContent, $acl;
                global $mosConfig_shownoauth, $mosConfig_live_site, $mosConfig_absolute_path, $realestatemanager_configuration;
                global $cur_template, $Itemid;

                PHP_realestatemanager::addTitleAndMetaTags();

                $currentcat = new stdClass();
        //if it is't from menus, get layout from config.

                $jinput = JFactory::getApplication()->input;

        //parameters
                $menu = new mosMenu($database);
                $menu->load($Itemid);
                $params = new mosParameters($menu->params);

                $menu_name = set_header_name_rem($menu, $Itemid);

                $params->def('header', $menu_name);
                $params->def('pageclass_sfx', '');
                $params->def('show_category', '1');
                $params->def('back_button', $mainframe->getCfg('back_button'));
                $pathway = sefRelToAbs('index.php?option=' . $option . '&amp;task=show_search&amp;Itemid=' . $Itemid);
                $pathway_name = _REALESTATE_MANAGER_LABEL_SEARCH;

                $currentcat->descrip = " ";
                $currentcat->align = 'right';

        //page image
                $currentcat->img = "./components/com_realestatemanager/images/rem_logo.png";

        //used to show table rows in alternating colours
                $tabclass = array('sectiontableentry1', 'sectiontableentry2');

        //listing type
                $hlisting = $jinput->get('listing_type') ? $jinput->get('listing_type') : _REALESTATE_MANAGER_LABEL_ALL;
                $listing_type[] = mosHtml::makeOption(_REALESTATE_MANAGER_LABEL_ALL, _REALESTATE_MANAGER_LABEL_ALL);
                $listing_type[] = mosHtml::makeOption(1, _REALESTATE_MANAGER_OPTION_FOR_RENT);
                $listing_type[] = mosHtml::makeOption(2, _REALESTATE_MANAGER_OPTION_FOR_SALE);
                $listing_type_list = mosHTML :: selectList($listing_type, 'listing_type',
                   'class="inputbox" size="1" style="width: 115px"', 'value', 'text', $hlisting);
                $params->def('listing_type_list', $listing_type_list);

        //listing status
                $hlistingstatus = $jinput->get('listing_status') ? $jinput->get('listing_status') : _REALESTATE_MANAGER_LABEL_ALL;
                $listing_status[] = mosHtml::makeOption(_REALESTATE_MANAGER_LABEL_ALL, _REALESTATE_MANAGER_LABEL_ALL);
                $listing_status1 = explode(',', _REALESTATE_MANAGER_OPTION_LISTING_STATUS);
                $i = 1;
                foreach ($listing_status1 as $listing_status2) {
                    $listing_status[] = mosHtml::makeOption($i, $listing_status2);
                    $i++;
                }
                $listing_status_list = mosHTML :: selectList($listing_status, 'listing_status', 
                  'class="inputbox" size="1" style="width: 115px"', 'value', 'text', $hlistingstatus);
                $params->def('listing_status_list', $listing_status_list);

        //property type
                $hproperty = $jinput->get('property_type') ? $jinput->get('property_type') : _REALESTATE_MANAGER_LABEL_ALL;
                $property_type[] = mosHtml::makeOption(_REALESTATE_MANAGER_LABEL_ALL, _REALESTATE_MANAGER_LABEL_ALL);
                $property_type1 = explode(',', _REALESTATE_MANAGER_OPTION_PROPERTY_TYPE);
                $i = 1;
                foreach ($property_type1 as $property_type2) {
                    $property_type[] = mosHtml::makeOption($i, $property_type2);
                    $i++;
                }
                $property_type_list = mosHTML :: selectList($property_type, 'property_type', 'class="inputbox"
                 size="1" style="width: 115px"', 'value', 'text', $hproperty);
                $params->def('property_type_list', $property_type_list);

        //categories        
                if (isset($langContent)) {
                    $lang = $langContent;
            // $query = "SELECT lang_code FROM #__languages WHERE sef = '$lang'";
            // $database->setQuery($query);
            // $lang = $database->loadResult();
                    $lang = " c.language = '$lang' or c.language like 'all' or c.language like '' "
                    . " or c.language like '*' or c.language is null ";
                } else {
                    $lang = "";
                }   

                $categories[] = mosHTML :: makeOption(_REALESTATE_MANAGER_LABEL_ALL, _REALESTATE_MANAGER_LABEL_ALL);
                $clist = com_house_categoryTreeList(0, '', true, $categories, $catid, $lang);

        //price
                $db = JFactory::getDBO();
                $query = "SELECT price  FROM   #__rem_houses ";
                $database->setQuery($query);
                if (version_compare(JVERSION, "3.0.0", "lt"))
                    $prices = $database->loadResultArray();
                else
                    $prices = $database->loadColumn();

                rsort($prices, SORT_NUMERIC);
                $max_price = $prices[0];
                $price[] = mosHTML :: makeOption(_REALESTATE_MANAGER_LABEL_FROM, _REALESTATE_MANAGER_LABEL_FROM);
                $price_to[] = mosHTML :: makeOption(_REALESTATE_MANAGER_LABEL_TO, _REALESTATE_MANAGER_LABEL_TO);

                $stepPrice = $max_price / 50;
                $stepPrice = (string) $stepPrice;
                $stepCount = strlen($stepPrice);
                if ($stepCount > 2) {
                    $stepFinalPrice = $stepPrice[0] . $stepPrice[1];
                    for ($i = 2; $i < $stepCount; $i++) {
                        $stepFinalPrice .= '0';
                    }
                    $stepFinalPrice = (int) $stepFinalPrice;
                }
                else
                    $stepFinalPrice = (int) $stepPrice;

                if($max_price == 0 || $stepFinalPrice == 0){
                    $price[] = mosHTML :: makeOption(0, 0);
                    $price_to[] = mosHTML :: makeOption(0, 0);
                }
                for ($i = 0; $i < $max_price; $i = $i + $stepFinalPrice) {
                    $price[] = mosHTML :: makeOption($i, $i);
                    $price_to[] = mosHTML :: makeOption($i, $i);
                }

//***************   begin show search_option    *********************
                if ($realestatemanager_configuration['search_option']['show']) {
                    $params->def('search_option', 1);
                    if (checkAccess_REM($realestatemanager_configuration['search_option']['registrationlevel'],
                      'NORECURSE', userGID_REM($my->id), $acl)) {
                        $params->def('search_option_registrationlevel', 1);
                }
            }
//**************   end show search_option     ******************************        

            $pricelist = mosHTML :: selectList($price, 'pricefrom2', 'class="inputbox" size="1"', 'value', 'text');
            $params->def('pricefrom2', $pricelist);
            $pricelistto = mosHTML :: selectList($price_to, 'priceto2', 'class="inputbox" size="1"', 'value', 'text');
            $params->def('priceto2', $pricelistto);

            $params->def('showsearch01', "{loadposition com_realestatemanager_show_search_01,xhtml}");
            $params->def('showsearch02', "{loadposition com_realestatemanager_show_search_02,xhtml}");
            $params->def('showsearch03', "{loadposition com_realestatemanager_show_search_03,xhtml}");
            $params->def('showsearch04', "{loadposition com_realestatemanager_show_search_04,xhtml}");
            $params->def('showsearch05', "{loadposition com_realestatemanager_show_search_05,xhtml}");

            HTML_realestatemanager::showSearchHouses($params, $currentcat, $clist, $option, $layout);
        }

        static function searchHouses($options, $catid, $option, $languagelocale, $ownername = '') {

            global $mainframe, $database, $my, $acl, $limitstart, $limit, $langContent;
            global $mosConfig_shownoauth, $mosConfig_live_site, $mosConfig_absolute_path;
            global $cur_template, $Itemid, $realestatemanager_configuration,$task, $layout;

            PHP_realestatemanager::addTitleAndMetaTags();

            $ownernameTMP = $ownername;

        //get current user groups
            $s = getWhereUsergroupsCondition("c");
            $session = JFactory::getSession();
            if ($ownername == '') {
                $pathway = sefRelToAbs('index.php?option=' . $option . '&amp;task=show_search&amp;Itemid=' . $Itemid);
                $pathway_name = _REALESTATE_MANAGER_LABEL_SEARCH;
            }

            if (array_key_exists("searchtext", $_REQUEST)) {
                $search = protectInjectionWithoutQuote('searchtext', '');
                $search = addslashes($search);
                $session->set("poisk", $search);
            }

            $poisk_search = $session->get("poisk", "");

            $where = array();
            $Houseid = " ";
            $Description = " ";
            $Title = " ";
            $Address = " ";
            $Country = " ";
            $Region = " ";
            $City = " ";
            $Zipcode = " ";
            $Extra1 = " ";
            $Extra2 = " ";
            $Extra3 = " ";
            $Extra4 = " ";
            $Extra5 = " ";
            $Extra6 = " ";
            $Extra7 = " ";
            $Extra8 = " ";
            $Extra9 = " ";
            $Extra10 = " ";
            $Rooms = " ";
            $Bathrooms = " ";
            $Bedrooms = " ";
            $Contacts = " ";
            $Agent = " ";
            $House_size = " ";
            $Lot_size = " ";
            $Built_year = " ";
            $Rent = " ";
            $RentSQL = " ";
            $RentSQL_JOIN_1 = " ";
            $RentSQL_JOIN_2 = " ";
            $RentSQL_rent_until = " ";

            if (isset($_REQUEST['exactly']) && $_REQUEST['exactly'] == "on") {
                $exactly = $poisk_search;
            } else {
                $exactly = "%$poisk_search%";
            }

        //sorting
            $item_session = JFactory::getSession();
            $sort_arr = $item_session->get('rem_housesort', '');
            if (is_array($sort_arr)) {
                $tmp1 = protectInjectionWithoutQuote('order_direction');
            //$tmp1= $database->Quote($tmp1);
                if ($tmp1 != '')
                    $sort_arr['order_direction'] = $tmp1;
                $tmp1 = protectInjectionWithoutQuote('order_field');
                if ($tmp1 != '')
                    $sort_arr['order_field'] = $tmp1;
                $item_session->set('rem_housesort', $sort_arr);
            } else {
                $sort_arr = array();
                $sort_arr['order_field'] = 'htitle';
                $sort_arr['order_direction'] = 'asc';
                $item_session->set('rem_housesort', $sort_arr);
            }
            if ($sort_arr['order_field'] == "price")
                $sort_string = "CAST( " . $sort_arr['order_field'] . " AS SIGNED)" . " " . $sort_arr['order_direction'];
            else
            $sort_string = $sort_arr['order_field'] . " " . $sort_arr['order_direction'];  //end sortering

        $is_add_or = false;
        $add_or_value = "  ";

        if ($poisk_search != '') {
            if (isset($_REQUEST['Houseid']) && $_REQUEST['Houseid'] == "on") {
                $Houseid = " ";
                if ($is_add_or)
                    $Houseid = " or ";
                $is_add_or = true;
                $Houseid .= "LOWER(b.houseid) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['Description']) && $_REQUEST['Description'] == "on") {
                $Description = " ";
                if ($is_add_or)
                    $Description = " or ";
                $is_add_or = true;
                $Description .=" LOWER(b.description) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['Title']) && $_REQUEST['Title'] == "on") {
                $Title = " ";
                if ($is_add_or)
                    $Title = " or ";
                $is_add_or = true;
                $Title .=" LOWER(b.htitle) LIKE '$exactly' ";
            }

            if (isset($_REQUEST['Address']) && $_REQUEST['Address'] == "on") {
                $Address = " ";
                if ($is_add_or)
                    $Address = " or ";
                $is_add_or = true;
                $Address .=" LOWER(b.hlocation) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['Country']) && $_REQUEST['Country'] == "on") {
                $Country = " ";
                if ($is_add_or)
                    $Country = " or ";
                $is_add_or = true;
                $Country .= "LOWER(b.hcountry) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['Region']) && $_REQUEST['Region'] == "on") {
                $Region = " ";
                if ($is_add_or)
                    $Region = " or ";
                $is_add_or = true;
                $Region .= "LOWER(b.hregion) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['City']) && $_REQUEST['City'] == "on") {
                $City = " ";
                if ($is_add_or)
                    $City = " or ";
                $is_add_or = true;
                $City .= "LOWER(b.hcity) LIKE '$exactly' ";
            }

            if (isset($_REQUEST['Zipcode']) && $_REQUEST['Zipcode'] == "on") {
                $Zipcode = " ";
                if ($is_add_or)
                    $Zipcode = " or ";
                $is_add_or = true;
                $Zipcode .= "LOWER(b.hzipcode) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['extra1']) && $_REQUEST['extra1'] == "on") {
                $Extra1 = " ";
                if ($is_add_or)
                    $Extra1 = " or ";
                $is_add_or = true;
                $Extra1 .= "LOWER(b.extra1) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['extra2']) && $_REQUEST['extra2'] == "on") {
                $Extra2 = " ";
                if ($is_add_or)
                    $Extra2 = " or ";
                $is_add_or = true;
                $Extra2 .= "LOWER(b.extra2) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['extra3']) && $_REQUEST['extra3'] == "on") {
                $Extra3 = " ";
                if ($is_add_or)
                    $Extra3 = " or ";
                $is_add_or = true;
                $Extra3 .= "LOWER(b.extra3) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['extra4']) && $_REQUEST['extra4'] == "on") {
                $Extra4 = " ";
                if ($is_add_or)
                    $Extra4 = " or ";
                $is_add_or = true;
                $Extra4 .= "LOWER(b.extra4) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['extra5']) && $_REQUEST['extra5'] == "on") {
                $Extra5 = " ";
                if ($is_add_or)
                    $Extra5 = " or ";
                $is_add_or = true;
                $Extra5 .= "LOWER(b.extra5) LIKE '$exactly' ";
            }

            if (isset($_REQUEST['rooms']) && $_REQUEST['rooms'] == "on") {
                $Rooms = " ";
                if ($is_add_or)
                    $Rooms = " or ";
                $is_add_or = true;
                $Rooms .= "LOWER(b.Rooms) LIKE '$exactly' ";
            }

            // if (isset($_REQUEST['Bathrooms']) && $_REQUEST['Bathrooms'] == "on") {
            //     $Bathrooms = " ";
            //     if ($is_add_or)
            //         $Bathrooms = " or ";
            //     $is_add_or = true;
            //     $Bathrooms .= "LOWER(b.bathrooms) LIKE '$exactly' ";
            // }
            // if (isset($_REQUEST['Bedrooms']) && $_REQUEST['Bedrooms'] == "on") {
            //     $Bedrooms = " ";
            //     if ($is_add_or)
            //         $Bedrooms = " or ";
            //     $is_add_or = true;
            //     $Bedrooms .= "LOWER(b.bedrooms) LIKE '$exactly' ";
            // }

            if (isset($_REQUEST['Contacts']) && $_REQUEST['Contacts'] == "on") {
                $Contacts = " ";
                if ($is_add_or)
                    $Contacts = " or ";
                $is_add_or = true;
                $Contacts .=" LOWER(b.contacts) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['Agent']) && $_REQUEST['Agent'] == "on") {
                $Agent = " ";
                if ($is_add_or)
                    $Agent = " or ";
                $is_add_or = true;
                $Agent .=" LOWER(b.agent) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['house_size']) && $_REQUEST['house_size'] = "on") {
                $House_size = " ";
                if ($is_add_or)
                    $House_size = " or ";
                $is_add_or = true;
                $House_size .=" LOWER(b.house_size) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['Lot_size']) && $_REQUEST['Lot_size'] = "on") {
                $Lot_size = " ";
                if ($is_add_or)
                    $Lot_size = " or ";
                $is_add_or = true;
                $Lot_size .=" LOWER(b.lot_size) LIKE '$exactly' ";
            }
            if (isset($_REQUEST['year']) && $_REQUEST['year'] = "on") {
                $House_size = " ";
                if ($is_add_or)
                    $House_size = " or ";
                $is_add_or = true;
                $House_size .=" LOWER(b.year) LIKE '$exactly' ";
            }

            if (isset($_REQUEST['Garages']) && $_REQUEST['Garages'] = "on") {
                $Garages = " ";
                if ($is_add_or)
                    $Garages = " or ";
                $is_add_or = true;
                $Garages .=" LOWER(b.garages) LIKE '$exactly' ";
            }
        }

        if (isset($_REQUEST['bathrooms']) ) {
            $where[] = " LOWER(b.bathrooms) >= ".(int)($_REQUEST['bathrooms'] )."";
        }
        if (isset($_REQUEST['bedrooms']) ) {
            $where[] = " LOWER(b.bedrooms) >= ".(int)($_REQUEST['bedrooms'] )."";
        }

        $listing_type = protectInjectionWithoutQuote('listing_type', '');
        $listing_status = protectInjectionWithoutQuote('listing_status', '');
        $property_type = protectInjectionWithoutQuote('property_type', '');
        $extra6 = protectInjectionWithoutQuote('extra6', '');
        $extra7 = protectInjectionWithoutQuote('extra7', '');
        $extra8 = protectInjectionWithoutQuote('extra8', '');
        $extra9 = protectInjectionWithoutQuote('extra9', '');
        $extra10 = protectInjectionWithoutQuote('extra10', '');
        if ($listing_type != _REALESTATE_MANAGER_LABEL_ALL && $listing_type != '') {
            $where[] = " LOWER(b.listing_type)='$listing_type'";
        }
        if ($listing_status != _REALESTATE_MANAGER_LABEL_ALL && $listing_status != '') {
            $where[] = " LOWER(b.listing_status)='$listing_status'";
        }
        if ($property_type != _REALESTATE_MANAGER_LABEL_ALL && $property_type != '') {
            $where[] = " LOWER(b.property_type)='$property_type'";
        }
        if ($extra6 != _REALESTATE_MANAGER_LABEL_ALL && $extra6 != '') {
            $where[] = " LOWER(b.extra6)='$extra6'";
        }
        if ($extra7 != _REALESTATE_MANAGER_LABEL_ALL && $extra7 != '') {
            $where[] = " LOWER(b.extra7)='$extra7'";
        }
        if ($extra8 != _REALESTATE_MANAGER_LABEL_ALL && $extra8 != '') {
            $where[] = " LOWER(b.extra8)='$extra8'";
        }
        if ($extra9 != _REALESTATE_MANAGER_LABEL_ALL && $extra9 != '') {
            $where[] = " LOWER(b.extra9)='$extra9'";
        }
        if ($extra10 != _REALESTATE_MANAGER_LABEL_ALL && $extra10 != '') {
            $where[] = " LOWER(b.extra10)='$extra10'";
        }
        $pricefrom = intval(protectInjectionWithoutQuote('pricefrom2', ''));
        $priceto = intval(protectInjectionWithoutQuote('priceto2', ''));
        if ($pricefrom > 0)
            $where[] = " CAST( b.price AS SIGNED) >= $pricefrom ";
        if ($priceto > 0)
            $where[] = " CAST( b.price AS SIGNED) <= $priceto ";

        if (isset($_REQUEST['ownername']) && $_REQUEST['ownername'] == "on")
            $ownername = "$exactly";

        if ($ownername != '' && $ownername != '%%'
          && !( $ownername == 'Guest' || $ownername == 'anonymous' || $ownername == _REALESTATE_MANAGER_LABEL_ANONYMOUS  )  ) {
            $query = "SELECT u.id FROM #__users AS u WHERE LOWER(u.id) LIKE '$ownername' OR LOWER(u.name) LIKE '$ownername';";
        $database->setQuery($query);
        if (version_compare(JVERSION, "3.0.0", "lt"))
            $owner_ids = $database->loadResultArray();
        else
            $owner_ids = $database->loadColumn();

        $ownername = "";
        if (count($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                if (isset($_REQUEST['ownername']) && $_REQUEST['ownername'] == "on") {
                        //search from frontend
                    if ($is_add_or)
                        $ownername .= " or ";
                    $is_add_or = true;
                    $ownername .= "b.owner_id='$owner_id'";
                } else {
                        //show owner houses
                    $where[] = "b.owner_id='$owner_id'";
                }
            }
        } else if (!$is_add_or) { 
            echo"<h1 style='text-align:center'>" . _REALESTATE_MANAGER_LABEL_SEARCH_NOTHING_FOUND . "</h1>";
            return;
        }
    } else if($ownername == 'Guest' || $ownername == 'anonymous' || $ownername == _REALESTATE_MANAGER_LABEL_ANONYMOUS ){
        if (isset($_REQUEST['ownername']) && $_REQUEST['ownername'] == "on") {
                //search from frontend
            if ($is_add_or)
                $ownername .= " or ";
            $is_add_or = true;
            $ownername .= "b.owner_id=''";
        } else {
                //show owner houses
            $where[] = "b.owner_id=''";
        }
    }

    $search_date_from = protectInjectionWithoutQuote('search_date_from', '');
    $search_date_from = addslashes(data_transform_rem($search_date_from, 'to'));
    $search_date_until = protectInjectionWithoutQuote('search_date_until', '');
    $search_date_until = addslashes(data_transform_rem($search_date_until, 'to'));

    if($realestatemanager_configuration['special_price']['show']){
        $sign = '=';      
    }else{
        $sign = '';
    }        

    if (isset($_REQUEST['search_date_from']) && (trim($_REQUEST['search_date_from']) ) &&
     trim($_REQUEST['search_date_until']) == "") {
        $RentSQL = "((fk_rentid = 0 OR b.id NOT IN (select dd.fk_houseid " .
    " from #__rem_rent AS dd where dd.rent_until >".$sign." ' " . $search_date_from .
    "' and dd.rent_from <= '" . $search_date_from . 
    "' and dd.fk_houseid=b.id and dd.rent_return is null)) AND (listing_type = \"1\"))";

            // print_r($RentSQL);
            // exit;

    if ($is_add_or)
        $RentSQL .= " AND ";
    $RentSQL_JOIN_1 = "\nLEFT JOIN #__rem_rent AS d ";
    $RentSQL_JOIN_2 = "\nON d.fk_houseid=b.id ";
}


if (isset($_REQUEST['search_date_until']) && (trim($_REQUEST['search_date_until']) )
   && trim($_REQUEST['search_date_from']) == "") {
    $RentSQL = "((fk_rentid = 0 OR b.id NOT IN (select dd.fk_houseid "
. "from #__rem_rent AS dd where dd.rent_from <".$sign." '" . $search_date_until . "' and dd.rent_until >= '"
. $search_date_until . "' and dd.fk_houseid=b.id and dd.rent_return is null)) AND (listing_type = \"1\"))";
if ($is_add_or)
    $RentSQL .= " AND ";
$RentSQL_JOIN_1 = "\nLEFT JOIN #__rem_rent AS d ";
$RentSQL_JOIN_2 = "\nON d.fk_houseid=b.id ";
}



if (isset($_REQUEST['search_date_until']) && (trim($_REQUEST['search_date_until']))
    && isset($_REQUEST['search_date_from']) && ( trim($_REQUEST['search_date_from']))) {
    $RentSQL = "((fk_rentid = 0 OR b.id NOT IN (select dd.fk_houseid from #__rem_rent AS dd
        where (dd.rent_until >".$sign." '" . $search_date_from . "' and dd.rent_from <".$sign." '" . $search_date_from . "') or " .
        " (dd.rent_from <".$sign." ' " . $search_date_until . "' and dd.rent_until >".$sign." '" . $search_date_until . "' ) or " .
        " (dd.rent_from >= '" . $search_date_from . "' and dd.rent_until <= '" . $search_date_until . "')  and dd.rent_return is null ) ) " .
        " AND (listing_type = \"1\"))";
        if ($is_add_or)
            $RentSQL .= " AND ";
        $RentSQL_JOIN_1 = "\nLEFT JOIN #__rem_rent AS d ";
        $RentSQL_JOIN_2 = "\nON d.fk_houseid=b.id ";
    }

    $RentSQL = $RentSQL . (($is_add_or) ? ( "( ( " . $Houseid . "  " . $Description .
        "  " . $Title . "  " . $Address .
        "  " . $Country . "  " . $Region . "  " . $City . "  " . $Zipcode . "  " . $Extra1 .
        "  " . $Extra2 . "  " . $Extra3 . "  " . $Extra4 . "  " . $Extra5 . "  " . $Rooms .
        "  " . $Bathrooms . "  " . $Bedrooms . "  " . $Contacts . "  " . $Agent .
        "  " . $House_size . " " . $Lot_size . " " . $Built_year . "  " . $ownername . "  ))") : (" "));

    if (trim($RentSQL) != "")
        array_push($where, $RentSQL);
        //select category, to which user has access
    $where[] = " ($s) ";
    $where[] = " c.published = '1' ";

        //select published and approved houses
    array_push($where, " b.published = '1' ");
    array_push($where, " b.approved = '1' ");

    if (isset($langContent)) {

        $lang = $langContent;
            // $query = "SELECT lang_code FROM #__languages WHERE sef = '$lang'";
            // $database->setQuery($query);
            // $lang = $database->loadResult();
        $where[] = " ( b.language = '$lang' or b.language like 'all' or b.language like '' "
        ." or b.language like '*' or b.language is null) ";
        $where[] = "  ( c.language = '$lang' or c.language like 'all' or c.language like '' "
        ." or c.language like '*' or c.language is null) ";
    }

    if ($catid)
        array_push($where, "c.id=" . intval($catid) . "");

    $query = "SELECT COUNT(DISTINCT b.id)
                    FROM #__rem_houses AS b
                    LEFT JOIN #__rem_categories AS hc ON b.id=hc.iditem
                    LEFT JOIN #__rem_main_categories AS c ON hc.idcat = c.id " .
                    $RentSQL_JOIN_1 . $RentSQL_JOIN_2 .
                    ((count($where) ? "\n WHERE " . implode(' AND ', $where) : ""));
                    $database->setQuery($query);
                    $total = $database->loadResult();
        $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6
        // getting all houses for this category
        $query = "SELECT distinct hc.idcat as idcat, b . * , c.title AS category_titel, c.ordering AS category_ordering, c.id as catid
                    FROM #__rem_houses AS b
                    LEFT JOIN #__rem_categories AS hc ON b.id=hc.iditem
                    LEFT JOIN #__rem_main_categories AS c ON hc.idcat = c.id " .
                    $RentSQL_JOIN_1 . $RentSQL_JOIN_2 .
                    ((count($where) ? "\n WHERE " . implode(' AND ', $where) : "")) .
                    " GROUP BY b.id ORDER BY $sort_string
                    \nLIMIT " . $pageNav->limitstart . "," . $pageNav->limit;
                    $database->setQuery($query);
                    $houses = $database->loadObjectList();

                    $currentcat = new stdClass();

        //parameters
                    if (version_compare(JVERSION, '3.0', 'ge')) {
                        $menu = new JTableMenu($database);
                        $menu->load($Itemid);
                        $params = new JRegistry;
                        $params->loadString($menu->params);
                    } else {
                        $menu = new mosMenu($database);
                        $menu->load($Itemid);
                        $params = new mosParameters($menu->params);
                    }

                    $menu_name = set_header_name_rem($menu, $Itemid);
                    $params->def('header', $menu_name);
                    $params->def('pageclass_sfx', '');
                    $params->def('category_name', _REALESTATE_MANAGER_LABEL_SEARCH);
                    $params->def('search_request', '1');
                    $params->def('hits', 1);
                    $params->def('show_rating', 1);
                    $params->def('sort_arr_order_direction', $sort_arr['order_direction']);
                    $params->def('sort_arr_order_field', $sort_arr['order_field']);
        // add wishlist markers ------------------------------------------
                    $query = "SELECT fk_houseid FROM `#__rem_users_wishlist` " . 
                    "WHERE fk_userid =" . $my->id;
                    $database->setQuery($query);
                    $result = $database->loadColumn();
                    $params->def('wishlist', $result);
        //-----------------------------------------------------------------
                    $database->setQuery("SELECT id FROM #__menu WHERE link='index.php?option=com_realestatemanager'");
                    if ($database->loadResult() != $Itemid)
                        $params->def('wrongitemid', '1');

                    if ($realestatemanager_configuration['rentstatus']['show']) {
                        $params->def('show_rentstatus', 1);
                        if (checkAccess_REM($realestatemanager_configuration['rentrequest']['registrationlevel'],
                           'NORECURSE', userGID_REM($my->id), $acl)) {
                            $params->def('show_rentrequest', 1);
                    }
                }
                if ($realestatemanager_configuration['housestatus']['show']) {
                    $params->def('show_housestatus', 1);
                    if (checkAccess_REM($realestatemanager_configuration['houserequest']['registrationlevel'],
                       'NORECURSE', userGID_REM($my->id), $acl)) {
                        $params->def('show_houserequest', 1);
                }
            }
            if ($realestatemanager_configuration['buystatus']['show']) {
                $params->def('show_buystatus', 1);
                if (checkAccess_REM($realestatemanager_configuration['buyrequest']['registrationlevel'],
                   'NORECURSE', userGID_REM($my->id), $acl)) {
                    $params->def('show_buyrequest', 1);
            }
        }

        //*****   begin add for Manager Add house: button 'Add a house'
        if ($realestatemanager_configuration['add_house']['show']) {
            $params->def('show_add_house', 1);
            if (checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_input_add_house', 1);
        }
    }
        //*********   end add for Manager Add house: button 'Add a house'   **

    if ($realestatemanager_configuration['price']['show']) {
        $params->def('show_pricestatus', 1);
        if (checkAccess_REM($realestatemanager_configuration['price']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl)) {
            $params->def('show_pricerequest', 1);
    }
}

if ($realestatemanager_configuration['cat_pic']['show'])
    $params->def('show_cat_pic', 1);
$params->def('back_button', $mainframe->getCfg('back_button'));
$currentcat->descrip = " ";
$currentcat->align = 'right';

        //page image
        //$currentcat->img = "./components/com_realestatemanager/images/rem_logo.png";
$currentcat->img = null;

        //$currentcat->header = $params->get( 'header' );
        //$currentcat->header = $currentcat->header .":". _REALESTATE_MANAGER_LABEL_SEARCH;
        //used to show table rows in alternating colours
$tabclass = array('sectiontableentry1', 'sectiontableentry2');
//         $params->def('rss_show', $realestatemanager_configuration['rss']['show']);
//          if ($realestatemanager_configuration['print_pdf']['show']) {
//             $params->def('show_print_pdf', 1);
//             if (checkAccess_REM($realestatemanager_configuration['print_pdf']['registrationlevel'],
//              'NORECURSE', userGID_REM($my->id), $acl)) {
//                 $params->def('show_input_print_pdf', 1);
//             }
//         }
//             if ($realestatemanager_configuration['print_view']['show']) {
//             $params->def('show_print_view', 1);
//             if (checkAccess_REM($realestatemanager_configuration['print_view']['registrationlevel'],
//              'NORECURSE', userGID_REM($my->id), $acl)) {
//                 $params->def('show_input_print_view', 1);
//             }
//         }
if ($realestatemanager_configuration['mail_to']['show']) {
    $params->def('show_mail_to', 1);
    if (checkAccess_REM($realestatemanager_configuration['mail_to']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_mail_to', 1);
}
}
if ($realestatemanager_configuration['add_house']['show']) {
    $params->def('show_add_house', 1);
    if (checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_add_house', 1);
}
}
if ($realestatemanager_configuration['search_option']['show']) {
    $params->def('search_option', 1);
    if (checkAccess_REM($realestatemanager_configuration['search_option']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('search_option_registrationlevel', 1);
}
}

        // wish list
if (($realestatemanager_configuration['wishlist']['show'])) {
    if (checkAccess_REM($realestatemanager_configuration['wishlist']['registrationlevel'],
       'RECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_add_to_wishlist', 1);
}
}        
        // show map for layout search_result list
if (($realestatemanager_configuration['searchlayout_map']['show'])) {
    if (checkAccess_REM($realestatemanager_configuration['searchlayout_map']['registrationlevel'],
       'RECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_searchlayout_map', 1);
}
}
        // show order by form for layout search_result list
if (($realestatemanager_configuration['searchlayout_orderby']['show'])) {
    if (checkAccess_REM($realestatemanager_configuration['searchlayout_orderby']['registrationlevel'],
       'RECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_searchlayout_orderby', 1);
}
}        

        // show search form 
if (($realestatemanager_configuration['searchlayout_form']['show'])) {
    if (checkAccess_REM($realestatemanager_configuration['searchlayout_form']['registrationlevel'],
       'RECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_searchlayout_form', 1);
}
}

$params->def('singleuser01', "{loadposition com_realestatemanager_single_user_house_01,xhtml}");
$params->def('singleuser02', "{loadposition com_realestatemanager_single_user_house_02,xhtml}");
$params->def('singleuser03', "{loadposition com_realestatemanager_single_user_house_03,xhtml}");
$params->def('singleuser04', "{loadposition com_realestatemanager_single_user_house_04,xhtml}");
$params->def('singleuser05', "{loadposition com_realestatemanager_single_user_house_05,xhtml}");
$params->def('notfound01', "{loadposition com_realestatemanager_nothing_found_house_01,xhtml}");
$params->def('notfound02', "{loadposition com_realestatemanager_nothing_found_house_02,xhtml}");
$params->def('view05', "{loadposition com_realestatemanager_view_house_05,xhtml}");
$params->def('ownerlist03', "{loadposition com_realestatemanager_owner_list_03,xhtml}");

if (isset($_REQUEST['searchLayout'])){
  $layout = $_REQUEST['searchLayout'];
} else {
  $layout = '';
}
if (isset($_REQUEST['typeLayout'])){
  $type = $_REQUEST['typeLayout'];
} else {
  $type = '';
}

if (count($houses)) {
    if (  $task == 'my_houses' || $task == 'show_my_houses' || $task == 'showmyhouses'  ) PHP_realestatemanager::showTabs();
        if ($task == 'search') {
         if( !isset($_REQUEST['searchLayout']) ) {

            $layout = $params->get('searchresultlayout');
            $layoutsearch = $params->get('showsearchhouselayout');
        }
        if (empty($layout)) $layout = 'default';
        if (empty($layoutsearch)) $layoutsearch = 'default';

        HTML_realestatemanager::displaySearchHouses($houses, $currentcat, $params, $tabclass, $catid, null, 
            $pageNav, false, $option, $layout, $layoutsearch);
    } else {
        HTML_realestatemanager::displayHouses($houses, $currentcat, $params, $tabclass, $catid, null, 
            $pageNav, false, $option, $layout, $type);
    }
} else {
  if ( $task == 'my_houses'
    || $task == 'show_my_houses' || $task == 'showmyhouses'  )
      PHP_realestatemanager::showTabs();

  positions_rem($params->get('notfound01'));
  $layoutsearch = $params->get('showsearchhouselayout', 'default');
  if ($params->get('show_searchlayout_form'))
    PHP_realestatemanager::showSearchHouses($option, $catid, $option, $layoutsearch);

print_r("<h1 style='text-align:center'>" . _REALESTATE_MANAGER_LABEL_SEARCH_NOTHING_FOUND .
 " </h1><br><br><div class='row-fluid'><div class='span9'></div></div>");
positions_rem($params->get('notfound02'));

            // "<div class='span3'><div class='rem_house_contacts'>
            // <div id='rem_house_titlebox'>" . _REALESTATE_MANAGER_SHOW_SEARCH . "</div> "
             // PHP_realestatemanager::showSearchHouses($option, $catid, $option, $layout);
             // print_r('</div></div></div>');
}
}

    /**
     * Compiles information to add or edit houses
     * @param integer bid The unique id of the record to edit (0 if new)
     * @param array option the current options
     */
    static function editHouse($option, $bid) {
        global $database, $my, $mosConfig_live_site, $realestatemanager_configuration, $Itemid, $acl, $mainframe;

        PHP_realestatemanager::addTitleAndMetaTags();

        $house = new mosRealEstateManager($database);

        // load the row from the db table
        $house->load(intval($bid));

        $numeric_houseids = Array();
        if (empty($house->houseid) &&
           $realestatemanager_configuration['houseid']['auto-increment']['boolean'] == 1) {
            $database->setQuery("select houseid from #__rem_houses order by houseid");
        $houseids = $database->loadObjectList();

        foreach ($houseids as $houseid) {
            if (is_numeric($houseid->houseid)) {
                $numeric_houseids[] = intval($houseid->houseid);
            }
        }

        if (count($numeric_houseids) > 0) {
            sort($numeric_houseids);
            $house->houseid = $numeric_houseids[count($numeric_houseids) - 1] + 1;  
        }
        else
            $house->houseid = 1;
    }
    $houseTMP = $house;

    $is_edit_all_houses = false ;
    if (checkAccess_REM($realestatemanager_configuration['option_edit']['registrationlevel'], 'RECURSE', userGID_REM($my->id), $acl)) {
        $is_edit_all_houses = true ;
    }

    if ($bid != 0 && $my->id != $house->owner_id && $is_edit_all_houses==false) {
        mosRedirect("index.php?option=$option");
        exit;
    }
    if ($bid == 0) {
        if (!$realestatemanager_configuration['add_house']['show'] || 
          !checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'], 
              'NORECURSE', userGID_REM($my->id), $acl)) {
            echo "<script> alert('" . _REALESTATE_MANAGER_ERROR_ACCESS_PAGE .
        "'); window.history.go(-1); </script>\n";
        exit();
    }

    $pathway = sefRelToAbs('index.php?option=' . $option .
       '&amp;task=show_add&amp;Itemid=' . $Itemid);
    $pathway_name = _REALESTATE_MANAGER_LABEL_TITLE_ADD_HOUSE;
} else {
    $pathway = sefRelToAbs('index.php?option=' . $option .
       '&amp;task=edit_house&amp;Itemid=' . $Itemid . '&amp;id=' . $bid);
    $pathway_name = _REALESTATE_MANAGER_LABEL_TITLE_EDIT_HOUSE;
}

$associateArray = array();
if($bid){
            //bch
    $call_from = 'frontend';
    $associateArray = edit_house_associate($house,$call_from);
}

$categories = array();
com_house_categoryTreeList(0, '', true, $categories);
if (count($categories) <= 1)
    mosRedirect("index.php?option=$option&section=categories",
       _REALESTATE_MANAGER_ADMIN_IMPEXP_ADD);
if (trim($house->id) != "")
    $house->setCatIds();
$maxsize = 5;
if (count($categories) > 6)
    $maxsize = 6;
$clist = mosHTML :: selectList($categories, 'catid[]', 'class="inputbox"
   multiple', 'value', 'text', ($house->catid));

        //get Rating
$retVal2 = mosRealEstateManagerOthers :: getRatingArray();
$rating = null;
for ($i = 0, $n = count($retVal2); $i < $n; $i++) {
    $help = $retVal2[$i];
    $rating[] = mosHTML :: makeOption($help[0], $help[1]);
}

        //delete ehouse?
$help = str_replace($mosConfig_live_site, "", $house->edok_link);
$delete_ehouse_yesno[] = mosHTML :: makeOption($help, _REALESTATE_MANAGER_YES);
$delete_ehouse_yesno[] = mosHTML :: makeOption('0', _REALESTATE_MANAGER_NO);
$delete_edoc = mosHTML :: RadioList($delete_ehouse_yesno, 'delete_edoc',
   'class="inputbox"', '0', 'value', 'text');

        // fail if checked out not by 'me'
if ($house->checked_out && $house->checked_out <> $my->id)
    mosRedirect("index2.php?option=$option", _REALESTATE_MANAGER_IS_EDITED);

if ($bid) {
    $house->checkout($my->id);
} else {
            // initialise new record
    $house->published = 0;
    $house->approved = 0;
}

        //Select list for listing type
$listing_type[] = mosHtml::makeOption(0, _REALESTATE_MANAGER_OPTION_SELECT);
$listing_type[] = mosHtml::makeOption(1, _REALESTATE_MANAGER_OPTION_FOR_RENT);
$listing_type[] = mosHtml::makeOption(2, _REALESTATE_MANAGER_OPTION_FOR_SALE);
$listing_type_list = mosHTML :: selectList($listing_type, 'listing_type',
   'class="inputbox" size="1"', 'value', 'text', $house->listing_type);

        //Select list for listing status
$listing_status[] = mosHtml::makeOption(0, _REALESTATE_MANAGER_OPTION_SELECT);
$listing_status1 = explode(',', _REALESTATE_MANAGER_OPTION_LISTING_STATUS);
$i = 1;
foreach ($listing_status1 as $listing_status2) {
    $listing_status[] = mosHtml::makeOption($i, $listing_status2);
    $i++;
}
$listing_status_list = mosHTML :: selectList($listing_status, 'listing_status',
   'class="inputbox" size="1"', 'value', 'text', $house->listing_status);

        //Select list for property type
$property_type[] = mosHtml::makeOption(0, _REALESTATE_MANAGER_OPTION_SELECT);
$property_type1 = explode(',', _REALESTATE_MANAGER_OPTION_PROPERTY_TYPE);
$i = 1;
foreach ($property_type1 as $property_type2) {
    $property_type[] = mosHtml::makeOption($i, $property_type2);
    $i++;
}
$property_type_list = mosHTML :: selectList($property_type, 'property_type',
   'class="inputbox" size="1"', 'value', 'text', $house->property_type);

if (trim($house->id) != "") {
    $query = "select * from #__rem_rent_sal WHERE fk_houseid='$house->id' order by `yearW`, `monthW`";
    $database->setQuery($query);
    $house_rent_sal = $database->loadObjectList();
}

if (trim($house->id) != "") {
    $query = "select main_img from #__rem_photos WHERE fk_houseid='$house->id' order by img_ordering,id";
    $database->setQuery($query);
    $house_temp_photos = $database->loadObjectList();
    foreach ($house_temp_photos as $house_temp_photo) {
        $house_photos[] = array($house_temp_photo->main_img, 
            rem_picture_thumbnail($house_temp_photo->main_img, '150', '150'));
    }
    $query = "select image_link from #__rem_houses WHERE id='$house->id'";
    $database->setQuery($query);
    $house_photo = $database->loadResult();

    if ($house_photo != '')
        $house_photo = array($house_photo, rem_picture_thumbnail($house_photo, '150', '150'));
}
if (trim($house->id) != "") {
    $query = "select * from #__rem_rent_sal WHERE fk_houseid='$house->id' order by `yearW`, `monthW`";
    $database->setQuery($query);
    $house_rent_sal = $database->loadObjectList();
}
///////////START check video/audio files\\\\\\\\\\\\\\\\\\\\\\
$tracks = array();
$videos = array();
$youtubeId = "";
if (!empty($house->id)) { 
  $database->setQuery("SELECT * FROM #__rem_video_source WHERE fk_house_id=" . $house->id);
  $videos = $database->loadObjectList();
}
$youtube = new stdClass();
for ($i = 0;$i < count($videos);$i++) {
  if (!empty($videos[$i]->youtube)) {
    $youtube->code = $videos[$i]->youtube;
    $youtube->id = $videos[$i]->id;
    break;
}
}
        if (!empty($house->id)) { //check video file
          $database->setQuery("SELECT * FROM #__rem_track_source WHERE fk_house_id=" . $house->id);
          $tracks = $database->loadObjectList();
      }
////////////////////////////////END check video/audio files \\\\\\\\\\\\\\\\\\
      $query = "SELECT * ";
      $query .= "FROM #__rem_feature as f ";
      $query .= "WHERE f.published = 1 ";
      $query .= "ORDER BY f.categories";
      $database->setQuery($query);
      $house_feature = $database->loadObjectList();

      for ($i = 0; $i < count($house_feature); $i++) {

        $feature = "";
        if (!empty($house->id)) {
            $query = "SELECT COUNT(id) ";
            $query .= "FROM #__rem_feature_houses ";
            $query .= "WHERE fk_featureid =" . $house_feature[$i]->id . " AND fk_houseid =" . $house->id;
            $database->setQuery($query);

            $feature = $database->loadResult();

            if ($feature == 1)
                $house_feature[$i]->check = 1; else
            $house_feature[$i]->check = 0;
        } else {
            $house_feature[$i]->check = 0;
        }
    }

    $currencys = explode(';', $realestatemanager_configuration['currency']);
    foreach ($currencys as $row) {
        if ($row != '') {
            $row = explode("=", $row);
               // $currency[] = mosHTML::makeOption($row[0], $row[0]);
            $temp_currency[] = mosHTML::makeOption($row[0], $row[0]);
        }
    }
    $currency = mosHTML :: selectList($temp_currency, 'priceunit', 'class="inputbox" size="1"',
       'value', 'text', $house->priceunit);
    $currency_spacial_price = mosHTML :: selectList($temp_currency, 'currency_spacial_price',
       'class="inputbox" size="1"', 'value', 'text', $house->priceunit);
    $query = "SELECT lang_code, title FROM #__languages";
    $database->setQuery($query);
    $languages = $database->loadObjectList();

    $languages_row[] = mosHTML::makeOption('*', 'All');
    foreach ($languages as $language) {
        $languages_row[] = mosHTML::makeOption($language->lang_code, $language->title);
    }
    $languages = mosHTML :: selectList($languages_row, 'language',
       'class="inputbox" size="1"', 'value', 'text', $house->language);

    for ($i = 6; $i <= 10; $i++) {
        $name = "_REALESTATE_MANAGER_EXTRA" . $i . "_SELECTLIST";
        $extra = explode(',', constant($name));
        $extraOption = '';
        $extraOption[] = mosHtml::makeOption(0, _REALESTATE_MANAGER_OPTION_SELECT);
        foreach ($extra as $key =>$extr) {
            $extraOption[] = mosHTML::makeOption($key+1, $extr);
        }

        switch ($i) {
            case 6:
            $extraSelect = $house->extra6;
            break;
            case 7:
            $extraSelect = $house->extra7;
            break;
            case 8:
            $extraSelect = $house->extra8;
            break;
            case 9:
            $extraSelect = $house->extra9;
            break;
            case 10:
            $extraSelect = $house->extra10;
            break;
        }
        $extra_list[] = mosHTML :: selectList($extraOption, 'extra' . $i,
           'class="inputbox" size="1"', 'value', 'text', $extraSelect);
    }

        // if ($my->id == $houseTMP->id)
        //     PHP_realestatemanager::showTabs();

    HTML_realestatemanager :: editHouse($option, $house, $clist, $ratinglist,
       $delete_edoc,$videos,$youtube, $tracks,  $listing_status_list, $property_type_list, $listing_type_list,
       $house_photo, $house_temp_photos, $house_photos, $house_rent_sal, $house_feature, $currency,
       $languages, $extra_list, $currency_spacial_price, $associateArray);
}


static function ajax_rent_calcualete($bid,$rent_from,$rent_until,$week){ 


    global $realestatemanager_configuration;
    
    $database = JFactory::getDBO();    
    
    $resulArr = calculatePriceREM ($bid,$rent_from,$rent_until,$realestatemanager_configuration,$database,$week);
    
    echo $resulArr[0].' '.$resulArr[1];
    exit; 
}

static function ajax_update_check_payment(){
    if (isset($_POST['order_id']))
    {
        $order_id = $_POST['order_id'];
    }
    $database = JFactory::getDBO();    
    $query = "UPDATE `val_rem_orders` SET `status` = 'En attente du chèque' WHERE `val_rem_orders`.`id` =" . $order_id;
    $database->setQuery($query);
    $database->query();   

    $query = "UPDATE `val_rem_orders_details` SET `status` = 'En attente du chèque' WHERE `val_rem_orders`.`fk_order_id` =" . $order_id;
    val_rem_orders_details
    echo "Nous validerons votre réservation lors de la récéption de votre chèque.";  
}

static function saveHouse($option, $id) {
    global $database, $menu, $Itemid, $mainframe, $my, $mosConfig_absolute_path,
    $mosConfig_live_site, $realestatemanager_configuration, $params, $catid,
    $currentcat, $acl;
    global $mosConfig_mailfrom, $session;

        ///////////////
    if (!$realestatemanager_configuration['add_house']['show']
        || !checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'], 
            'RECURSE', userGID_REM($my->id), $acl)) {
        mosRedirect('index.php?option=com_realestatemanager&Itemid=' . $Itemid);
    exit;
}

        //check how the other info should be provided
$house = new mosRealEstateManager($database);
$post = JRequest::get('post', JREQUEST_ALLOWHTML);

if (!$house->bind($post)) {
    echo "<script> alert('" . $house->getError() . "'); window.history.go(-1); </script>\n";
    exit();
}

if ((strlen($house->owneremail) > 0) && ($house->owner_id == 0))
    $house->owner_id = $my->id;

/*************Call function to Save changes for associated houses*************************/
save_house_associate();
/*****************************************************************************************/

        // $house->save();
        //save of the main image

if (isset($_POST['yearW']) || isset($_POST['monthW'])) {
    $id = $_POST['id'];
    $monthW = $_POST['monthW'];
    $yearW = $_POST['yearW'];
    $week = $_POST['week'];
    $midweek = $_POST['midweek'];
    $weekend = $_POST['weekend'];
    for ($i = 0; $i < count($_POST['yearW']); $i++) {
                //if (($week[$i]!='') and ($weekend[$i]!='') and ($midweek[$i]!='')) {
        $database->setQuery("INSERT INTO #__rem_rent_sal (fk_houseid, monthW, yearW, week, weekend, midweek) "
          ."VALUES (" . $id . ", " . $monthW[$i] . ", " . $yearW[$i] . ", '" .
          $week[$i] . "', '" . $weekend[$i] . "', '" 
          . $midweek[$i] . "')");
        $database->query();
                //}
    }
        } //end if

        if (isset($_POST['edok_link']))
            $house->edok_link = protectInjectionWithoutQuote('edok_link', '');

        //delete ehouse file if neccesary
        $delete_edoc = mosGetParam($_POST, 'delete_edoc', 0);
        if ($delete_edoc != '0') {
            $retVal = @unlink($mosConfig_absolute_path . $delete_edoc);
            $house->edok_link = "";
        }

        //storing e-house
        if (isset($_FILES['edoc_file'])){
          $edfile = $_FILES['edoc_file'];
          $uid = md5(uniqid(rand(), 1));
          $edfile['name'] = $uid . $edfile['name'];
          $newpath = JPATH_COMPONENT . '/edocs/' . $edfile['name'];

            //check if fileupload is correct
          if ($realestatemanager_configuration['edocs']['allow']
              && intval($edfile['error']) > 0 && intval($edfile['error']) < 4) {
              echo "<script> alert('" . _REALESTATE_MANAGER_LABEL_EDOCUMENT_UPLOAD_ERROR .
          "'); window.history.go(-1); </script>\n";
          exit();
      } else if ($realestatemanager_configuration['edocs']['allow'] && intval($edfile['error']) != 4) {
          $uploaddir = $mosConfig_absolute_path .
          $realestatemanager_configuration['edocs']['location'];
          $file_new = $uploaddir . $uid . $_FILES['edoc_file']['name'];
              ///
          $ext = pathinfo($_FILES['edoc_file']['name'], PATHINFO_EXTENSION);
          $ext = strtolower($ext);
          $allowed_exts = explode(",", $realestatemanager_configuration['allowed_exts']);
          foreach ($allowed_exts as $key => $allowed_ext) {
              $allowed_exts[$key] = strtolower($allowed_ext);
          }

          $file['type'] = $_FILES['edoc_file']['type'];
          $db = JFactory::getDbo();
          $db->setQuery("SELECT mime_type FROM #__rem_mime_types WHERE `mime_ext` = " . $db->quote($ext) . 
            " and mime_type = " . $db->quote($file['type']));
          $file_db_mime = $db->loadResult();
          if ($file_db_mime != $file['type']) {
              echo "<script> alert(' ". _REALESTATE_MANAGER_FILE_MIME_TYPE_NOT_MATCH . " - " . 
              $_FILES['edoc_file']['name'] . "'); window.history.go(-1); </script>\n";
              exit();
          }

          if (!copy($edfile['tmp_name'], $file_new)) {
              echo "<script> alert('error: not copy'); window.history.go(-1); </script>\n";
              exit();
          } else {
              $house->edok_link = $mosConfig_live_site .
              $realestatemanager_configuration['edocs']['location'] . $edfile['name'];
          }
      }
  }

  if (is_string($house)) {
    echo "<script> alert('" . $house . "'); window.history.go(-1); </script>\n";
    exit();
}

$house->date = date("Y-m-d H:i:s");
if (!$house->check()) {
    echo "<script> alert('" . $house->getError() . "'); window.history.go(-1); </script>\n";
    exit();
}

        //************approve on add begin
if ($realestatemanager_configuration['approve_on_add']['show']) {
    if (checkAccess_REM($realestatemanager_configuration['approve_on_add']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
       $house->approved = 1;
}
} 
        //************approve on add begin
if ($realestatemanager_configuration['publish_on_add']['show']) {
    if (checkAccess_REM($realestatemanager_configuration['publish_on_add']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
       $house->published = 1;
}
}         

/********************************** if count car group > count car user set status unpulish***************************/

$count_house_single_all = getCountHouseForSingleUserREM($my, $database, $realestatemanager_configuration);
$count_house_single_user = $count_house_single_all[0];       
$count_house_for_single_group = $count_house_single_all[1];    

if($count_house_single_user >= $count_house_for_single_group){
    $house->published = 0; 
}

/**********************************************************************************************************************/


$house->checked_out = 0;


if (!$house->store()) {
    echo "<script> alert('" . $house->getError() . "'); window.history.go(-1); </script>\n";
    exit();
}

$uploaddir = $mosConfig_absolute_path . '/components/com_realestatemanager/photos/';
$code = guid();
if ($_FILES['image_link']['name'] != '') {
            ///
    $ext = pathinfo($_FILES['image_link']['name'], PATHINFO_EXTENSION);
    $ext = strtolower($ext);
    $allowed_exts = explode(",", $realestatemanager_configuration['allowed_exts_img']);
    foreach ($allowed_exts as $key => $allowed_ext) {
        $allowed_exts[$key] = strtolower($allowed_ext);
    }

    $file['type'] = $_FILES['image_link']['type'];
    $db = JFactory::getDbo();
    $db->setQuery("SELECT mime_type FROM #__rem_mime_types WHERE `mime_ext` = " .
       $db->quote($ext) . " and mime_type = " . $db->quote($file['type']));
    $file_db_mime = $db->loadResult();
    if ($file_db_mime != $file['type']) {
        echo "<script> alert(' " . _REALESTATE_MANAGER_FILE_MIME_TYPE_NOT_MATCH . ". - " .
        $_FILES['image_link']['name'] . "'); window.history.go(-1); </script>\n";
        exit();
    }
            ///
    $uploadfile = $uploaddir . $code . "_" . $_FILES['image_link']['name'];
    $file_name = $code . "_" . $_FILES['image_link']['name'];
    if (copy($_FILES['image_link']['tmp_name'], $uploadfile)) {
     $database->setQuery("UPDATE #__rem_houses SET image_link='$file_name' WHERE id=" . $house->id);
     if (!$database->query())
        echo "<script> alert('" . $database->getErrorMsg() . "');</script>\n";
}
        } //end if

        $house->saveCatIds($house->catid);
        $house->checkin();

        
        
        /********************************** if count photo group > count photo user not published*****************/
        $count_foto_for_single_group = '';
        $user_group = userGID_REM($my->id);       
        $user_group_mas = explode(',', $user_group);
        $max_count_foto = 0;
        foreach ($user_group_mas as $value) {            
            $count_foto_for_single_group = $realestatemanager_configuration['user_manager_rem'][$value]['count_foto'];
            if($count_foto_for_single_group>$max_count_foto){
                $max_count_foto = $count_foto_for_single_group;
            }            
        }
        $count_foto_for_single_group = $max_count_foto;
        $query = "select main_img from #__rem_photos WHERE fk_houseid='$house->id' order by img_ordering,id";
        $database->setQuery($query);
        $house_temp_photos = $database->loadObjectList();
        if(count($house_temp_photos) != 0)
        {
            $count_foto_for_single_group = $count_foto_for_single_group - count($house_temp_photos);
        }
        /*********************************************************************************************************/
        //save  photos
        $uploaddir = $mosConfig_absolute_path . '/components/com_realestatemanager/photos/';
        if (array_key_exists("new_photo_file", $_FILES)) {
            for ($i = 0; $i < $count_foto_for_single_group; $i++) {
                if (!empty($_FILES['new_photo_file']['name'][$i])) {
                    $code = guid();
                    ////
                    $ext = pathinfo($_FILES['new_photo_file']['name'][$i], PATHINFO_EXTENSION);
                    $ext = strtolower($ext);
                    $allowed_exts = explode(",", $realestatemanager_configuration['allowed_exts_img']);
                    foreach ($allowed_exts as $key => $allowed_ext) {
                        $allowed_exts[$key] = strtolower($allowed_ext);
                    }
                    if (!in_array($ext, $allowed_exts)) {
                        echo "<script> alert(' File ext. not allowed to upload! - " .
                        $_FILES['new_photo_file']['name'][$i] . "'); window.history.go(-1); </script>\n";
                        exit();
                    }
                    $file['type'] = $_FILES['new_photo_file']['type'][$i];
                    $db = JFactory::getDbo();
                    $db->setQuery("SELECT mime_type FROM #__rem_mime_types WHERE `mime_ext` = " .
                       $db->quote($ext) . " and mime_type = " . $db->quote($file['type']));
                    $file_db_mime = $db->loadResult();
                    if ($file_db_mime != $file['type']) {
                        echo "<script> alert(' " . _REALESTATE_MANAGER_FILE_MIME_TYPE_NOT_MATCH . " - " .
                        $_FILES['new_photo_file']['name'][$i] . "'); window.history.go(-1); </script>\n";
                        exit();
                    }
                    ////
                    $uploadfile = $uploaddir . $code . "_" . $_FILES['new_photo_file']['name'][$i];
                    if (copy($_FILES['new_photo_file']['tmp_name'][$i], $uploadfile)) {
                        $file_name = $code . "_" . $_FILES['new_photo_file']['name'][$i];
                        $database->setQuery("INSERT INTO #__rem_photos (fk_houseid,main_img) VALUES ('$house->id','$file_name')");
                        if (!$database->query()) {
                            echo "<script> alert('" . $database->getErrorMsg() . "');</script>\n";
                            $mini_file_name = rem_picture_thumbnail($file_name, 1);
                        }
                    }
                }
            } //for
        } //end if
        //ordering_photo
        if(JRequest::getVar('rem_img_ordering')){
            $ordering = JRequest::getVar('rem_img_ordering');
            $ordering = explode(',', $ordering);
            foreach ($ordering as $key => $value) {
                $query = "UPDATE #__rem_photos SET img_ordering = $key WHERE main_img='".$value."'";
                $database->setQuery($query);
                $database->query();
            }
        }
  /////////////save video/tracks functions\\\\\\\\\\\\\\\\\\\\\\
        storeVideo($house);
        storeTrack($house);
  /////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
        //check the files marked for deletion
        if (array_key_exists("del_main_photo", $_POST)) {
            $del_main_photo = $_POST['del_main_photo'];
            if ($del_main_photo != '') {

                $database->setQuery("select image_link  FROM  #__rem_houses where  houseid ="
                    . $house->houseid . "");
                $image_link = $database->loadObjectList();

                $house->image_link = '';

                unlink($mosConfig_absolute_path . '/components/com_realestatemanager/photos/'
                   . $image_link[0]->image_link);

                //separation of the file name in the name and extension
                $del_main_phot = pathinfo($image_link[0]->image_link);
                $del_main_photo_type = '.' . $del_main_phot['extension'];
                $del_main_photo_name = basename($image_link[0]->image_link, $del_main_photo_type);

                $path = $mosConfig_absolute_path . '/components/com_realestatemanager/photos/';
                $check_files = JFolder::files($path, '^' . $del_main_photo_name . '.*$', false, true);
                foreach ($check_files as $check_file) {
                    unlink($check_file);
                }
            }

            //Update DB

            $database->setQuery("UPDATE #__rem_houses SET image_link='' WHERE id=" . $house->id);
            if (!$database->query())
                echo "<script> alert('" . $database->getErrorMsg() . "');</script>\n";
        } //end if

        if (isset($_POST['del_photos']) && (count($_POST['del_photos']) != 0)) {
            foreach ($_POST['del_photos'] as $del_photo) {
                $database->setQuery("DELETE FROM #__rem_photos WHERE main_img='$del_photo'");
                if ($database->query()) {
                    unlink($mosConfig_absolute_path . '/components/com_realestatemanager/photos/' . $del_photo);
                } else {
                    echo '<script>alert("Can\'t delete");window.history.go(-1);</script>';
                }
            }
        }
        $house->checkin();

        if (isset($_POST['del_rent_sal'])) {
            for ($i = 0; $i < count($_POST['del_rent_sal']); $i++) {
                $del_rent_sal = $_POST['del_rent_sal'][$i];
                $database->setQuery("DELETE FROM #__rem_rent_sal WHERE id ='$del_rent_sal'");
                $database->query();
            }
        }

        if(!empty($_POST['feature'])) {	
            $feature = $_POST['feature']; 
            $database->setQuery("DELETE FROM #__rem_feature_houses WHERE fk_houseid = ".$house->id );
            $database->query();

            for ($i=0;$i<count($feature);$i++) {
                $database->setQuery("INSERT INTO #__rem_feature_houses (fk_houseid, fk_featureid) VALUES ("
                    .$house->id . ", " . $feature[$i] . ")");
                $database->query();
            }
        } else {
            $database->setQuery("DELETE FROM #__rem_feature_houses WHERE fk_houseid = " . $house->id);
            $database->query();
        }

        deleteVideos($house->id);
        deleteTracks($house->id);
        
        // Parameters
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $menu = new JTableMenu($database);
            $menu->load($Itemid);
            $params = new JRegistry;
            $params->loadString($menu->params);
        } else {
            $menu = new mosMenu($database);
            $menu->load($Itemid);
            $params = new mosParameters($menu->params);
        }

        //$app = JFactory::getApplication();
        //$menu1 = $app->getMenu();

        $menu_name = set_header_name_rem($menu, $Itemid);
        //if ($menu1->getItem($Itemid)) $menu_name = $menu1->getItem($Itemid)->title; else $menu_name = '';

        $params->def('header', $menu_name);
        $params->def('pageclass_sfx', '');
        $params->def('back_button', $mainframe->getCfg('back_button'));

        $currentcat = new stdClass();
        $currentcat->descrip = _REALESTATE_MANAGER_LABEL_REAL_ESTATE_THANKS;

        // page image
        $currentcat->img = "./components/com_realestatemanager/images/rem_logo.png";

        $currentcat->header = $params->get('header');


        if ($realestatemanager_configuration['add_email']['show'] &&
            $realestatemanager_configuration['add_email']['address'] != "") {
            $params->def('show_email', 1);
        if (checkAccess_REM($realestatemanager_configuration['add_email']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl)) {
            $params->def('show_input_email', 1);
    }
}

if ($params->get('show_input_email')) {
    $mail_to = explode(",", $realestatemanager_configuration['add_email']['address']);
    $userid = $my->id;
            //select user (added rent request)
    $select = "SELECT name, email FROM #__users WHERE id=" . $userid . ";";
    $database->setQuery($select);
    $item_user = $database->loadObjectList();
    echo $database->getErrorMsg();

    $select = "SELECT a.*, cc.name AS category " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_categories as hc on hc.iditem = a.id" .
    "\nLEFT JOIN #__rem_main_categories AS cc ON cc.id = hc.idcat" .
    "\nWHERE a.id = " . $house->id . "";


    $database->setQuery($select);
    $item_house = $database->loadObjectList();
    echo $database->getErrorMsg();
    $houseid = _REALESTATE_MANAGER_LABEL_PROPERTYID;
            //for ($i = 0;$i < count($mail_to);$i++){

    $username = ($userid == 0) ? $item_user[0]->name : _REALESTATE_MANAGER_LABEL_ANONYMOUS;
    $message = str_replace("{username}", $username, _REALESTATE_MANAGER_EMAIL_NOTIFICATION_ADD_HOUSE);
    $message = str_replace("{title}", $item_house[0]->htitle, $message);
    $message = str_replace("{id}", $item_house[0]->houseid, $message);
    $message = str_replace("{date}", $item_house[0]->date, $message);
    $message = str_replace("{category}", $item_house[0]->category, $message);

    mosMail($mosConfig_mailfrom, _REALESTATE_MANAGER_LABEL_ANONYMOUS, $mail_to, _REALESTATE_MANAGER_NEW_HOUSE_ADDED,
       $message, true);

            //}
}
        //********************   end add send mail for admin   ****************
$backlink = JRoute::_($_SERVER['HTTP_REFERER']);
HTML_realestatemanager :: showRentRequestThanks($params, $backlink, $currentcat);
}

static function secretImage() {
    $session = JFactory::getSession();
    $pas = $session->get('captcha_keystring', 'default');
    $new_img = new PWImageRealestate();
    $new_img->set_show_string($pas);
    $new_img->get_show_image(2.2, array(mt_rand(0, 50), mt_rand(0, 50), mt_rand(0, 50)), array(mt_rand(200, 255),
        mt_rand(200, 255), mt_rand(200, 255)));
    exit;
}

function checkAccess_REM($accessgroupid, $recurse, $usersgroupid, $acl) {
    $usersgroupid = explode(',', $usersgroupid);

        //parse usergroups
    $tempArr = array();
    $tempArr = explode(',', $accessgroupid);

    for ($i = 0; $i < count($tempArr); $i++) {
        if (($tempArr[$i] == $usersgroupid OR in_array($tempArr[$i], $usersgroupid)) || $tempArr[$i] == -2) {
                //allow access
            return true;
        } else {
            if ($recurse == 'RECURSE') {
                if (is_array($usersgroupid)) {
                    for ($j = 0; $j < count($usersgroupid); $j++) {
                        if (in_array($usersgroupid[$j], $tempArr))
                            return 1;
                    }
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

    static function showTabs() {
        global $mosConfig_live_site, $realestatemanager_configuration, $database, $Itemid, $my, $option;
        $acl = JFactory::getACL();
        $doc = JFactory::getDocument();
        $doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/realestatemanager.css');

        $menu = new mosMenu($database);
        $menu->load($Itemid);
        $params = new mosParameters($menu->params);


        if ($option == "com_comprofiler") {
         return;
     }

     $userid = $my->id;
     $query = "SELECT u.id, u.name AS username FROM #__users AS u WHERE u.id = " . $userid;
     $database->setQuery($query);
     $ownerslist = $database->loadObjectList();
     foreach ($ownerslist as $owner) {
        $username = $owner->username;
    }

    $query = "SELECT h.owner_id FROM #__rem_houses AS h" .
    " INNER JOIN #__rem_rent_request AS r ON h.id=r.fk_houseid " .
    " WHERE h.owner_id = '" . $my->id . "' AND r.status=0";
    $database->setQuery($query);
    $ownerrenthouse = $database->loadObjectList();
    foreach ($ownerrenthouse as $owner) {
        $rent_owner_id = $owner->owner_id;
        break;
    }

    $query = "SELECT h.owner_id  FROM #__rem_houses AS h" .
    " INNER JOIN  #__rem_buying_request AS br ON h.id=br.fk_houseid" .
    " WHERE h.owner_id = '" . $my->id . "'";
    $database->setQuery($query);
    $ownerbuyhouse = $database->loadObjectList();
    foreach ($ownerbuyhouse as $owner) {
        $buy_owner_id = $owner->owner_id;
        break;
    }

    $query = "SELECT * FROM #__rem_rent AS r WHERE r.fk_userid = " . $my->id;
    $database->setQuery($query);
    $current_user_rent_history_array = $database->loadObjectList();
    $check_for_show_rent_history = 0;
    if (isset($current_user_rent_history_array)) {
        foreach ($current_user_rent_history_array as $temp) {
            if ($temp->fk_userid == $my->id)
                $check_for_show_rent_history = 1;
        }
    }

    if ($realestatemanager_configuration['cb_edit']['show']) {
        $params->def('show_edit', 1);
        $i = checkAccess_REM($realestatemanager_configuration['cb_edit']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl);
        if ($i)
            $params->def('show_edit_registrationlevel', 1);
    }

    if (isset($rent_owner_id) && $my->id == $rent_owner_id) {
        if (($realestatemanager_configuration['cb_rent']['show'])) {
            $params->def('show_rent', 1);
            $i = checkAccess_REM($realestatemanager_configuration['cb_rent']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i)
                $params->def('show_rent_registrationlevel', 1);
        }
    }

    if (isset($buy_owner_id) && $my->id == $buy_owner_id) {
        if (($realestatemanager_configuration['cb_buy']['show'])) {
            $params->def('show_buy', 1);
            $i = checkAccess_REM($realestatemanager_configuration['cb_buy']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i)
                $params->def('show_buy_registrationlevel', 1);
        }
    }

    if ($check_for_show_rent_history != 0) {
        if (($realestatemanager_configuration['cb_history']['show'])) {
            $params->def('show_history', 1);
            $i = checkAccess_REM($realestatemanager_configuration['cb_history']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i)
                $params->def('show_history_registrationlevel', 1);
        }
    }

    HTML_realestatemanager::showTabs($params, $userid, $username, $comprofiler, $option);
}

static function editMyHouses($option) {
    global $database, $Itemid, $mainframe, $my, $realestatemanager_configuration, $acl;

    PHP_realestatemanager::addTitleAndMetaTags();


    $is_edit_all_houses = false ;
    if (checkAccess_REM($realestatemanager_configuration['option_edit']['registrationlevel'], 'RECURSE', userGID_REM($my->id), $acl)) {
        $is_edit_all_houses = true ;
    }

    $menu = new JTableMenu($database);

    $menu->load($Itemid);
    $params = new mosParameters($menu->params);

    $limit = $realestatemanager_configuration['page']['items'];
    $limitstart = protectInjectionWithoutQuote('limitstart', 0);

    $menu_name = set_header_name_rem($menu, $Itemid);
    $params->def('header', $menu_name);

        //check user
    if ($my->email == null) {
        mosRedirect("index.php", "Please login");
        exit;
    }



    if( !$is_edit_all_houses  ) $who_edit = " owner_id='$my->id' ";
    else $who_edit = " ";

    $database->setQuery("SELECT COUNT(id) FROM `#__rem_houses` " . 
        ($who_edit == " " ? "" : " WHERE $who_edit"));
    $total = $database->loadResult();
        $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6
        
        //getting my cars
        $selectstring = "SELECT a.*, GROUP_CONCAT(cc.title SEPARATOR ', ') AS category,
        l.id as rentid, l.rent_from as rent_from, l.rent_return as rent_return,
        l.rent_until as rent_until, u.name AS editor" .
        "\nFROM #__rem_houses AS a" .
        "\nLEFT JOIN #__rem_categories AS hc ON hc.iditem = a.id" .
        "\nLEFT JOIN #__categories AS cc ON cc.id = hc.idcat" .
        "\nLEFT JOIN #__rem_rent AS l ON l.fk_houseid = a.id  and l.rent_return is null " .
        "\nLEFT JOIN #__users AS u ON u.id = a.checked_out" .
        ($who_edit == " " ? "" : " WHERE $who_edit") .
                // "\nWHERE owner_id='" . $my->id . "' " .
        "\nGROUP BY a.id" .
        "\nORDER BY a.htitle " .
        "\nLIMIT " . $pageNav->limitstart . "," . $pageNav->limit . ";";
        $database->setQuery($selectstring);
        $houses = $database->loadObjectList();

        $rows = $database->loadObjectList();
        $date = date(time());
        foreach ($houses as $row) {
            $check = strtotime($row->checked_out_time);
            $remain = 7200 - ($date - $check);
            if (($remain <= 0) && ($row->checked_out != 0)) {
                $database->setQuery("UPDATE #__rem_houses SET checked_out=0,checked_out_time=0");
                $database->query();
            }
        }

        /****************************add block filter**********************************/
        // $usermenu[] = mosHTML::makeOption('0', _REALESTATE_MANAGER_LABEL_SELECT_ALL_USERS);
        // $selectstring = "SELECT usr.id,jei.owner_id, usr.username 
        //                  FROM  #__rem_houses AS jei  \n
        //                  LEFT JOIN #__users AS usr ON usr.id = jei.owner_id" . " 
        //                  GROUP BY usr.id  " . "\n
        //                  ORDER BY usr.username ";
        // $database->setQuery($selectstring);
        // $users_list = $database->loadObjectList();
        // $useranonimus = new stdClass();
        // $useranonimus->username = 'anonymous';
        // $useranonimus->owner_id = 'anonymous';
        // if (count($users_list) >=1) {
        //     $users_list[] = $useranonimus;
        // }
        // if ($database->getErrorNum()) {
        //     echo $database->stderr();
        //     return false;
        // }
        // foreach($users_list as $item) {
        //     if ($item->owner_id == 0 and $item->username == '') continue;
        //     $usermenu[] = mosHTML::makeOption($item->owner_id, $item->username);
        // }
        // // print_r($usermenu);exit;
        // $userlist = mosHTML::selectList($usermenu, 'select_owner_id', 'class="inputbox" size="1" 
        // onchange="bl_buttonClickSelectOwnerId();"', 'value', 'text', $select_owner_id);

        /****************************end block filter**********************************/    


        $params->def('my01', "{loadposition com_realestatemanager_my_house_01,xhtml}");
        $params->def('my02', "{loadposition com_realestatemanager_my_house_02,xhtml}");
        $params->def('my03', "{loadposition com_realestatemanager_my_house_03,xhtml}");
        $params->def('my04', "{loadposition com_realestatemanager_my_house_04,xhtml}");
        $params->def('my05', "{loadposition com_realestatemanager_my_house_05,xhtml}");

        HTML_realestatemanager::showMyHouses($houses, $params, $pageNav, $option);
    }

    static function deleteHouse() {
        global $database, $my, $option, $Itemid, $mosConfig_absolute_path;
        $do = protectInjectionWithoutQuote('task');
        $bid = mosGetParam($_REQUEST, 'bid');

        //get real user houses id
        if (count($bid)) {
            $database->setQuery("SELECT id FROM #__rem_houses WHERE owner_id='" . $my->id
               . "' AND id IN (" . implode(', ', $bid) . ")");
            if (version_compare(JVERSION, "3.0.0", "lt"))
                $bid = $database->loadResultArray();
            else
                $bid = $database->loadColumn();
            if (count($bid)) {
                $bids = implode(',', $bid);
                foreach ($bid as $h_id) {
                  $sql = "SELECT src FROM #__rem_video_source WHERE fk_house_id =". $h_id;
                  $database->setQuery($sql);
                  $videos = $database->loadColumn();
                  if ($videos) {
                    foreach($videos as $name) {
                      if (substr($name, 0, 4) != "http" && file_exists($mosConfig_absolute_path . $name)) 
                        unlink($mosConfig_absolute_path . $name);
                }
            }
                  $sql = "DELETE FROM #__rem_video_source 
                  WHERE (fk_house_id = $h_id)";
                  $database->setQuery($sql);
                  $database->query();

                  $sql = "SELECT src FROM #__rem_track_source WHERE fk_house_id =". $h_id;
                  $database->setQuery($sql);
                  $track = $database->loadColumn();
                  if ($track) {
                    foreach($track as $name) {
                      if (substr($name, 0, 4) != "http" && file_exists($mosConfig_absolute_path . $name)) 
                        unlink($mosConfig_absolute_path . $name);
                }
            }
                  $sql = "DELETE FROM #__rem_track_source 
                  WHERE (fk_house_id = $h_id)";
                  $database->setQuery($sql);
                  $database->query();
              }
              $database->setQuery("SELECT image_link FROM #__rem_houses WHERE id IN (" . $bids . ")");
              $image_link = $database->loadObjectList();
              for ($i = 0; $i < count($image_link); $i++) {
                $image_link_name = substr($image_link[$i]->image_link, 0, strrpos($image_link[$i]->image_link, "."));
                $image_link_type = substr($image_link[$i]->image_link, strrpos($image_link[$i]->image_link, "."));
                @unlink($mosConfig_absolute_path . "/components/com_realestatemanager/photos/"
                   . $image_link_name . "_gallery" . $image_link_type);
                @unlink($mosConfig_absolute_path . "/components/com_realestatemanager/photos/"
                   . $image_link_name . "_mini" . $image_link_type);
                @unlink($mosConfig_absolute_path . "/components/com_realestatemanager/photos/"
                   . $image_link[$i]->image_link);
            }

            $database->setQuery("SELECT thumbnail_img, main_img FROM #__rem_photos WHERE fk_houseid IN (" . $bids . ")");
            $del_photos = $database->loadObjectList();

            for ($i = 0; $i <= count($del_photos); $i++) {
                @unlink($mosConfig_absolute_path . "/components/com_realestatemanager/photos/"
                   . $del_photos[$i]->thumbnail_img);
                @unlink($mosConfig_absolute_path . "/components/com_realestatemanager/photos/"
                   . $del_photos[$i]->main_img);
            }
            $database->setQuery("DELETE FROM #__rem_photos WHERE fk_houseid IN (" . $bids . ")");
            $database->query();
            $database->setQuery("DELETE FROM #__rem_review WHERE fk_houseid IN (" . $bids . ")");
            $database->query();
            $database->setQuery("DELETE FROM #__rem_categories WHERE iditem IN (" . $bids . ");");
            $database->query();
            $database->setQuery("DELETE FROM #__rem_houses WHERE id IN (" . $bids . ");");
            $database->query();
            $database->setQuery("DELETE FROM #__rem_feature_houses WHERE fk_houseid IN ($bids)");
            $database->query();
        }
    }
    if ($option == 'com_comprofiler') {
        $redirect = JRoute::_("index.php?option=" . $option .
         "&task=show_add&is_show_data=1&task=edit_my_houses&Itemid=" . $Itemid);
    } else {
        $redirect = JRoute::_("index.php?option=" . $option . "&task=edit_my_houses&Itemid=" . $Itemid);
    }
    mosRedirect($redirect);
}

static function publishHouse() {
    global $database, $my, $option, $Itemid, $realestatemanager_configuration;
    $do = protectInjectionWithoutQuote('task');
    $bid = mosGetParam($_REQUEST, 'bid');

    /**************************************if mass publish cheack count car***********************************************/       
    if (count($bid)){ 
        $count_house_all = getCountHouseForSingleUserREM($my,$database,$realestatemanager_configuration);
        $count_house_single_user = $count_house_all[0];
        $count_house_for_single_group = $count_house_all[1]; 

        if(($count_house_single_user + count($bid))<= $count_house_for_single_group){

            $database->setQuery("SELECT id FROM #__rem_houses WHERE owner_id='" . $my->id .
             "' AND id IN (" . implode(', ', $bid) . ")");

            if (version_compare(JVERSION, '3.0', 'lt')){
                $bid = $database->loadResultArray();
            }else{
                $bid = $database->loadColumn();
            }

            $bids = implode(',', $bid);
                $database->setQuery("UPDATE #__rem_houses SET published = 1
                  \n WHERE owner_id='" . $my->id . "' AND id IN (" . $bids . ");");
                $database->query();               
            }else{              
                echo "<script> alert('"._REALESTATE_MANAGER_YOU_CAN_PUBLISH_ONLY. " "
                . $count_house_for_single_group. " " . _REALESTATE_MANAGER_ADMIN_COUNT_OF_ITEMS .
                "'); window.history.go(-1); </script>\n";
                exit;
            }
        }

        /**************************************************************************************************/ 
        
        if ($option == 'com_comprofiler') {
            $redirect = JRoute::_("index.php?option=" . $option . 
              "&task=show_add&is_show_data=1&task=edit_my_houses&Itemid=" . $Itemid);
        } else {
            $redirect = JRoute::_("index.php?option=" . $option . "&task=edit_my_houses&Itemid=" . $Itemid);
        }
        mosRedirect($redirect);
    }

    static function unpublishHouse() {
        global $database, $my, $option, $Itemid;
        $do = protectInjectionWithoutQuote('task');
        $bid = mosGetParam($_REQUEST, 'bid');
        //get real user houses id
        if (count($bid)) {
            $database->setQuery("SELECT id FROM #__rem_houses WHERE owner_id='"
               . $my->id . "' AND id IN (" . implode(', ', $bid) . ")");
            if (version_compare(JVERSION, "3.0.0", "lt"))
                $bid = $database->loadResultArray();
            else
                $bid = $database->loadColumn();
            if (count($bid)) {
                $bids = implode(',', $bid);
                $database->setQuery("UPDATE #__rem_houses SET published = 0
                  \n WHERE owner_id='" . $my->id . "' AND id IN (" . $bids . ");");
                $database->query();
            }
        }
        if ($option == 'com_comprofiler') {
            $redirect = JRoute::_("index.php?option=" . $option .
               "&task=show_add&is_show_data=1&task=edit_my_houses&Itemid=" . $Itemid);
        } else {
            $redirect = JRoute::_("index.php?option=" . $option .
               "&task=edit_my_houses&Itemid=" . $Itemid);
        }
        mosRedirect($redirect);
    }

    static function listRssCategories($languagelocale) {
        global $mainframe, $database, $my, $acl, $LIMIT, $total, $langContent;
        global $mosConfig_shownoauth, $mosConfig_live_site, $mosConfig_absolute_path;
        global $cur_template, $Itemid, $realestatemanager_configuration;
        $catid = mosGetParam($_REQUEST, 'catid', "");
        $s = getWhereUsergroupsCondition("c");
        if ($catid == "")
            $where_catid = ""; else
        $where_catid = " AND idcat=" . intval($catid);

        if (isset($langContent)) {

            $lang = $langContent;
            // $query = "SELECT lang_code FROM #__languages WHERE sef = '$lang'";
            // $database->setQuery($query);
            // $lang = $database->loadResult();
            $lang = " and ( h.language = '$lang' or h.language like 'all' or "
            ." h.language like '' or h.language like '*' or h.language is null) "
            . " AND ( c.language = '$lang' or c.language like 'all' or "
            ." c.language like '' or c.language like '*' or c.language is null) ";
        } else {
            $lang = "";
        }

        $query = "SELECT c.id AS cid, c.title as ctitle, c.description as cdesc, h.id as bid, h.*, " .
        " r.rent_from, r.rent_until, r.user_name, u.name as ownername " .
        " FROM #__rem_main_categories AS c " .
        " LEFT JOIN #__rem_categories AS hc ON hc.idcat=c.id" .
        " LEFT JOIN #__rem_houses AS h ON h.id=hc.iditem " .
        " LEFT JOIN #__users as u ON u.id=h.owner_id" .
        " LEFT JOIN #__rem_rent AS r ON r.fk_houseid=h.id" .
        " WHERE c.section='com_realestatemanager' " . $lang .
        " AND c.published='1' " .
        " AND h.published='1' " .
        " AND h.approved='1'" .
        " AND ($s)" .
        $where_catid .
        " GROUP BY h.id " .
        " ORDER BY h.date desc";

        $database->setQuery($query);
        $cat_all = $database->loadObjectList();

        // Parameters
        $menu = new mosMenu($database);
        $menu->load($Itemid);
        $params = new mosParameters($menu->params);

        if (($realestatemanager_configuration['contacts']['show'])) {
            $params->def('show_contacts_line', 1);
            $i = checkAccess_REM($realestatemanager_configuration['contacts']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl);
            if ($i)
                $params->def('show_contacts_registrationlevel', 1);
        }
        //take all efiles
        HTML_realestatemanager :: showRssCategories($params, $cat_all, $catid);
    }

    static function ownersList($option) {  
        global $database, $my, $Itemid, $mainframe, $realestatemanager_configuration,
        $langContent, $acl, $mosConfig_list_limit, $limit, $limitstart;

        PHP_realestatemanager::addTitleAndMetaTags();

        $symbol = protectInjectionWithoutQuote('letindex', '');
        $symbol_str = '';
        if ($symbol) {
            $symbol_str = " AND (LOWER(u.name) LIKE '$symbol%' ) ";
        }
        //getting groups of user
        $s = getWhereUsergroupsCondition("c");


        $menu = new JTableMenu($database);
        $menu->load($Itemid);
        $params = new mosParameters($menu->params);
        $database->setQuery("SELECT id FROM #__menu WHERE link='index.php?option=com_realestatemanager'");

        $params->def('header', _REALESTATE_MANAGER_LABEL_TITLE_OWNERSLIST);

        if (checkAccess_REM($realestatemanager_configuration['ownerslist']['registrationlevel'],
           'NORECURSE', userGID_REM($my->id), $acl) &&
            $realestatemanager_configuration['ownerslist']['show']) {
            $params->def('ownerslist_show', 1);
    }


    if (isset($langContent)) {

        $lang = $langContent;
            // $query = "SELECT lang_code FROM #__languages WHERE sef = '$lang'";
            // $database->setQuery($query);
            // $lang = $database->loadResult();
        $lang = " and ( rm.language = '$lang' or rm.language like 'all' or "
        . " rm.language like '' or rm.language like '*' or rm.language is null) "
        . " AND ( c.language = '$lang' or c.language like 'all' or "
        . " c.language like '' or c.language like '*' or c.language is null) ";
    } else {
        $lang = "";
    }

    $db = JFactory::getDBO();
    $query = "SELECT COUNT(DISTINCT u.email)
                \nFROM #__rem_houses AS rm
                \nLEFT JOIN #__rem_categories AS rc ON rc.iditem=rm.id
                \nLEFT JOIN #__rem_main_categories AS c ON c.id=rc.idcat
                \nLEFT JOIN #__users AS u ON rm.owner_id=u.id
                \nWHERE rm.published=1 AND rm.approved=1 AND c.published=1" .
                "      AND ($s) $lang $symbol_str ";
                $db->setQuery($query);
                $total = $db->loadResult();
        $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6
        $query = "SELECT u.name, COUNT( rm.id ) AS houses
          FROM #__rem_houses AS rm
          LEFT JOIN #__rem_categories AS rc ON rc.iditem=rm.id
          LEFT JOIN #__rem_main_categories AS c ON c.id=rc.idcat
          LEFT JOIN #__users AS u ON rm.owner_id = u.id
          WHERE rm.published=1 AND rm.approved=1 " . $lang . " and c.published=1 AND rm.owner_id>0
          AND ($s) $symbol_str
          GROUP BY u.name
          ORDER BY u.name
          LIMIT $pageNav->limitstart,$pageNav->limit;";

          $db->setQuery($query);
          $ownerslist = $db->loadObjectList();


          $query = "SELECT DISTINCT UPPER( SUBSTRING( u.name, 1, 1 ) ) AS symb
          FROM #__rem_houses AS rm
          LEFT JOIN #__rem_categories AS rc ON rc.iditem=rm.id
          LEFT JOIN #__rem_main_categories AS c ON c.id=rc.idcat
          LEFT JOIN #__users AS u ON rm.owner_id = u.id
          WHERE rm.published=1 AND rm.approved=1 AND c.published=1 AND rm.owner_id>0
          AND ($s) $lang
          ORDER BY u.name";
          $db->setQuery($query);
          $symb = $db->loadObjectList();
          if (count($symb) > 0) {
            $symb_list_str = '<div style="display:inline; margin-left:auto;margin-right:auto;">';
            foreach ($symb as $symbol) {
                $symb_list_str .= '<span style="padding:5px; ">' .
                '<a href="index.php?option=' . $option .
                '&task=owners_list' .
                '&letindex=' . $symbol->symb . '&Itemid=' . $Itemid .
                '">' . $symbol->symb . '</a></span>';
            }
            $symb_list_str.="</div>";
            $params->def('symb_list_str', $symb_list_str);
        }

        $params->def('ownerlist01', "{loadposition com_realestatemanager_owner_list_01,xhtml}");
        $params->def('ownerlist02', "{loadposition com_realestatemanager_owner_list_02,xhtml}");
        $params->def('ownerlist03', "{loadposition com_realestatemanager_owner_list_03,xhtml}");

        HTML_realestatemanager :: showOwnersList($params, $ownerslist, $pageNav);
    }

    static function viewUserHouses($option, $languagelocale) {
        global $database, $my, $Itemid, $mainframe, $user_configuration,$task;


        PHP_realestatemanager::addTitleAndMetaTags();

        if (version_compare(JVERSION, '3.0', 'ge')) {
            $menu = new JTableMenu($database);
            $menu->load($Itemid);
            $menu_name = set_header_name_rem($menu, $Itemid);
            $params = new JRegistry;
            $params->loadString($menu->params);
        } else {
            $menu = new mosMenu($database);
            $menu->load($Itemid);
            $menu_name = set_header_name_rem($menu, $Itemid);
            $params = new mosParameters($menu->params);
        }
        $database->setQuery("SELECT id FROM #__menu WHERE link='index.php?option=com_realestatemanager'");


//         if ($my->email == null && !(JRequest::getVar('owners'))) {
//             mosRedirect("index.php?option=com_realestatemanager&Itemid=" . $Itemid, _REALESTATE_MANAGER_PLEASE_LOGIN);
//             exit;
//         }
        $user = mosGetParam($_REQUEST, 'name');

        if (!isset($user)) {
            $params = @$mainframe->getParams();
            $user = $params->get('username');
            if (!isset($user) OR $user == '') {
                if (isset($_REQUEST['name'])) {
                  $user = $_REQUEST['name'];
              }elseif(isset($_REQUEST['userId'])) {
                  $user = intval($_REQUEST['userId']);
                  $query='SELECT * FROM #__users WHERE id='.$user;
                  $database->setQuery($query);
                  $info=$database->loadObject();
                  $user = $info->name;
              } elseif (isset($_SESSION) && isset($_SESSION['rem_user']) ) {
                    $user = $_SESSION['rem_user']; // for SMS
                } elseif (isset($_SESSION) && isset($_SESSION['sms_user']) ) {
                    $user = $_SESSION['sms_user']; // for SMS
                } else {
                    $user = "Guest";
                }
            }
        }
        $anonym_flag = false;
        if ($user == '' || $user == 'Guest' || $user == 'anonymous' || $user == _REALESTATE_MANAGER_LABEL_ANONYMOUS  ) {
            $user = _REALESTATE_MANAGER_LABEL_ANONYMOUS;
            $anonym_flag = true;
        }

        $params->def('header', ((trim($menu_name)) ? $menu_name . ":" : "") .
           _REALESTATE_MANAGER_LABEL_TITLE_USER_HOUSES);
        $pathway = sefRelToAbs('index.php?option=' . $option . '&amp;task=owners_list&amp;Itemid=' . $Itemid);

        $pathway_name = $user;

        $pathway = sefRelToAbs('index.php?option=' . $option . '&amp;task=view_user_houses&amp;Itemid='
           . $Itemid . '&amp;name=' . $user);
        // for 1.6
        $path_way = $mainframe->getPathway();
        $path_way->addItem($pathway_name, $pathway);

        PHP_realestatemanager::searchHouses($option, 0, $option, $languagelocale, $user);
    }

    static function rentBeforeEndNotify($option) {
        global $database, $realestatemanager_configuration, $Itemid, $mosConfig_mailfrom;

        $send_email = 0;
        if (($realestatemanager_configuration['rent_before_end_notify']) &&
            trim($realestatemanager_configuration['rent_before_end_notify_email']) != ""
            && is_numeric($realestatemanager_configuration['rent_before_end_notify_days'])) {
            $send_email = 1;
    }

    if ($send_email) {
        $mail_to = explode(",", $realestatemanager_configuration['rent_before_end_notify_email']);

        $zapros = "SELECT h.id, h.houseid, h.htitle, r.rent_from,r.rent_until,r.user_name,r.user_email " .
        " FROM #__rem_houses as h " .
        " left join #__rem_rent as r on r.fk_houseid = v.id " .
        " WHERE r.rent_return IS NULL and TIMESTAMPDIFF(DAY, now(),rent_until ) = " .
        $realestatemanager_configuration['rent_before_end_notify_days'] . " ; ";
        $database->setQuery($zapros);
        $item_house = $database->loadObjectList();
        echo $database->getErrorMsg();

        $message = _REALESTATE_MANAGER_HOUSES_EXPIRE_NOTICE . '<br /><br />';

        foreach ($item_house as $item) {
          $message .= str_replace("{username}", $item->user_name,
             _REALESTATE_MANAGER_EMAIL_NOTIFICATION_RENT_BEFORE_END);
          $message = str_replace("{user_email}", $item->user_email, $message);
          $message = str_replace("{house_title}", $item->htitle, $message);
          $message = str_replace("{ID}", $item->id, $message);
          $message = str_replace("{PropertyID}", $item->houseid, $message);
      }

      if (count($item_house) > 0)
        mosMail($mosConfig_mailfrom, _REALESTATE_MANAGER_RENT_EXPIRE_NOTICE, $mail_to, 
          _REALESTATE_MANAGER_RENT_EXPIRE_NOTICE, $message, true);
}
}

static function rent_requests($option, $bid) { 
    global $database, $my, $mainframe, $mosConfig_list_limit, $realestatemanager_configuration, $Itemid;

    PHP_realestatemanager::addTitleAndMetaTags();

    if ($my->email == null) {
        mosRedirect("index.php?option=com_realestatemanager&Itemid=" . $Itemid, _REALESTATE_MANAGER_PLEASE_LOGIN);
        exit;
    }

    $limit = $realestatemanager_configuration['page']['items'];
    $limitstart = mosGetParam($_REQUEST, 'limitstart', 0);
    $database->setQuery("SELECT count(*) FROM #__rem_houses AS a" .
        "\nLEFT JOIN #__rem_rent_request AS l" .
        "\nON l.fk_houseid = a.id" .
        "\nWHERE l.status = 0 AND a.owner_id LIKE '$my->id'");
    $total = $database->loadResult();
    echo $database->getErrorMsg();
        $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6

        $database->setQuery("SELECT * FROM #__rem_houses AS a" .
            "\nLEFT JOIN #__rem_rent_request AS l" .
            "\nON l.fk_houseid = a.id" .
            "\nWHERE l.status = 0 AND a.owner_id LIKE '$my->id'" .
            "\nORDER BY l.id DESC" .
            "\nLIMIT $pageNav->limitstart,$pageNav->limit;");
        $rent_requests = $database->loadObjectList();
        echo $database->getErrorMsg();

        foreach ($rent_requests as $request) {
            if($request->associate_house){
                if($assoc_rem = getAssociateHouses($request->fk_houseid)){
                    $database->setQuery("SELECT group_concat(distinct a.htitle) FROM #__rem_houses AS a" .
                      "\n LEFT JOIN #__rem_rent_request AS l ON l.fk_houseid = a.id" .
                      "\n WHERE a.id in ($assoc_rem) AND a.id != $request->fk_houseid");
                    $request->title_assoc = $database->loadResult(); 
                }
            }
        }  

        PHP_realestatemanager::showTabs();
        HTML_realestatemanager :: showRequestRentHouses($option, $rent_requests,  $pageNav);
    }

    static function decline_rent_requests($option, $bids) {
        global $database, $realestatemanager_configuration, $Itemid;
        $datas = array();
        foreach ($bids as $bid) {
            $rent_request = new mosRealEstateManager_rent_request($database);
            $rent_request->load($bid);
            $tmp = $rent_request->decline();
            if ($tmp != null) {
                echo "<script> alert('" . $tmp . "'); window.history.go(-1); </script>\n";
                exit;
            }
            foreach ($datas as $c => $data) {
                if ($rent_request->user_email == $data['email']) {
                    $datas[$c]['ids'][] = $rent_request->fk_houseid;
                    continue 2;
                }
            }
            $datas[] = array('email' => $rent_request->user_email,
               'name' => $rent_request->user_name, 'id' => $rent_request->fk_houseid);
        }

        if ($realestatemanager_configuration['rent_answer']) {
            if (isset($datas[0]['name']) || isset($datas[0]['email']) || isset($datas[0]['id'])) {
                PHP_realestatemanager::sendMailRentRequest($datas, _REALESTATE_MANAGER_ADMIN_CONFIG_RENT_ANSWER_DECLINED);
            }
        }

        if ($option == "com_comprofiler") {
            mosRedirect("index.php?option=" . $option .
               "&task=rent_requests&is_show_data=1&Itemid=" . $Itemid);
        } else {
            mosRedirect("index.php?option=" . $option .
               "&task=rent_requests&Itemid=" . $Itemid);
        }
    }

    static function accept_rent_requests($option, $bids) {
        global $database, $realestatemanager_configuration, $Itemid;
        $datas = array();
        foreach ($bids as $bid) {
            $rent_request = new mosRealEstateManager_rent_request($database);
            $rent_request->load($bid);
            $tmp = $rent_request->accept();
            if ($tmp != null) {
                echo "<script> alert('" . $tmp . "'); window.history.go(-1); </script>\n";
                exit;
            }
            foreach ($datas as $c => $data) {
                if ($rent_request->user_email == $data['email']) {
                    $datas[$c]['ids'][] = $rent_request->fk_houseid;
                    continue 2;
                }
            }
            $datas[] = array('email' => $rent_request->user_email,
               'name' => $rent_request->user_name, 'id' => $rent_request->fk_houseid);
        }

        if ($realestatemanager_configuration['rent_answer']) {
            if (isset($datas[0]['name']) || isset($datas[0]['email']) || isset($datas[0]['id'])) {
                PHP_realestatemanager::sendMailRentRequest($datas, _REALESTATE_MANAGER_ADMIN_CONFIG_RENT_ANSWER_ACCEPTED);
            }
        }

        if ($option == "com_comprofiler") {
            mosRedirect("index.php?option=" . $option .
               "&task=rent_requests&is_show_data=1&Itemid=" . $Itemid);
        } else {
            mosRedirect("index.php?option=" . $option . "&task=rent_requests&Itemid=" . $Itemid);
        }
    }

    static function sendMailRentRequest($datas, $answer) {
        global $database, $mosConfig_mailfrom, $realestatemanager_configuration;
        $conf = JFactory::getConfig();

        foreach ($datas as $key => $data) {
            $mess = null;
            $zapros = "SELECT htitle FROM #__rem_houses WHERE id=" . $data['id'];
            $database->setQuery($zapros);
            $item = $database->loadResult();
            echo $database->getErrorMsg();
            $database->setQuery("SELECT u.name AS ownername,u.email as owneremail
                    \nFROM #__users AS u
                    \nLEFT JOIN #__rem_houses AS rm ON rm.owner_id=u.id
                    \nWHERE rm.id=" . $data['id']);
            echo $database->getErrorMsg();
            $ownerdata = $database->loadObjectList();

            $datas[$key]['title'] = $item;

            $message = _REALESTATE_MANAGER_EMAIL_NOTIFICATION_RENT_REQUEST_ANSWER;
            $message = str_replace("{title}", $datas[$key]['title'], $message);
            $message = str_replace("{answer}", $answer, $message);
            $message = str_replace("{username}", $datas[$key]['name'], $message);
            if ($answer == _REALESTATE_MANAGER_ADMIN_CONFIG_RENT_ANSWER_ACCEPTED) {
                $message = str_replace("{ownername}", $ownerdata[0]->ownername, $message);
                $message = str_replace("{owneremail}", $ownerdata[0]->owneremail, $message);
            } else {
                $message = str_replace("{ownername}", '', $message);
                $message = str_replace("{owneremail}", '', $message);
            }

            mosMail($mosConfig_mailfrom, $conf->_registry['config']['data']->fromname, $data['email']
                ,_REALESTATE_MANAGER_EMAIL_RENT_ANSWER_SUBJECT, $message, true);
        }
    }

    static function sendMailBuyingRequest($datas, $answer) {
        global $database, $mosConfig_mailfrom, $realestatemanager_configuration;
        $conf = JFactory::getConfig();
        foreach ($datas as $key => $data) {
            $mess = null;
            $zapros = "SELECT htitle FROM #__rem_houses WHERE id=" . $data['id'];
            $database->setQuery($zapros);
            $item = $database->loadResult();
            echo $database->getErrorMsg();
            $database->setQuery("SELECT u.name AS ownername,u.email AS owneremail
                    \nFROM #__users AS u
                    \nLEFT JOIN #__rem_houses AS rm ON rm.owner_id=u.id
                    \nWHERE rm.id=" . $data['id']);
            echo $database->getErrorMsg();
            $ownerdata = $database->loadObjectList();

            $datas[$key]['title'] = $item;

            $message = _REALESTATE_MANAGER_EMAIL_NOTIFICATION_BUYING_REQUEST_ANSWER;
            $message = str_replace("{title}", $datas[$key]['title'], $message);
            $message = str_replace("{answer}", $answer, $message);
            $message = str_replace("{username}", $datas[$key]['name'], $message);
            if ($answer == _REALESTATE_MANAGER_ADMIN_CONFIG_RENT_ANSWER_ACCEPTED) {
                $message = str_replace("{ownername}", $ownerdata[0]->ownername, $message);
                $message = str_replace("{owneremail}", $ownerdata[0]->owneremail, $message);
            } else {
                $message = str_replace("{ownername}", '', $message);
                $message = str_replace("{owneremail}", '', $message);
            }

            mosMail($mosConfig_mailfrom, $conf->_registry['config']['data']->fromname, $data['email'],
               _REALESTATE_MANAGER_EMAIL_RENT_ANSWER_SUBJECT, $message, true);
        }
    }

    static function buying_requests($option) {
        global $database, $mainframe, $my, $mosConfig_list_limit, $realestatemanager_configuration, $Itemid;

        if ($my->email == null) {
            mosRedirect("index.php?option=com_realestatemanager&Itemid=" . $Itemid, _REALESTATE_MANAGER_PLEASE_LOGIN);
            exit;
        }

        $limit = $realestatemanager_configuration['page']['items'];
        $limitstart = mosGetParam($_REQUEST, 'limitstart', 0);

        $database->setQuery("SELECT count(*) FROM #__rem_houses AS a" .
            "\n LEFT JOIN #__rem_buying_request AS s" .
            "\n ON s.fk_houseid = a.id" .
            "\n WHERE s.status = 0 AND a.owner_id LIKE '" . $my->id . "'");
        $total = $database->loadResult();
        echo $database->getErrorMsg();

        $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6

        $database->setQuery("SELECT * FROM #__rem_houses AS a" .
            "\n LEFT JOIN #__rem_buying_request AS s" .
            "\n ON s.fk_houseid = a.id" .
            "\n WHERE s.status = 0 AND a.owner_id LIKE '" . $my->id . "'" .
            "\n ORDER BY s.id DESC" .
            "\n LIMIT " . $pageNav->limitstart . "," . $pageNav->limit . ";");
        $buy_requests = $database->loadObjectList();

        foreach ($buy_requests as $request) {
            if($request->associate_house){
                if($assoc_rem = getAssociateHouses($request->fk_houseid)){
                    $database->setQuery("SELECT group_concat(distinct a.htitle) FROM #__rem_houses AS a" .
                      "\n LEFT JOIN #__rem_buying_request AS s ON s.fk_houseid = a.id" .
                      "\n WHERE a.id in ($assoc_rem) AND a.id != $request->fk_houseid");
                    $request->tiitle_assoc = $database->loadResult(); 
                }
            }
        } 
        echo $database->getErrorMsg();
        PHP_realestatemanager::showTabs();

        HTML_realestatemanager::showRequestBuyingHouses($option, $buy_requests, $pageNav, $Itemid);
    }

    static function accept_buying_requests($option, $bids) {
        global $database, $Itemid, $realestatemanager_configuration;
        foreach ($bids as $bid) {
            $buying_request = new mosRealEstateManager_buying_request($database);
            $buying_request->load($bid);

            $datas[] = array('name' => $buying_request->customer_name,
                'email' => $buying_request->customer_email,
                'id' => $buying_request->fk_houseid);
            $buying_request->delete();
            /* if ($tmp!=null){
              echo "<script> alert('".$tmp."'); window.history.go(-1); </script>\n";
              exit;
          } */
      }
      if ($realestatemanager_configuration['buy_answer']) {
        if (isset($datas[0]['name']) || isset($datas[0]['email']) || isset($datas[0]['id'])) {
            PHP_realestatemanager::sendMailBuyingRequest($datas, _REALESTATE_MANAGER_ADMIN_CONFIG_RENT_ANSWER_ACCEPTED);
        }
    }
    if ($option == "com_comprofiler") {
        mosRedirect(JRoute::_("index.php?option=" . $option .
           "&task=buying_requests&is_show_data=1&Itemid=" . $Itemid));
    } else {
        mosRedirect(JRoute::_("index.php?option=" . $option .
           "&task=buying_requests&Itemid=" . $Itemid));
    }
}

static function decline_buying_requests($option, $bids) {
    global $database, $Itemid;
    foreach ($bids as $bid) {
        $buying_request = new mosRealEstateManager_buying_request($database);
        $buying_request->load($bid);


        $datas[] = array('name' => $buying_request->customer_name,
            'email' => $buying_request->customer_email,
            'id' => $buying_request->fk_houseid
        );
        $tmp = $buying_request->decline();
        if ($tmp != null) {
            echo "<script> alert('" . $tmp . "'); window.history.go(-1); </script>\n";
            exit();
        }
    }
    if ($realestatemanager_configuration['buy_answer']) {
        if (isset($datas[0]['name']) || isset($datas[0]['email']) || isset($datas[0]['id'])) {
            PHP_realestatemanager::sendMailBuyingRequest($datas, _REALESTATE_MANAGER_ADMIN_CONFIG_RENT_ANSWER_DECLINED);
        }
    }
    if ($option == "com_comprofiler") {
        mosRedirect("index.php?option=" . $option .
           "&task=buying_requests&is_show_data=1&Itemid=" . $Itemid);
    } else {
        mosRedirect("index.php?option=" . $option . "&task=buying_requests&Itemid=" . $Itemid);
    }
}

static function rent($option, $bid) {
    global $database, $my;

    PHP_realestatemanager::addTitleAndMetaTags();
    if (!is_array($bid) || count($bid) !== 1) {
        echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_SELECT_ONE_ITEM .
        "'); window.history.go(-1);</script>\n";
        exit;
    }
    if (!array_key_exists("bid", $_REQUEST)) {
        echo "<script> alert('" . _REALESTATE_MANAGER_TOOLBAR_RENT_HOUSES .
        "'); window.history.go(-1);</script>\n";
        exit;
    }
    $bid_house = implode(',', $bid);
    $select = "SELECT a.*, cc.name AS category, l.id as rentid, l.rent_from as rent_from, " .
    "l.rent_return as rent_return, l.rent_until as rent_until, " .
    "l.user_name as user_name, l.user_email as user_email " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_categories as hc on hc.iditem = a.id" .
    "\nLEFT JOIN #__rem_main_categories AS cc ON cc.id = hc.idcat" .
    "\nLEFT JOIN #__rem_rent AS l ON l.id = a.fk_rentid" .
    "\nWHERE a.id = $bid_house";
    $database->setQuery($select);
    $house1 = $database->loadObject();
    if ($house1->listing_type != 1) {
      ?>
      <script type = "text/JavaScript" language = "JavaScript">
          alert("<?php echo _REALESTATE_MANAGER_ADMIN_NOT_FOR_RENT ?>");
          window.history.go(-1);
      </script>
      <?php

      exit;
  }
  $bids = implode(',', $bid);
  $bids = getAssociateHouses($bids);
  $houses_assoc[]= $house1;
  if($bids){
    $select = "SELECT a.*, cc.name AS category, l.id as rentid, l.rent_from as rent_from, " .
    "l.rent_return as rent_return, l.rent_until as rent_until, " .
    "l.user_name as user_name, l.user_email as user_email " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_categories as hc on hc.iditem = a.id" .
    "\nLEFT JOIN #__rem_main_categories AS cc ON cc.id = hc.idcat" .
    "\nLEFT JOIN #__rem_rent AS l ON l.id = a.fk_rentid" .
    "\nWHERE a.id in ($bids)";
    $database->setQuery($select);
    $houses_assoc = $database->loadObjectList();

            //for rent or not
    $count = count($houses_assoc);
    for ($i = 0; $i < $count; $i++) {
        if ($houses_assoc[$i]->listing_type != 1) {
            ?>
            <script type = "text/JavaScript" language = "JavaScript">
                alert("<?php echo _REALESTATE_MANAGER_ADMIN_NOT_FOR_RENT_ASOC ?>");
                window.history.go(-1);
            </script>
            <?php

            exit;
        }
    }
}
        // get list of categories


$userlist[] = mosHTML :: makeOption('-1', '----------');
$database->setQuery("SELECT id AS value, name AS text from #__users ORDER BY name");
$userlist = array_merge($userlist, $database->loadObjectList());
$usermenu = mosHTML :: selectList($userlist, 'userid', 'class="inputbox" size="1"', 'value', 'text', '-1');
HTML_realestatemanager :: showRentHouses($option, $house1, $houses_assoc, $usermenu, "rent");
}

static function saveRent($option, $bids, $task = "") {


    global $database, $Itemid, $realestatemanager_configuration;

    $checkh = mosGetParam($_REQUEST, 'checkHouse');

    if ($checkh != "on") {
        echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_SELECT_ONE_ITEM .
        "'); window.history.go(-1);</script>\n";
        exit;
    }
        /////////////////////
    if (isset($id)
        && $id != 0
        && $my->id
        != $house->owner_id
    ) {
        mosRedirect('index.php?option=com_realestatemanager&Itemid=' . $Itemid);
    exit;
}



$data = JFactory::getDBO();
$houseid = protectInjectionWithoutQuote('houseid');
$id = protectInjectionWithoutQuote('id');
$rent_from = protectInjectionWithoutQuote('rent_from');
$rent_until = protectInjectionWithoutQuote('rent_until');

$rent_from = data_transform_rem($rent_from);
$rent_until = data_transform_rem($rent_until);
$ids[] = $id ;  
$ids = implode(',', $ids);
$ids = getAssociateHouses($ids);
if($ids == "") $ids = $id;
$ids = explode(',', $ids);

if( $task == "edit_rent" ){
  $ids = explode(',', $bids[0]);
}

for($i = 0, $n = count($ids); $i < $n; $i++){
    $rent = new mosRealEstateManager_rent($database);
    if($task == "edit_rent"  ){
      $rent->load($ids[$i]);
      $fk_houseid = $rent->fk_houseid;
  } else {
      $fk_houseid = $ids[$i] ;
  }
  $query = "SELECT * FROM #__rem_rent where fk_houseid= " . $fk_houseid . " AND rent_return is NULL ";
  $database->setQuery($query);
  $rentTerm = $database->loadObjectList();
  $rent_from = substr($rent_from, 0, 10);
  $rent_until = substr($rent_until, 0, 10);
  foreach ($rentTerm as $oneTerm){
    if($task == "edit_rent"  ){
      if ($ids[$i] == $oneTerm->id)
        continue;
}
$oneTerm->rent_from = substr($oneTerm->rent_from, 0, 10);
$oneTerm->rent_until = substr($oneTerm->rent_until, 0, 10);
$returnMessage = checkRentDayNightREM (($oneTerm->rent_from),($oneTerm->rent_until),
   $rent_from, $rent_until, $realestatemanager_configuration);
if(strlen($returnMessage) > 0){
    echo "<script> alert('$returnMessage'); window.history.go(-1); </script>\n";          
    exit;
}       
}

$rent->rent_from = $rent_from;
$rent->rent_until = $rent_until;
$rent->fk_houseid = $fk_houseid;
$userid = protectInjectionWithoutQuote('userid');

if ($userid == "-1") {
    $rent->user_name = mosGetParam($_REQUEST, 'user_name', '');
    $rent->user_email = mosGetParam($_REQUEST, 'user_email', '');
} else {
    $rent->fk_userid = $userid;
    $query = "SELECT name FROM #__users WHERE id=" . $userid . "";
    $database->setQuery($query);
    $user_name_for_rent = $database->loadObjectList();
    $rent->user_name = $user_name_for_rent[0]->name;
    $rent->user_email = mosGetParam($_REQUEST, 'user_email', '');
}

if (!$rent->check($rent)) {
    echo "<script> alert('" . addslashes($rent->getError()) .
    "'); window.history.go(-1); </script>\n";
    exit();
}

if (!$rent->store()) {
    echo "<script> alert('" . addslashes($rent->getError()) .
    "'); window.history.go(-1); </script>\n";
    exit();
}

$rent->checkin();
$house = new mosRealEstateManager($database);
$house->load($fk_houseid);
$house->fk_rentid = $rent->id;
$house->store();
$house->checkin();
}



if ($option == 'com_comprofiler')
    $link_for_mosRedirect = JRoute::_("index.php?option=" . $option .
       "&task=edit_my_houses&Itemid=" . $Itemid); else
$link_for_mosRedirect = JRoute::_("index.php?option=" . $option .
   "&task=edit_my_houses&Itemid=" . $Itemid);
mosRedirect($link_for_mosRedirect);
}


static function edit_rent($option, $bid) {

    global $database, $my;
    if (!is_array($bid) || count($bid) !== 1) {
        echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_SELECT_ONE_ITEM ."'); window.history.go(-1);</script>\n";
        exit;
    }

    $bid_house = implode(',', $bid);
    $select = "SELECT a.*, cc.name AS category, l.id as rentid, l.rent_from as rent_from, " .
    "l.rent_return as rent_return, l.rent_until as rent_until, " .
    "l.user_name as user_name, l.user_email as user_email " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_categories as hc on hc.iditem = a.id" .
    "\nLEFT JOIN #__rem_main_categories AS cc ON cc.id = hc.idcat" .
    "\nLEFT JOIN #__rem_rent AS l ON l.fk_houseid = a.id" .
    "\nWHERE a.id = $bid_house";
    $database->setQuery($select);
    $house1 = $database->loadObject();
    if ($house1->listing_type != 1) {
      ?>
      <script type = "text/JavaScript" language = "JavaScript">
          alert("<?php echo _REALESTATE_MANAGER_ADMIN_NOT_FOR_RENT ?>");
          window.history.go(-1);
      </script>
      <?php
      exit;
  }



  $bids = implode(',', $bid);
  $bids = getAssociateHouses($bids);
  if($bids == "") $bids = implode(',', $bid);
  $houses_rents_assoc= array();
  $title_assoc = array();
  if($bids){


    $select = "SELECT a.*, cc.name AS category, l.id as rentid, l.rent_from as rent_from, " .
    "l.rent_return as rent_return, l.rent_until as rent_until, " .
    "l.user_name as user_name, l.user_email as user_email " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_categories as hc on hc.iditem = a.id" .
    "\nLEFT JOIN #__rem_main_categories AS cc ON cc.id = hc.idcat" .
    "\nLEFT JOIN #__rem_rent AS l ON l.fk_houseid = a.id" .
    "\nWHERE a.id in ($bids)";
    $database->setQuery($select);
    $houses_rents_assoc = $database->loadObjectList();

    $select = "SELECT a.htitle  " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_rent AS l ON l.fk_houseid = a.id" .
    "\nWHERE a.id in ($bids)";
    $database->setQuery($select);
    $title_assoc = $database->loadObjectList();

    $count = count($houses_rents_assoc);
    for ($i = 0; $i < $count; $i++) {
        if ($houses_rents_assoc[$i]->listing_type != 1) {
            ?>
            <script type = "text/JavaScript" language = "JavaScript">
                alert("<?php echo _REALESTATE_MANAGER_ADMIN_NOT_FOR_RENT_ASOC ?>");
                window.history.go(-1);
            </script>
            <?php
            exit;
        }
    }

    $is_rent_out = false;
    for ($i = 0; $i < count($houses_rents_assoc); $i++) {

      if ( $houses_rents_assoc[$i]->rent_from != '' && $houses_rents_assoc[$i]->rent_return == '' )
      {
        $is_rent_out = true ;
        break ;
    }
}

if ( !$is_rent_out ){
    ?>
    <script type = "text/JavaScript" language = "JavaScript">
        alert("<?php echo _REALESTATE_MANAGER_ADMIN_HOUSE_NOT_IN_RENT ?>");
        window.history.go(-1);
    </script>
    <?php
    exit;
}

      //check rent_return == null count for all assosiate
$ids = explode(',', $bids);
$rent_count = -1;
$all_assosiate_rent = array();
$count = count($ids);
for ($i = 0; $i < $count; $i++) {

    $query = "SELECT * FROM #__rem_rent WHERE fk_houseid = " . $ids[$i] .
    " and rent_return is null ORDER BY rent_from";
            // print_r($query);
    $database->setQuery($query);
    $all_assosiate_rent_item = $database->loadObjectList();

    if ( $rent_count != -1 && $rent_count != count($all_assosiate_rent_item) )
    {
        ?>
        <script type = "text/JavaScript" language = "JavaScript">
            alert("<?php echo _REALESTATE_MANAGER_ADMIN_RENT_ASSOCIATED ?>");
            window.history.go(-1);
        </script>
        <?php

        exit;
    }
    $rent_count = count($all_assosiate_rent_item);
           // print_r($rent_count);exit;
    $all_assosiate_rent[] = $all_assosiate_rent_item;
}
}

    // get list of users
$userlist[] = mosHTML :: makeOption('-1', '----------');
$database->setQuery("SELECT id AS value, name AS text from #__users ORDER BY name");
$userlist = array_merge($userlist, $database->loadObjectList());
$usermenu = mosHTML :: selectList($userlist, 'userid', 'class="inputbox" size="1"', 'value', 'text', '-1');

HTML_realestatemanager :: editRentHouses($option, $house1, $houses_rents_assoc,
   $title_assoc, $usermenu, $all_assosiate_rent, "edit_rent");
}


static function rent_return($option, $bid) {
    global $database, $my, $Itemid;

    PHP_realestatemanager::addTitleAndMetaTags();

    if (!is_array($bid) || count($bid) !== 1) {
        echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_SELECT_ONE_ITEM .
        "'); window.history.go(-1);</script>\n";
        exit;
    }
    $bid_house = implode(',', $bid);
    $select = "SELECT a.*, cc.name AS category, l.id as rentid, l.rent_from as rent_from, " .
    "l.rent_return as rent_return, l.rent_until as rent_until, " .
    "l.user_name as user_name, l.user_email as user_email " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_categories as hc on hc.iditem = a.id" .
    "\nLEFT JOIN #__rem_main_categories AS cc ON cc.id = hc.idcat" .
    "\nLEFT JOIN #__rem_rent AS l ON l.fk_houseid = a.id" .
    "\nWHERE a.id = $bid_house";
    $database->setQuery($select);
    $house1 = $database->loadObject();
    if ($house1->listing_type != 1) {
      ?>
      <script type = "text/JavaScript" language = "JavaScript">
          alert("<?php echo _REALESTATE_MANAGER_ADMIN_NOT_FOR_RENT ?>");
          window.history.go(-1);
      </script>
      <?php

      exit;
  }  
  $bids = getAssociateHouses($bid_house);
  if($bids == "") $bids = $bid_house;
  $houses_rents_assoc = array();
  $title_assoc = array();
  if($bids){
    $select = "SELECT a.*, cc.name AS category, l.id as rentid, l.rent_from as rent_from, " .
    "l.rent_return as rent_return, l.rent_until as rent_until, " .
    "l.user_name as user_name, l.user_email as user_email " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_categories as hc on hc.iditem = a.id" .
    "\nLEFT JOIN #__rem_main_categories AS cc ON cc.id = hc.idcat" .
    "\nLEFT JOIN #__rem_rent AS l ON l.fk_houseid = a.id" .
    "\nWHERE a.id in ($bids)";
    $database->setQuery($select);
    $houses_rents_assoc = $database->loadObjectList();

    $select = "SELECT a.htitle " .
    "\nFROM #__rem_houses AS a" .
    "\nLEFT JOIN #__rem_rent AS l ON l.fk_houseid = a.id" .
    "\nWHERE a.id in ($bids)"; 
    $database->setQuery($select);
    $title_assoc = $database->loadObjectList();
    
    
    $count = count($houses_rents_assoc);
    for ($i = 0; $i < $count; $i++) {
        if ($houses_rents_assoc[$i]->listing_type != 1) {
            ?>
            <script type = "text/JavaScript" language = "JavaScript">
                alert("<?php echo _REALESTATE_MANAGER_ADMIN_NOT_FOR_RENT_ASOC ?>");
                window.history.go(-1);
            </script>
            <?php
            exit;
        }
    }


    $is_rent_out = false;
    for ($i = 0; $i < count($houses_rents_assoc); $i++) {

      if ( $houses_rents_assoc[$i]->rent_from != '' && $houses_rents_assoc[$i]->rent_return == '' )
      {
        $is_rent_out = true ;
        break ;
    }
}        

if (!$is_rent_out )
{
    ?>
    <script type = "text/JavaScript" language = "JavaScript">
        alert("<?php echo _REALESTATE_MANAGER_ADMIN_ALERT_NOT_IN_RENT ?>");
        window.history.go(-1);
    </script>
    <?php
    exit;
}

          //check rent_return == null count for all assosiate
$ids = explode(',', $bids);
$rent_count = -1;
$all_assosiate_rent = array();
$count = count($ids);
for ($i = 0; $i < $count; $i++) {

    $query = "SELECT * FROM #__rem_rent WHERE fk_houseid = " . $ids[$i] . 
    " and rent_return is null ORDER BY rent_from"; 
            // print_r($query);
    $database->setQuery($query);
    $all_assosiate_rent_item = $database->loadObjectList();
    
    if ( $rent_count != -1 && $rent_count != count($all_assosiate_rent_item) )
    {
        ?>
        <script type = "text/JavaScript" language = "JavaScript">
            alert("<?php echo _REALESTATE_MANAGER_ADMIN_RENT_ASSOCIATED ?>");
            window.history.go(-1);
        </script>
        <?php
        exit;
    }
    $rent_count = count($all_assosiate_rent_item);
           // print_r($rent_count);exit;
    $all_assosiate_rent[] = $all_assosiate_rent_item;
} 
}
        // get list of users
$userlist[] = mosHTML :: makeOption('-1', '----------');
$database->setQuery("SELECT id AS value, name AS text from #__users ORDER BY name");
$userlist = array_merge($userlist, $database->loadObjectList());
$usermenu = mosHTML :: selectList($userlist, 'userid', 'class="inputbox" size="1"', 'value', 'text', '-1');
HTML_realestatemanager :: editRentHouses($option, $house1, $houses_rents_assoc,
   $title_assoc, $usermenu, $all_assosiate_rent, "rent_return");
}

static function saveRent_return($option, $lids) {
    global $database, $my, $Itemid;

    $houseid = mosGetParam($_REQUEST, 'houseid');
    $id = mosGetParam($_REQUEST, 'id');
    $rent_from = mosGetParam($_REQUEST, 'rent_from');
    $rent_until = mosGetParam($_REQUEST, 'rent_until');
    $check_vids = implode(',', $lids); 
    if ($check_vids == 0 || count($lids) > 1)
    {
      echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_SELECT_ONE_ITEM .
      "'); window.history.go(-1);</script>\n";
      exit;
  }
  $r_ids = explode(',', $lids[0]);       
  $rent = new mosRealEstateManager_rent($database);
  for ($i = 0, $n = count($r_ids); $i < $n; $i++) {

    $rent->load($r_ids[$i]); 
    if ($rent->rent_return != null) {
        echo "<script> alert('". _REALESTATE_MANAGER_ADMIN_RENT_ALERT_RETURNED .
        "'); window.history.go(-1);</script>\n";
        exit;
    }
    $rent->rent_return = date("Y-m-d H:i:s");
    if (!$rent->check($rent)) {
        echo "<script> alert('" . $rent->getError() . "'); window.history.go(-1); </script>\n";
        exit;
    }
    if (!$rent->store()) {
        echo "<script> alert('" . $rent->getError() . "'); window.history.go(-1); </script>\n";
        exit;
    }
    $rent->checkin();
    $is_update_house_lend = true;
    if ($is_update_house_lend) {
        $house = new mosRealEstateManager($database);
        $house->load($id);
        $query = "SELECT * FROM #__rem_rent where fk_houseid= " . $id . " AND rent_return is NULL";
        $database->setQuery($query);
        $info_rents = $database->loadObjectList();
        if (isset($info_rents[0])) {
            $house->fk_rentid = $info_rents[0]->id;
            $is_update_house_lend = FALSE;
        } else {
            $house->fk_rentid = 0;
        }
        $house->store();
        $house->checkin();
    }
}

if ($option == 'com_comprofiler') {
    $link_for_mosRedirect = JRoute::_("index.php?option=" . $option .
       "&task=edit_my_houses&Itemid=" . $Itemid);
} else {
    $link_for_mosRedirect = JRoute::_("index.php?option=" . $option .
       "&task=edit_my_houses&Itemid=" . $Itemid);
}
mosRedirect($link_for_mosRedirect);
}

static function rent_history($option) {
    global $database, $my, $Itemid, $realestatemanager_configuration, $mosConfig_list_limit;

    PHP_realestatemanager::addTitleAndMetaTags();

    if ($my->email == null) {
        mosRedirect("index.php?option=com_realestatemanager&Itemid=" .
           $Itemid, _REALESTATE_MANAGER_PLEASE_LOGIN);
        exit;
    }

    $menu = new mosMenu($database);
    $menu->load($Itemid);
    $params = new mosParameters($menu->params);


    $limit = $realestatemanager_configuration['page']['items'];
    $limitstart = mosGetParam($_REQUEST, 'limitstart', 0);

    $database->setQuery("SELECT count(*) FROM #__rem_rent AS l " .
        "\nLEFT JOIN #__rem_houses AS a ON a.id = l.fk_houseid" .
        "\nWHERE l.fk_userid = '" . $my->id . "'");
    $total = $database->loadResult();
    echo $database->getErrorMsg();

        $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6

        $query = "SELECT l.*,a.* FROM #__rem_rent AS l " .
        "\nLEFT JOIN #__rem_houses AS a ON a.id = l.fk_houseid" .
        "\nWHERE l.fk_userid = '" . $my->id . "' ORDER BY l.id DESC LIMIT " .
        $pageNav->limitstart . "," . $pageNav->limit . ";";

        $database->setQuery($query);
        $houses = $database->loadObjectList();
        PHP_realestatemanager::showTabs();
        HTML_realestatemanager :: showRentHistory($option, $houses, $pageNav);
    }

    static function ShowAllHouses($layout = "default", $printItem) {
        global $mainframe, $database, $acl, $my, $langContent;
        global $mosConfig_shownoauth, $mosConfig_live_site, $mosConfig_absolute_path;
        global $cur_template, $Itemid, $realestatemanager_configuration,
        $mosConfig_list_limit, $limit, $total, $limitstart;

        PHP_realestatemanager::addTitleAndMetaTags();

        if (isset($langContent)) {

            $lang = $langContent;
            // $query = "SELECT lang_code FROM #__languages WHERE sef = '$lang'";
            // $database->setQuery($query);
            // $lang = $database->loadResult();

            $lang = " and ( h.language = '$lang' or h.language like 'all' or "
            ." h.language like '' or h.language like '*' or h.language is null) "
            . " AND ( c.language = '$lang' or c.language like 'all' or "
            ." c.language like '' or c.language like '*' or c.language is null) ";
        } else {
            $lang = "";
        }

        //sorting
        $item_session = JFactory::getSession();
        $sort_arr = $item_session->get('rem_housesort', '');
        if (is_array($sort_arr)) {
            $tmp1 = protectInjectionWithoutQuote('order_direction');
            if ($tmp1 != '')
                $sort_arr['order_direction'] = $tmp1;
            $tmp1 = protectInjectionWithoutQuote('order_field');
            if ($tmp1 != '')
                $sort_arr['order_field'] = $tmp1;
            $item_session->set('rem_housesort', $sort_arr);
        } else {
            $sort_arr = array();
            $sort_arr['order_field'] = 'htitle';
            $sort_arr['order_direction'] = 'asc';
            $item_session->set('rem_housesort', $sort_arr);
        }
        if ($sort_arr['order_field'] == "price")
            $sort_string = "CAST( " . $sort_arr['order_field'] . " AS SIGNED)" . " " .
        $sort_arr['order_direction'];
        else
            $sort_string = $sort_arr['order_field'] . " " . $sort_arr['order_direction'];

        //getting groups of user
        $s = getWhereUsergroupsCondition("c");

        $query = "SELECT COUNT(DISTINCT h.id)
        \nFROM #__rem_houses AS h"
        . "\nLEFT JOIN #__rem_categories AS hc ON hc.iditem=h.id"
        . "\nLEFT JOIN #__rem_main_categories AS c ON c.id=hc.idcat"
        . "\nWHERE h.published='1' AND h.approved='1' AND c.published='1'  $lang
        AND ($s)";

        $database->setQuery($query);
        $total = $database->loadResult();

        $pageNav = new JPagination($total, $limitstart, $limit);

        // getting all items for this category
        $query = "SELECT h.*,hc.idcat AS catid,hc.idcat AS idcat, c.title as category_titel
        \nFROM #__rem_houses AS h "
        . "\nLEFT JOIN #__rem_categories AS hc ON hc.iditem=h.id "
        . "\nLEFT JOIN #__rem_main_categories AS c ON c.id=hc.idcat "
        . "\nWHERE h.published='1' AND h.approved='1'  "
        . "\nAND c.published='1' $lang AND ($s) "
        . "\nGROUP BY h.id "
        . "\nORDER BY " . $sort_string
        . "\nLIMIT $pageNav->limitstart,$pageNav->limit;";
        $database->setQuery($query);

        $houses = $database->loadObjectList();

        $query = "SELECT h.*,c.id, c.parent_id, c.title, c.published, c.image,COUNT(hc.iditem) as houses, '1' as display" .
        " \n FROM  #__rem_main_categories as c
        \n LEFT JOIN #__rem_categories AS hc ON hc.idcat=c.id
        \n LEFT JOIN #__rem_houses AS h ON h.id=hc.iditem
        \n WHERE c.section='com_realestatemanager' 
        AND c.published=1 AND ({$s}) $lang 
        \n GROUP BY c.id
        \n ORDER BY c.parent_id DESC, c.ordering ";

        $database->setQuery($query);
        $cat_all = $database->loadObjectList();

        foreach ($cat_all as $k1 => $cat_item1) {
            if (PHP_realestatemanager::is_exist_curr_and_subcategory_houses($cat_all[$k1]->id)) {
                foreach ($cat_all as $cat_item2) {
                    if ($cat_item1->id == $cat_item2->parent_id) {
                        $cat_all[$k1]->houses += $cat_item2->houses;
                    }
                }
            } else
            $cat_all[$k1]->display = 0;
        }

        if (version_compare(JVERSION, '3.0', 'ge')) {
            $menu = new JTableMenu($database);
            $menu->load($Itemid);
            $params = new JRegistry;
            $params->loadString($menu->params);
        } else {
            $menu = new mosMenu($database);
            $menu->load($Itemid);
            $params = new mosParameters($menu->params);
        }

        $menu_name = set_header_name_rem($menu, $Itemid);
        // add wishlist markers ------------------------------------------
        $query = "SELECT fk_houseid FROM `#__rem_users_wishlist` " . 
        "WHERE fk_userid =" . $my->id;
        $database->setQuery($query);
        $result = $database->loadColumn();
        $params->def('wishlist', $result);
        //-----------------------------------------------------------------
        $params->def('rss_show', $realestatemanager_configuration['rss']['show']);
        $params->def('header', $menu_name);
        $params->def('pageclass_sfx', '');
        //$params->set('category_name', $category->title);
        $params->def('show_category', '1');

        // wish list
        if (($realestatemanager_configuration['wishlist']['show'])) {
            if (checkAccess_REM($realestatemanager_configuration['wishlist']['registrationlevel'],
               'RECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_add_to_wishlist', 1);
        }
    }

    if (($realestatemanager_configuration['rentstatus']['show'])) {
        if (checkAccess_REM($realestatemanager_configuration['rentrequest']['registrationlevel'],
           'RECURSE', userGID_REM($my->id), $acl)) {
            $params->def('show_rentstatus', 1);
        $params->def('show_rentrequest', 1);
    }
}

if (($realestatemanager_configuration['housestatus']['show'])) {
    if (checkAccess_REM($realestatemanager_configuration['houserequest']['registrationlevel'],
       'RECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_housestatus', 1);
    $params->def('show_houserequest', 1);
}
}
//     //add to path category name
//     PHP_realestatemanager::constructPathway($category);


if ($realestatemanager_configuration['reviews']['show']) {
    $params->def('show_reviews', 1);
    if (checkAccess_REM($realestatemanager_configuration['reviews']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_inputreviews', 1);
}
}
//***************   begin add for  Manager print pdf: button 'print PDF'    *********************
if ($realestatemanager_configuration['print_pdf']['show']) {
    $params->def('show_print_pdf', 1);
    if (checkAccess_REM($realestatemanager_configuration['print_pdf']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_print_pdf', 1);
}
}
//**************   end add for  Manager print pdf: button 'print PDF'    ******************************
//*************   begin add for  Manager print view: button 'print VIEW'    **************************
if ($realestatemanager_configuration['print_view']['show']) {
    $params->def('show_print_view', 1);
    if (checkAccess_REM($realestatemanager_configuration['print_view']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_print_view', 1);
}
}
//********************   end add for  Manager print view: button 'print VIEW'    ********************
//*******************   begin add for  Manager mail to: button 'mail to'    ************************
if ($realestatemanager_configuration['mail_to']['show']) {
    $params->def('show_mail_to', 1);
    if (checkAccess_REM($realestatemanager_configuration['mail_to']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_mail_to', 1);
}
}
//********************   end add for  Manager mail to: button 'mail to'    *************************
//**************   begin add for  Manager add_house: button 'Add house'    *********************
if ($realestatemanager_configuration['add_house']['show']) {
    $params->def('show_add_house', 1);
    if (checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_input_add_house', 1);
}
}
//*************   end add for  Manager add_house: button 'Add house'    ***********************
//***************   begin show search_option    *********************
if ($realestatemanager_configuration['search_option']['show']) {
    $params->def('search_option', 1);
    if (checkAccess_REM($realestatemanager_configuration['search_option']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('search_option_registrationlevel', 1);
}
}
//**************   end show search_option     ******************************
$params->def('sort_arr_order_direction', $sort_arr['order_direction']);
$params->def('sort_arr_order_field', $sort_arr['order_field']);

                //add for show in category picture
if ($realestatemanager_configuration['cat_pic']['show'])
    $params->def('show_cat_pic', 1);

$params->def('search_request', 1);
$params->def('show_rating', 1);
$params->def('hits', 1);
$params->def('back_button', $mainframe->getCfg('back_button'));

        // used to show table rows in alternating colours
$tabclass = array('sectiontableentry1', 'sectiontableentry2');

$params->def('minifotohigh', $realestatemanager_configuration['foto']['high']);
$params->def('minifotowidth', $realestatemanager_configuration['foto']['width']);

        // price
if ($realestatemanager_configuration['price']['show']) {
    $params->def('show_pricestatus', 1);
    if (checkAccess_REM($realestatemanager_configuration['price']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('show_pricerequest', 1);
}
}

$params->def('singlecategory01', "{loadposition com_realestatemanager_all_house_01,xhtml}");
$params->def('singlecategory02', "{loadposition com_realestatemanager_all_house_02,xhtml}");
$params->def('singlecategory03', "{loadposition com_realestatemanager_all_house_03,xhtml}");
$params->def('singlecategory04', "{loadposition com_realestatemanager_all_house_04,xhtml}");
$params->def('singlecategory05', "{loadposition com_realestatemanager_all_house_05,xhtml}");
$params->def('singlecategory06', "{loadposition com_realestatemanager_all_house_06,xhtml}");
$params->def('singlecategory07', "{loadposition com_realestatemanager_all_house_07,xhtml}");
$params->def('singlecategory08', "{loadposition com_realestatemanager_all_house_08,xhtml}");
$params->def('singlecategory09', "{loadposition com_realestatemanager_all_house_09,xhtml}");
$params->def('singlecategory10', "{loadposition com_realestatemanager_all_house_10,xhtml}");
$params->def('singlecategory11', "{loadposition com_realestatemanager_all_house_11,xhtml}");

switch ($printItem) {
    case 'pdf':
    HTML_realestatemanager::displayAllHousesPdf($houses, $params, $tabclass, $pageNav);
    break;

    case 'print':
    HTML_realestatemanager::displayAllHousePrint($houses, $params, $tabclass, $pageNav);
    break;

    default:
    HTML_realestatemanager::displayAllHouses($houses, $params, $tabclass, $pageNav, $layout);
    break;
}
}


    //this function check - is exist folders under this category 
static function is_exist_subcategory_houses($catid)
{
    global $database, $my;

    $s = getWhereUsergroupsCondition("cc");

    $query = "SELECT *, COUNT(a.id) AS numlinks FROM #__rem_main_categories AS cc"
    . "\n  JOIN #__rem_categories AS hc ON hc.idcat = cc.id"
    . "\n  JOIN #__rem_houses AS a ON a.id = hc.iditem"
    . "\n WHERE a.published='1' AND a.approved='1' AND section='com_realestatemanager' "
    . " AND cc.parent_id='" . intval($catid) . "' AND cc.published='1' AND ($s) "
    . "\n GROUP BY cc.id"
    . "\n ORDER BY cc.ordering";
    $database->setQuery($query);

    $categories = $database->loadObjectList();
    if (count($categories) != 0)
        return true;

    $query = "SELECT id "
    . "FROM #__rem_main_categories AS cc "
    . " WHERE section='com_realestatemanager' AND parent_id='" .
    intval($catid) . "' AND published='1' AND ($s) ";
    $database->setQuery($query);
    $categories = $database->loadObjectList();

    if (count($categories) == 0)
        return false;

    foreach ($categories as $k) {
        if (PHP_realestatemanager::is_exist_subcategory_houses($k->id))
            return true;
        }
        return false;
    }

    static function is_exist_curr_and_subcategory_houses($catid) {
        global $database, $my;

        $s = getWhereUsergroupsCondition("cc");

        $query = "SELECT *, COUNT(a.id) AS numlinks FROM #__rem_main_categories AS cc"
        . "\n  JOIN #__rem_categories AS hc ON hc.idcat = cc.id"
        . "\n  JOIN #__rem_houses AS a ON a.id = hc.iditem"
        . "\n WHERE a.published='1' AND a.approved='1' AND section='com_realestatemanager' "
        . " AND cc.id='" . intval($catid) . "' AND cc.published='1' AND ($s) "
        . "\n GROUP BY cc.id"
        . "\n ORDER BY cc.ordering";
        $database->setQuery($query);

        $categories = $database->loadObjectList();
        if (count($categories) != 0)
            return true;

        $query = "SELECT id "
        . "FROM #__rem_main_categories AS cc "
        . " WHERE section='com_realestatemanager' AND parent_id='" .
        intval($catid) . "' AND published='1' AND ($s) ";
        $database->setQuery($query);
        $categories = $database->loadObjectList();

        if (count($categories) == 0)
            return false;

        foreach ($categories as $k) {
            if (PHP_realestatemanager::is_exist_curr_and_subcategory_houses($k->id))
                return true;
            }
            return false;
        }

        static function listCategories($catid, $layout, $languagelocale) {
            global $mainframe, $database, $my, $acl, $langContent;
            global $mosConfig_shownoauth, $mosConfig_live_site, $mosConfig_absolute_path;
            global $cur_template, $Itemid, $realestatemanager_configuration;

            PHP_realestatemanager::addTitleAndMetaTags();

            $s = getWhereUsergroupsCondition("c");



            if (isset($langContent)) {

                $lang = $langContent;
            // $query = "SELECT lang_code FROM #__languages WHERE sef = '$lang'";
            // $database->setQuery($query);
            // $lang = $database->loadResult();
                $lang = " and ( h.language = '$lang' or h.language like 'all' or "
                ." h.language like '' or h.language like '*' or h.language is null) "
                . " AND ( c.language = '$lang' or c.language like 'all' or " 
                . " c.language like '' or c.language like '*' or c.language is null) ";
            } else {
                $lang = "";
            }

            $query = "SELECT h.*,c.id, c.parent_id, c.title, c.image,COUNT(hc.iditem) as houses, '1' as display" .
            "\n FROM  #__rem_main_categories as c " .
            "\n LEFT JOIN #__rem_categories AS hc ON hc.idcat=c.id " .
            "\n LEFT JOIN #__rem_houses AS h ON h.id=hc.iditem AND ".
            "( h.published || isnull(h.published) ) AND ( h.approved || isnull(h.approved ) )" .
            "\n WHERE c.section='com_realestatemanager' AND c.published=1 AND h.published = 1 AND h.approved = 1
            \n  $lang AND ({$s})     
            \n GROUP BY c.id ORDER BY c.parent_id DESC, c.ordering";


            $database->setQuery($query);
            $cat_all = $database->loadObjectList();
// print_r($query);print_r($cat_all); exit;
            foreach ($cat_all as $k1 => $cat_item1) {

              $cat_all[$k1]->display = PHP_realestatemanager::is_exist_curr_and_subcategory_houses($cat_all[$k1]->id);

          }

          $currentcat = new stdClass();

        // Parameters
          $menu = new JTableMenu($database);
          $menu->load($Itemid);
          $params = new mosParameters($menu->params);
          $menu_name = set_header_name_rem($menu, $Itemid);

          $params->def('header', $menu_name);
          $params->def('pageclass_sfx', '');
          $params->def('back_button', $mainframe->getCfg('back_button'));

        // page header
          $currentcat->header = $params->get('header');

        //*****   begin add for Manager Add house: button 'Add a house'
          if (($realestatemanager_configuration['add_house']['show'])) {
            $params->def('show_add_house', 1);
            if (checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'],
               'NORECURSE', userGID_REM($my->id), $acl)) {
                $params->def('show_input_add_house', 1);
        }
    }
        //*********   end add for Manager Add house: button 'Add a house'   **
        //show_button_my_houses
    if ($my->email != null) {
        $params->def('show_button_my_houses', 1);
    }

    if (checkAccess_REM($realestatemanager_configuration['rss']['registrationlevel'],
       'RECURSE', userGID_REM($my->id), $acl) &&
        $realestatemanager_configuration['rss']['show']) {
        $params->def('rss_show', 1);
}

if (checkAccess_REM($realestatemanager_configuration['ownerslist']['registrationlevel'],
   'RECURSE', userGID_REM($my->id), $acl) &&
    $realestatemanager_configuration['ownerslist']['show']) {
    $params->def('ownerslist_show', 1);
}
//***************   begin show search_option    *********************
if ($realestatemanager_configuration['search_option']['show']) {
    $params->def('search_option', 1);
    if (checkAccess_REM($realestatemanager_configuration['search_option']['registrationlevel'], 
      'NORECURSE', userGID_REM($my->id), $acl)) {
        $params->def('search_option_registrationlevel', 1);
}
}
//**************   end show search_option     ******************************        

        //add for show in category picture
if ($realestatemanager_configuration['cat_pic']['show'])
    $params->def('show_cat_pic', 1);

        // page description
$currentcat->descrip = _REALESTATE_MANAGER_DESC;

        // used to show table rows in alternating colours
$tabclass = array('sectiontableentry1', 'sectiontableentry2');

$params->def('allcategories01', "{loadposition com_realestatemanager_all_categories_01,xhtml}");
$params->def('allcategories02', "{loadposition com_realestatemanager_all_categories_02,xhtml}");
$params->def('allcategories03', "{loadposition com_realestatemanager_all_categories_03,xhtml}");
$params->def('allcategories04', "{loadposition com_realestatemanager_all_categories_04,xhtml}");
$params->def('allcategories05', "{loadposition com_realestatemanager_all_categories_05,xhtml}");
$params->def('allcategories06', "{loadposition com_realestatemanager_all_categories_06,xhtml}");
$params->def('allcategories07', "{loadposition com_realestatemanager_all_categories_07,xhtml}");
$params->def('allcategories08', "{loadposition com_realestatemanager_all_categories_08,xhtml}");
$params->def('allcategories09', "{loadposition com_realestatemanager_all_categories_09,xhtml}");
$params->def('allcategories10', "{loadposition com_realestatemanager_all_categories_10,xhtml}");


HTML_realestatemanager::showCategories($params, $cat_all, $catid, $tabclass, $currentcat, $layout);
}

static function paypal() {
    global $database, $realestatemanager_configuration;
    $operation=JRequest::getVar('operation');
    if(isset($operation) && $operation == 'success') {
        $dispatcher = JDispatcher::getInstance();
        $plugin_name = 'paypal';
        $plugin = JPluginHelper::importPlugin( 'payment',$plugin_name);
        $a = '';
        $userName = '';
        $userEmail = '';
        $html = $dispatcher->trigger('validateIPN');
        if(isset($html[0]))$html = $html[0];
        if(JRequest::getVar('payer_email','') || count($html)>2){
            $userId  = JRequest::getVar('userId');
            if(JRequest::getVar('userId','')){
                $sql = "SELECT  name,username,email FROM  `#__users` WHERE id= '".JRequest::getVar('userId')."'";
                $database->setQuery($sql);
                $result = $database->loadObjectList();
                $result = $result['0'];
                $userName = $result->name;
                $userEmail = $result->email;
            }
            if(!$userName)$userName = JRequest::getVar('first_name');
                if(!$userEmail)$userEmail = JRequest::getVar('payer_email');
                    $house_id = JRequest::getVar('houseId');
                    if($house_id){
                    if(count($html)>2){///paralel payment
                        if($html['payKey']){
                            $query = "SELECT id FROM #__rem_orders_details "
                            ."\n WHERE txn_id = '".$html['payKey']."' "
                            ."\n AND status='".$html['responseEnvelope']['ack']."'";
                            $database->setQuery($query);
                            $result = $database->loadResult();
                            if(!empty($result)){
                                JError::raiseWarning(0,_REALESTATE_MANAGER_PAYPAL_F5_ERROR);
                                return;
                            }
                        }
                        $status = $html['responseEnvelope']['ack'];
                        $payer_id = '';
                        $txn_id = $html['payKey'];
                        $txn_type = 'comission_payment';
                        $order_currency_code = JRequest::getVar('currency_code');
                        $orderId = JRequest::getVar('orderId');
                        $payer_status = '';
                        $mc_gross = 0;
                        $userEmail = $html['senderEmail'];
                        $html['En attente de paiement_reason'] = 'Receiver List:<br>________________________';
                        foreach ($html['paymentInfoList']['paymentInfo'] as $value) {
                            $mc_gross += $value['receiver']['amount'];
                            $html['En attente de paiement_reason'] .= '<br>Email:'.$value['receiver']['email']
                            .'<br>Amount:'.$value['receiver']['amount']
                            .'<br>Status:'.$value['senderTransactionStatus'];
                            if($value['senderTransactionStatus'] == 'En attente de paiement'){
                                $html['En attente de paiement_reason'] .= '<br>Reason:'.$value['En attente de paiementReason'];
                            }
                            $html['En attente de paiement_reason'] .= '<br>________________________';
                        }
                        $raw_data = serialize($html);
                    }else{
                        $status = JRequest::getVar('payment_status');
                        $payer_id = JRequest::getVar('payer_id');
                        $txn_id = JRequest::getVar('txn_id');
                        $txn_type = JRequest::getVar('txn_type');
                        $payer_status = JRequest::getVar('payer_status');
                        $mc_gross = JRequest::getVar('mc_gross');
                        $order_currency_code = JRequest::getVar('mc_currency');
                        $orderId = JRequest::getVar('orderId');
                        $raw_data = serialize($_REQUEST);
                    }
                    $sql = "SELECT order_calculated_price FROM #__rem_orders_details
                    WHERE fk_order_id='".$orderId."'
                    AND status = 'En attente de paiement'
                    ORDER BY order_date DESC";
                    $database->setQuery($sql);
                    $calculated_price = $database->loadResult();
                    $sql = "SELECT htitle FROM #__rem_houses WHERE id='".$house_id."'";
                    $database->setQuery($sql);
                    $htitle = $database->loadResult();
                    $sql = "UPDATE #__rem_orders SET order_date = now(), status='".$status."',
                    payer_id='".$payer_id."',
                    order_price='".$mc_gross."',
                    order_currency_code='".$order_currency_code."',
                    txn_id='".$txn_id."',
                    txn_type='".$txn_type."',
                    fk_user_id = '".$userId."',
                    email = '".$userEmail."',
                    name = '".$userName."',
                    order_calculated_price = '".$calculated_price."',
                    payer_status='".$payer_status."' WHERE id = '".$orderId."'";
                    $database->setQuery($sql);
                    $database->query();
                    $sql = "INSERT INTO `#__rem_orders_details`(fk_order_id,fk_user_id,email,fk_houses_htitle,
                    name,status,order_date,fk_house_id,
                    txn_type,txn_id,payer_id,payer_status,order_calculated_price,order_price,
                    order_currency_code, payment_details)
                    VALUES ('".$orderId."',
                    '".JRequest::getVar('userId')."',
                    '".$userEmail."',
                    '".$htitle."',
                    '".$userName."',
                    '".$status."',
                    now(),
                    '".$house_id."',
                    '".$txn_type."',
                    '".$txn_id."',
                    '".$payer_id."',
                    '".$payer_status."',
                    '".$calculated_price."',
                    '".$mc_gross."',
                    '".$order_currency_code."',
                    ".$database->Quote($raw_data).")";
                    $database->setQuery($sql);
                    $database->query();
                }else{
                    JError::raiseWarning(0,_REALESTATE_MANAGER_PAYPAL_ERROR);
                    return;
                }
                echo _REALESTATE_MANAGER_MESSAGE_SUCCESSFULL_PAYMENT;
            }
        } elseif(isset($_GET['operation']) && JRequest::getVar('operation') == 'cancel') {
            echo _REALESTATE_MANAGER_MESSAGE_UNSUCCESSFULL_PAYMENT;
        }
    }

    static function getMonthCal($month, $year, $id) {
      global $database, $realestatemanager_configuration;
      $query = "SELECT rent_from, rent_until, rent_return FROM #__rem_rent WHERE fk_houseid='$id' ORDER BY rent_from";
      $database->setQuery($query);
      $calenDate = $database->loadObjectList();        
      $skip = date("w", mktime(0, 0, 0, $month, 1, $year)) - 1;
      if ($skip < 0){
        $skip = 6;
    }
    $daysInMonth = date("t", mktime(0, 0, 0, $month, 1, $year));      
    /*******************************get only rent days*****************************/  
    $rentDataArr = array();
    $i=0;
    foreach ($calenDate as &$value) {
        if(!($value->rent_return)){
          if(isset($calenDate[($i+1)]) && $calenDate[($i+1)]->rent_from == $calenDate[$i]->rent_until){
            $calenDate[($i+1)]->rent_from = $calenDate[$i]->rent_from;
            unset($calenDate[$i]);
            $i++;
            continue;
        }   
        array_push($rentDataArr, $value);
    }$i++;
}
$calenDate = $rentDataArr;       
$calendar = '';
$day = 1;
$smonth = PHP_realestatemanager::getMonth($month);
$calendar = '<table class="rem_tableC" style="border-collapse: separate;'.
' border-spacing: 2px;text-align:center"><tr class="year"><th colspan = "7">' .
$smonth . ' ' . $year . '</th></tr><tr class="days"><th>' . JText::_('MON') .
'</th><th>' . JText::_('TUE') . '</th><th>' . JText::_('WED') . '</th><th>' .
JText::_('THU') . '</th><th>' . JText::_('FRI') . '</th><th>' . JText::_('SAT') .
'</th><th>' . JText::_('SUN') . '</th></tr>';
for ($i = 0; $i < 6; $i++) {
    $calendar .= '<tr>';
    for ($j = 0; $j < 7; $j++) {
      if (($skip > 0) or ($day > $daysInMonth)){
        $calendar .= '<td> &nbsp; </td>';
        $skip--;
    }else{ 
        $isAvilable = getAvilableRM($calenDate,$month,$year,$realestatemanager_configuration,$day);
        $calendar .= '<td class="'.$isAvilable.'">' . $day . '</td>';
        $day++;
    }
}
$calendar .= '</tr>';
}
$calendar .= '</table>';

return $calendar;
}

static function getCalendarPrice($month, $year, $id){
  global $database;
  $query = "SELECT * FROM `#__rem_rent_sal` " .
  " WHERE (`fk_houseid`='$id') and (`yearW`='$year') and (`monthW`='$month')";
  $database->setQuery($query);
  $calenWeeks = $database->loadObjectList();
  if (!empty($calenWeeks)){
    $calenWeek = $calenWeeks[0];
    $calendar = "";
    $calendar = '<table style="text-align:left">';
    $calendar .= '<tr><td><b>' . _REALESTATE_MANAGER_LABEL_CALENDAR_WEEK . '<b></td></tr>';
    $calendar .= '<tr><td>' . str_replace("\n", "<br>\n", $calenWeek->week) . '</td></tr>';
    $calendar .= '<tr><td><b>' . _REALESTATE_MANAGER_LABEL_CALENDAR_WEEKEND . '<b></td></tr>';
    $calendar .= '<tr><td>' . str_replace("\n", "<br>\n", $calenWeek->weekend) . '</td></tr>';
    $calendar .= '<tr><td><b>' . _REALESTATE_MANAGER_LABEL_CALENDAR_MIDWEEK . '</b></td></tr>';
    $calendar .= '<tr><td><span>' . str_replace("\n", "<br>\n", $calenWeek->midweek) . '<span></td></tr>';
    $calendar .= '</table>';
    return $calendar;
}
}

static function getCalendar($month, $year, $id){
  $month = (int) $month;
  $year = (int) $year;

  if ($month == 1)
  {
      $month1 = 12;
      $year1 = $year - 1;
  } else
  {
      $month1 = $month - 1;
      $year1 = $year;
  }

  if ($month == 12)
  {
      $month2 = 1;
      $month3 = 2;
      $year2 = $year3 = $year + 1;
  } else
  {
      $month2 = $month + 1;
      $month3 = $month + 2;
      $year2 =$year3 = $year;
  }
  if($month3 > 12){
    $month3 = $month3 - 12;
    $year3 = $year + 1;
}
$calendar = new stdClass();
$calendar->tab1 = PHP_realestatemanager::getMonthCal($month1, $year1, $id);
$calendar->tab2 = PHP_realestatemanager::getMonthCal($month, $year, $id);
$calendar->tab3 = PHP_realestatemanager::getMonthCal($month2, $year2, $id);
$calendar->tab4 = PHP_realestatemanager::getMonthCal($month3, $year3, $id);
$calendar->tab21 = PHP_realestatemanager::getCalendarPrice($month1, $year1, $id);
$calendar->tab22 = PHP_realestatemanager::getCalendarPrice($month, $year, $id);
$calendar->tab23 = PHP_realestatemanager::getCalendarPrice($month2, $year2, $id);
$calendar->tab24 = PHP_realestatemanager::getCalendarPrice($month3, $year3, $id);

return $calendar;
}

static function addHouseToWishlist() {
    global $database, $my;

    $owner_id = $my->id;
    $houseid = JFactory::getApplication()->input->getInt('id', '');
    if ($houseid != '') {
        $query = "SELECT id FROM `#__rem_users_wishlist`
        WHERE fk_houseid = $houseid AND fk_userid=$owner_id";
        $database->setQuery($query);
        $result = $database->loadResult();

        if ($result) {
            $message = _REALESTATE_MANAGER_LABEL_WISHLIST_ALREDY_IN;
            echo new JResponseJson(true, $message);
            jexit();
        }

        $query = "INSERT INTO `#__rem_users_wishlist` (fk_houseid,fk_userid) VALUES ($houseid, $owner_id)";
        $database->setQuery($query);
        $database->query();
        $message = _REALESTATE_MANAGER_LABEL_WISHLIST_ADDED; 
    } else {
        $message = 'Error adding to wish list!'; // _BOOKLIBRARY_LABEL_ERRORWISHLIST;
    }
    echo new JResponseJson(true, $message);
    jexit();
}

static function removeHouseFromWishlist() {
    global $database, $my;

    $owner_id = $my->id;
    $houseid = JFactory::getApplication()->input->getInt('id', '');
    if ($houseid != '') {
        $query = "DELETE FROM `#__rem_users_wishlist` 
        WHERE fk_houseid = $houseid AND fk_userid=$owner_id";
        $database->setQuery($query);
        $database->query();
        $message = _REALESTATE_MANAGER_LABEL_WISHLIST_REMOVED; 
    } else {
        $message = 'Error to delete from wish list'; 
    }
    echo new JResponseJson(true, $message );
    jexit();
}

static function showWishlist($option, $task) {
    global $mainframe, $database, $my, $acl, $realestatemanager_configuration, $Itemid, $limit;

    $owner_id = $my->id;
    if(empty($owner_id) && $task == 'wishlist'){
        // mosRedirect("index.php?", "Please login");
        JFactory::getApplication()->enqueueMessage('Please login');
        return;
    }
    PHP_realestatemanager::addTitleAndMetaTags();
        //parameters
    if (version_compare(JVERSION, '3.0', 'ge')) {
        $menu = new JTableMenu($database);
        $menu->load($Itemid);
        $params = new JRegistry;
        $params->loadString($menu->params);
    } else {
        $menu = new mosMenu($database);
        $menu->load($Itemid);
        $params = new mosParameters($menu->params);
    }
    $database->setQuery("SELECT id FROM `#__menu` WHERE link='index.php?option=com_realestatemanager'");
    if ($database->loadResult() != $Itemid){
      $params->def('wrongitemid', '1');
  }
    // $limit = $realestatemanager_configuration['page']['items'];
  $limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
  if (!$params->get('wrongitemid')){
    $pathway = sefRelToAbs('index.php?option=' . $option . '&amp;task=wishlist&amp;Itemid=' . $Itemid);
    $path_way = $mainframe->getPathway();
    $path_way->addItem(_REALESTATE_MANAGER_LABEL_SEARCH, $pathway);
}
$menu_name = set_header_name_rem($menu, $Itemid);
$params->def('header', $menu_name);

$query = "SELECT COUNT(id) 
FROM `#__rem_users_wishlist` \n " . "
WHERE fk_userid=$owner_id";
$database->setQuery($query);
$total = $database->loadResult();
    $pageNav = new JPagination($total, $limitstart, $limit); // for J 1.6

    $selectstring = "SELECT a.*, GROUP_CONCAT(cc.title SEPARATOR ', ') AS category, l.id AS rentid, l.rent_from AS rent_from, l.rent_return AS rent_return, l.rent_until AS rent_until, u.name AS editor, l.user_name AS user_name,l.user_email AS user_email, l.user_mailing AS user_mailing, cc.title AS category_titel, cc.id AS idcat
    FROM #__rem_houses AS a " .
    "\n LEFT JOIN #__rem_categories AS vc ON vc.iditem = a.id " .
    "\n LEFT JOIN #__rem_main_categories AS cc ON cc.id = vc.idcat " .
    "\n LEFT JOIN #__rem_rent AS l ON a.fk_rentid = l.id " .
    "\n LEFT JOIN #__users AS u ON u.id = a.checked_out " .
    "\n WHERE a.id in (SELECT fk_houseid FROM " . 
    "\n #__rem_users_wishlist  WHERE fk_userid=$owner_id)" .
    "\n GROUP BY a.id" .
    "\n LIMIT " . $pageNav->limitstart . "," . $pageNav->limit . ";";
    $database->setQuery($selectstring);
    $houses = $database->loadObjectList();

    if (!$houses) {
        // mosRedirect("index.php?", "Wishlist is empty!");
        JFactory::getApplication()->enqueueMessage('Wishlist is empty!');
        return;
    }

    $params->def('wishlist01', "{loadposition com_realestatemanager_my_house_01,xhtml}");
    $params->def('wishlist02', "{loadposition com_realestatemanager_my_house_02,xhtml}");
    $params->def('wishlist03', "{loadposition com_realestatemanager_my_house_03,xhtml}");
    $params->def('wishlist04', "{loadposition com_realestatemanager_my_house_04,xhtml}");
    $params->def('wishlist05', "{loadposition com_realestatemanager_my_house_05,xhtml}");
    HTML_realestatemanager::showWishlist($houses, $params, $pageNav, $option);
}
}