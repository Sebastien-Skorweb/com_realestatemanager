<?php
if (!defined('_VALID_MOS') && !defined('_JEXEC'))
  die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 *
 * @package  RealEstateManager
 * @copyright 2012 Andrey Kvasnevskiy-OrdaSoft(akbet@mail.ru); Rob de Cleen(rob@decleen.com) 
 * Homepage: http://www.ordasoft.com
 * @version: 3.9 Pro
 *
 * */
jimport( 'joomla.plugin.helper' );
if (version_compare(JVERSION, "3.0.0", "lt"))
  jimport('joomla.html.toolbar');

require_once($mosConfig_absolute_path . "/components/com_realestatemanager/functions.php");
require_once($mosConfig_absolute_path . "/components/com_realestatemanager/realestatemanager.php");
//require_once($mosConfig_absolute_path."/administrator/components/com_realestatemanager/menubar_ext.php");

$GLOBALS['mosConfig_live_site'] = $mosConfig_live_site = JURI::root(); 
$GLOBALS['mosConfig_absolute_path'] = $mosConfig_absolute_path; //for 1.6
$GLOBALS['mainframe'] = $mainframe = JFactory::getApplication();

$templateDir = JPATH_THEMES . DS . JFactory::getApplication()->getTemplate() . DS;
$GLOBALS['templateDir'] = $templateDir;
$GLOBALS['doc'] = $doc = JFactory::getDocument();
$g_item_count = 0;

// add stylesheet
$doc->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
$doc->addStyleSheet('//maxcdn.bootstrapcdn.com/bootstrap/2.3.2/css/bootstrap.min.css');
$doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/animate.css');
//$doc->addScript('//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js');
$doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/jQuerREL-ui.css');
$doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/realestatemanager.css');
$doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/lightbox/css/lightbox.css');
$doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/TABS/tabcontent.css');

// add js
$doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/includes/functions.js');

if(checkJavaScriptIncludedRE("jQuerREL-1.2.6.js") === false ) {
  $doc->addScript(JURI::root(true) . '/components/com_realestatemanager/lightbox/js/jQuerREL-1.2.6.js');
} 

$doc->addScriptDeclaration("jQuerREL=jQuerREL.noConflict();");

if(checkJavaScriptIncludedRE("jQuerREL-ui.js") === false ) {
  $doc->addScript(JURI::root(true) . '/components/com_realestatemanager/includes/jQuerREL-ui.js');
}

$doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/includes/wishlist.js');
$doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/lightbox/js/lightbox-2.6.min.js');
$doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/includes/jquery.raty.js');
$doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/TABS/tabcontent.js');


class HTML_realestatemanager {

  static function editHouse($option, & $row, & $clist, & $rating, & $delete_edoc, $videos,
    & $youtube,
    & $tracks, & $listing_status_list, 
    & $property_type_list, & $listing_type_list, & $house_photo,&$house_temp_photos, & $house_photos, & $house_rent_sal,
    & $house_feature, & $currency, & $languages, & $extra_list, & $currency_spacial_price, & $associateArray) {
    global $realestatemanager_configuration;
    global $my, $mosConfig_live_site, $mainframe, $Itemid, $doc;

    if($realestatemanager_configuration['special_price']['show']){
      $switchTranslateDayNight = _REALESTATE_MANAGER_RENT_SPECIAL_PRICE_PER_DAY;       
    }else{
      $switchTranslateDayNight = _REALESTATE_MANAGER_RENT_SPECIAL_PRICE_PER_NIGHT;    
    }

    $acl = JFactory::getACL();

    $allowed_exts_file = explode(",", $realestatemanager_configuration['allowed_exts']);
    foreach ($allowed_exts_file as $key => $allowed_ext_file) {
      $allowed_exts_file[$key] = strtolower($allowed_ext_file);
    }
    $allowed_exts = explode(",", $realestatemanager_configuration['allowed_exts_img']);
    foreach ($allowed_exts as $key => $allowed_ext) {
      $allowed_exts[$key] = strtolower($allowed_ext);
    }
    ?>

    <!---------------------------------Start AJAX load track------------------------------>

    <script language="javascript" type="text/javascript">
      var request = null;
      var tid=1;
      function createRequest_track() {
        if (request != null)
          return;
        try {
         request = new XMLHttpRequest();
       } catch (trymicrosoft) {
        try {
         request = new ActiveXObject("Msxml2.XMLHTTP");
       } catch (othermicrosoft) {
        try {
          request = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (failed) {
          request = null;
        }
      }
    }
    if (request == null) 
      alert(" :-( ___ Error creating request object! ");
  }

  function testInsert_track(id1,upload){
    for(var i=1; i< t_counter; i++){
      if(upload.id != ('new_upload_track'+i) && 
        document.getElementById('new_upload_track'+i).value == upload.value){
        return false;
    }
  }
  return true;   
}

function refreshRandNumber_track(id1,upload){
  id=id1;
  if(testInsert_track(id1,upload)){
    createRequest_track();
    var url = "<?php echo $mosConfig_live_site . "/administrator/index.php?option=$option&task=checkFile&format=raw";
    ?>&file="+upload.value+"&path=<?php
    echo str_replace("\\", "/", $mosConfig_live_site) . '/components/com_realestatemanager/media/track/'?>";
    try{
    request.onreadystatechange = updateRandNumber_track;
    request.open("GET", url,true);
    request.send(null);
  }catch (e)
  {
    alert(e);
  }
}
else
{
  alert("You alredy select this track file");
  upload.value="";
}
}

function updateRandNumber_track() {
if (request.readyState == 4) {
document.getElementById("randNumTrack"+tid).innerHTML = request.responseText;
}
}
</script>

<!-------------------------------- END Ajax load track---------------------------------->

<!-------------------------------- START Ajax load video---------------------------------->


<script language="javascript" type="text/javascript">

 var request = null;
 var vid=1;

 function createRequest_video(){
  if (request != null)
    return;
  try {
   request = new XMLHttpRequest();
 } catch (trymicrosoft) {
  try {
    request = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (othermicrosoft) {
    try {
      request = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (failed) {
      request = null;
    }
  }
}
if (request == null) 
  alert(" :-( ___ Error creating request object! ");
}

function testInsertVideo(id1,upload){
  for(var i=1 ;i< v_counter; i++){
    if(upload.id != ('new_upload_video'+i) && 
      document.getElementById('new_upload_video'+i).value == upload.value)
    {
      return false;
    }
  }
  return true;   
}

function refreshRandNumber_video(id1,upload){
  id=id1;
  if(testInsertVideo(id1,upload)){
    createRequest_video();
    var url = "<?php echo $mosConfig_live_site . "/administrator/index.php?option=$option&task=checkFile&format=raw";
    ?>&file="+upload.value+"&path=<?php
    echo str_replace("\\", "/", $mosConfig_live_site) . '/components/com_realestatemanager/media/video/' ?>";
    try{
    request.onreadystatechange = updateRandNumber_video;
    request.open("GET",url,true);
    request.send(null);
  }catch (e)
  {
    alert(e);
  }
}
else
{
  alert("You alredy select this video file");
  upload.value="";
}
}

function updateRandNumber_video() {
if (request.readyState == 4) {
document.getElementById("randNumVideo"+vid).innerHTML = request.responseText;
}
}
</script>


<!-------------------------------- END Ajax load video---------------------------------->


<script language="javascript" type="text/javascript">
  function changeButtomName() {
    document.getElementById('v_add').value = "<?php echo _REALESTATE_MANAGER_LABEL_VIDEO_ADD_ALTERNATIVE_VIDEO ?>";  
  }

  var v_counter=0;
  function new_videos(){
    div = document.getElementById("v_items");
    button = document.getElementById("v_add");
    v_counter++;
    newitem='<div class="vm_video_block">'+
    '<span class="rem_col_url">'+
    '<strong>' + 
    "<?php echo _REALESTATE_MANAGER_LABEL_VIDEO_UPLOAD ?>"+v_counter+
    ': </strong>'+
    '</span>'+
    '<span>'+
    '<input type="file"'+
    'onClick="document.save_add.new_upload_video_url'+
    v_counter+'" value =""'+
    ' onChange="refreshRandNumber_video('+v_counter+',this);"'+
    ' name="new_upload_video'+v_counter+'" id="new_upload_video'+v_counter+
    '" value="" size="45">'+
    '<span id="randNumVideo'+v_counter+'" style="color:red;"></span>'+
    '</span>'+
    '</div>'+
    '<div><span style="text-align:center">OR</span></div>';
    newnode = document.createElement("span");
    newnode.innerHTML = newitem;
    div.insertBefore(newnode,button);

    newitem = '<div>'+
    '<span class="rem_col_url">'+
    '<strong>'+
    "<?php echo _REALESTATE_MANAGER_LABEL_VIDEO_UPLOAD_URL; ?>" +v_counter+
    ': </strong>'+
    '</span>'+
    '<span>'+
    '<input type="text"'+
    ' name="new_upload_video_url'+v_counter+'"'+ 
    ' id="new_upload_video_url'+v_counter+'" value="" size="45">'+
    '</span>'+
    '</div>'+
    '<div><span>OR</span></div>';
    newnode = document.createElement("span");
    newnode.innerHTML = newitem;
    div.insertBefore(newnode,button);

    newitem = '<div>'+
    '<span class="rem_col_url">'+
    '<strong>'+
    "<?php echo _REALESTATE_MANAGER_LABEL_VIDEO_UPLOAD_YOUTUBE_CODE; ?>" + 
    ': </strong>'+
    '</span>'+
    '<span>'+
    '<input type="text"'+
    ' name="new_upload_video_youtube_code'+v_counter+'"'+
    ' id="new_upload_video_youtube_code'+v_counter+'" value="" size="45">'+
    '</span>'+
    '</div>'+
    '<?php echo _REALESTATE_MANAGER_LABEL_PRIOTITY; ?>'
    newnode=document.createElement("span");
    newnode.innerHTML=newitem;
    div.insertBefore(newnode,button);
    var allowed_files = 5;
    if(v_counter + <?php echo count($videos); ?> >= allowed_files) {
      button.setAttribute("style","display:none");
    }
    changeButtomName();
  }

  var t_counter=0;
  function new_tracks(){
    div = document.getElementById("t_items");
    button = document.getElementById("t_add");
    t_counter++;
    newitem = '<div class="rem_video_block">'+
    '<span class="rem_col_url">'+
    '<strong>'+
    "<?php echo _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD ?>"+t_counter+
    ': </strong></span>'+
    '<span>'+
    '<input type="file"'+
    ' onClick="document.save_add.new_upload_track'+t_counter+'" value =""'+
    ' onChange="refreshRandNumber_track('+t_counter+',this);"'+
    ' name="new_upload_track'+t_counter+'"'+
    ' id="new_upload_track'+t_counter+'" value="" size="45">'+
    '<span id="randNumTrack'+t_counter+'" style="color:red;"></span>'+
    '</span>'+
    '</div>'+
    '<div><span> OR </span></div>';
    newnode = document.createElement("span");
    newnode.innerHTML = newitem;
    div.insertBefore(newnode,button);

    newitem = '<div>'+
    '<span class="rem_col_url">'+
    '<strong>'+
    "<?php echo _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD_URL; ?>"+t_counter+
    ': </strong></span>'+
    '<span>'+
    '<input type="text"'+
    ' name="new_upload_track_url'+t_counter+'"'+
    ' id="new_upload_track_url'+t_counter+'" value="" size="45">'+
    '</span>'+
    '</div><br/>';
    newnode = document.createElement("span");
    newnode.innerHTML=newitem;
    div.insertBefore(newnode,button);

    newitem = '<div>'+
    '<span class="rem_col_url">'+
    '<strong>'+
    "<?php echo _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD_KIND; ?>"+t_counter+
    ':</strong>'+
    '</span>'+
    '<span>'+
    '<input type="text"'+
    ' name="new_upload_track_kind'+t_counter+'"'+
    ' id="new_upload_track_kind'+t_counter+'" value="" size="45">'+
    '</span>'+
    '</div><br/>';
    newnode = document.createElement("span");
    newnode.innerHTML=newitem;
    div.insertBefore(newnode,button);
    
    newitem = '<div>'+
    '<span class="rem_col_url">'+
    '<strong>'+
    "<?php echo _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD_SCRLANG; ?>"+t_counter+
    ':</strong>'+
    '</span>'+
    '<span>'+
    '<input type="text"'+
    ' name="new_upload_track_scrlang'+t_counter+'"'+
    ' id="new_upload_track_scrlang'+t_counter+'" value="" size="45">'+
    '</span>'+
    '</div><br/>';
    newnode = document.createElement("span");
    newnode.innerHTML = newitem;
    div.insertBefore(newnode,button);

    newitem = '<div>'+
    '<span class="rem_col_url">'+
    '<strong>'+
    "<?php echo _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD_LABEL; ?>"+t_counter+
    ':</strong>'+
    '</span>'+
    '<span>'+
    '<input type="text"'+
    ' name="new_upload_track_label'+t_counter+'"'+
    ' id="new_upload_track_label'+t_counter+'" value="" size="45">'+
    '</span>'+
    '</div><br/>';
    newnode = document.createElement("span");
    newnode.innerHTML=newitem;
    div.insertBefore(newnode,button);
  }
</script>
<script language="javascript" type="text/javascript">
  jQuerREL(document).ready(function () {
    jQuerREL('input,textarea').focus(function(){
      jQuerREL(this).data('placeholder',jQuerREL(this).attr('placeholder'))
      jQuerREL(this).attr('placeholder','')
      jQuerREL(this).css('color','#a3a3a3');
      jQuerREL(this).css('border-color','#ddd');
    });
    jQuerREL('input,textarea').blur(function(){
      jQuerREL(this).attr('placeholder',jQuerREL(this).data('placeholder'));
    });
  });
</script>
<script language="javascript" type="text/javascript">
  var availableExt = Array();
  var k=0;
  <?php foreach ($allowed_exts as $N_A){ ?>
   availableExt[k]= '<?php echo strtolower($N_A); ?>';
   k++;
   <?php } ?>
   var availableExtFile = Array();
   var l=0;
   <?php foreach ($allowed_exts_file as $N_A_file){ ?>
     availableExtFile[l]= '<?php echo strtolower($N_A_file); ?>';
     l++;
     <?php } ?>
     function findPosY(obj) {
      var curtop = 0;
      if (obj.offsetParent) {
        while (1) {
          curtop+=obj.offsetTop;
          if (!obj.offsetParent) {
            break;
          }
          obj=obj.offsetParent;
        }
      } else if (obj.y) {
        curtop+=obj.y;
      }
      return curtop-20;
    }
    function trim(string){
      return string.replace(/(^\s+)|(\s+$)/g, "");
    }
    function isValidNumber(str){
      myregexp = new RegExp("^[0-9]*$");
      mymatch = myregexp.exec(str);
      if(str == "")
        return true;
      if(mymatch != null)
        return true;
      return false;
    }
    function isValidPrice(str){
      myregexp = new RegExp("^[0-9]*\.{0,1}[0-9]*$");
      mymatch = myregexp.exec(str);
      if(str == "")
        return true;
      if(mymatch != null)
        return true;
      return false;
    }
    function submitbutton(pressbutton) {
      var form = document.save_add;

      if (pressbutton == 'submit2') {
        var fileUrl = form.image_link.value,
        parts, ext = ( parts = fileUrl.split("/").pop().split(".") ).length > 1 ? parts.pop().toLowerCase() : "";
        if(form.edoc_file != undefined){
          var fileUrl2 = form.edoc_file.value,
          parts2, ext2 = ( parts2 = fileUrl2.split("/").pop().split(".") ).length > 1 ? parts2.pop().toLowerCase() : "";
        }
        var post_max_size = <?php echo return_bytes(ini_get('post_max_size')) ?>;
        var upl_max_fsize = <?php echo return_bytes(ini_get('upload_max_filesize')) ?>;
        var file_upl = <?php echo ini_get('file_uploads') ?>;
        var total_file_size = 0;
        if (trim(form.houseid.value) == '') {
          window.scrollTo(0,findPosY(document.getElementById('houseid_label')));
          document.getElementById('houseid').placeholder = 
          "<?php echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_EDIT_HOUSEID_CHECK; ?>";
          document.getElementById('houseid').style.borderColor = "#FF0000";
          document.getElementById('houseid').style.color = "#FF0000";
          return;
        } else if (form.catid.value == ''){
          window.scrollTo(0,findPosY(document.getElementById('category_label')));
          document.getElementById('alert_category').innerHTML =
          "<?php echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_EDIT_CATEGORY; ?>";
          document.getElementById('alert_category').style.color = "#FF0000";
          return;
        } else if (form.htitle.value == ''){
          window.scrollTo(0,findPosY(document.getElementById('title_label')));
          document.getElementById('alert_title').placeholder =
          "<?php echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_EDIT_TITLE; ?>";
          document.getElementById('alert_title').style.borderColor = "#FF0000";
          document.getElementById('alert_title').style.color = "#FF0000";
          return;
        } else if (form.hlocation.value == ''){
          window.scrollTo(0,findPosY(document.getElementById('hlocation')));
          document.getElementById('hlocation').placeholder =
          "<?php echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_EDIT_ADDRESS; ?>";
          document.getElementById('hlocation').style.borderColor = "#FF0000";
          document.getElementById('hlocation').style.color = "#FF0000";
          return;
        } else if (<?php echo(count($house_photo));?> < 2 && form.image_link.value == ''){
          window.scrollTo(0,findPosY(document.getElementById('image_link_alert')));
          document.getElementById('image_link_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_LABEL_PICTURE_URL_UPLOAD; ?>";
          document.getElementById('image_link_alert').style.color = "#FF0000";
          return;
        } else if (form.image_link.value != '' && (jQuerREL.inArray(ext, availableExt) == -1)){
          window.scrollTo(0,findPosY(document.getElementById('image_link_alert')));
          document.getElementById('image_link_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_LABEL_PICTURE_FILE_NOT_ALLOWED; ?>";
          document.getElementById('image_link_alert').style.color = "#FF0000";
          return;
        } else if (form.edoc_file != undefined && (form.edoc_file.value != ''  &&  jQuerREL.inArray(ext2, availableExtFile) == -1)){
          window.scrollTo(0,findPosY(document.getElementById('rooms_alert')));
          document.getElementById('alert_edoc').innerHTML =
          "<?php echo _REALESTATE_MANAGER_LABEL_PICTURE_FILE_NOT_ALLOWED; ?>";
          document.getElementById('alert_edoc').style.color = "#FF0000";
          return;
        } else if (form.price.value == '' || form.price.value == 0 || !isValidPrice(form.price.value)){
          window.scrollTo(0,findPosY(document.getElementById('price_alert')));
          document.getElementById('price_alert_warning').innerHTML =
          "<?php echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_EDIT_PRICE; ?>";
          document.getElementById('price_alert_warning').style.color = "red";
          document.getElementById('price').style.borderColor = "#FF0000";
          document.getElementById('price').style.color = "#FF0000";
          return;
        } else if (form.house_size.value == '' || !isValidNumber(form.house_size.value)){
          window.scrollTo(0,findPosY(document.getElementById('rooms_alert')));
          document.getElementById('house_size_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_BUILD_HOUSE_SIZE; ?>";
          document.getElementById('house_size_alert').style.color = "red";
          document.getElementById('house_size').style.borderColor = "#FF0000";
          document.getElementById('house_size').style.color = "#FF0000";
          return;
        } else if (form.rooms.value == '' || !isValidNumber(form.rooms.value)){
          window.scrollTo(0,findPosY(document.getElementById('rooms_alert')));
          document.getElementById('rooms_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_ROOMS; ?>";
          document.getElementById('rooms_alert').style.color = "red";
          document.getElementById('rooms').style.borderColor = "#FF0000";
          document.getElementById('rooms').style.color = "#FF0000";
          return;
        } else if (form.bedrooms.value == '' || !isValidNumber(form.bedrooms.value)){
          window.scrollTo(0,findPosY(document.getElementById('rooms_alert')));
          document.getElementById('bedrooms_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_BEDROOMS; ?>";
          document.getElementById('bedrooms_alert').style.color = "red";
          document.getElementById('bedrooms').style.borderColor = "#FF0000";
          document.getElementById('bedrooms').style.color = "#FF0000";
          return;
        } else if (form.year.value == ''){
          window.scrollTo(0,findPosY(document.getElementById('rooms_alert')));
          document.getElementById('alert_year').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_BUILD_YEAR; ?>";
          document.getElementById('alert_year').style.color = "red";
          document.getElementById('alert_year').style.color = "#FF0000";
          return;
        } else if (document.getElementById('owneremail').value == ''){
          window.scrollTo(0,findPosY(document.getElementById('owneremail_alert')));
          document.getElementById('owneremail_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_EMAIL; ?>";
          document.getElementById('owneremail_alert').style.color = "red";
          document.getElementById('owneremail').style.borderColor = "#FF0000";
          document.getElementById('owneremail').style.color = "#FF0000";
          return;
        } else if (!isValidNumber(form.bathrooms.value)){
          window.scrollTo(0,findPosY(document.getElementById('rooms_alert')));
          document.getElementById('bathrooms_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_INVALID_NUMBER; ?>";
          document.getElementById('bathrooms_alert').style.color = "red";
          document.getElementById('bathrooms').style.color = "#FF0000";
          return;
        } else if (!isValidNumber(form.lot_size.value)){
          window.scrollTo(0,findPosY(document.getElementById('hzipcode')));
          document.getElementById('lot_size_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_INVALID_NUMBER; ?>";
          document.getElementById('lot_size_alert').style.color = "red";
          document.getElementById('lot_size').style.color = "#FF0000";
          return;
        } else if (!isValidNumber(form.garages.value)){
          window.scrollTo(0,findPosY(document.getElementById('rooms_alert')));
          document.getElementById('garages_alert').innerHTML =
          "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_INVALID_NUMBER; ?>";
          document.getElementById('garages_alert').style.color = "red";
          document.getElementById('garages').style.color = "#FF0000";
          return;
        }else if(form.new_upload_track_url1){
          for (i = 1;document.getElementById('new_upload_track_url'+i); i++) {
            if(document.getElementById('new_upload_track'+i).value != '' 
              || document.getElementById('new_upload_track_url'+i).value != ''){
              if(document.getElementById('new_upload_track_kind'+i).value == ''){
                window.scrollTo(0,findPosY(document.getElementById('new_upload_track_kind'+i))-100);
                document.getElementById('new_upload_track_kind'+i).placeholder = "<?php
                echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_TRACK_KIND; ?>";
                document.getElementById('new_upload_track_kind'+i).style.borderColor = "#FF0000";
                document.getElementById('new_upload_track_kind'+i).style.color = "#FF0000";
                return;
              }else if(document.getElementById('new_upload_track_scrlang'+i).value == ''){
              window.scrollTo(0,findPosY(document.getElementById('new_upload_track_scrlang'+i))-100);
              document.getElementById('new_upload_track_scrlang'+i).placeholder = "<?php
              echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_TRACK_LANGUAGE; ?>";
              document.getElementById('new_upload_track_scrlang'+i).style.borderColor = "#FF0000";
              document.getElementById('new_upload_track_scrlang'+i).style.color = "#FF0000";
              return;
            }else if(document.getElementById('new_upload_track_label'+i).value == ''){
            window.scrollTo(0,findPosY(document.getElementById('new_upload_track_label'+i))-100);
            document.getElementById('new_upload_track_label'+i).placeholder = "<?php
            echo _REALESTATE_MANAGER_ADMIN_INFOTEXT_JS_TRACK_TITLE; ?>";
            document.getElementById('new_upload_track_label'+i).style.borderColor = "#FF0000";
            document.getElementById('new_upload_track_label'+i).style.color = "#FF0000";
            return;
          }
        }
      }
    }

    for (i = 1;document.getElementById('new_upload_video'+i); i++){
    if(document.getElementById('new_upload_video'+i).files.length){
    if(document.getElementById('new_upload_video'+i).value != ''){
    total_file_size += document.getElementById('new_upload_video'+i).files[0].size;
    if(!file_upl){
    window.scrollTo(0,findPosY(document.getElementById('new_upload_video'+i))-100);
    document.getElementById('error_video').innerHTML = "<?php
    echo _REALESTATE_MANAGER_SETTINGS_VIDEO_ERROR_UPLOAD_OFF; ?>";
    document.getElementById('new_upload_video'+i).style.borderColor = "#FF0000";
    document.getElementById('new_upload_video'+i).style.color = "#FF0000";
    document.getElementById('error_video').style.color = "#FF0000";
    return;
  }
  if(document.getElementById('new_upload_video'+i).files[0].size >= post_max_size){
  window.scrollTo(0,findPosY(document.getElementById('new_upload_video'+i))-100);
  document.getElementById('error_video').innerHTML = "<?php
  echo _REALESTATE_MANAGER_SETTINGS_VIDEO_ERROR_POST_MAX_SIZE; ?>";
  document.getElementById('new_upload_video'+i).style.borderColor = "#FF0000";
  document.getElementById('new_upload_video'+i).style.color = "#FF0000";
  document.getElementById('error_video').style.color = "#FF0000";
  return;
}
if(document.getElementById('new_upload_video'+i).files[0].size >= upl_max_fsize){
window.scrollTo(0,findPosY(document.getElementById('new_upload_video'+i))-100);
document.getElementById('error_video').innerHTML = "<?php
echo _REALESTATE_MANAGER_SETTINGS_VIDEO_ERROR_UPLOAD_MAX_SIZE; ?>";
document.getElementById('new_upload_video'+i).style.borderColor = "#FF0000";
document.getElementById('new_upload_video'+i).style.color = "#FF0000";
document.getElementById('error_video').style.color = "#FF0000";
return;
}
}
}
}

if(total_file_size >= post_max_size){
if(document.getElementById('error_video')){
window.scrollTo(0,findPosY(document.getElementById('error_video'))-100);
document.getElementById('error_video').innerHTML = "<?php
echo JText::_('_REALESTATE_MANAGER_SETTINGS_VIDEO_ERROR_POST_MAX_SIZE'); ?>";
document.getElementById('error_video').style.borderColor = "#FF0000";
document.getElementById('error_video').style.color = "#FF0000";
document.getElementById('error_video').style.color = "#FF0000";
return;
}
}

}
form.submit();
}
</script>
<?php
if ($option != 'com_realestatemanager') {
  $form_action = "index.php?option=" . $option . 
  "&task=save_add&is_show_data=1&Itemid=" . $Itemid ;
}
else
  $form_action = "index.php?option=" . $option . "&task=save_add&Itemid=" . $Itemid;
?>
<form action="<?php echo sefRelToAbs($form_action); ?>" method="post" name="save_add" 
  id="save_add" enctype="multipart/form-data">

  <div class="admin_table_47">

    <div class="row_add_house" >
      <?php if (!isset($my->email)) : ?>
        <div class="alert alert-error"  >
          <button type="button" class="close" data-dismiss="alert">×</button>
          <?php echo _REALESTATE_MANAGER_WARNING_NO_LOGIN; ?>
        </div>
      <?php else: ?>
        <input type="hidden" name="owneremail" value="<?php echo $my->email; ?>"/>
      <?php endif; ?>
      <input type="hidden" name="id" value="<?php echo $row->id; ?>"/>
    </div>
    <div class="row-fluid">
      <div class="span12">
        <div class="rem_house_contacts">
          <div id="rem_house_titlebox">
            <?php echo _REALESTATE_MANAGER_LABEL_OVERVIEW; ?>
          </div>

          <div class="row_add_house" id="title_label" >
            <span><?php echo _REALESTATE_MANAGER_LABEL_TITLE; ?>:*</span>
            <input id="alert_title" class="inputbox" type="text"
            name="htitle" size="60" value="<?php echo $row->htitle; ?>" />
          </div>

          <div class="row_add_house" id="category_label" >
            <div id="alert_category"></div>
            <span><?php echo _REALESTATE_MANAGER_LABEL_CATEGORY; ?>:*</span>
            <span><?php echo $clist; ?></span>
          </div>

          <div class="row_add_house">
            <span><?php echo _REALESTATE_MANAGER_LABEL_COMMENT; ?>:</span>
            <div class="editor_area"><?php editorArea('editor1', $row->description,
            'description', 300, 50, '70', '10', false); ?></div>
            <!--<textarea name="description" cols="50" rows="8" ><?php //echo $row->description;  ?></textarea>-->
          </div>

          <div class="row_add_house" id='houseid_label' >
            <span><?php echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?>:*</span>
            <input class="inputbox" type="text" id="houseid" name="houseid"
            size="20" maxlength="20" value="<?php echo $row->houseid; ?>" />
            <input type="hidden" name="idtrue" id="idtrue" value="<?php echo $row->id_true; ?>"/>
          </div>

        </div>
      </div>
    </div>

    <div class="row-fluid">
      <div class="span12">
        <div class="rem_house_contacts">
          <div id="rem_house_titlebox">
            <?php echo _REALESTATE_MANAGER_LABEL_PHOTOS; ?>
          </div>

          <div class="row_add_house">
            <div id="image_link_alert"></div>
            <span><?php echo _REALESTATE_MANAGER_LABEL_PICTURE_URL_UPLOAD; ?>:*</span>
            <input class="inputbox" type="file" name="image_link"
            value="<?php echo $row->image_link; ?>" size="25" maxlength="250" />
          </div>

          <div class="row_add_house">
            <?php if ($house_photo != '') { ?>
            <span><?php echo _REALESTATE_MANAGER_LABEL_SELECT_PHOTO_TO_REMOVE; ?>:</span>
            <div style="display:inline-block; margin-left:10px;">
              <img alt="photo" src="<?php echo $mosConfig_live_site .
              "/components/com_realestatemanager/photos/" . $house_photo[1]; ?>"/>
              <div style="text-align:center">
                <input type="checkbox" name="del_main_photo"
                value="<?php echo $house_photo[0]; ?>" />
              </div>
            </div>
            <?php } else echo '<span></span>'; ?>
          </div>
          <!--/////////////////////////////////////////////////upload other foto -->
          <div class="row_add_house">
            <?php
            count($house_photos);
            $user_group = userGID_REM($my->id);
            $user_group_mas = explode(',', $user_group);
            $max_count_foto = 0;
            foreach ($user_group_mas as $value) {
              $count_foto_for_single_group =
              $realestatemanager_configuration['user_manager_rem'][$value]['count_foto'];
              if($count_foto_for_single_group>$max_count_foto){
                $max_count_foto = $count_foto_for_single_group;
              }
            }
            $count_foto_for_single_group = $max_count_foto;
            ?>
            <span> <?php echo _REALESTATE_MANAGER_LABEL_OTHER_PICTURES_URL_UPLOAD; ?>:</span>
            <script language="javascript" type="text/javascript">
              var photos=0;
              function new_photos_rem(){
                div= document.getElementById("items");
                photos++;
                var allowed_files = <?php echo $count_foto_for_single_group;?>;
                if (<?php echo count($house_photos); ?> < allowed_files) {
                  newitem="<input type=\"file\" multiple='true' name=\"new_photo_file[]";
                  newitem+="\" value=\"\"size=\"45\"><br>";
                  newnode= document.createElement("span");
                  newnode.innerHTML=newitem;
                  div.appendChild(newnode);
                }else{
                  newitem="<p> <?php echo _REALESTATE_MANAGER_MAX_PHOTOS_LIMIT; ?>: "+
                  <?php echo $count_foto_for_single_group;?> + " </p>";
                  newnode= document.createElement("span");
                  newnode.innerHTML=newitem;
                  div.appendChild(newnode);
                }
              }
            </script>
            <div ID="items">
              <script> new_photos_rem();</script>
            </div>
          </div>
          <!--/////////////////////////////////////////////////end to upload photos gallery -->

          <?php if (count($house_photos) != 0) { ?>
          <div class="row_add_house">
            <span><?php echo _REALESTATE_MANAGER_LABEL_SELECT_PHOTO_FROM_GALLERY; ?>:</span>
            <div id="rem_img_sortable" style="display:inline-block">
              <?php
              for ($i = 0; $i < count($house_photos); $i++) {
                ?>
                <div id="<?php echo $house_temp_photos[$i]->main_img;?>" style="display:inline-block; margin-bottom:10px;">
                  <img src="<?php echo $mosConfig_live_site .
                  "/components/com_realestatemanager/photos/" .
                  $house_photos[$i][1]; ?>" alt="no such file"/>
                  <div style="text-align:center"><input type="checkbox"
                    name="del_photos[]" value="<?php echo $house_photos[$i][0]; ?>" /></div>
                  </div>
                  <?php } ?>
                </div>
                <input id="rem_img_ordering" type="hidden" name="rem_img_ordering" value="">
                <script type="text/javascript">
                  jQuerREL( "#rem_img_sortable" ).sortable({
                    scroll: false,
                    'update': function (event, ui) {
                      var order = jQuerREL(this).sortable('toArray');
                      jQuerREL( "#rem_img_ordering" ).val(order);
                    }
                  }); 
                </script>
              </div>
              <?php } ?>

            </div>
          </div>
        </div>


        <div class="row-fluid">
          <div class="span12">
            <div class="rem_house_contacts">
              <div id="rem_house_titlebox">
                <?php echo _REALESTATE_MANAGER_LABEL_PRICING; ?>
              </div>
              <div class="row_add_house">
                <span><?php echo _REALESTATE_MANAGER_LABEL_LISTING_TYPE; ?>:</span>
                <span><?php echo $listing_type_list; ?></span>
              </div>

              <div class="row_add_house">
                <span><?php echo _REALESTATE_MANAGER_LABEL_LISTING_STATUS; ?>:</span>
                <span><?php echo $listing_status_list; ?></span>
              </div>

              <div class="row_add_house" id="price_alert">
                <div id="price_alert_warning"></div>
                <span><?php echo _REALESTATE_MANAGER_LABEL_PRICE; ?>:*</span>
                <div style="display:inline-block;">
                  <input class="inputbox" type="text" id="price" name="price" size="15"
                  value="<?php echo $row->price; ?>" />
                  <?php echo $currency; ?>
                </div>
              </div>
              <div class="row_add_house">
                <div class="rem_specprice">
                  <!-- begin sp price -->

                  <script language="javascript" type="text/javascript">

                    jQuerREL(document).ready(function() {
                      jQuerREL( "#price_from, #price_to" ).datepicker(
                      {
                        minDate: "+0",
                        dateFormat: "<?php echo transforDateFromPhpToJquery();?>"

                      });
                    });
                  </script>

                  <script language="javascript" type="text/javascript">

                    jQuerREL(document).ready(function() {
                      jQuerREL(" #subPrice ").bind(" click ", function( event ) {
                        var rent_from = jQuerREL("#price_from").val();
                        var rent_to = jQuerREL("#price_to").val();
                        var special_price = jQuerREL("#special_price").val();
                        var comment_price = jQuerREL('#comment_price').val();
                        var currency_spacial_price = "<?php echo $row->priceunit; ?>";
                        var id = <?php echo (0 + $row->id);?> ;
                        if(id && id > 0){
                          jQuerREL.ajax({
                            type: "POST",
                            url: "index.php?option=com_realestatemanager&task=ajax_rent_price&bid="+id+
                            "&rent_from="+rent_from+"&rent_until="+rent_to+
                            "&special_price="+special_price+"&comment_price="+comment_price+
                            "&currency_spacial_price="+currency_spacial_price,
                            data: { " #do " : " #1 " },
                            update: jQuerREL(" #SpecialPriseBlock "),
                            success: function( data ) {
                              jQuerREL("#SpecialPriseBlock").html(data);
                            }
                          });
                        } else{
                          alert("<?php echo _REALESTATE_MANAGER_TO_ADD_SPRICE_YOU_NEED; ?>");
                        }
                      });
                    });

                  </script>

                  <div class="accordion" id="accordion2">
                    <div class="accordion-group">
                      <div class="accordion-heading" id="rem_house_titlebox">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
                          <?php echo _REALESTATE_MANAGER_RENT_ADD_SPECIAL_PRICE;  ?>
                        </a>
                      </div>
                      <div id="collapseTwo" class="accordion-body collapse">
                        <div class="accordion-inner">
                          <div class="price_col">
                            <div>
                              <div style="display:inline-block">
                                <div><?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_FROM; ?></div>
                                <p><input type="text" id="price_from" name="price_from"></p>
                              </div>

                              <div style="display:inline-block">
                                <div><?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_UNTIL; ?></div>
                                <p><input type="text" id="price_to" name="price_to"></p>
                              </div>

                            </div>
                            <div style="display:inline-block">
                              <div><?php echo _REALESTATE_MANAGER_LABEL_PRICE; ?></div>
                              <input id="special_price" class="inputbox price" type="text"
                              name="special_price" size="15" value="" />
                            </div>

                            <div>
                              <div><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_COMMENT;?></div>
                              <textarea id="comment_price" rows="5" cols="25" name="comment_price"></textarea>
                            </div>

                            <div>
                              <input id="subPrice" class="inputbox" type="button" name="new_price"
                              value="<?php echo _REALESTATE_MANAGER_RENT_ADD_SPECIAL_PRICE; ?>"/>
                            </div>
                          </div>
                          <div id ="message-here" style ='color: red; font-size: 14px;' ></div>
                          <div id ='SpecialPriseBlock'>
                            <table class="adminlist adminlist_04" width ="100%" align ='center'>
                              <tr>
                                <th class="title" width ="15%" align ='center'><?php
                                echo $switchTranslateDayNight; ?></th>
                                <th class="title" align ='center' width ="20%"><?php
                                echo _REALESTATE_MANAGER_FROM; ?></th>
                                <th class="title" align ='center' width ="20%"><?php
                                echo _REALESTATE_MANAGER_TO; ?></th>
                                <th class="title" align ='center' width ="30%"><?php
                                echo _REALESTATE_MANAGER_LABEL_REVIEW_COMMENT; ?></th>
                                <th class="title" align ='center' width ="15%"><?php
                                echo _REALESTATE_MANAGER_LABEL_CALENDAR_SELECT_DELETE; ?></th>
                              </tr>
                              <?php
                              for ($i = 0; $i < count($house_rent_sal); $i++) {
                                $DateToFormat = str_replace("D",'d',(str_replace("M",'m',
                                  (str_replace('%','',$realestatemanager_configuration['date_format'])))));
                                $date_from = new DateTime($house_rent_sal[$i]->price_from);
                                $date_to = new DateTime($house_rent_sal[$i]->price_to);
                                ?>
                                <tr celpadding="5">
                                  <?php
                                  if ($realestatemanager_configuration['price_unit_show'] == '1') {
                                    if ($realestatemanager_configuration['sale_separator']['show'] == '1') { ?>
                                    <td align ='center'><?php echo
                                    formatMoney($house_rent_sal[$i]->special_price, true,
                                      $realestatemanager_configuration['price_format']). ' ' ?></td>
                                      <?php   } else { ?>
                                      <td align ='center'><?php echo $house_rent_sal[$i]->special_price ?></td>
                                      <?php   }
                                    } else {
                                      if ($realestatemanager_configuration['sale_separator']['show'] == '1') { ?>
                                      <td align ='center'><?php echo $house_rent_sal[$i]->priceunit.
                                      ' '.formatMoney($house_rent_sal[$i]->special_price, true,
                                        $realestatemanager_configuration['price_format']); ?></td>
                                        <?php  } else { ?>
                                        <td align ='center'><?php echo $house_rent_sal[$i]->priceunit ?></td>
                                        <?php   }
                                      } ?>
                                      <td align ='center'><?php echo date_format($date_from, "$DateToFormat"); ?></td>
                                      <td align ='center'><?php echo date_format($date_to, "$DateToFormat"); ?></td>
                                      <td align ='center'><?php echo $house_rent_sal[$i]->comment_price; ?></td>
                                      <td align ='center'><input type="checkbox" name="del_rent_sal[]"
                                       value="<?php echo $house_rent_sal[$i]->id; ?>" /></td>
                                     </tr>
                                     <?php } ?>
                                   </table>
                                 </div>
                                 <!--******************************************************-->
                               </div>
                             </div>
                           </div>
                         </div>



                       </div> 
                     </div> 

                   </div>
                 </div>
               </div>
               <?php
               if(count($house_feature)){?>
               <div class="row-fluid">
                <div class="span12">
                  <div class="rem_house_contacts">
                    <div id="rem_house_titlebox">
                      <?php echo _REALESTATE_MANAGER_LABEL_AMENITIES; ?>
                    </div>

                    <div class="row_house_checkbox row_add_house">
                      <?php
                      for ($i = 0; $i < count($house_feature); $i++) {
                        if ($i != 0) {
                          if ($house_feature[$i]->categories !== $house_feature[$i - 1]->categories)
                            echo "<div class='rem_features_category'>" . $house_feature[$i]->categories . "</div>";
                        } else
                        echo "<div class='rem_features_category'>" . $house_feature[$i]->categories . "</div>";
                        ?>

                        <div class="rem_features_name">
                          <input type="checkbox" <?php if ($house_feature[$i]->check)
                          echo "checked"; ?> name="feature[]" value="<?php
                          echo $house_feature[$i]->id; ?>"><?php echo $house_feature[$i]->name; ?>
                          <?php if ($house_feature[$i]->image_link != '') { ?>
                          <img alt="photo" src="<?php
                          echo "$mosConfig_live_site/components/com_realestatemanager/featured_ico/"
                          . $house_feature[$i]->image_link; ?>"></img>
                          <?php } ?>
                        </div>

                        <?php
                      } ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php
            }?>

            <div class="rem_house_contacts">
              <div id="rem_house_titlebox">
                <?php echo _REALESTATE_MANAGER_TAB_LOCATION; ?>
              </div>
              <div class="row-fluid">
                <div class="span5 rem_addlocation">
                  <div class="row_add_house">
                    <span><?php echo _REALESTATE_MANAGER_LABEL_COUNTRY; ?>:</span>
                    <input class="inputbox" type="text" id="hcountry" name="hcountry"
                    size="30" value="<?php echo $row->hcountry; ?>" />
                  </div>

                  <div class="row_add_house">
                    <span><?php echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?>:*</span>
                    <input class="inputbox" type="text" id="hlocation" name="hlocation"
                    size="60" value="<?php echo $row->hlocation; ?>" />
                  </div>

                  <div class="row_add_house">
                    <span><?php echo _REALESTATE_MANAGER_LABEL_CITY; ?>:</span>
                    <input class="inputbox" type="text" id="hcity" name="hcity" size="30"
                    value="<?php echo $row->hcity; ?>" />
                  </div>

                  <div class="row_add_house">
                    <span><?php echo _REALESTATE_MANAGER_LABEL_REGION; ?>:</span>
                    <input class="inputbox" type="text" id="hregion" name="hregion"
                    size="30" value="<?php echo $row->hregion; ?>" />
                  </div>

                  <div class="row_add_house">
                    <span><?php echo _REALESTATE_MANAGER_LABEL_ZIPCODE; ?>:</span>
                    <input class="inputbox" type="text" id="hzipcode" name="hzipcode"
                    size="30" value="<?php echo $row->hzipcode; ?>" />
                  </div>

                  <div class="row_add_house" style="display:none">
                    <input class="inputbox" type="text" id="hlatitude" name="hlatitude"
                    size="20" value="<?php echo $row->hlatitude; ?>" readonly/>
                  </div>

                  <div class="row_add_house" style="display:none">
                    <input class="inputbox" type="text" id="hlongitude" name="hlongitude"
                    size="20" value="<?php echo $row->hlongitude; ?>" readonly/>
                    <input type="hidden" id="map_zoom" name="map_zoom" value="<?php echo $row->map_zoom; ?>" />
                  </div>

                  <div class="row_add_house">
                    <span style="visibility:hidden"><?php echo _REALESTATE_MANAGER_LABEL_GEOCOOR; ?></span>
                    <input type="button" id="button_show_address" value="<?php
                    echo _REALESTATE_MANAGER_BUTTON_SHOW_ADDRESS; ?>" onclick="codeAddress()">
                  </div>
                </div>
                <div class="span7">
                  <div class="rem_addlocation_map">
                    <div id="map_canvas" class="re_map_canvas"></div>
                    <!--Image google map-->
                    <?php
                    $api_key = $realestatemanager_configuration['api_key'] ? "key=" . $realestatemanager_configuration['api_key'] : JFactory::getApplication()->enqueueMessage("<a target='_blank' href='//developers.google.com/maps/documentation/geocoding/get-api-key'>" . _REALESTATE_MANAGER_GOOGLEMAP_API_KEY_LINK_MESSAGE . "</a>", _REALESTATE_MANAGER_GOOGLEMAP_API_KEY_ERROR); 
                    ?>
                    <script src="//maps.googleapis.com/maps/api/js?<?php echo $api_key ?>"
                     type="text/javascript"></script>
                     <script type="text/javascript">
                       setTimeout(function() {
                        vm_initialize();
                      },20);
                       function vm_initialize(){
                        var map;
                        var lastmarker = null;
                        var marker = null;
                        var mapOptions;
                        var myOptions = {
                         zoom: <?php if ($row->map_zoom) echo $row->map_zoom;
                         else echo 1; ?>,
                         center: new google.maps.LatLng(<?php
                          if ($row->hlatitude) echo $row->hlatitude; else echo 0; ?>,<?php
                          if ($row->hlongitude) echo $row->hlongitude; else echo 0; ?>),
                          scrollwheel: false,
                          zoomControlOptions: {
                          style: google.maps.ZoomControlStyle.LARGE
                        },
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                      };
                      geocoder = new google.maps.Geocoder();
                      var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
                      var bounds = new google.maps.LatLngBounds ();
                      <?php if ($row->hlatitude && $row->hlongitude) {
                        ?>
                        //Set the marker coordinates
                        var lastmarker = new google.maps.Marker({
                          position: new google.maps.LatLng(<?php
                           echo $row->hlatitude; ?>, <?php echo $row->hlongitude; ?>)
                         });
                         lastmarker.setMap(map);
                         <?php } ?>
                         //If the zoom, then store it in the field map_zoom
                         google.maps.event.addListener(map,"zoom_changed", function(){
                         document.getElementById("map_zoom").value=map.getZoom();
                       });
                       google.maps.event.addListener(map,"click", function(e){
                       //Initialize marker
                       marker = new google.maps.Marker({
                       position: new google.maps.LatLng(e.latLng.lat(),e.latLng.lng())
                     });
                     //Delete marker
                     if(lastmarker) lastmarker.setMap(null);;
                     //Add marker to the map
                     marker.setMap(map);
                     //Output marker information
                     document.getElementById("hlatitude").value=e.latLng.lat();
                     document.getElementById("hlongitude").value=e.latLng.lng();
                     //Memory marker to delete
                     lastmarker = marker;
                   });

                 }
                 function updateCoordinates(latlng)
                 {
                  if(latlng)
                  {
                    document.getElementById('hlatitude').value = latlng.lat();
                    document.getElementById('hlongitude').value = latlng.lng();
                    document.getElementById("map_zoom").value=map.getZoom();
                  }
                }



                function toggleBounce() {

                if (marker.getAnimation() != null) {
                marker.setAnimation(null);
              } else {
              marker.setAnimation(google.maps.Animation.BOUNCE);
            }
          }


          function codeAddress() {
          var marker;
          myOptions = {
          zoom:14,
          scrollwheel: false,
          zoomControlOptions: {
          style: google.maps.ZoomControlStyle.LARGE
        },
        mapTypeId: google.maps.MapTypeId.ROADMAP
      }
      map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
      var address = document.getElementById('hlocation').value + " " +
      document.getElementById('hcountry').value+ " " +
      document.getElementById('hregion').value+ " " +
      document.getElementById('hcity').value+ " " +
      document.getElementById('hzipcode').value + " " +
      document.getElementById('hlatitude').value + " " +
      document.getElementById('hlongitude').value;
      geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
      map.setCenter(results[0].geometry.location);
      updateCoordinates(results[0].geometry.location);
      if (marker) marker.setMap(null);
      marker = new google.maps.Marker({
      map: map,
      position: results[0].geometry.location,
      draggable: true,
      animation: google.maps.Animation.DROP
    });
    google.maps.event.addListener(marker, 'click', toggleBounce);
    google.maps.event.addListener(marker, "dragend", function() {
    updateCoordinates(marker.getPosition());
  });



} else {
vm_initialize();
alert("Please check the accuracy of Address");
}
});
}

</script>
<span><?php echo _REALESTATE_MANAGER_LABEL_CLICKMAP; ?></span>
</div>
</div>
</div>
</div>


<div class="row-fluid">
  <div class="span12">
    <div class="rem_house_contacts">
      <div id="rem_house_titlebox">
        <?php echo _CATEGORIES__DETAILS; ?>
      </div> 
      <div class="row_add_house">
        <span><?php echo _REALESTATE_MANAGER_LABEL_PROPERTY_TYPE; ?>:</span>
        <span><?php echo $property_type_list; ?></span>
      </div>

      <div class="row_add_house" id="rooms_alert">
        <div id="lot_size_alert"></div>
        <span><?php echo _REALESTATE_MANAGER_LABEL_LOT_SIZE; ?>, <?php 
        echo _REALESTATE_MANAGER_LABEL_SIZE_SUFFIX_AR; ?>:</span>
        <input class="inputbox" type="text" id="lot_size" name="lot_size" 
        size="30" value="<?php echo $row->lot_size; ?>" />
      </div>

      <div class="row_add_house">
        <div id="house_size_alert"></div>
        <span><?php echo _REALESTATE_MANAGER_LABEL_HOUSE_SIZE; ?>, <?php 
        echo _REALESTATE_MANAGER_LABEL_SIZE_SUFFIX; ?>:*</span>
        <input class="inputbox" type="text" id="house_size" name="house_size" 
        size="30" value="<?php echo $row->house_size; ?>" />
      </div>

      <div class="row_add_house">
        <div id="rooms_alert"></div>
        <span><?php echo _REALESTATE_MANAGER_LABEL_ROOMS; ?>:*</span>
        <input class="inputbox" type="text" id="rooms" name="rooms"
        size="10" value="<?php echo $row->rooms; ?>" />
      </div>

      <div class="row_add_house">
        <div id="bathrooms_alert"></div>  
        <span><?php echo _REALESTATE_MANAGER_LABEL_BATHROOMS; ?>:</span>
        <input class="inputbox" type="text" id="bathrooms" name="bathrooms"
        size="10" value="<?php echo $row->bathrooms; ?>" />
      </div>

      <div class="row_add_house">
        <div id="bedrooms_alert"></div>
        <span><?php echo _REALESTATE_MANAGER_LABEL_BEDROOMS; ?>:*</span>
        <input class="inputbox" type="text" id="bedrooms" name="bedrooms"
        size="10" value="<?php echo $row->bedrooms; ?>" />
      </div>

      <div class="row_add_house">
        <div id="garages_alert"></div>
        <span><?php echo _REALESTATE_MANAGER_LABEL_GARAGES; ?>:</span>
        <input class="inputbox" type="text" id="garages" name="garages"
        size="30" value="<?php echo $row->garages; ?>" />
      </div>

      <div class="row_add_house">
        <div id="alert_year"></div>
        <span><?php echo _REALESTATE_MANAGER_LABEL_BUILD_YEAR; ?>:*</span>
        <span>
          <select name="year" id="year" class="inputbox" size="1">
            <?php
            print_r("<option value=''>");
            print_r(_REALESTATE_MANAGER_OPTION_SELECT);
            print_r("</option>");
            $num = 1900;
            for ($i = 0; $num <= date('Y'); $i++) {
              print_r("<option value=\"");
              print_r($num);
              print_r("\"");
              if ($num == $row->year) {
                print(" selected= \"true\" ");
              }
              print_r(">");
              print_r($num);
              print_r("</option>");
              $num++;
            }
            ?>
          </select>
        </span>
      </div>

      <?php if ($realestatemanager_configuration['edocs']['allow']) { ?>

      <div class="row_add_house">
        <div id="alert_edoc"></div>
        <span><?php echo _REALESTATE_MANAGER_LABEL_EDOCUMENT_UPLOAD; ?>:</span>
        <input class="inputbox" type="file" name="edoc_file" value=""
        size="25" maxlength="250" onClick="document.save_add.edok_link.value ='';"/>
      </div>

      <div class="row_add_house">
        <span><?php echo _REALESTATE_MANAGER_LABEL_EDOCUMENT_UPLOAD_URL; ?>:</span>
        <input class="inputbox" type="text" name="edok_link" value="<?php
        echo $row->edok_link; ?>" size="50" maxlength="250"/>
      </div>

      <?php } if (strlen($row->edok_link) > 0) { ?>
      <div class="row_add_house">
        <span><?php echo _REALESTATE_MANAGER_LABEL_EDOCUMENT_DELETE; ?>:</span>
        <span><?php echo $delete_edoc; ?></span>
      </div>
      <?php } ?>
      <div>
        <span id="error_video"></span>
      </div>
      <table>
        <?php
                    ///////////////////////////////START add video and track\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
        if($realestatemanager_configuration['videos_tracks']['show']) {  
          $out='';
          if (count($videos) > 0 && empty($youtube->code)) {
            $out .= '<div>'.
            '<span></span>'.
            '</div>'.
            '<div>'. 
            '<span>'._REALESTATE_MANAGER_LABEL_VIDEO.':</span>'.
            '</div>';
            for ($i = 0;$i < count($videos);$i++) {
              $out .='<div>' .
              '<span>'._REALESTATE_MANAGER_LABEL_VIDEO_ATTRIBUTE.($i+1).':</span>'.
              '<span>';
              if(isset($videos[$i]->src) && substr($videos[$i]->src, 0, 4) != "http" 
                && empty($videos[$i]->youtube)){
                $out .='<input type="text" name="video'.$i.'"'.
              ' id="video'.$i.'"' .
              ' size="60"'.
              ' value="'.$mosConfig_live_site . $videos[$i]->src.'"'.
              ' readonly="readonly" />';
            }else{
              $out .='<input type="text" name="video_url'.$i.'"'.
              ' id="video_url'.$i.'"'.
              ' size="60" value="'. $videos[$i]->src . '"'.
              ' readonly="readonly" />';
            }
            $out .='</span>'.
            '</div>'.
            '<div>'.
            '<span>'._REALESTATE_MANAGER_LABEL_VIDEO_DELETE . ':</span>'.
            '<span>';
            if(isset($videos[$i]->id)) 
              $out .= '<input type="checkbox" name="video_option_del'. $videos[$i]->id .'"'.
            'value="' . $videos[$i]->id .'">'.
            '</span>'.
            '</div>';
          }
        } else if (!empty($youtube->code)) {
          $out .= '<div>'.
          '<span align="right">'._REALESTATE_MANAGER_LABEL_VIDEO_ATTRIBUTE.':</span>'.
          '<span>'.
          '<input type="text"'.
          ' name="youtube_code'.$youtube->id.'"'.
          ' id="youtube_code'.$youtube->id.'"'.
          ' size="60" value="' . $youtube->code .'" />'.
          '</span>'.
          '</div>'.
          '<div>'.
          '<span align="right">'._REALESTATE_MANAGER_LABEL_VIDEO_DELETE . ':</span>'.
          '<span>'.
          '<input type="checkbox"'. 
          ' name="youtube_option_del'.$youtube->id.'"'.
          ' value="'.$youtube->id.'">'.
          '</span>'.
          '</div>';
        }
        $out .= '<div class="row_add_house">';
        if(empty($youtube->code) && count($videos) < 5){
          if(count($videos) > 0)
            $out .= '<span></span>';
          else
            $out .= '<span>'._REALESTATE_MANAGER_LABEL_VIDEO.':</span>';
          $out .= '<div id="v_items">'.
          ' <input id="v_add" type="button"'.
          ' name="new_video"'.
          ' value="'._REALESTATE_MANAGER_LABEL_ADD_NEW_VIDEO_FILE.'"'.
          ' onClick="new_videos()"/>'.
          '</div>'.
          '</div>';
        }
        if (count($tracks) > 0) {
          $out .= '<div>'.
          '<span></span>'.
          '</div>'.
          '<div>'.
          '<span valign="top" align="left">'. _REALESTATE_MANAGER_LABEL_TRACK .':</span>'.
          '</div>';
          for ($i = 0;$i < count($tracks);$i++) {
            $out .='<div>'.
            '<span align="right">' . _REALESTATE_MANAGER_LABEL_TRACK_UPLOAD_URL.($i+1).':</span>'.
            '<span>';
            if (isset($tracks[$i]->src) && substr($tracks[$i]->src, 0, 4) != "http"){
              $out .='<input type="text"'.
              ' class="trackitems"'.
              ' size="60"'.
              ' value="'.$mosConfig_live_site.$tracks[$i]->src.'"'.
              ' readonly="readonly"/>';
            }else{
              $out .='<input type="text"'.
              ' class="trackitems"'.
              ' size="60"'. 
              ' value="'.$tracks[$i]->src.'"'.
              ' readonly="readonly"/>';
            }
            if (!empty($tracks[$i]->kind)) 
              $out .= '<input class="trackitems"'.
            ' type="text"'.
            ' size="60"'.
            ' value="'.$tracks[$i]->kind.'"'.
            ' readonly="readonly"/>';
            if (!empty($tracks[$i]->scrlang)) 
              $out .= '<input class="trackitems"'.
            ' type="text"'.
            ' size="60"'.
            ' value="'.$tracks[$i]->scrlang.'"'.
            ' readonly="readonly"/>';
            if (!empty($tracks[$i]->label)) 
              $out .= '<input class="trackitems"'.
            ' type="text"'.
            ' size="60"'.
            ' value="'.$tracks[$i]->label.'"'.
            ' readonly="readonly"/>';
            $out .= '</span>'.
            '</div>'.
            '<div>'.
            '<span align="right">'._REALESTATE_MANAGER_LABEL_TRACK_DELETE.':</span>'.
            '<span>';
            if(isset($tracks[$i]->id))
              $out .=  '<input type="checkbox"'.
            ' name="track_option_del'.$tracks[$i]->id.'"'.
            ' value="'.$tracks[$i]->id .'">';
          }
          $out .= '<div class="row_add_house">';
          if(count($tracks) > 0)
            $out .= '<span></span>';
          else
            $out .= '<span>'._REALESTATE_MANAGER_LABEL_TRACK.'</span>';
          $out .= '<span id="t_items">'.
          ' <input id="t_add" type="button"'.
          ' name="new_track"'.
          ' value="'._REALESTATE_MANAGER_LABEL_ADD_NEW_TRACK.'"'.
          ' onClick="new_tracks()"/>'.
          '</span>'.
          '</div>';
        }else{
          $out .='<div class="row_add_house">'.
          '<span>'._REALESTATE_MANAGER_LABEL_TRACK.':</span>'.
          '<span id="t_items">'.
          '<input id="t_add" type="button" name="new_track"'.
          ' value="'._REALESTATE_MANAGER_LABEL_ADD_NEW_TRACK.'"'.
          ' onClick="new_tracks()"/>'.
          '</span>'.
          '</div>';
        }
        echo $out;
      }            
                    ///////////////////////////////END edd video and track\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
      ?></table>

      <!--******************************************************************-->

      <?php
      if ($realestatemanager_configuration['extra1'] == 0
       && $realestatemanager_configuration['extra2'] == 0
       && $realestatemanager_configuration['extra3'] == 0
       && $realestatemanager_configuration['extra4'] == 0
       && $realestatemanager_configuration['extra5'] == 0
       && $realestatemanager_configuration['extra6'] == 0
       && $realestatemanager_configuration['extra7'] == 0
       && $realestatemanager_configuration['extra8'] == 0
       && $realestatemanager_configuration['extra9'] == 0
       && $realestatemanager_configuration['extra10'] == 0) {

      } else {
        ?>


        <?php if ($realestatemanager_configuration['extra1'] == 1) { ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA1; ?>:</span>
          <input class="inputbox" type="text" name="extra1" size="30"
          value="<?php echo $row->extra1; ?>" />
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra2'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA2; ?>:</span>
          <input class="inputbox" type="text" name="extra2" size="30"
          value="<?php echo $row->extra2; ?>" />
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra3'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA3; ?>:</span>
          <input class="inputbox" type="text" name="extra3" size="30"
          value="<?php echo $row->extra3; ?>" />
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra4'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA4; ?>:</span>
          <input class="inputbox" type="text" name="extra4" size="30"
          value="<?php echo $row->extra4; ?>" />
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra5'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA5; ?>:</span>
          <input class="inputbox" type="text" name="extra5" size="30"
          value="<?php echo $row->extra5; ?>" />
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra6'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA6; ?>:</span>
          <span><?php echo $extra_list[0]; ?></span>
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra7'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA7; ?>:</span>
          <span><?php echo $extra_list[1]; ?></span>
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra8'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA8; ?>:</span>
          <span><?php echo $extra_list[2]; ?></span>
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra9'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA9; ?>:</span>
          <span><?php echo $extra_list[3]; ?></span>
        </div>

        <?php
      }
      if ($realestatemanager_configuration['extra10'] == 1) {
        ?>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_EXTRA10; ?>:</span>
          <span><?php echo $extra_list[4]; ?></span>
        </div>

        <?php } ?>


        <?php } ?>
        <!--**************************************************************-->

      </div>
      <div class="rem_house_contacts">
        <div id="rem_house_titlebox">
          <?php echo _REALESTATE_MANAGER_LABEL_AGENT_INFO; ?>
        </div> 
        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_AGENT; ?>:</span>
          <input class="inputbox" type="text" name="agent" size="30"
          value="<?php echo $row->agent; ?>" />
        </div>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_CONTACTS; ?>:</span>
          <input class="inputbox" type="text" name="contacts" size="40"
          value="<?php echo $row->contacts; ?>" />
        </div>

        <div class="row_add_house">
          <span><?php echo _REALESTATE_MANAGER_LABEL_OWNER; ?>:</span>

          <span>
            <?php if ($my->guest): ?>
              <input type="text" name="name" readonly/>
            <?php else: ?>
              <input type="text" name="name" value="<?php echo $my->name; ?>" readonly/>
            <?php endif; ?>  
          </span>
        </div>
        <div class="row_add_house" id="owneremail_alert">
          <div id="owneremail_alert"></div>
          <span><?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_EMAIL; ?>:*</span>
          <span>
            <?php if (trim($row->owneremail) != ""): ?>
              <input type='text' name='owneremail' id="owneremail"
              value="<?php echo $row->owneremail; ?>"/>
            <?php else: ?>
              <input type='text' name='owneremail' id="owneremail"
              value="<?php echo $my->email; ?>"/>
            <?php endif; ?>
          </span>
        </div>
      </div>
      <div class="rem_house_contacts">
        <div id="rem_house_titlebox">
          <?php echo _REALESTATE_MANAGER_LABEL_LANGUAGE_NAME; ?>
        </div> 
        <div class="row_add_house">
          <?php
          /*******************************************    language    ***********************/

          if(!empty($associateArray) && !empty($row->language) && $row->language != ''
           && $row->language != '*'){
            ?> 
            <div><?php echo _REALESTATE_MANAGER_LANG_ASSOCIATE_HOUSES; ?>:</div>                         
            
            <?php
            $j =1;
            foreach ($associateArray as $lang=>$value) {
              $displ = '';
              if(!$value['list']){
                $displ = 'none';
              }
              ?>    
              <div style="display: <?php echo $displ?>">
                <span style="display:inline-block; width:200px;"><?php echo $lang; ?>:</span>
                <span><?php echo $value['list']; ?>
                  <input class="inputbox" id="associate_house" type="text"
                  name="associate_house<?php echo $j;?>" size="20" readonly="readonly"
                  maxlength="20" style="width:25px;" value="<?php echo $value['assocId']; ?>" />
                  <input style="display: none" name="associate_house_lang<?php
                  echo $j;?>" value="<?php echo $lang ?>"/></span>

                </div>
                <?php

                $j++;
              }
            }else{
              ?>
              <span><?php echo _REALESTATE_MANAGER_LANG_ASSOCIATE_HOUSES; ?>:</span> 
              <span><?php echo _REALESTATE_MANAGER_FOR_HOUSES_WITH_LANG;  ?></span> 
              <?php
            }
            /*********************************************************************************************/
            ?>     
          </div>
          <div class="row_add_house">
            <span class="admin_col_01"><?php echo _REALESTATE_MANAGER_LABEL_LANGUAGE; ?>:</span>
            <span class="admin_col_02"><?php echo $languages; ?></span>
          </div>
        </div>  
      </div>
    </div>


    <?php
    $month = date("m", mktime(0, 0, 0, date('m'), 1, date('Y')));
    $year = date("Y", mktime(0, 0, 0, date('m'), 1, date('Y')));
    $placeholder = $realestatemanager_configuration['calendar']['placeholder'];
    ?>

    <script language="javascript" type="text/javascript">        

      var itW=0;
      function new_calen_rent(){
        div=document.getElementById("itemsW");
        button=document.getElementById("addW");
        itW++;
        newitem="<strong>" + "<?php echo _REALESTATE_MANAGER_LABEL_CALENDAR_NEW_PRICE; ?>"
        + itW + ": </strong><br />";
        newitem+="<select name=\"yearW[]\"><option value=\"2012\" "
        + " <?php if ($year == '2012') echo "selected" ?> "
        + " >2012</option><option value=\"2013\" "
        + " <?php if ($year == '2013') echo "selected" ?> "
        + " >2013</option><option value=\"2014\" "
        + " <?php if ($year == '2014') echo "selected" ?> "
        + " >2014</option><option value=\"2015\" "
        + " <?php if ($year == '2015') echo "selected" ?> "
        + " >2015</option><option value=\"2016\" "
        + " <?php if ($year == '2016') echo "selected" ?> "
        + " >2016</option><option value=\"2017\" "
        + " <?php if ($year == '2017') echo "selected" ?> "
        + " >2017</option></select>";
        newitem+="<select name=\"monthW[]\"><option value=\"1\" "
        + " <?php if ($month == '1') echo "selected" ?> "
        + " ><?php echo JText::_('JANUARY'); ?>"
        + "</option><option value=\"2\" "
        + " <?php if ($month == '2') echo "selected" ?> "
        + " ><?php echo JText::_('FEBRUARY'); ?>"
        + "</option><option value=\"3\" "
        + " <?php if ($month == '3') echo "selected" ?> " + " >"
        + "<?php echo JText::_('MARCH'); ?>" + "</option><option value=\"4\" "
        + " <?php if ($month == '4') echo "selected" ?> "
        + " >April</option><option value=\"5\" "
        + " <?php if ($month == '5') echo "selected" ?> " + " >"
        + "<?php echo JText::_('MAY'); ?>"
        + "</option><option value=\"6\" "
        + " <?php if ($month == '6') echo "selected" ?> "
        + " >" + "<?php echo JText::_('JUNE'); ?>"
        + "</option><option value=\"7\" "
        + " <?php if ($month == '7') echo "selected" ?> "
        + " >" + "<?php echo JText::_('JULY'); ?>" + "</option>";
        newitem+="<option value=\"8\" " + " <?php if ($month == '8') echo "selected" ?> "
        + "  >" + "<?php echo JText::_('AUGUST'); ?>"
        + "</option><option value=\"9\" "
        + " <?php if ($month == '9') echo "selected" ?> "
        + " >" + "<?php echo JText::_('SEPTEMBER'); ?>"
        + "</option><option value=\"10\" "
        + " <?php if ($month == '10') echo "selected" ?> "
        + " >" + "<?php echo JText::_('OCTOBER'); ?>"
        + "</option><option value=\"11\" "
        + " <?php if ($month == '11') echo "selected" ?> "
        + " >" + "<?php echo JText::_('NOVEMBER'); ?>"
        + "</option><option value=\"12\" "
        + " <?php if ($month == '12') echo "selected" ?> "
        + " >" + "<?php echo JText::_('DECEMBER'); ?>"
        + "</option></select><br />";
        newitem+="<b>Week</b><br /><textarea rows=\"5\" cols=\"25\" name=\"week[]\">"
        + "<?php echo $placeholder; ?>"
        + "</textarea><br /><b>Weekend</b><br /><textarea rows=\"5\" cols=\"25\" name=\"weekend[]\">"
        + "<?php echo $placeholder; ?>"
        + "</textarea><br /><b>Midweek</b><br /><textarea rows=\"5\" cols=\"25\" name=\"midweek[]\">"
        + "<?php echo $placeholder; ?>" + "</textarea><br /><br /><br />";
        newnode=document.createElement("span");
        newnode.innerHTML=newitem;
        div.insertBefore(newnode,button);
      }
    </script>

    <?php if (checkAccess_REM($realestatemanager_configuration['add_house']['registrationlevel'],
     'NORECURSE', userGID_REM($my->id), $acl) ) {
      ?>
      <input  type="button" name="submit2" value="<?php echo _REALESTATE_MANAGER_LABEL_BUTTON_SAVE; ?>"
      class="button" onclick="javascript:submitbutton('submit2');">
      <?php }
      ?>
    </div>

    <?php
            //************publish on add begin

    if ($realestatemanager_configuration['approve_on_add']['show']) {
      if (checkAccess_REM($realestatemanager_configuration['approve_on_add']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        ?><input type="hidden" name="approved" value="1"/><?php
    } else {
      ?><input type="hidden" name="approved" value="0"/><?php
    }
  } else {
    ?><input type="hidden" name="approved" value="0"/><?php } ?>
    <?php
    if ($realestatemanager_configuration['publish_on_add']['show']) {
      if (checkAccess_REM($realestatemanager_configuration['publish_on_add']['registrationlevel'],
       'NORECURSE', userGID_REM($my->id), $acl)) {
        ?><input type="hidden" name="published" value="1"/><?php
    } else {
      ?><input type="hidden" name="published" value="0"/><?php
    }
  } else {
    ?><input type="hidden" name="published" value="0"/><?php } ?>

  </form>
  <?php
}

static function displayLicense($id) {
  global $mosConfig_live_site, $doc;
  $doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/realestatemanager.css');

  $session = JFactory::getSession();
  $pas = $session->get("ssmid", "default");
  $sid_1 = $session->getId();
  $house = $session->get("obj_house", "default");
  if (!($session->get("ssmid", "default")) || $pas == "" || $pas != $sid_1 || $_COOKIE['ssd'] != $sid_1 ||
    !array_key_exists("HTTP_REFERER", $_SERVER) || $_SERVER["HTTP_REFERER"] == "" ||
    strpos($_SERVER["HTTP_REFERER"], $_SERVER['SERVER_NAME']) === false) {
    echo '<H3 align="center">Link failure</H3>';
  exit;
}
echo '<style type="text/css"><!--#frm {width: 95%;height: 200px;border-width: thin;}--></style>';
echo '<form name="dlform" method="POST" action="' . sefRelToAbs($mosConfig_live_site .
  '/index.php?option=com_realestatemanager&amp;task=downitsf&amp;id=' . @$house->id) . ' ">';
echo '<H2 align = "center" style="text-align: center;">' . _LICENSE_AGREEMENT_TITLE . '</H2>';
echo '';
echo '<IFRAME src="' . $mosConfig_live_site . '/components/com_realestatemanager/mylicense.php" 
width="95%" height="230" name="frm" id="frm" SCROLLING="auto" noresize>';
echo '</IFRAME>';
echo '<input type="hidden" name="id" value="' . $id . '" />';
echo '<input type="hidden" name="task" value="downitsf" />';
echo '<input type="hidden" name="ssidPost" value="' . $session->getId() . '" >';
echo '<div align="right" style="text-align:right;>';
echo '<BR /> <font size="3"><strong>' . _LICENSE_AGREEMENT_ACCEPT . '</strong></font> <input
type="radio" name="choice" checked="checked" onclick="document.getElementById(\'DBB\').disabled=true;" />';
echo _REALESTATE_MANAGER_NO;
echo '<input type="radio" name="choice" onclick="document.getElementById(\'DBB\').removeAttribute(
  \'disabled\');" >';
  echo _REALESTATE_MANAGER_YES . '&nbsp;&nbsp;&nbsp;';
  echo '<input type="submit" ID="DBB" name="downbutton" disabled="disabled"
  value="download" />&nbsp;&nbsp;&nbsp;&nbsp;';
  echo '<br /><br /><br /><br />';
  echo '</div>';
  echo '</form>';
}

static function showRentRequest(& $houses, & $currentcat, & $params, & $tabclass,
 & $catid, & $sub_categories, $option) {
  $pageNav = new JPagination(0, 0, 0);

  HTML_realestatemanager::displayHouses($houses, $currentcat, $params, $tabclass,
   $catid, $sub_categories, $pageNav, $option);
        // add the formular for send to :-)
}

static function displayHouses_empty($rows, $currentcat, &$params, $tabclass, $catid,
 $categories, &$pageNav = null,$is_exist_sub_categories=false, $option) {
  positions_rem($params->get('allcategories01'));
  ?>
  <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
   <?php echo $currentcat->header; ?>
 </div>
 <?php positions_rem($params->get('allcategories02')); ?>
 <table class="basictable table_48" border="0" cellpadding="4" cellspacing="0" width="100%">
  <tr>
    <td>
      <?php echo $currentcat->descrip; ?>
    </td>     
    <td width="120" align="center">
      <img src="./components/com_realestatemanager/images/rem_logo.png"
      align="right" alt="Real Estate Manager logo"/>
    </td>
  </tr>
</table>
<?php
if ($is_exist_sub_categories) {
  ?>
  <?php positions_rem($params->get('singlecategory07')); ?>
  <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
    <?php echo _REALESTATE_MANAGER_LABEL_FETCHED_SUBCATEGORIES . " : " .
    $params->get('category_name'); ?>
  </div>
  <?php positions_rem($params->get('singlecategory08')); ?>
  <?php
  HTML_realestatemanager::listCategories($params, $categories, $catid, $tabclass, $currentcat);
}
}

static function displayHouses(&$rows, $currentcat, &$params, $tabclass, $catid, $categories,
  &$pageNav = null,$is_exist_sub_categories=false, $option, $layout = "default", $type = "alone_category") {
  global $mosConfig_absolute_path, $Itemid;  
  $type = 'alone_category'; 
  require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
}    

static function displaySearchHouses(&$rows, $currentcat, &$params, $tabclass, $catid, $categories,
  &$pageNav = null,$is_exist_sub_categories=false, $option, $layout = "default", $layoutsearch = "default") {
  global $mosConfig_absolute_path, $Itemid;  
  $type = 'search_result';

  if ($params->get('show_searchlayout_form'))
    PHP_realestatemanager::showSearchHouses($option, $catid, $option, $layoutsearch);  

  require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
}

    /*
    * function for wishlist
    */ 
    static function showWishlist(&$rows, &$params, &$pageNav, &$option){
      global $mosConfig_absolute_path,$Itemid;
      $layout = 'List';
      $type = 'wishlist';
      require getLayoutPath::getLayoutPathCom('com_realestatemanager',$type, $layout);
    }


    static function displayAllHouses(&$rows, &$params, $tabclass, &$pageNav, $layout = "default") {        
      global $mosConfig_absolute_path,$Itemid;
      $type = 'all_houses';
      require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
    }

//pdf0
    static function displayHousesPdf($rows, $currentcat, &$params, $tabclass, $catid, $categories, &$pageNav) {
      $session = JFactory::getSession();
      $arr = $session->get("array", "default");
      global $hide_js, $Itemid, $mosConfig_live_site, $mosConfig_absolute_path, $option;
      global $limit, $total, $limitstart, $task, $paginations, $mainframe, $realestatemanager_configuration;
      global $doc;


      $doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/realestatemanager.css');
      ob_end_clean();
      ob_start();
      ?>
      <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
        <?php echo $currentcat->header; ?>
      </div>
      <br />

      <div id="list">
        <table class="basictable table_49" width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="10%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
              <?php echo _REALESTATE_MANAGER_LABEL_COVER; ?>
            </td>
            <td width="40%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
              <?php echo _REALESTATE_MANAGER_LABEL_TITLE; ?>
            </td>
            <td width="40%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
              <?php echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?>
            </td>
            <td width="15%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
              <?php echo _REALESTATE_MANAGER_LABEL_PRICE ?>
            </td>
            <?php
            if ($params->get('hits')) {
              ?>
              <td height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>" align="right">
               <?php echo _REALESTATE_MANAGER_LABEL_HITS; ?>
             </td>
             <?php
           }
           if ($params->get('search_request')) {
            ?>
            <td height="20" class="sectiontableheader<?php
            echo $params->get('pageclass_sfx'); ?>" align="right">
            <?php echo _REALESTATE_MANAGER_LABEL_CATEGORY; ?>
          </td>
          <?php
        }
        if ($params->get('show_rentstatus')) {
          ?>
          <td height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
            <?php echo _REALESTATE_MANAGER_LABEL_RENT_CB; ?>
          </td>
          <?php
        }
        ?>
      </tr>
      <?php
      $available = false;
      $k = 0;
//****************************************   add my perenos
      $total = count($rows);
      foreach ($rows as $row) {
//****************************************   add my perenos
        $link = 'index.php?option=' . $option . '&amp;task=view&amp;id=' . $row->id
             . '&amp;catid=' . $row->catid[0] . '&amp;Itemid=' . $Itemid;     //
             ?>
             <tr class="<?php echo $tabclass[$k]; ?>" >
              <td style="padding-left:5px; padding-top:5px; padding-right:10px;">
                <?php
                $house = $row;
            //for local images
                $imageURL = ($house->image_link);

                if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
                $file_name = rem_picture_thumbnail($imageURL,
                 $realestatemanager_configuration['fotogallery']['width'],
                 $realestatemanager_configuration['fotogallery']['high']);
                $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
                echo '<img alt="' . $house->htitle . '" title="' . $house->htitle .
                '" src="' . $file . '" border="0" class="little">';            

                ?>                
              </td>
              <td >
                <a href="<?php echo sefRelToAbs($link); ?>" class="category<?php
                echo $params->get('pageclass_sfx'); ?>">
                <?php echo $row->htitle; ?>
              </a>
            </td>
            <td>
             <?php echo $row->hlocation; ?>
           </td>
           <td >
            <?php echo $row->price . $row->priceunit; ?>
          </td>
          <?php
          if ($params->get('hits')) {
            ?>
            <td align="left">
              <?php echo $row->hits; ?>
            </td>
          </tr>
          <?php }
        } ?>
      </table>
    </div>
    <?php

    $tbl = ob_get_contents();
    ob_end_clean();

    require_once($mosConfig_absolute_path . "/components/com_realestatemanager/tcpdf/config/lang/eng.php");
    require_once($mosConfig_absolute_path . "/components/com_realestatemanager/tcpdf/tcpdf.php");

    $pdf = new TCPDF1('P', 'mm', 'A4', true, 'UTF-8', false);

    $pdf->SetTitle('Realestate Manager');
    $pdf->SetFont('freesans', 'B', 20);
    $pdf->AddPage();
    $pdf->SetFont('freesans', '', 10);
    $pdf->writeHTML($tbl, true, false, false, false, '');
    $pdf->Output('Real_Estate_manager.pdf', 'I');
    exit;
  }

  static function displayHousesPrint($rows, $currentcat, &$params, $tabclass, $catid, $categories, &$pageNav) {
    $session = JFactory::getSession();
    $arr = $session->get("array", "default");

    global $hide_js, $Itemid, $mosConfig_live_site, $mosConfig_absolute_path;
    global $limit, $total, $limitstart, $task, $paginations,
    $mainframe, $realestatemanager_configuration;

    global $doc;

    $doc->addStyleSheet($mosConfig_live_site .
     '/components/com_realestatemanager/includes/realestatemanager.css');
     ?>
     <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
      <table class="basictable table_50">
        <tr>
          <td>
            <?php echo $currentcat->header; ?>
          </td>
          <td align="right">
            <a href="#" onclick="window.print();return false;"><img
             src="./components/com_realestatemanager/images/printButton.png" alt="Print"  /></a>
           </td>
         </tr>      
       </table>
     </div>
     <br />


     <div id="list">
      <table class="basictable table_51" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="10%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
            <?php echo _REALESTATE_MANAGER_LABEL_COVER; ?>
          </td>
          <td width="40%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
            <?php echo _REALESTATE_MANAGER_LABEL_TITLE; ?>
          </td>
          <td width="40%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
            <?php echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?>
          </td>
          <td width="15%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
            <?php echo _REALESTATE_MANAGER_LABEL_PRICE ?>
          </td>
          <?php
          if ($params->get('hits')) {
            ?>
            <td height="20"
            class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>" align="right">
            <?php echo _REALESTATE_MANAGER_LABEL_HITS; ?>
          </td>
          <?php
        }
        if ($params->get('search_request')) {
          ?>
          <td height="20"
          class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>" align="right">
          <?php echo _REALESTATE_MANAGER_LABEL_CATEGORY; ?>
        </td>
        <?php
      }
      if ($params->get('show_rentstatus')) {
        ?>
        <td height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
          <?php echo _REALESTATE_MANAGER_LABEL_RENT_CB; ?>
        </td>
        <?php
      }
      ?>
    </tr>
    <?php
    $available = false;
    $k = 0;
//****************************************   add my perenos
    $total = count($rows);
    foreach ($rows as $row) {
//****************************************   add my perenos
      $link = 'index.php?option=com_realestatemanager&amp;task=view&amp;id='
                             . $row->id . '&amp;catid=' . $row->catid[0] . '&amp;Itemid=' . $Itemid;     //
                             ?>
                             <tr class="<?php echo $tabclass[$k]; ?>" >
                              <td style="padding-left:5px; padding-top:5px; padding-right:10px;">
                                <?php
                                $house = $row;
                        //for local images
                                $imageURL = ($house->image_link);

                                if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
                                $file_name = rem_picture_thumbnail($imageURL,
                                 $realestatemanager_configuration['fotogallery']['width'],
                                 $realestatemanager_configuration['fotogallery']['high']);
                                $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
                                echo '<img alt="' . $house->htitle . '" title="' . $house->htitle .
                                '" src="' . $file . '" border="0" class="little">';     

                                ?>                

                              </td>
                              <td >
                                <a href="<?php echo sefRelToAbs($link); ?>"
                                 class="category<?php echo $params->get('pageclass_sfx'); ?>">
                                 <?php echo $row->htitle; ?>
                               </a>                  
                             </td>
                             <td>
                              <?php
                              echo $row->hlocation;
                              ?>
                            </td>
                            <td >
                              <?php echo $row->price . $row->priceunit; ?>
                            </td>
                            <?php
                            if ($params->get('hits')) {
                              ?>
                              <td align="left">
                                <?php echo $row->hits; ?>
                              </td>
                            </tr>                      
                            <?php }
                          } ?>
                        </table>
                      </div>
                      <?php
       // exit;
                    }

                    static function displayAllHousesPdf($rows, &$params, $tabclass, &$pageNav) {
                      $session = JFactory::getSession();
                      $arr = $session->get("array", "default");

                      global $hide_js, $Itemid, $mosConfig_live_site, $mosConfig_absolute_path, $option;
                      global $limit, $total, $limitstart, $task, $paginations,
                      $mainframe, $realestatemanager_configuration;

                      global $doc;

                      $doc->addStyleSheet($mosConfig_live_site .
                       '/components/com_realestatemanager/includes/realestatemanager.css');

                      ob_end_clean();
                      ob_start();
                      ?>

                      <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
                      </div>

                      <div id="list">
                        <table class="basictable table_52" width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td width="10%" height="20" class="sectiontableheader<?php
                            echo $params->get('pageclass_sfx'); ?>">
                            <?php echo _REALESTATE_MANAGER_LABEL_COVER; ?>
                          </td>
                          <td width="40%" height="20" class="sectiontableheader<?php
                          echo $params->get('pageclass_sfx'); ?>">
                          <?php echo _REALESTATE_MANAGER_LABEL_TITLE; ?>
                        </td>
                        <td width="40%" height="20" class="sectiontableheader<?php
                        echo $params->get('pageclass_sfx'); ?>">
                        <?php echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?>
                      </td>
                      <td width="15%" height="20" class="sectiontableheader<?php
                      echo $params->get('pageclass_sfx'); ?>">
                      <?php echo _REALESTATE_MANAGER_LABEL_PRICE ?>
                    </td>
                    <?php
                    if ($params->get('hits')) {
                      ?>
                      <td height="20" class="sectiontableheader<?php
                      echo $params->get('pageclass_sfx'); ?>" align="right">
                      <?php echo _REALESTATE_MANAGER_LABEL_HITS; ?>
                    </td>
                    <?php
                  }
                  if ($params->get('search_request')) {
                    ?>
                    <td height="20" class="sectiontableheader<?php
                    echo $params->get('pageclass_sfx'); ?>" align="right">
                    <?php echo _REALESTATE_MANAGER_LABEL_CATEGORY; ?>
                  </td>
                  <?php
                }
                if ($params->get('show_rentstatus')) {
                  ?>
                  <td height="20" class="sectiontableheader<?php
                  echo $params->get('pageclass_sfx'); ?>">
                  <?php echo _REALESTATE_MANAGER_LABEL_RENT_CB; ?>
                </td>
                <?php
              }
              ?>
            </tr>
            <?php
            $available = false;
            $k = 0;
//****************************************   add my perenos
            $total = count($rows);
            if (isset($_GET['lang']))
              $lang = $_GET['lang']; else
            $lang = '*';
            foreach ($rows as $row) {
//****************************************   add my perenos
              $link = 'index.php?option=' . $option . '&amp;task=view&amp;id='
                             . $row->id . '&amp;catid=' . $row->catid[0] . '&amp;Itemid=' . $Itemid;     //
                             ?>
                             <tr class="<?php echo $tabclass[$k]; ?>" >
                              <td style="padding-left:5px; padding-top:5px; padding-right:10px;">
                                <?php
                                $house = $row;

                          //for local images
                                $imageURL = ($house->image_link);

                                if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
                                $file_name = rem_picture_thumbnail($imageURL,
                                 $realestatemanager_configuration['fotogallery']['width'],
                                 $realestatemanager_configuration['fotogallery']['high']);
                                $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
                                echo '<img alt="' . $house->htitle . '" title="' . $house->htitle .
                                '" src="' . $file . '" border="0" class="little">';     

                                ?>      
                              </td>
                              <td >
                                <a href="<?php echo sefRelToAbs($link); ?>"
                                 class="category<?php echo $params->get('pageclass_sfx'); ?>">
                                 <?php echo $row->htitle; ?>
                               </a>
                             </td>
                             <td>
                              <?php echo $row->hlocation; ?>
                            </td>
                            <td >
                              <?php echo $row->price . $row->priceunit; ?>
                            </td>
                            <?php
                            if ($params->get('hits')) {
                              ?>
                              <td align="left">
                                <?php echo $row->hits; ?>
                              </td>
                              <?php
                            }
                            if ($params->get('search_request')) {
                              ?>
                              <td align="right">
                                <?php
                                $link1 = 'index.php?option=com_realestatemanager&amp;task=showCategory&amp;catid='
                                . $row->catid[0] . '&amp;Itemid=' . $Itemid;
                                ?>

                                <a href="<?php echo sefRelToAbs($link1); ?>"
                                 class="category<?php echo $params->get('pageclass_sfx'); ?>">
                                 <?php echo $row->category_titel; ?>
                               </a>
                               </td><?php
                             }
                             if ($params->get('show_rentstatus')) {
                              if ($params->get('show_rentrequest')) {
                                $data1 = JFactory::getDBO();
                                $query = "SELECT  b.rent_from , b.rent_until  FROM #__rem_rent AS b " .
                                " LEFT JOIN #__rem_houses AS c ON b.fk_houseid = c.id " .
                                " WHERE c.id=" . $row->id .
                                " AND c.published='1' AND c.approved='1' AND b.rent_return IS NULL";
                                $data1->setQuery($query);
                                $rents1 = $data1->loadObjectList();
                                ?>
                                <td align="center" width="100%">
                                  <?php
                                  if (($row->listing_type == 1) && !isset($rents1[0]->rent_until)) {
                                    echo "<img src='" . $mosConfig_live_site .
                                    "/components/com_realestatemanager/images/available.png' ".
                                    " alt='Available' name='image' border='0' align='middle' />";
                                  } else if ($row->fk_rentid != 0 && isset($rents1[0]->rent_until)) {
                                    echo _REALESTATE_MANAGER_LABEL_RENT_FROM_UNTIL . "<br />";
                                    for ($a = 0; $a < count($rents1); $a++) {
                                      $from_until = substr($rents1[$a]->rent_from, 0, 10) .
                                      "&nbsp;/&nbsp;" .
                                      substr($rents1[$a]->rent_until, 0, 10) . "\n";
                                      print_r($from_until);
                                    }
                                  } else if (($row->listing_type != 1)) {
                                    echo "<img src='" . $mosConfig_live_site .
                                    "/components/com_realestatemanager/images/not_available.png' ".
                                    "alt='Not Available' name='image' border='0' align='middle' />";
                                  } ?>            
                                </td>

                                <?php
                              }
                            }
                            ?>
                          </tr>
                          <?php } ?>
                        </table>
                      </div>
                      <?php
                      $tbl = ob_get_contents();
                      ob_end_clean();

                      require_once($mosConfig_absolute_path . "/components/com_realestatemanager/tcpdf/config/lang/eng.php");
                      require_once($mosConfig_absolute_path . "/components/com_realestatemanager/tcpdf/tcpdf.php");

                      $pdf = new TCPDF1('P', 'mm', 'A4', true, 'UTF-8', false);

                      $pdf->SetTitle('Realestate Manager');
                      $pdf->SetFont('freesans', 'B', 20);
                      $pdf->AddPage();
                      $pdf->SetFont('freesans', '', 10);
                      $pdf->writeHTML($tbl, true, false, false, false, '');
                      $pdf->Output('Real_Estate_manager.pdf', 'I');
                      exit;
                    }

                    static function displayAllHousePrint($rows, &$params, $tabclass, &$pageNav) {
                      $session = JFactory::getSession();
                      $arr = $session->get("array", "default");

                      global $hide_js, $Itemid, $mosConfig_live_site, $mosConfig_absolute_path;
                      global $limit, $total, $limitstart, $task, $paginations, $mainframe, $realestatemanager_configuration;

                      global $doc;

                      $doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/realestatemanager.css');
                      ?>
                      <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
                        <table class="basictable table_53">
                          <tr>
                            <td align="right">
                              <a href="#" onclick="window.print();return false;"><img 
                                src="./components/com_realestatemanager/images/printButton.png" alt="Print"  /></a>
                              </td>
                            </tr>      
                          </table>
                        </div>

                        <div id="list">
                          <table class="basictable table_54" width="100%" border="1" cellspacing="0" cellpadding="0">
                            <tr>
                              <td width="10%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
                                <?php echo _REALESTATE_MANAGER_LABEL_COVER; ?>
                              </td>
                              <td width="40%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
                                <?php echo _REALESTATE_MANAGER_LABEL_TITLE; ?>
                              </td>
                              <td width="40%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
                                <?php echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?>
                              </td>
                              <td width="15%" height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
                                <?php echo _REALESTATE_MANAGER_LABEL_PRICE ?>
                              </td>
                              <?php
                              if ($params->get('hits')) {
                                ?>
                                <td height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>" align="right">
                                  <?php echo _REALESTATE_MANAGER_LABEL_HITS; ?>
                                </td>
                                <?php
                              }
                              if ($params->get('search_request')) {
                                ?>
                                <td height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>" align="right">
                                  <?php echo _REALESTATE_MANAGER_LABEL_CATEGORY; ?>
                                </td>
                                <?php
                              }
                              if ($params->get('show_rentstatus')) {
                                ?>
                                <td height="20" class="sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
                                  <?php echo _REALESTATE_MANAGER_LABEL_ACCESSED_FOR_RENT; ?>
                                </td>
                                <?php
                              }
                              ?>
                            </tr>
                            <?php
                            $available = false;
                            $k = 0;
//****************************************   add my perenos
                            $total = count($rows);
                            foreach ($rows as $row) {
//****************************************   add my perenos
                              $link = 'index.php?option=com_realestatemanager&amp;task=view&amp;id='
                             . $row->id . '&amp;catid=' . $row->catid[0] . '&amp;Itemid=' . $Itemid;     //
                             ?>
                             <tr class="<?php echo $tabclass[$k]; ?>" >
                              <td style="padding-left:5px; padding-top:5px; padding-right:10px;">
                                <?php
                                $house = $row;
                                
                                //for local images
                                $imageURL = ($house->image_link);

                                if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
                                $file_name = rem_picture_thumbnail($imageURL,
                                 $realestatemanager_configuration['fotogallery']['width'],
                                 $realestatemanager_configuration['fotogallery']['high']);
                                $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
                                echo '<img alt="' . $house->htitle . '" title="' . $house->htitle .
                                '" src="' . $file . '" border="0" class="little">';     

                                ?>      

                              </td>
                              <td >
                                <a href="<?php echo sefRelToAbs($link); ?>" class="category<?php
                                echo $params->get('pageclass_sfx'); ?>">
                                <?php echo $row->htitle; ?>
                              </a>                  
                            </td>
                            <td>
                              <?php echo $row->hlocation; ?>
                            </td>
                            <td >
                              <?php echo $row->price . $row->priceunit; ?>
                            </td>
                            <?php
                            if ($params->get('hits')) {
                              ?>
                              <td align="left">
                                <?php echo $row->hits; ?>
                              </td>
                              <?php
                            }
                            if ($params->get('search_request')) {
                              ?>
                              <td align="right">
                                <?php
                                $link1 = 'index.php?option=com_realestatemanager&amp;task=showCategory&amp;catid='
                                . $row->catid[0] . '&amp;Itemid=' . $Itemid;
                                ?>
                                <a href="<?php echo sefRelToAbs($link1); ?>" class="category<?php
                                echo $params->get('pageclass_sfx'); ?>">
                                <?php echo $row->category_titel; ?>
                              </a>
                              </td><?php
                            }
                            if ($params->get('show_rentstatus')) {
                              if ($params->get('show_rentrequest')) {
                                $data1 = JFactory::getDBO();
                                $query = "SELECT  b.rent_from , b.rent_until  FROM #__rem_rent AS b " .
                                " LEFT JOIN #__rem_houses AS c ON b.fk_houseid = c.id " .
                                " WHERE c.id=" . $row->id .
                                " AND c.published='1' AND c.approved='1' AND b.rent_return IS NULL";
                                $data1->setQuery($query);
                                $rents1 = $data1->loadObjectList();
                                ?>
                                <td align="center" width="100%">
                                  <?php
                                  if (($row->listing_type == 1) && !isset($rents1[0]->rent_until)) {
                                    echo "<img src='" . $mosConfig_live_site .
                                    "/components/com_realestatemanager/images/available.png' ".
                                    " alt='Available' name='image' border='0' align='middle' />";
                                  } else if ($row->fk_rentid != 0 && isset($rents1[0]->rent_until)) {
                                    echo _REALESTATE_MANAGER_LABEL_RENT_FROM_UNTIL . "<br />";
                                    for ($a = 0; $a < count($rents1); $a++) {
                                      $from_until = substr($rents1[$a]->rent_from, 0, 10) .
                                      "&nbsp;/&nbsp;" .
                                      substr($rents1[$a]->rent_until, 0, 10) . "\n";
                                      print_r($from_until);
                                    }
                                  } else if (($row->listing_type != 1)) {
                                    echo "<img src='" . $mosConfig_live_site .
                                    "/components/com_realestatemanager/images/not_available.png' ".
                                    " alt='Not Available' name='image' border='0' align='middle' />";
                                  }   ?>            
                                </td>

                                <?php
                              }
                            }
                            ?>
                          </tr>                      
                          <?php } ?>
                        </table>
                      </div>
                      <?php
       // exit;
                    }

    /**
     * Displays the house
     */
    static function displayHouse(& $house, & $tabclass, & $params, & $currentcat, & $rating,
     & $house_photos,$videos,$tracks, $id, $catid, $option, & $house_feature, & $currencys_price, $layout = "default") {
      global $mosConfig_absolute_path;
      $type = 'view_house';
      require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
    }

    static function displayHouseMainPdf(& $house, & $tabclass, & $params,
     & $currentcat, & $rating, & $house_photos) {
      global $hide_js, $mainframe, $Itemid, $realestatemanager_configuration,
      $mosConfig_live_site, $mosConfig_absolute_path, $my;
      global $doc;

      $doc->addStyleSheet($mosConfig_live_site .
       '/components/com_realestatemanager/includes/realestatemanager.css');
      JPluginHelper::importPlugin('content');
      $dispatcher = JDispatcher::getInstance();
      ob_end_clean();
      ob_start();
      ?>
      <table class="basictable table_55" align="center">
        <tr>
          <td colspan ="2" align="center" class="title_td">
            <?php echo $house->htitle; ?>
          </td>
        </tr>
        <tr>
          <td nowrap="nowrap" align="center" colspan="2">
            <?php

                    //for local images
            $imageURL = ($house->image_link);

            if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
            $file_name = rem_picture_thumbnail($imageURL,
             $realestatemanager_configuration['fotomain']['width'],
             $realestatemanager_configuration['fotomain']['high']);
            $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
            echo '<img alt="' . $house->htitle . '" title="' . $house->htitle .
            '" src="' . $file . '" border="0" class="little">';     
            ?>
          </td>
        </tr>
        <tr>
          <td class="first_td" align="right">       
            <strong><?php echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?>:</strong>
          </td>
          <td width="270px" align="left" >
            <?php echo $house->hlocation; ?>
          </td>
        </tr>
        <?php if (trim($house->description)) { ?>
        <tr>
          <td valign="top" class="first_td">
            <strong><?php echo _REALESTATE_MANAGER_LABEL_COMMENT; ?>:</strong>
          </td>
          <td width="270px" align="justify">
            <?php
            positions_rem($house->description);
            ?>
          </td>
        </tr>
        <?php } if ($realestatemanager_configuration['owner']['show']
         && $house->ownername != '' && $house->owneremail != '') {
          ?>
          <tr>
            <td class="first_td" align="right">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_OWNER; ?>:</strong>
            </td>
            <td align="left">
              <strong><?php echo $house->ownername, ', ', $house->owneremail; ?></strong>
            </td>
          </tr>
          <?php
        }
        if ($house->listing_type != 0) {
          ?>
          <tr>
            <td class="first_td" align="right">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_LISTING_TYPE; ?>:</strong>
            </td>
            <td width="270px" align="left">
              <?php
              $listing_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
              $listing_type[1] = _REALESTATE_MANAGER_OPTION_FOR_RENT;
              $listing_type[2] = _REALESTATE_MANAGER_OPTION_FOR_SALE;
              echo $listing_type[$house->listing_type];
              ?>
            </td>
          </tr>
          <?php
        }
        if ($params->get('show_contacts_line')) {
          if ($params->get('show_contacts_registrationlevel')) {
            if (trim($house->contacts)) {
              ?>
              <tr>
                <td nowrap="nowrap" align="left" class="first_td">
                  <strong><?php echo _REALESTATE_MANAGER_LABEL_CONTACTS; ?>:</strong>
                </td>
                <td width="270px" align="left">
                  <?php echo $house->contacts; ?>
                </td>
              </tr>
              <?php }
            }
          } ?>
          <?php
          if ($house->listing_type == 1) {
            $rent = $house->getRent();
            if ($rent == null) {
              $help['name'] = '';
              $help['until'] = '';
              $help['rent'] = '';
            } else {
              if ($rent->rent_until != null) {
                $help['rent'] = data_transform_rem($rents[$e]->rent_from) . "  =>  "
                . data_transform_rem($rents[$e]->rent_until);
                $help['name'] = $rent->user_name;
                $id = $rent->fk_houseid;
                $database = JFactory::getDBO();
                $select = "SELECT rent_from , rent_until FROM #__rem_rent AS a ".
                " WHERE fk_houseid=" . $id . " AND rent_return IS NULL";
                $database->setQuery($select);
                $rents = 0;
                $rents = $database->loadObjectList();
                $num = count($rents);
              } else {
                $help['rent'] = $help['rent'] . _REALESTATE_MANAGER_LABEL_RENT_FROM_UNTIL_NOT_KNOWN;
              }
                } //end else
                ?>
                <?php if (isset($rents)) { ?>
                <tr>
                  <td align="right" class="title_td">
                    <strong><?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM_UNTIL; ?>:</strong>
                  </td>
                </tr>
                <?php
                for ($e = 0, $m = count($rents); $e < $m; $e++) {
                  print("<tr><td align=\"right\"><strong></strong></td><td>");
                  $date = data_transform_rem($rents[$e]->rent_from) . "  =>  "
                  . data_transform_rem($rents[$e]->rent_until);
                  print_r($date);
                  print(" </td></tr>");
                }
              }
            }
            //end if
            ?>
            <?php if ($house->price != "" && $params->get('show_pricerequest') == '1') { ?>
            <tr>
              <td nowrap="nowrap" align="right" class="title_td">
                <strong><?php echo _REALESTATE_MANAGER_LABEL_PRICE; ?>:</strong>
              </td>
              <td align="left">
                <?php echo $house->price . " " . $house->priceunit; ?>
              </td>
            </tr>               
            <?php 
          } ?>
          <?php if (trim($house->rooms)) { ?>
          <tr>
            <td nowrap="nowrap" align="right" class="title_td" >
              <strong><?php echo _REALESTATE_MANAGER_LABEL_ROOMS; ?>:</strong>
            </td>
            <td align="left">
              <?php echo $house->rooms; ?>
            </td>
          </tr>
          <?php } ?>
          <?php if (trim($house->bathrooms)) { ?>
          <tr>
            <td nowrap="nowrap" align="right" class="title_td" >
              <strong><?php echo _REALESTATE_MANAGER_LABEL_BATHROOMS; ?>:</strong>
            </td>
            <td align="left">
              <?php echo $house->bathrooms; ?>
            </td>
          </tr>
          <?php } ?>
          <?php if (trim($house->bedrooms)) { ?>
          <tr>

            <td nowrap="nowrap" align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_BEDROOMS; ?>:</strong>
            </td>
            <td align="left">
              <?php echo $house->bedrooms; ?>
            </td>
          </tr>
          <?php } ?>
          <?php if (trim($house->agent)) { ?>		
          <tr>
            <td nowrap="nowrap" align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_AGENT; ?>:</strong>
            </td>
            <td align="left">
              <?php echo $house->agent; ?>
            </td>
          </tr>
          <?php } ?>
          <?php
          if ($params->get('show_contacts_line')) {

            if ($params->get('show_contacts_registrationlevel')) {

              if (trim($house->contacts)) {
                ?>		
                <tr>
                  <td nowrap="nowrap" align="right"class="title_td" >
                    <strong><?php echo _REALESTATE_MANAGER_LABEL_CONTACTS; ?>:</strong>
                  </td>
                  <td align="left">
                    <?php echo $house->contacts; ?>
                  </td>
                </tr>
                <?php }
              }
            } ?>
            <?php
            if ($house->listing_status != 0) {
              $listing_status1 = explode(',', _REALESTATE_MANAGER_OPTION_LISTING_STATUS);
              $i = 1;
              foreach ($listing_status1 as $listing_status2) {
                $listing_status[$i] = $listing_status2;
                $i++;
              }
              ?>
              <tr>
                <td nowrap="nowrap" align="right" class="title_td" >
                  <strong><?php echo _REALESTATE_MANAGER_LABEL_LISTING_STATUS; ?>:</strong>
                </td>
                <td align="left">
                  <?php echo $listing_status[$house->listing_status]; ?>
                </td>
              </tr>
              <?php } ?>
              <?php
              if ($house->property_type != 0) {
                $property_type1 = explode(',', _REALESTATE_MANAGER_OPTION_PROPERTY_TYPE);
                $i = 1;
                foreach ($property_type1 as $property_type2) {
                  $property_type[$i] = $property_type2;
                  $i++;
                }
                ?>
                <tr>
                  <td nowrap="nowrap" align="right" class="title_td">
                    <strong><?php echo _REALESTATE_MANAGER_LABEL_PROPERTY_TYPE; ?>:</strong>
                  </td>
                  <td align="left">
                    <?php echo $property_type[$house->property_type]; ?>
                  </td>
                </tr>
                <?php } ?>
                <?php if (trim($house->lot_size)) { ?>
                <tr>
                  <td nowrap="nowrap" align="right" class="title_td">
                    <strong><?php echo _REALESTATE_MANAGER_LABEL_LOT_SIZE; ?>:</strong>
                  </td>
                  <td>
                    <?php echo $house->lot_size; ?>
                  </td>
                </tr>
                <?php } ?>
                <?php if (trim($house->house_size)) { ?>
                <tr>
                  <td nowrap="nowrap" align="right" class="title_td">
                    <strong><?php echo _REALESTATE_MANAGER_LABEL_HOUSE_SIZE; ?>:</strong>
                  </td>
                  <td>
                    <?php echo $house->house_size; ?>
                  </td>
                </tr>
                <?php } ?>
                <?php if (trim($house->garages)) { ?>
                <tr>
                  <td nowrap="nowrap" align="right" class="title_td">
                    <strong><?php echo _REALESTATE_MANAGER_LABEL_GARAGES; ?>:</strong>
                  </td>
                  <td align="left">
                    <?php echo $house->garages; ?>
                  </td>
                </tr>
                <?php } ?>
                <?php if (trim($house->year)) { ?>
                <tr>
                  <td nowrap="nowrap" align="right" class="title_td">
                    <strong><?php echo _REALESTATE_MANAGER_LABEL_BUILD_YEAR; ?>:</strong>
                  </td>
                  <td align="left">
                    <?php echo $house->year; ?>
                  </td>
                </tr>
                <?php } ?>
              </table>


              <?php
              $tbl = ob_get_contents();
              ob_end_clean();
              require_once($mosConfig_absolute_path . "/components/com_realestatemanager/tcpdf/config/lang/eng.php");
              require_once($mosConfig_absolute_path . "/components/com_realestatemanager/tcpdf/tcpdf.php");

              $pdf = new TCPDF1('P', 'mm', 'A4', true, 'UTF-8', false);
        //$pdf->SetAuthor('');
              $pdf->SetTitle('Real Estate manager');
              $pdf->SetFont('freesans', 'B', 20);
              $pdf->AddPage();
        //$pdf->Write(0, 'Real Estate manager', '', 0, 'L', true, 0, false, false, 0);
              $pdf->SetFont('freesans', '', 10);
              $pdf->writeHTML($tbl, true, false, false, false, '');
              $pdf->Output('Real_Estate_manager.pdf', 'I');
              exit;
            }

            static function displayHouseMainprint(& $house, & $tabclass, & $params,
             & $currentcat, & $rating, & $house_photos) {
              global $hide_js, $mainframe, $Itemid, $realestatemanager_configuration,
              $mosConfig_live_site, $mosConfig_absolute_path, $my;
              global $doc;

              $doc->addStyleSheet($mosConfig_live_site .
               '/components/com_realestatemanager/includes/realestatemanager.css');

              JPluginHelper::importPlugin('content');
              $dispatcher = JDispatcher::getInstance();
              ?>  
              <table class="basictable table_56" align="center">
                <tr>
                  <td colspan ="2" align="center" class="title_td">
                    <?php echo $house->htitle; ?>
                    <a href="#" onclick="window.print();return false;"><img

                      src="<?php echo $mosConfig_live_site;?>/components/com_realestatemanager/images/printButton.png" alt="Print"/></a>
                    </td>
                  </tr>
                  <tr>
                    <td nowrap="nowrap" align="center" colspan="2">
                      <?php

            //for local images
                      $imageURL = ($house->image_link);

                      if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
                      $file_name = rem_picture_thumbnail($imageURL,
                       $realestatemanager_configuration['fotomain']['width'],
                       $realestatemanager_configuration['fotomain']['high']);
                      $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
                      echo '<img alt="' . $house->htitle . '" title="' . $house->htitle .
                      '" src="' . $file . '" border="0" class="little">';     

                      ?>          
                    </td>
                  </tr>
                  <tr>
                    <td class="first_td" align="right">       
                      <strong><?php echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?>:</strong>
                    </td>
                    <td width="270px" align="left" >
                      <?php echo $house->hlocation; ?>
                    </td>
                  </tr>
                  <?php if (trim($house->description)) { ?>	<tr>

                    <td valign="top" class="first_td" align="right">
                      <strong><?php echo _REALESTATE_MANAGER_LABEL_COMMENT; ?>:</strong>
                    </td>
                    <td width="270px" align="justify">
                      <?php
                      positions_rem($house->description);
                      ?>
                    </td>
                  </tr>
                  <?php } if ($realestatemanager_configuration['owner']['show']
                   && $house->ownername != '' && $house->owneremail != '') {
                    ?>
                    <tr>
                      <td class="first_td" align="right">
                        <strong><?php echo _REALESTATE_MANAGER_LABEL_OWNER; ?>:</strong>
                      </td>
                      <td  align="left">
                        <strong><div class="strong"><?php
                        echo $house->ownername, ', ', $house->owneremail; ?>
                      </div></strong>
                    </td>
                  </tr>
                  <?php
                }
                if ($house->listing_type != 0) {
                  ?>
                  <tr>
                    <td class="first_td" align="right">
                      <strong><?php echo _REALESTATE_MANAGER_LABEL_LISTING_TYPE; ?>:</strong>
                    </td>
                    <td width="270px" align="left">
                      <?php
                      $listing_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
                      $listing_type[1] = _REALESTATE_MANAGER_OPTION_FOR_RENT;
                      $listing_type[2] = _REALESTATE_MANAGER_OPTION_FOR_SALE;
                      echo $listing_type[$house->listing_type];
                      ?>
                    </td>
                  </tr>
                  <?php
                }
                if ($params->get('show_contacts_line')) {
                  if ($params->get('show_contacts_registrationlevel')) {
                    if (trim($house->contacts)) {
                      ?>
                      <tr>
                        <td nowrap="nowrap" align="right" class="first_td">
                          <strong><?php echo _REALESTATE_MANAGER_LABEL_CONTACTS; ?>:</strong>
                        </td>
                        <td width="270px" align="left">
                          <?php echo $house->contacts; ?>
                        </td>
                      </tr>
                      <?php }
                    }
                  } ?>

                  <?php
                  if ($house->listing_type == 1) {
                    $rent = $house->getRent();

                    if ($rent == null) {
                      $help['name'] = '';
                      $help['until'] = '';
                      $help['rent'] = '';
                    } else {
                      if ($rent->rent_until != null) {
                        $help['rent'] = substr($rent->rent_from, 0, 10) . "   " . substr($rent->rent_until, 0, 10);
                        $help['name'] = $rent->user_name;
                        $id = $rent->fk_houseid;
                        $database = JFactory::getDBO();
                        $select = "SELECT rent_from , rent_until FROM #__rem_rent AS a ".
                        " WHERE fk_houseid=" . $id . " AND rent_return IS NULL";
                        $database->setQuery($select);
                        $rents = 0;
                        $rents = $database->loadObjectList();
                        $num = count($rents);
                      } else {
                        $help['rent'] = $help['rent'] . _REALESTATE_MANAGER_LABEL_RENT_FROM_UNTIL_NOT_KNOWN;
                      }
            } //end else
            ?>

            <?php if (isset($rents)) { ?>
            <td align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM_UNTIL; ?>:</strong>
            </td>
            <td>
            </td>
          </tr>
          <?php
          for ($e = 0, $m = count($rents); $e < $m; $e++) {
            print("<td align=\"right\"><strong></strong></td><td>");
            $date = substr($rents[$e]->rent_from, 0, 10) . "   " . substr($rents[$e]->rent_until, 0, 10);
            print_r($date);
            print(" </td></tr>");
          }
        }
        ?>

        <?php
      }
                //end if
      ?>
      <?php if ($house->price != "" && $params->get('show_pricerequest') == '1') { ?>
      <tr>
        <td nowrap="nowrap" align="right" class="title_td">
          <strong><?php echo _REALESTATE_MANAGER_LABEL_PRICE; ?>:</strong>
        </td>
        <td>
          <?php echo $house->price . " " . $house->priceunit; ?>
        </td>
      </tr>
      <?php 
    } ?>
    <?php if (trim($house->rooms)) { ?>
    <tr>
      <td nowrap="nowrap" align="right" class="title_td" >
        <strong><?php echo _REALESTATE_MANAGER_LABEL_ROOMS; ?>:</strong>
      </td>
      <td>
        <?php echo $house->rooms; ?>
      </td>
    </tr>
    <?php } ?>
    <?php if (trim($house->bathrooms)) { ?>
    <tr>
      <td nowrap="nowrap" align="right" class="title_td" >
        <strong><?php echo _REALESTATE_MANAGER_LABEL_BATHROOMS; ?>:</strong>
      </td>
      <td>
        <?php echo $house->bathrooms; ?>
      </td>
    </tr>
    <?php } ?>
    <?php if (trim($house->bedrooms)) { ?>
    <tr>

      <td nowrap="nowrap" align="right" class="title_td">
        <strong><?php echo _REALESTATE_MANAGER_LABEL_BEDROOMS; ?>:</strong>
      </td>
      <td>
        <?php echo $house->bedrooms; ?>
      </td>
    </tr>
    <?php } ?>

    <?php if (trim($house->agent)) { ?>		
    <tr>
      <td nowrap="nowrap" align="right" class="title_td">
        <strong><?php echo _REALESTATE_MANAGER_LABEL_AGENT; ?>:</strong>
      </td>
      <td>
        <?php echo $house->agent; ?>
      </td>
    </tr>
    <?php } ?>
    <?php
    if ($params->get('show_contacts_line')) {

      if ($params->get('show_contacts_registrationlevel')) {

        if (trim($house->contacts)) {
          ?>		
          <tr>
            <td nowrap="nowrap" align="right" class="title_td" >
              <strong><?php echo _REALESTATE_MANAGER_LABEL_CONTACTS; ?>:</strong>
            </td>
            <td>
              <?php echo $house->contacts; ?>
            </td>
          </tr>
          <?php }
        }
      } ?>
      <?php
      if ($house->listing_status != 0) {
        $listing_status1 = explode(',', _REALESTATE_MANAGER_OPTION_LISTING_STATUS);
        $i = 1;
        foreach ($listing_status1 as $listing_status2) {
          $listing_status[$i] = $listing_status2;
          $i++;
        }
        ?>
        <tr>
          <td nowrap="nowrap" align="right" class="title_td" >
            <strong><?php echo _REALESTATE_MANAGER_LABEL_LISTING_STATUS; ?>:</strong>
          </td>
          <td>
            <?php echo $listing_status[$house->listing_status]; ?>
          </td>
        </tr>
        <?php } ?>
        <?php
        if ($house->property_type != 0) {
          $property_type1 = explode(',', _REALESTATE_MANAGER_OPTION_PROPERTY_TYPE);
          $i = 1;
          foreach ($property_type1 as $property_type2) {
            $property_type[$i] = $property_type2;
            $i++;
          }
          ?>
          <tr>
            <td nowrap="nowrap" align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_PROPERTY_TYPE; ?>:</strong>
            </td>
            <td>
              <?php echo $property_type[$house->property_type]; ?>
            </td>
          </tr>
          <?php } ?>
          <?php if (trim($house->lot_size)) { ?>
          <tr>
            <td nowrap="nowrap" align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_LOT_SIZE; ?>:</strong>
            </td>
            <td>
              <?php echo $house->lot_size; ?>
            </td>
          </tr>
          <?php } ?>
          <?php if (trim($house->house_size)) { ?>
          <tr>
            <td nowrap="nowrap" align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_HOUSE_SIZE; ?>:</strong>
            </td>
            <td>
              <?php echo $house->house_size; ?>
            </td>
          </tr>
          <?php } ?>
          <?php if (trim($house->garages)) { ?>
          <tr>
            <td nowrap="nowrap" align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_GARAGES; ?>:</strong>
            </td>
            <td>
              <?php echo $house->garages; ?>
            </td>
          </tr>
          <?php } ?>
          <?php if (trim($house->year)) { ?>
          <tr>
            <td nowrap="nowrap" align="right" class="title_td">
              <strong><?php echo _REALESTATE_MANAGER_LABEL_BUILD_YEAR; ?>:</strong>
            </td>
            <td>
              <?php echo $house->year; ?>
            </td>
          </tr>
          <?php } ?>
        </table>

        <?php
        exit();
      }

    /**
     * Display links to categories
     */
    static function showCategories(&$params, &$categories, &$catid, &$tabclass, &$currentcat, $layout) {
      global $mosConfig_absolute_path;
      $type = 'all_categories';
      require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
    }

    static function showAddButton($Itemid) {
      global $mosConfig_live_site;
      ?>
      <form action="<?php
      echo sefRelToAbs("index.php?option=com_realestatemanager&amp;task=show_add&amp;Itemid="
      . $Itemid); ?>" method="post" name="show_add" enctype="multipart/form-data">
      <input  type="submit" name="submit" value="<?php
      echo _REALESTATE_MANAGER_LABEL_BUTTON_ADD_HOUSE; ?>" class="button"/>
    </form>
    <?php
  }

  static function showButtonMyHouses() {
    global $mosConfig_live_site, $Itemid;
    ?>
    <form action="<?php
    echo sefRelToAbs("index.php?option=com_realestatemanager&amp;task=my_houses&amp;Itemid="
    . $Itemid); ?>" method="post" name="show_my_houses">
    <input  type="submit" name="submit" value="<?php
    echo _REALESTATE_MANAGER_LABEL_SHOW_MY_HOUSES; ?>" class="button"/>
  </form>
  <?php
}

static function showOwnersButton() {
  global $mosConfig_live_site, $Itemid;
  ?>
  <form action="<?php
  echo sefRelToAbs("index.php?option=com_realestatemanager&amp;task=owners_list&amp;Itemid="
  . $Itemid); ?>" method="post" name="ownerslist">
  <input  type="submit" name="submit" value="<?php
  echo _REALESTATE_MANAGER_LABEL_BUTTON_OWNERSLIST; ?>" class="button"/>
</form>
<?php
}

static function showSearchHouses($params, $currentcat, $clist, $option, $layout = "default") {
  global $mosConfig_absolute_path, $task;
  $type = $task == "search" ? "show_search_result" : "show_search_house";
        // $type = 'show_search_house';
  require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
}

/////////////////////////////////////

static function showRssCategories($params, &$categories, &$catid) {
  global $hide_js, $Itemid, $acl, $mosConfig_live_site, $my;
  global $limit, $total, $limitstart, $paginations, $mainframe, $realestatemanager_configuration;
  $mosConfig_live_site_http = JURI::root();
  echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  echo '<!-- generator="Real Estate manager" -->' . "\n";
  echo '<?xml-stylesheet href="" type="text/css"?>' . "\n";
  echo '<?xml-stylesheet href="" type="text/xsl"?>' . "\n";
  echo '<rss version="2.0">' . "\n";
  echo "<channel>\n";
  if (!$categories) {
    echo "<title>" . $mosConfig_live_site_http . " - " .
    _REALESTATE_MANAGER_TITLE . "</title>\n";
    echo "<description>" . _REALESTATE_MANAGER_TITLE . " - " .
    _REALESTATE_MANAGER_ERROR_HAVENOT_HOUSES_RSS . "</description>\n";
  } else {
    if (!$catid) {
      echo "<title>" . $mosConfig_live_site_http . " - " .
      _REALESTATE_MANAGER_TITLE . " - ALL</title>\n";
      echo "<description><![CDATA[" . _REALESTATE_MANAGER_TITLE .
      "  " . $categories[0]->cdesc . "]]></description>\n";
    } else {
      echo "<title>" . $mosConfig_live_site_http . " - " .
      _REALESTATE_MANAGER_TITLE . " - " . $categories[0]->ctitle . "</title>\n";
      echo "<description><![CDATA[" . _REALESTATE_MANAGER_TITLE .
      "  " . $categories[0]->cdesc . "]]></description>\n";
    }
  }
  echo "<link>" . $mosConfig_live_site . "</link>\n";
  echo "<lastBuildDate>" . date("Y-m-d H:i:s") . "</lastBuildDate>\n";
  echo "<generator>" . _REALESTATE_MANAGER_TITLE . "</generator>\n";

  for ($i = 0; $i < count($categories); $i++) {
            //Select list for listing type
    $listing_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
    $listing_type[1] = _REALESTATE_MANAGER_OPTION_FOR_RENT;
    $listing_type[2] = _REALESTATE_MANAGER_OPTION_FOR_SALE;

            //Select list for listing status type
    $listing_status_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
    $listing_status_type1 = explode(',', _REALESTATE_MANAGER_OPTION_LISTING_STATUS);
    $j = 1;
    foreach ($listing_status_type1 as $listing_status_type2) {
      $listing_status_type[$j] = $listing_status_type2;
      $j++;
    }
            //Select list for property type
    $property_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
    $property_type1 = explode(',', _REALESTATE_MANAGER_OPTION_PROPERTY_TYPE);
    $j = 1;
    foreach ($property_type1 as $property_type2) {
      $property_type[$j] = $property_type2;
      $j++;
    }


    $category = $categories[$i];
    echo "<item>";
    echo "<title>" . $category->htitle . "</title>" . "\n";
    echo "<link>" . $mosConfig_live_site_http . "/index.php?option=com_realestatemanager&amp;Itemid=" .
    $Itemid . "&amp;task=view&amp;id=" . $category->bid . "&amp;catid="
    . $category->cid . "</link>" . "\n";
    echo "<description><![CDATA[";
            //for local images
    $imageURL = ($category->image_link);

    if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
    $file_name = rem_picture_thumbnail($imageURL,
     $realestatemanager_configuration['fotomain']['width'],
     $realestatemanager_configuration['fotomain']['high']);
    $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
    echo '<br /><img alt="' . $category->htitle . '" title="' . $category->htitle .
    '" src="' . $file . '" border="0" class="little">';
    if (trim($category->description))
      echo "<br /><description><b>" . _REALESTATE_MANAGER_LABEL_DESCRIPTION .
    ": </b>" . $category->description . "</description>";
    if ($category->listing_type != 0)
      echo "<br /><listing_type><b>" . _REALESTATE_MANAGER_LABEL_LISTING_TYPE .
    ": </b>" . $listing_type[$category->listing_type] . "</listing_type>";
    if ($category->price > 0)
      echo "<br /><price><b>" . _REALESTATE_MANAGER_LABEL_PRICE . ": </b>" .
    $category->price . "</price>";
    if (trim($category->hlocation))
      echo "<br /><hlocation><b>" . _REALESTATE_MANAGER_LABEL_ADDRESS .
    ": </b>" . $category->hlocation . "</hlocation>";
    echo "<br /><owner><b>" . _REALESTATE_MANAGER_LABEL_OWNER . ": </b>"
    . $category->owneremail . "</owner>";
    if (trim($category->year))
      echo "<br /><year><b>" . _REALESTATE_MANAGER_LABEL_YEAR . ": </b>"
    . $category->year . "</year>";
    if (trim($category->rooms))
      echo "<br /><rooms><b>" . _REALESTATE_MANAGER_LABEL_ROOMS . ": </b>"
    . $category->rooms . "</rooms>";
    if (trim($category->bathrooms))
      echo "<br /><bathrooms><b>" . _REALESTATE_MANAGER_LABEL_BATHROOMS . ": </b>"
    . $category->bathrooms . "</bathrooms>";
    if (trim($category->bedrooms))
      echo "<br /><bedrooms><b>" . _REALESTATE_MANAGER_LABEL_BEDROOMS . ": </b>"
    . $category->bedrooms . "</bedrooms>";
    if ($category->listing_status != 0)
      echo "<br /><listing_status><b>" . _REALESTATE_MANAGER_LABEL_LISTING_STATUS
    . ": </b>" . $listing_status_type[$category->listing_status] . "</listing_status>";
    if (trim($category->contacts))
      if ($params->get('show_contacts_line')) {
        if ($params->get('show_contacts_registrationlevel')) {
          echo "<br /><contacts><b>" . _REALESTATE_MANAGER_LABEL_CONTACTS
          . ": </b>" . $category->contacts . "</contacts>";
        }
      }
      echo "]]></description>\n";
      echo "<pubDate>" . $category->date . "</pubDate>\n";
      echo "</item>\n";
    }
    ?>
  </channel>
</rss>
<?php
exit;
}

static function showOwnersList(&$params, &$ownerslist, &$pageNav, &$layout = "default") {
  global $mosConfig_absolute_path, $realestatemanager_configuration;
  $type = 'owner_houses';
  require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
}

static function showRentRequestThanks($params, $backlink, $currentcat, $houseid=NULL, $time_difference=NULL) { 
  global $Itemid, $doc, $mosConfig_live_site, $hide_js, $catid,
  $option, $realestatemanager_configuration;;
  $doc->addStyleSheet($mosConfig_live_site .
   '/components/com_realestatemanager/includes/realestatemanager.css');
   ?>
   <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
   </div>
   <?php

   if($houseid){
           $item_name = $houseid->htitle;							//'Donate to website.com';
           $paypal_real_or_test =  $realestatemanager_configuration['paypal_real_or_test']['show'];

           if($paypal_real_or_test==0)
            $paypal_path = 'www.sandbox.paypal.com';        
          else
            $paypal_path = 'www.paypal.com';

          if($time_difference){
                $amount = $time_difference[0]; // price 198
                $currency_code = $time_difference[1] ; // priceunit  
              }
              else{
                $amount= $houseid->price; // 210
                $currency_code = $houseid->priceunit;
              }

              $amountLine='';
              $amountLine .= '<input type="hidden" name="amount" value="'.$amount.'" />'."\n"; 
            }

            ?> 

            <div class="save_add_table">

              <div class="descrip"><?php echo $currentcat->descrip; ?></div>  
            </div>

            <?php
            if ($option != 'com_realestatemanager') {
              $form_action = "index.php?option=" . $option . "&Itemid=" . $Itemid  ;
            }
            else
              $form_action = "index.php?option=com_realestatemanager&Itemid=" . $Itemid;
            ?>
            <div class="basictable_15 basictable">
              <div>
                <?php 
                $acompte = 0.30;
                echo '<br/> '._REALESTATE_MANAGER_TOTAL_PRICE .$amount.' '.$currency_code;
                echo '<br/> '._REALESTATE_MANAGER_ACCOUNT_L_PRICE .$amount*$acompte.' '.$currency_code.'</div>';
                ?>
              </div>
              <div class="paypal"><h3>Paiement par PayPal</h3>
                <?php

                        //paypal button denis 25.12.2013

                if($params->get('paypal_buy_status') && $params->get('paypal_buy_status_rl')):
                  if($params->get('paypal_buy_status') == 1
                    and isset($amount) and isset($currency_code) ){
                    if($params->get('paypal_buy_status_rl') == 1){
                     echo '<br/> '._REALESTATE_MANAGER_RENT_NOW_BY_PAYPAL.' <br/><br/>';
                     $houseid->price=$amount*$acompte;
                     echo HTML_realestatemanager :: getSaleForm($houseid,$realestatemanager_configuration);
                     ?>
                     <?php
                   }
                 }
                 if($params->get('paypal_buy_status') == 2 and isset($amount) and isset($currency_code) ){
                  if($params->get('paypal_buy_status_rl') == 2){
                    echo '<br/> '._REALESTATE_MANAGER_RENT_NOW_BY_PAYPAL.' <br/><br/>';
                    $houseid->price=$amount*$acompte;
                    echo HTML_realestatemanager :: getSaleForm($houseid,$realestatemanager_configuration);
                  }
                }
                ?>

              </form>

                        <?php //end paypal button
                      else: 
                        ?>
                        
                        <input class="button" type="submit" ONCLICK="window.location.href='<?php $user = JFactory::getUser(); echo $backlink; ?>'" value="OK">

                      <?php endif;?>
                    </div>
                    <div class="cheque">
                      <h3>Paiement par chèque</h3>
                      <p>Veuillez adresser un chèque d'un montant de <?php echo $amount*$acompte.' '.$currency_code; ?> à l'odre : MME LETORT Monique</p>
                      <form action="index.php" method="post">
                       <input type="hidden" name="item_number" value="<?php echo $_REQUEST['orderID']; ?>" />
                       <input id="cheque" name="cheque" class="btn btn-info" type="button" onclick="updatechequepayment();" value="Payer par chèque"></input>
                       <script type="text/javascript">
                         function updatechequepayment() {
                           //instruction pour un paiement par chèque
                           var order_id = <?php echo $_REQUEST['orderID']; ?>;
                           jQuerREL.ajax({ 
                            type: "POST",
                            url: "index.php?option=com_realestatemanager&task=ajax_update_check_payment",
                            data : 'order_id=' + order_id,
                            success: function( data ) {
                              jQuerREL("#response-cheque").html(data);
                            }
                          });
                         }
                       </script>
                     </form>
                     <p id="response-cheque"></p>
                   </div>
                 </div>
                 <?php


               }
//********************************************************************************************************
               static function getSaleForm($realestate,$realestatemanager_configuration){
                if($realestate){
                  getHTMLPayPalRM($realestate,$realestatemanager_configuration['plugin_name_select']);
                }
              }
//********************************************************************************************************    


              static function showTabs(&$params, &$userid, &$username, &$comprofiler, &$option) {

                global $mosConfig_live_site, $doc;
                $doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/TABS/tabcontent.css');
                $doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/TABS/tabcontent.js');


                ?>

                <?php 
                if(checkJavaScriptIncludedRE("jQuerREL-1.2.6.js") === false ) {
                  $doc->addScript(JURI::root(true) . '/components/com_realestatemanager/lightbox/js/jQuerREL-1.2.6.js');
                } 
                ?>
                <script type="text/javascript">jQuerREL=jQuerREL.noConflict();</script>
              </br> <!-- br for plugin simplemembership!!! -->
              <div class='tabs_buttons'>
                <ul id="countrytabs" class="shadetabs">
                  <?php

                  if ($params->get('show_edit_registrationlevel')) {
                    ?>
                    <li>
                      <a class="my_houses_edit" href="<?php echo JRoute::_('index.php?option='
                       . $option .'&userId='.$userid . '&task=edit_my_houses' . $comprofiler, false);
                       ?>"><?php echo _REALESTATE_MANAGER_SHOW_TABS_SHOW_MY_HOUSES; ?></a>
                     </li>
                     <?php
                   }

                   if ($params->get('show_rent')) {

                    if ($params->get('show_rent_registrationlevel')) {
                      ?>
                      <li>
                        <a class="my_houses_rent" href="<?php echo JRoute::_('index.php?option='
                         . $option . '&userId='.$userid . '&task=rent_requests' . $comprofiler , false);
                         ?>"><?php echo _REALESTATE_MANAGER_SHOW_TABS_RENT_REQUESTS; ?></a>
                       </li>
                       <?php
                     }
                   }
                   if ($params->get('show_buy')) {
                    if ($params->get('show_buy_registrationlevel')) {
                      ?>
                      <li>
                        <a class="my_houses_buy" href="<?php echo JRoute::_('index.php?option='
                         . $option . '&userId='.$userid . '&task=buying_requests' . $comprofiler , false);
                         ?>"><?php echo _REALESTATE_MANAGER_SHOW_TABS_BUYING_REQUESTS; ?></a>
                       </li>
                       <?php
                     }
                   }
                   if ($params->get('show_history')) {
                    if ($params->get('show_history_registrationlevel')) {
                      ?>
                      <li>
                        <a class="my_houses_history" href="<?php echo JRoute::_('index.php?option='
                         . $option . '&userId='.$userid . '&task=rent_history' . $comprofiler , false);
                         ?>"><?php echo _REALESTATE_MANAGER_LABEL_CBHISTORY_ML; ?></a>
                       </li>
                       <?php
                     }
                   }
                   ?>
                 </ul>
               </div>
               <script type="text/javascript">
                jQuerREL(document).ready(function(){
                  var atr = jQuerREL("#adminForm div:first-child").attr("id");
                  if(!atr){
                    atr = jQuerREL("#adminForm table:first-child").attr("id");
                  }
                  jQuerREL("#countrytabs > li > a."+atr).addClass("selected");
                  jQuerREL("#countrytabs > li > a").click(function(){
                   jQuerREL("#countrytabs > li > a").removeClass("selected");
                   jQuerREL(this).addClass("selected");
                 });
                });

              </script>
              <?php
            }

            static function showMyHouses(&$houses, &$params, &$pageNav, $option, $layout = "default") {
              global $mosConfig_absolute_path, $Itemid;
                //require($mosConfig_absolute_path.
                // "/components/com_realestatemanager/views/my_houses/tmpl/".$layout.".php");
              $type = 'my_houses';
              require getLayoutPath::getLayoutPathCom('com_realestatemanager', $type, $layout);
            }

            static function showRentHouses($option, $house1, $rows, & $userlist, $type) {
              global $my, $mosConfig_live_site, $mainframe, $doc, $Itemid, $realestatemanager_configuration;
              ?>
              <?php 
              if(checkJavaScriptIncludedRE("jQuerREL-1.2.6.js") === false ) {
                $doc->addScript(JURI::root(true) . '/components/com_realestatemanager/lightbox/js/jQuerREL-1.2.6.js');
              } 
              ?>
              <script type="text/javascript">jQuerREL=jQuerREL.noConflict();</script>


              <?php 

              if(checkJavaScriptIncludedRE("jQuerREL-ui.js") === false ) {
                $doc->addScript(JURI::root(true) . '          echo $mosConfig_live_site; ?>/components/com_realestatemanager/includes/jQuerREL-ui.js');
              }

              ?>

              <?php
              $doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/includes/functions.js');
              $doc->addStyleSheet($mosConfig_live_site .
               '/components/com_realestatemanager/includes/realestatemanager.css');
               ?>
               <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
               <form action="index.php" method="get" name="adminForm" id="adminForm">
                <?php
                if ($type == "rent" || $type == "edit_rent") {
                  ?>
                  <div class="my_real_table_rent">
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_TO . ":"; ?></span>
                      <span class="col_02"><?php echo $userlist; ?></span>
                    </div>
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_USER . ":"; ?></span>
                      <span class="col_02"><input type="text" name="user_name" class="inputbox" /></span>
                    </div>
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_EMAIL . ":"; ?></span>
                      <span class="col_02"><input type="text" name="user_email" class="inputbox" /></span>
                    </div>
                    <script>
                      Date.prototype.toLocaleFormat = function(format) {
                        var f = {Y : this.getYear() + 1900,m : this.getMonth() + 
                          1,d : this.getDate(),H : this.getHours(),M : this.getMinutes(),S : this.getSeconds()}
                          for(k in f)
                            format = format.replace('%' + k, f[k] < 10 ? "0" + f[k] : f[k]);
                          return format;
                        };

                        window.onload = function ()

                        {
                          var today = new Date();
                          var date = today.toLocaleFormat("<?php echo $realestatemanager_configuration['date_format'] ?>");
                          document.getElementById('rent_from').value = date;
                          document.getElementById('rent_until').value = date;
                        };

                      </script>
                      <!--///////////////////////////////calendar///////////////////////////////////////-->
                      <script language="javascript" type="text/javascript">
                        <?php
                        $house_id_fordate =  $house1->id;
                        $date_NA = available_dates($house_id_fordate);
                        ?>
                        var unavailableDates = Array();
                        jQuerREL(document).ready(function() {
                        //var unavailableDates = Array();
                        var k=0;
                        <?php if(!empty($date_NA)){?>
                        <?php foreach ($date_NA as $N_A){ ?>
                        unavailableDates[k]= '<?php echo $N_A; ?>';
                        k++;
                        <?php } ?>
                        <?php } ?>

                        function unavailable(date) {
                        dmy = date.getFullYear() + "-" + ('0'+(date.getMonth() + 1)).slice(-2) +
                        "-" + ('0'+date.getDate()).slice(-2);
                        if (jQuerREL.inArray(dmy, unavailableDates) == -1) {
                        return [true, ""];
                      } else {
                      return [false, "", "Unavailable"];
                    }
                  }

                  jQuerREL( "#rent_from, #rent_until" ).datepicker(
                  {

                    minDate: "+0",
                    dateFormat: "<?php echo transforDateFromPhpToJquery();?>",
                    beforeShowDay: unavailable,

                  });
                });



              </script>


              <!--///////////////////////////////////////////////////////////////////////////-->
              <div class="my_real">
                <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM . ":"; ?></span>
                <p><input type="text" id="rent_from" name="rent_from"></p>
              </div>
              <div class="my_real">
                <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_TIME; ?></span>
                <p><input type="text" id="rent_until" name="rent_until"></p>
              </div>
            </div>


            <?php } else { 
             echo "";
           } 
           $all = JFactory::getDBO();
           $query = "SELECT * FROM #__rem_rent";
           $all->setQuery($query);
           $num = $all->loadObjectList();
           ?>
           <div class="table_63">

            <div class="row_01">
              <span class="col_01">
                <?php if ($type != 'rent') {
                  ?>
                  <input type="checkbox" name="toggle" value="" onClick="rem_checkAll(this);" />
                  <span class="toggle_check">check All</span>
                  <?php } ?>
                </span>

              </div>

              <?php


              if ($type == "rent")
              {
                ?>
                <td align="center">  <input class="inputbox"  type="checkbox"
                  name="checkHouse" id="checkHouse" size="0" maxlength="0" value="on" /></td>
                  <?php
                } else if ($type == "edit_rent"){ ?>
                <input type="hidden"  name="checkHouse" id="checkHouse" value="on" /></td>
                <?php
              }
              $assoc_title = '';
              for ($t = 0, $z = count($rows); $t < $z; $t++) {
                if($rows[$t]->id != $house1->id) $assoc_title .= " ".$rows[$t]->htitle;
              }

              print_r("
                <td align=\"center\">". $house1->id ."</td>
                <td align=\"center\">" . $house1->houseid . "</td>
                <td align=\"center\">" . $house1->htitle . " ( " . $assoc_title ." ) " . "</td>
                </tr>");


              for ($j = 0, $n = count($rows); $j < $n; $j++) {
                $row = $rows[$j];

                ?>


                <input class="inputbox" type="hidden" name="houseid" id="houseid"
                size="0" maxlength="0" value="<?php echo $house1->houseid; ?>" />
                <input class="inputbox" type="hidden" name="id" id="id" size="0"
                maxlength="0" value="<?php echo $row->id; ?>" />
                <input class="inputbox" type="hidden" name="htitle" id="htitle"
                size="0" maxlength="0" value="<?php echo $row->htitle; ?>" />
                <?php

                $house = $row->id;
                $data = JFactory::getDBO();
                $query = "SELECT * FROM #__rem_rent WHERE fk_houseid=" . $house . " ORDER BY rent_return ";
                $data->setQuery($query);
                $allrent = $data->loadObjectList();



                $num = 1;
                for ($i = 0, $nn = count($allrent); $i < $nn; $i++) {
                  ?>

                  <div class="box_rent_real">

                    <div class="row_01 row_rent_real">
                      <?php if (!isset($allrent[$i]->rent_return) && $type != "rent") {
                        ?>
                        <span class="rent_check_vid">
                          <input type="checkbox" id="cb<?php echo $i; ?>" 
                          name="bid[]" value="<?php echo $allrent[$i]->id; ?>" 
                          onClick="isChecked(this.checked);" />
                        </span>
                        <?php } else {
                          ?>
                          <?php } ?>
                          <span class="col_01">id</span>
                          <span class="col_02"><?php echo $num; ?></span>
                        </div>

                        <div class="row_02 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?></span>  
                          <span class="col_02"><?php echo $row->houseid; ?></span>
                        </div>

                        <div class="row_03 row_rent_real">
                          <?php echo $row->htitle; ?>
                        </div>

                        <div class="from_until_return">
                          <div class="row_04 row_rent_real">
                            <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM; ?></span>  
                            <span class="col_02"><?php echo data_transform_rem($allrent[$i]->rent_from); ?></span>
                          </div>
                          <br />
                          <div class="row_05 row_rent_real">
                            <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_UNTIL; ?></span>  
                            <span class="col_02"><?php echo data_transform_rem($allrent[$i]->rent_until); ?></span>
                          </div>
                          <br />
                          <div class="row_06 row_rent_real">
                            <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_RETURN; ?></span>  
                            <span class="col_02"><?php echo data_transform_rem($allrent[$i]->rent_return); ?></span>
                          </div>
                        </div>

                      </div>
                      <?php
                      if ($allrent[$i]->fk_userid != null)
                        print_r("<div class='rent_user'>" . $allrent[$i]->user_name . "</div>");
                      else
                        print_r("<div class='rent_user'>" . $allrent[$i]->user_name . $allrent[$i]->user_email . "</div>");
                      $num++;
                    }

                  }
                  ?>
                </div> <!-- table_63 -->


                <input type="hidden" name="option" value="<?php echo $option; ?>" />
                <input type="hidden" id="adminFormTaskInput" name="task" value="" />
                <input type="hidden" name="save" value="1" />
                <input type="hidden" name="boxchecked" value="1" />
                <?php
                if ($option != "com_realestatemanager") {
                  ?>
                  <input type="hidden" name="is_show_data" value="1" />
                  <?php
                }
                ?>
                <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />

                <?php if ($type == "rent") { ?>
                <input type="button" name="rent_save" value="<?php 
                echo _REALESTATE_MANAGER_LABEL_BUTTON_RENT; ?>" onclick="rem_buttonClickRent(this)"/>
                <?php } ?>
                <?php if ($type == "rent_return") { ?>
                <input type="button" name="rentout_save" value="<?php 
                echo _REALESTATE_MANAGER_LABEL_RENT_RETURN; ?>" onclick="rem_buttonClickRent(this)"/>
                <?php } ?>
              </form>
              <?php
            }



            static function editRentHouses($option, $house1, $rows, $title_assoc, & $userlist, & $all_assosiate_rent, $type) {
              global $my, $mosConfig_live_site, $mainframe, $doc, $Itemid, $realestatemanager_configuration;

              ?>
              <?php 
              if(checkJavaScriptIncludedRE("jQuerREL-1.2.6.js") === false ) {
                $doc->addScript(JURI::root(true) . '/components/com_realestatemanager/lightbox/js/jQuerREL-1.2.6.js');
              } 
              ?>
              <script type="text/javascript">jQuerREL=jQuerREL.noConflict();</script>



              <?php 

              if(checkJavaScriptIncludedRE("jQuerREL-ui.js") === false ) {
                $doc->addScript(JURI::root(true) . '/components/com_realestatemanager/includes/jQuerREL-ui.js');
              }


              ?>



              <?php


              $doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/includes/functions.js');
              $doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/realestatemanager.css');
              ?>

              <!--///////////////////////////////calendar///////////////////////////////////////-->
              <script language="javascript" type="text/javascript">
                jQuerREL(document).ready(function() {
                  jQuerREL( "#rent_from, #rent_until" ).datepicker(
                  {
                    dateFormat: "<?php echo transforDateFromPhpToJquery();?>",
                  });
                });



              </script>


              <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
              <form action="index.php" method="post" name="adminForm" id="adminForm">
                <?php
                if ($type == "rent" || $type == "edit_rent") {
                  ?>
                  <div class="my_real_table_rent">
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_TO . ":"; ?></span>
                      <span class="col_02"><?php echo $userlist; ?></span>
                    </div>
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_USER . ":"; ?></span>
                      <span class="col_02"><input type="text" name="user_name" class="inputbox" /></span>
                    </div>
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_EMAIL . ":"; ?></span>
                      <span class="col_02"><input type="text" name="user_email" class="inputbox" /></span>
                    </div>
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM . ":"; ?></span>
                      <p><input type="text" id="rent_from" name="rent_from"></p>
                    </div>
                    <div class="my_real">
                      <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_TIME; ?></span>
                      <p><input type="text" id="rent_until" name="rent_until"></p>
                    </div>
                  </div>

                  <!--/////////////////////////////////////////////-->



                  <?php } else { 
                    echo "";
                  }
                  $all = JFactory::getDBO();
                  $query = "SELECT * FROM #__rem_rent ";
                  $all->setQuery($query);
                  $num = $all->loadObjectList();
                  ?>

                  <div class="table_63">

                    <div class="row_01">
                      <span class="col_01">
                      </span>
                    </div>

                    <?php
                    $assoc_title = ''; 
                    for ($t = 0, $z = count($title_assoc); $t < $z; $t++) {
                      if($title_assoc[$t]->htitle != $house1->htitle) $assoc_title .= " ".$title_assoc[$t]->htitle; 
                    }

                 //show rent history what we may change
                    ?>

                    <input class="inputbox" type="hidden"  name="houseid" id="houseid" size="0"
                    maxlength="0" value="<?php echo $house1->houseid; ?>" />
                    <input class="inputbox"  type="hidden"  name="id" id="id" size="0" maxlength="0"
                    value="<?php echo $house1->id; ?>" />
                    <input class="inputbox"  type="hidden"  name="id2" id="id2" size="0" maxlength="0"
                    value="<?php echo $house1->id; ?>" />   
                    <?php

                    if ($type == "edit_rent"){ ?>
                    <input type="hidden"  name="checkHouse" id="checkHouse" value="on" />
                    <?php
                  }

                  $num = 1;
                  for ($y = 0, $n2 = count($all_assosiate_rent[0]); $y < $n2; $y++) {
                    $assoc_rent_ids = '';
                    for ($j = 0, $n3 = count($all_assosiate_rent); $j < $n3; $j++) {
                      if($assoc_rent_ids != "" ) $assoc_rent_ids .= ",".$all_assosiate_rent[$j][$y]->id;
                      else $assoc_rent_ids = $all_assosiate_rent[$j][$y]->id;
                    }
                    ?>

                    <div class="box_rent_real">

                      <div class="row_01 row_rent_real">

                        <span class="rent_check_vid">
                          <input type="checkbox" id="cb<?php echo $y; ?>" name="bid[]"
                          value="<?php echo $assoc_rent_ids; ?>" onClick="Joomla.isChecked(this.checked);" />

                        </span>

                        <span class="col_01">id</span>
                        <span class="col_02"><?php echo $num; ?></span>
                      </div>

                      <div class="row_02 row_rent_real">
                        <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?></span>
                        <span class="col_02"><?php echo $house1->houseid; ?></span>
                      </div>

                      <div class="row_03 row_rent_real">
                        <?php echo $house1->htitle . " ( " . $assoc_title ." ) " ?>
                      </div>

                      <div class="from_until_return">
                        <div class="row_04 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_USER; ?></span>
                          <span class="col_02"><?php echo $all_assosiate_rent[0][$y]->user_name; ?></span>
                        </div>
                        <br />
                        <div class="row_04 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM; ?></span>
                          <span class="col_02"><?php echo data_transform_rem($all_assosiate_rent[0][$y]->rent_from); ?></span>
                        </div>
                        <br />
                        <div class="row_05 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_UNTIL; ?></span>
                          <span class="col_02"><?php echo data_transform_rem($all_assosiate_rent[0][$y]->rent_until); ?></span>
                        </div>
                        <br />
                        <div class="row_06 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_RETURN; ?></span>
                          <span class="col_02"><?php echo data_transform_rem($all_assosiate_rent[0][$y]->rent_return); ?></span>
                        </div>
                      </div>

                    </div>

                    <?php

                    $num++;
                  }

                  ?>
                  <div class="box_rent_real">
                    <div class="row_01 row_rent_real">---------------------------------------
                    </div>
                  </div>

                  <?php
                   //show rent history what we can't change
                  for ($j = 0, $n = count($rows); $j < $n; $j++) {
                    $row = $rows[$j];
                    if($row->rent_return == "" ) continue ;

                    $num = 1;
                    ?>


                    <div class="box_rent_real">
                      <div class="row_01 row_rent_real">
                        <span class="col_01">id</span>
                        <span class="col_02"><?php echo $num; ?></span>
                      </div>
                      <div class="row_01 row_rent_real">
                        <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?></span>
                        <span class="col_02"><?php echo $row->houseid; ?></span>
                      </div>
                      <div class="row_02 row_rent_real"><?php echo $row->htitle ; ?> </div>
                      <div class="from_until_return">
                        <div class="row_04 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_USER; ?></span>
                          <span class="col_02"><?php echo $row->user_name; ?></span>
                        </div>
                        <br />
                        <div class="row_04 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM; ?></span>
                          <span class="col_02"><?php echo data_transform_rem($row->rent_from); ?></span>
                        </div>
                        <br />
                        <div class="row_05 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_UNTIL; ?></span>
                          <span class="col_02"><?php echo data_transform_rem($row->rent_until); ?></span>
                        </div>
                        <br />
                        <div class="row_06 row_rent_real">
                          <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_RENT_RETURN; ?></span>
                          <span class="col_02"><?php echo data_transform_rem($row->rent_return); ?></span>
                        </div>
                      </div>
                    </div>

                    <?php } ?>



                  </div> <!-- table_63 -->


                  <input type="hidden" name="option" value="<?php echo $option; ?>" />
                  <input type="hidden" id="adminFormTaskInput" name="task" value="" />
                  <input type="hidden" name="save" value="1" />
                  <input type="hidden" name="boxchecked" value="1" />
                  <?php
                  if ($option != "com_realestatemanager") {
                    ?>
                    <input type="hidden" name="is_show_data" value="1" />
                    <?php
                  }
                  ?>
                  <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />

                  <?php if ($type == "rent" ) { ?>
                  <input type="button" name="rent_save" value="<?php
                  echo _REALESTATE_MANAGER_LABEL_BUTTON_RENT; ?>" onclick="rem_buttonClickRent(this)"/>
                  <?php } ?>
                  <?php if ($type == "edit_rent") { ?>
                  <input type="button" name="edit_rent" value="<?php
                  echo _REALESTATE_MANAGER_LABEL_BUTTON_RENT; ?>" onclick="rem_buttonClickRent(this)"/>
                  <input type="hidden" name="save" value="1" />
                  <?php } ?>
                  <?php if ($type == "rent_return") { ?>
                  <input type="button" name="rentout_save" value="<?php
                  echo _REALESTATE_MANAGER_LABEL_RENT_RETURN; ?>" onclick="rem_buttonClickRent(this)"/>
                  <?php } ?>
                </form>
                <?php
              } 


              static function showRentHistory($option, $rows, $pageNav) {
                global $my, $Itemid, $mosConfig_live_site, $mainframe, $doc;
                $session = JFactory::getSession();
                $arr = $session->get("array", "default");
                $doc->addStyleSheet($mosConfig_live_site . '/components/com_realestatemanager/includes/realestatemanager.css');
                ?>
                <form action="index.php" method="get" name="adminForm" id="adminForm">
                  <table id="my_houses_history" class="table_64 basictable">
                    <tr>
                      <th align = "center" width="5%">#</th>
                      <th align = "center" class="title" width="5%" nowrap="nowrap"><?php
                      echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?></th>
                      <th align = "center" class="title" width="25%" nowrap="nowrap"><?php
                      echo _REALESTATE_MANAGER_LABEL_TITLE; ?></th>
                      <th align = "center" class="title" width="15%" nowrap="nowrap"><?php
                      echo _REALESTATE_MANAGER_LABEL_RENT_FROM; ?></th>
                      <th align = "center" class="title" width="20%" nowrap="nowrap"><?php
                      echo _REALESTATE_MANAGER_LABEL_RENT_UNTIL; ?></th>
                      <th align = "center" class="title" width="20%" nowrap="nowrap"><?php
                      echo _REALESTATE_MANAGER_LABEL_RENT_RETURN; ?></th>
                    </tr>
                    <?php
                    $numb = 0;
                    for ($i = 0, $n = count($rows); $i < $n; $i++) {
                      $row = $rows[$i];
                      $house = $row->id;
                      $title = $row->htitle;
                      $numb++;
                      print_r("<td align=\"center\">" . $numb . "</td>
                       <td align=\"center\">" . $row->houseid . "</td>
                       <td align=\"center\">" . $row->htitle . "</td>
                       <td align=\"center\">" . data_transform_rem($row->rent_from) . "</td>
                       <td align=\"center\">" . data_transform_rem($row->rent_until) . "</td>
                       <td align=\"center\">" . data_transform_rem($row->rent_return) . "</td></tr>");
                    }
                    ?>

                  </table>

                  <div id="pagenavig">
                    <?php
                    $paginations = $arr;
                    if ($paginations && ($pageNav->total > $pageNav->limit))
                      echo $pageNav->getPagesLinks();
                    ?>
                  </div>

                </form>
                <?php
              }

              static function showRequestRentHouses($option, $rent_requests, &$pageNav) {
                global $my, $mosConfig_live_site, $mainframe, $doc, $Itemid;
                $session = JFactory::getSession();
                $arr = $session->get("array", "default");
                $doc->addScript($mosConfig_live_site .
                 '/components/com_realestatemanager/includes/functions.js');
                $doc->addStyleSheet($mosConfig_live_site .
                 '/components/com_realestatemanager/includes/realestatemanager.css');
                 ?>
                 <form action="index.php" method="get" name="adminForm" id="adminForm">
                  <table id="my_houses_rent" cellpadding="4" cellspacing="0"
                  border="0" width="100%" class="basictable table_65">
                  <tr>
                    <th align = "center" width="20">
                      <input type="checkbox" name="toggle" value="" onClick="rem_checkAll(this);" />
                    </th>
                    <th align = "center" width="30">#</th>
                    <th align = "center" class="title" width="10%" nowrap="nowrap">
                      <?php echo _REALESTATE_MANAGER_LABEL_RENT_FROM; ?></th>
                      <th align = "center" class="title" width="10%" nowrap="nowrap">
                        <?php echo _REALESTATE_MANAGER_LABEL_RENT_UNTIL; ?></th>
                        <th align = "center" class="title" width="5%" nowrap="nowrap">
                          <?php echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?></th>
                          <th align = "center" class="title" width="15%" nowrap="nowrap">
                            <?php echo _REALESTATE_MANAGER_LABEL_TITLE; ?></th>
                            <th align = "center" class="title" width="15%" nowrap="nowrap">
                              <?php echo _REALESTATE_MANAGER_LABEL_RENT_USER; ?></th>
                              <th align = "center" class="title" width="15%" nowrap="nowrap">
                                <?php echo _REALESTATE_MANAGER_LABEL_RENT_EMAIL; ?></th>
                                <th align = "center" class="title" width="20%" nowrap="nowrap">
                                  <?php echo _REALESTATE_MANAGER_LABEL_RENT_ADRES; ?></th>
                                </tr>
                                <?php
                                for ($i = 0, $n = count($rent_requests); $i < $n; $i++) {
                                  $row = $rent_requests[$i];
                                  $assoc_title = ''; 
                                  $assoc_title = (isset($row->title_assoc))? " ( " . $row->title_assoc ." ) "  : '';
                                  if($assoc_title){
                                    for ($t = 0, $z = count($assoc_title); $t < $z; $t++) {
                                     if($assoc_title[$t]->htitle != $row->htitle) 
                                       $assoc_title .= " ".$assoc_title[$t]->htitle; 
                                   }
                                 }
                                 ?>
                                 <tr class="row<?php echo $i % 2; ?>">
                                  <td width="20" align="center">
                                    <?php
                                    echo mosHTML::idBox($i, $row->id, 0, 'bid');
                                    ?>
                                  </td>
                                  <td align = "center"><?php echo $row->id; ?></td>
                                  <td align = "center">
                                    <?php echo $row->rent_from; ?>
                                  </td>
                                  <td align = "center">
                                    <?php echo $row->rent_until; ?>
                                  </td>
                                  <td align = "center">
                                    <?php
                                    $data = JFactory::getDBO();
                                    $query = "SELECT houseid FROM #__rem_houses where id = " . $row->fk_houseid . " ";
                                    $data->setQuery($query);
                                    $houseid = $data->loadObjectList();
                                    echo $houseid[0]->houseid;
                                    ?>
                                  </td>
                                  <td align = "center">
                                    <?php echo $row->htitle . $assoc_title; ?>
                                  </td>
                                  <td align = "center">
                                    <?php echo $row->user_name; ?>
                                  </td>
                                  <td align = "center">
                                    <a href=mailto:"<?php echo $row->user_email; ?>">
                                      <?php echo $row->user_email; ?>
                                    </a>
                                  </td>
                                  <td align = "center">
                                    <?php echo $row->user_mailing; ?>
                                  </td>
                                </tr>
                                <?php
                              }
                              ?>
                            </table>

                            <div id="pagenavig">
                              <?php
                              $paginations = $arr;
                              if ($paginations && ($pageNav->total > $pageNav->limit)) {
                                echo $pageNav->getPagesLinks();
                              }
                              ?>
                            </div>

                            <input type="hidden" name="option" value="<?php echo $option; ?>" />
                            <?php
                            if ($option != "com_realestatemanager") {
                              ?>
                              <input type="hidden" name="is_show_data" value="1" />
                              <?php
                            }
                            ?>
                            <input type="hidden" id="adminFormTaskInput" name="task" value="" />
                            <input type="hidden" name="boxchecked" value="0" />
                            <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
                            <input type="button" name="acceptButton" value="<?php echo _REALESTATE_MANAGER_TOOLBAR_ADMIN_ACCEPT; ?>"
                            onclick="rem_buttonClickRentRequest(this)"/>
                            <input type="button" name="declineButton" value="<?php echo _REALESTATE_MANAGER_TOOLBAR_ADMIN_DECLINE; ?>"
                            onclick="rem_buttonClickRentRequest(this)"/>
                          </form>
                          <?php
                        }

                        static function listCategories(&$params, $cat_all, $catid, $tabclass, $currentcat) {
                          global $Itemid, $mosConfig_live_site, $doc;
                          $doc->addStyleSheet($mosConfig_live_site .
                           '/components/com_realestatemanager/includes/realestatemanager.css');
                           ?>
                           <?php positions_rem($params->get('allcategories04')); ?>
                           <div class="basictable table_58">
                            <div class="row_01">
                              <span class="col_01 sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
                                <?php echo _REALESTATE_MANAGER_LABEL_CATEGORY; ?>
                              </span>
                              <?php if ($params->get('rss_show')): ?>
                                <span class=" col_03 sectiontableheader">
                                  <a href="<?php echo 
                                  sefRelToAbs("index.php?option=com_realestatemanager&task=show_rss_categories&Itemid="
                                  . $Itemid); ?>">
                                  <img src="./components/com_realestatemanager/images/rss.png"
                                  alt="All categories RSS" align="right" title="All categories RSS"/>
                                </a>
                              </span>
                            <?php endif; ?>
                            <span class="col_02 sectiontableheader<?php echo $params->get('pageclass_sfx'); ?>">
                              <?php echo _REALESTATE_MANAGER_LABEL_HOUSES; ?> 
                            </span>
                          </div>
                          <br/>
                          <div class="row_02">
                            <?php positions_rem($params->get('allcategories05')); ?>
                            <?php HTML_realestatemanager::showInsertSubCategory($catid, $cat_all, $params, $tabclass, $Itemid, 0); ?>
                          </div>
                        </div>

                        <?php positions_rem($params->get('allcategories06')); ?>
                        <?php
                      }

                      /* function for show subcategory */

                      static function showInsertSubCategory($id, $cat_all, $params, $tabclass, $Itemid, $deep) {
                        global $g_item_count, $realestatemanager_configuration, $mosConfig_live_site;
                        global $doc;

                        $doc->addStyleSheet($mosConfig_live_site .
                         '/components/com_realestatemanager/includes/realestatemanager.css');

                        $deep++;
                        for ($i = 0; $i < count($cat_all); $i++) {
                          if (($id == $cat_all[$i]->parent_id) && ($cat_all[$i]->display == 1)) {
                            $g_item_count++;

                            $link = 'index.php?option=com_realestatemanager&amp;task=showCategory&amp;catid='
                            . $cat_all[$i]->id . '&amp;Itemid=' . $Itemid;
                            ?>
                            <div class="table_59 <?php echo $tabclass[($g_item_count % 2)]; ?>">
                              <span class="col_01">
                                <?php
                                if ($deep != 1) {
                                  $jj = $deep;
                                  while ($jj--) {
                                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                                  }
                                  echo "&nbsp;|_";
                                }
                                ?>
                              </span>
                              <span class="col_01">
                                <?php if (($params->get('show_cat_pic')) && ($cat_all[$i]->image != "")) { ?>
                                <img src="./images/stories/<?php echo $cat_all[$i]->image; ?>"
                                alt="picture for subcategory" height="48" width="48" />&nbsp;
                                <?php } else {
                                  ?>
                                  <a <?php echo "href=" . sefRelToAbs($link); ?> class="category<?php
                                  echo $params->get('pageclass_sfx'); ?>" style="text-decoration: none"><img
                                  src="./components/com_realestatemanager/images/folder.png"
                                  alt="picture for subcategory" height="48" width="48" /></a>&nbsp;
                                  <?php } ?>
                                </span>
                                <span class="col_02">
                                  <a href="<?php echo sefRelToAbs($link); ?>"
                                   class="category<?php echo $params->get('pageclass_sfx'); ?>">
                                   <?php echo $cat_all[$i]->title; ?>
                                 </a>
                               </span>
                               <span class="col_03">
                                <?php if ($cat_all[$i]->houses == '') echo "0";else echo $cat_all[$i]->houses; ?>
                              </span>
                              <?php if ($params->get('rss_show')): ?>
                                <span class="col_04">
                                  <a href="<?php echo 
                                  sefRelToAbs("index.php?option=com_realestatemanager&task=show_rss_categories&catid="
                                  . $cat_all[$i]->id . "&Itemid=" . $Itemid); ?>">
                                  <img src="./components/com_realestatemanager/images/rss2.png"
                                  alt="Category RSS" align="right" title="Category RSS"/>
                                </a>
                              </span>
                            <?php endif; ?>
                          </div>
                          <?php
                          if ($realestatemanager_configuration['subcategory']['show'])
                            HTML_realestatemanager::showInsertSubCategory($cat_all[$i]->id,
                             $cat_all, $params, $tabclass, $Itemid, $deep);
            }//end if ($id == $cat_all[$i]->parent_id)
        }//end for(...) 
      }

      static function showRequestBuyingHouses($option, $buy_requests, $pageNav, $Itemid) {

        global $my, $mosConfig_live_site, $mainframe, $doc;
        $session = JFactory::getSession();
        $arr = $session->get("array", "default");
        $doc->addScript($mosConfig_live_site . '/components/com_realestatemanager/includes/functions.js');
        $doc->addStyleSheet($mosConfig_live_site .
         '/components/com_realestatemanager/includes/realestatemanager.css');
         ?>
         <form action="index.php" method="get" name="adminForm" id="adminForm">

          <table id="my_houses_buy" cellpadding="4" cellspacing="0" border="0"
          width="100%" class="basictable table_66">
          <tr>
            <th align = "center" width="5%"><input type="checkbox" name="toggle"
             value="" onClick="rem_checkAll(this);" /></th>
             <th align = "center" width="5%">#</th>
             <th align = "center" class="title" width="10%" nowrap="nowrap"><?php
             echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?></th>
             <th align = "center" class="title" width="15%" nowrap="nowrap"><?php
             echo _REALESTATE_MANAGER_LABEL_ADDRESS; ?></th>
             <th align = "center" class="title" width="15%" nowrap="nowrap"><?php
             echo _REALESTATE_MANAGER_LABEL_RENT_USER; ?></th>
             <th align = "center" class="title" width="20%" nowrap="nowrap"><?php
             echo _REALESTATE_MANAGER_LABEL_COMMENT; ?></th>
             <th align = "center" class="title" width="15%" nowrap="nowrap"><?php
             echo _REALESTATE_MANAGER_LABEL_RENT_EMAIL; ?></th>
             <th align = "center" class="title" width="15%" nowrap="nowrap"><?php
             echo _REALESTATE_MANAGER_LABEL_BUYING_ADRES; ?></th>
           </tr>
           <?php
           for ($i = 0, $n = count($buy_requests); $i < $n; $i++) {
            $row = $buy_requests[$i];
            ?>
            <tr class="row<?php echo $i % 2; ?>">
              <td width="20">
                <div align = "center">
                  <?php echo mosHTML::idBox($i, $row->id, 0, 'bid'); ?>
                </div>
              </td>
              <td align = "center"><?php echo $row->id; ?></td>
              <td align = "center"><?php echo $row->fk_houseid; ?></td>
              <td align = "center"><?php echo $row->hlocation; ?></td>
              <td align = "center"><?php echo $row->customer_name; ?></td>
              <td align = "center" width="20%"><?php echo $row->customer_comment; ?></td>
              <td align = "center">
                <a href=mailto:"<?php echo $row->customer_email; ?>">
                  <?php echo $row->customer_email; ?>
                </a>
              </td>
              <td align = "center"><?php echo $row->customer_phone; ?></td>
            </tr>
            <?php } ?>
          </table>

          <div id="pagenavig">
            <?php
            $paginations = $arr;
            if ($paginations && ($pageNav->total > $pageNav->limit)) {
              echo $pageNav->getPagesLinks();
            }
            ?>
          </div>

          <input type="hidden" name="option" value="<?php echo $option; ?>" />
          <?php
          if ($option != "com_realestatemanager") {
            ?>
            <input type="hidden" name="is_show_data" value="1" />
            <?php
          }
          ?>
          <input type="hidden" id="adminFormTaskInput" name="task" value="" />
          <input type="hidden" name="boxchecked" value="0" />
          <input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
          <input type="button" name="acceptButton" value="<?php echo _REALESTATE_MANAGER_TOOLBAR_ADMIN_ACCEPT; ?>"
          onclick="rem_buttonClickBuyRequest(this)"/>
          <input type="button" name="declineButton" value="<?php echo _REALESTATE_MANAGER_TOOLBAR_ADMIN_DECLINE; ?>"
          onclick="rem_buttonClickBuyRequest(this)"/>
        </form>
        <?php
      }


      static function add_google_map(&$rows) {
       global $realestatemanager_configuration, $doc, $mosConfig_live_site, $database, $Itemid;
       $api_key = $realestatemanager_configuration['api_key'] ? "key=" . $realestatemanager_configuration['api_key'] : JFactory::getApplication()->enqueueMessage("<a target='_blank' href='//developers.google.com/maps/documentation/geocoding/get-api-key'>" . _REALESTATE_MANAGER_GOOGLEMAP_API_KEY_LINK_MESSAGE . "</a>", _REALESTATE_MANAGER_GOOGLEMAP_API_KEY_ERROR); 
       $doc->addScript("//maps.googleapis.com/maps/api/js?$api_key"); ?>

       <script type="text/javascript">

        window.addEvent('domready', function() {
          initialize2();
        });

        function initialize2(){
          var map;
          var marker = new Array();
          var myOptions = {
            scrollwheel: false,
            zoomControlOptions: {
              style: google.maps.ZoomControlStyle.LARGE
            },
            mapTypeId: google.maps.MapTypeId.ROADMAP
          };
          var imgCatalogPath = "<?php echo $mosConfig_live_site; ?>/components/com_realestatemanager/";
          var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
          var bounds = new google.maps.LatLngBounds ();

          <?php
          $newArr = explode(",", _REALESTATE_MANAGER_HOUSE_MARKER);
          $j = 0; 
          for ($i = 0;$i < count($rows);$i++) { 
            if ($rows[$i]->hlatitude && $rows[$i]->hlongitude) {
              $numPick = '';
              if (isset($newArr[$rows[$i]->property_type])) {
                $numPick = $newArr[$rows[$i]->property_type];
              }
              ?>

              var srcForPic = "<?php echo $numPick; ?>";
              var image = '';
              if(srcForPic.length){
              var image = new google.maps.MarkerImage(imgCatalogPath + srcForPic,
              new google.maps.Size(32, 32),
              new google.maps.Point(0,0),
              new google.maps.Point(16, 32));
            }

            marker.push(new google.maps.Marker({
            icon: image,
            position: new google.maps.LatLng(<?php echo $rows[$i]->hlatitude; ?>,
            <?php echo $rows[$i]->hlongitude; ?>),
            map: map,
            title: "<?php echo $database->Quote($rows[$i]->htitle); ?>"
          }));
          bounds.extend(new google.maps.LatLng(<?php echo $rows[$i]->hlatitude; ?>,
          <?php echo $rows[$i]->hlongitude; ?>));


          var infowindow  = new google.maps.InfoWindow({});
          google.maps.event.addListener(marker[<?php echo $j; ?>], 'mouseover', 
          function() {
          <?php
          if (strlen($rows[$i]->htitle) > 45)
            $htitle = mb_substr($rows[$i]->htitle, 0, 25) . '...';
          else {
            $htitle = $rows[$i]->htitle;
          }
          ?>

          var title =  "<?php echo $htitle ?>";
          <?php 
                  //for local images
          $imageURL = ($rows[$i]->image_link);

          if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
          $file_name = rem_picture_thumbnail($imageURL,
           $realestatemanager_configuration['fotogal']['width'],
           $realestatemanager_configuration['fotogal']['high']);
          $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
          ?>
          var imgUrl =  "<?php echo $file; ?>";

          var price =  "<?php echo $rows[$i]->price; ?>";
          var priceunit =  "<?php echo $rows[$i]->priceunit; ?>";

          var contentStr = '<div>'+
            '<a onclick=window.open("<?php echo JRoute::_("index.php?option=com_realestatemanager&task=view&id={$rows[$i]->id}&catid={$rows[$i]->idcat}&Itemid={$Itemid}");?>") >'+
             '<img width = "100%" src = "'+imgUrl+'">'+
           '</a>' +
         '</div>'+
         '<div id="marker_link">'+
          '<a onclick=window.open("<?php echo JRoute::_("index.php?option=com_realestatemanager&task=view&id={$rows[$i]->id}&catid={$rows[$i]->idcat}&Itemid={$Itemid}");?>") >' + title + '</a>'+
        '</div>'+
        '<div id="marker_price">'+
          '<a onclick=window.open("<?php echo JRoute::_("index.php?option=com_realestatemanager&task=view&id={$rows[$i]->id}&catid={$rows[$i]->idcat}&Itemid={$Itemid}");?>") >' + price +' ' + priceunit + '</a>'+
        '</div>';

        infowindow.setContent(contentStr);
        infowindow.open(map,marker[<?php echo $j; ?>]);
      });


          var myLatlng = new google.maps.LatLng(<?php echo $rows[$i]->hlatitude;
           ?>,<?php echo $rows[$i]->hlongitude; ?>);
           var myZoom = <?php echo $rows[$i]->map_zoom; ?>;
           <?php
           $j++;
         }
       }
       ?>
       if (<?php echo $j; ?>>1) map.fitBounds(bounds);
       else if (<?php echo $j; ?>==1) {map.setCenter(myLatlng);map.setZoom(myZoom)}
       else {map.setCenter(new google.maps.LatLng(0,0));map.setZoom(0);}
     }
   </script>
   <?php    
 }


}

//class html
