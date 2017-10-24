<?php 
if (!defined('_VALID_MOS') && !defined('_JEXEC')) 
  die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
/**
 *
 * @package  RealestateManager
 * @copyright 2012 Andrey Kvasnevskiy-OrdaSoft(akbet@mail.ru); Rob de Cleen(rob@decleen.com);
 * Homepage: http://www.ordasoft.com
 * @version: 3.9 PRO
 *
 *
 */
global $hide_js, $mainframe, $Itemid, $realestatemanager_configuration, 
$mosConfig_live_site, $mosConfig_absolute_path, $my, $doc, $arr, $acl,$langContent;

if (version_compare(JVERSION, "3.0.0", "lt"))
  JHTML::_('behavior.mootools');
else
  JHtml::_('behavior.framework', true);

if (!JFactory::getApplication()->get('os_lightbox')) 
  JFactory::getApplication()->set('os_lightbox', true);
// add os_lightbox
////////////Adding google map
  $realestatemanager_configuration = $GLOBALS['realestatemanager_configuration'];
  if ($realestatemanager_configuration['location_tab']['show'] 
    || $realestatemanager_configuration['street_view']['show']) {
    $api_key = $realestatemanager_configuration['api_key'] ? "key=" . $realestatemanager_configuration['api_key'] : JFactory::getApplication()->enqueueMessage("<a target='_blank' href='//developers.google.com/maps/documentation/geocoding/get-api-key'>" . _REALESTATE_MANAGER_GOOGLEMAP_API_KEY_LINK_MESSAGE . "</a>", _REALESTATE_MANAGER_GOOGLEMAP_API_KEY_ERROR); 
  $doc->addScript("//maps.googleapis.com/maps/api/js?$api_key");
  if($realestatemanager_configuration['street_view']['show']){
    ?>
    <script type="text/javascript">  
      window.addEvent('domready', function() {
        initialize();
      });
      var map;
      var myLatlng=new google.maps.LatLng(<?php
        if ($house->hlatitude && $house->hlatitude != '')
          echo $house->hlatitude;
        else
          echo 0;
        ?>,<?php
        if ($house->hlongitude && $house->hlongitude != '')
          echo $house->hlongitude;
        else
          echo 0;
        ?>);
      var sv = new google.maps.StreetViewService();

      var panorama;
      function initialize(){
        var myOptions = {
          zoom: <?php if ($house->map_zoom)
          echo $house->map_zoom;
          else
            echo 1;
          ?>,
          center: myLatlng,
          scrollwheel: false,
          zoomControlOptions: {
            style: google.maps.ZoomControlStyle.LARGE
          },
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        if(document.getElementById("map_canvas") != undefined){
          map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
        }
        var imgCatalogPath = "<?php echo $mosConfig_live_site;
        ?>/components/com_realestatemanager/";
        <?php
        $newArr = explode(",", _REALESTATE_MANAGER_HOUSE_MARKER);
        $numPick = '';
        if (isset($newArr[$house->property_type])) {
          $numPick = $newArr[$house->property_type];
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
        var marker = new google.maps.Marker({ icon: image,position: myLatlng });
        marker.setMap(map);
        var panoramaOptions = {
          position: myLatlng,
          pov: {
            heading: 34,
            pitch: 10
          }
        };
        var streetViewService = new google.maps.StreetViewService();
        var STREETVIEW_MAX_DISTANCE = 50;
        streetViewService.getPanoramaByLocation(myLatlng, STREETVIEW_MAX_DISTANCE, function (streetViewPanoramaData, status) {
          if (status === google.maps.StreetViewStatus.OK) {
            // ok
            var panorama = new google.maps.StreetViewPanorama(document.getElementById('map_pano'), panoramaOptions);
            map.setStreetView(panorama);
          } else {
            document.getElementById('map_pano').style.display = "none";
            // no street view available in this range, or some error occurred
          }
        });

      }
    </script>
    <?php
  }else{
    ?>
    <script type="text/javascript">  
      window.addEvent('domready', function() {
        initialize();
      });
      var map;
      var myLatlng=new google.maps.LatLng(<?php
        if ($house->hlatitude && $house->hlatitude != '')
          echo $house->hlatitude;
        else
          echo 0;
        ?>,<?php
        if ($house->hlongitude && $house->hlongitude != '')
          echo $house->hlongitude;
        else
          echo 0;
        ?>); 
      function initialize(){
        var myOptions = {
          zoom: <?php if ($house->map_zoom)
          echo $house->map_zoom;
          else
            echo 1;
          ?>,
          center: myLatlng,
          scrollwheel: false,
          zoomControlOptions: {
            style: google.maps.ZoomControlStyle.LARGE
          },
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
        var imgCatalogPath = "<?php echo $mosConfig_live_site;
        ?>/components/com_realestatemanager/";
        <?php
        $newArr = explode(",", _REALESTATE_MANAGER_HOUSE_MARKER);
        $numPick = '';
        if (isset($newArr[$house->property_type])) {
          $numPick = $newArr[$house->property_type];
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
        var marker = new google.maps.Marker({ icon: image,position: myLatlng });
        marker.setMap(map);
      }
    </script>
    <?php }
  }
  ?>
  <div id="overDiv" ></div>

  <?php
  JPluginHelper::importPlugin('content');
  $dispatcher = JDispatcher::getInstance();
  ?>  
  <script language="javascript" type="text/javascript">

    function review_submitbutton() {
      var form = document.review_house;
    // do field validation
    var rating_checked = false; 
    for (c = 0;  c < form.rating.length; c++){
      if (form.rating[c].checked){
        rating_checked = true;
        form.rating.value = c ;
      } 
    }
    if (form.title.value == "") {
      alert( "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_REVIEW_TITLE; ?>" );
    } else if (form.comment == "") {
      alert( "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_REVIEW_COMMENT; ?>" );
    } else if (! form.rating.value) {                
      alert( "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_REVIEW_RATING; ?>" );
    } else {
      form.submit();
    }
  }
//*****************   begin add for show/hiden button "Add review" ********************
function button_hidden( is_hide ) {
  var el = document.getElementById('button_hidden_review');
  var el2 = document.getElementById('hidden_review');
  if(is_hide){
    el.style.display = 'none';
    el2.style.display = 'block';
  } else {
    el.style.display = 'block';
    el2.style.display = 'none';
  }
}

function buying_request_submitbutton() {
  var form = document.buying_request;
  if (form.customer_name.value == "") {
    document.getElementById('customer_name_warning').innerHTML =
    "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_NAME; ?>";
    document.getElementById('customer_name_warning').style.color = "red";
    document.getElementById('alert_name_buy').style.borderColor = "red";
    document.getElementById('alert_name_buy').style.color = "red";
  } else if (form.customer_email.value == ""|| !isValidEmail(form.customer_email.value)) {
    document.getElementById('customer_email_warning').innerHTML =
    "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_EMAIL; ?>";
    document.getElementById('customer_email_warning').style.color = "red";
    document.getElementById('alert_mail_buy').style.borderColor = "red";
    document.getElementById('alert_mail_buy').style.color = "red";
  } else if (!isValidPhoneNumber(form.customer_phone.value)){
    document.getElementById('customer_phone_warning').innerHTML =
    "<?php echo _REALESTATE_MANAGER_REQUEST_PHONE; ?>";
    document.getElementById('customer_phone_warning').style.color = "red";
    document.getElementById('customer_phone').style.borderColor = "red";
    document.getElementById('customer_phone').style.color = "red";
  } else {
    form.submit();
  }
}
function isValidPhoneNumber(str){
  myregexp = new RegExp("^([_0-9() -;,]*)$");
  mymatch = myregexp.exec(str);
  if(str == "")
    return true;
  if(mymatch != null)
    return true;
  return false;
}
function isValidEmail(str){
  myregexp = new RegExp("^([a-zA-Z0-9_-]+\.)*[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)*\.[a-zA-Z]{2,6}$");
  mymatch = myregexp.exec(str);
  if(str == "")
    return false;
  if(mymatch != null)
    return true;
  return false;
}        
</script>

<?php
if ($params->get('show_rentstatus') && $params->get('show_rentrequest') 
  && !$params->get('rent_save') && !$params->get('search_request')) {
    ?>

    <!--///////////////////////////////calendar///////////////////////////////////////-->
    <script language="javascript" type="text/javascript">  

      <?php
      $house_id_fordate =  $house->id;
      $date_NA = available_dates($house_id_fordate);    
      ?>

      var unavailableDates = Array();

      jQuerREL(document).ready(function() {
        var k=0;
        <?php if(!empty($date_NA)){?>
          <?php foreach ($date_NA as $N_A){ ?>
           unavailableDates[k]= '<?php echo $N_A; ?>';
           k++;
           <?php } ?>
           <?php } ?>

           function unavailableFrom(date) {
            dmy = date.getFullYear() + "-" + ('0'+(date.getMonth() + 1)).slice(-2) + 
            "-" + ('0'+date.getDate()).slice(-2);
            if (jQuerREL.inArray(dmy, unavailableDates) == -1) {
              return [true, ""];
            } else {
              return [false, "", "Unavailable"];
            }
          }

          function unavailableUntil(date) {
            dmy = date.getFullYear() + "-" + ('0'+(date.getMonth() + 1)).slice(-2) + 
            "-" + ('0'+(date.getDate()-("<?php  if(!$realestatemanager_configuration['special_price']['show']) echo '1';?>"))).slice(-2);
            if (jQuerREL.inArray(dmy, unavailableDates) == -1) {
              return [true, ""];
            } else {
              return [false, "", "Unavailable"];
            }
          }



          jQuerREL( "#rent_from" ).datepicker(
          {
            minDate: "+0",
            dateFormat: "<?php echo transforDateFromPhpToJquery();?>",
            beforeShowDay: unavailableFrom,
            onClose: function () {
              jQuerREL.ajax({ 
                type: "POST",
                update: jQuerREL(" #alert_date "),
                success: function( data ) {
                  jQuerREL("#alert_date").html("");
                }
              });

              var rent_from = jQuerREL(" #rent_from ").val();
              var rent_until = jQuerREL(" #rent_until ").val();
              var week = jQuerREL(" #week ").val();
              jQuerREL.ajax({ 
                type: "POST",
                url: "index.php?option=com_realestatemanager&task=ajax_rent_calcualete"
                +"&bid=<?php echo $house->id; ?>&rent_from="+
                rent_from+"&rent_until="+rent_until+"&week="+week,
                data: { " #do " : " #1 " },
                update: jQuerREL(" #message-here "),
                success: function( data ) {
                  jQuerREL("#message-here").html(data);
                  jQuerREL("#calculated_price").val(data);
                }
              });
            }
          });          

          jQuerREL( "#rent_until" ).datepicker(
          {

            minDate: "+0",
            dateFormat: "<?php echo transforDateFromPhpToJquery();?>",
            beforeShowDay: unavailableUntil,
            onClose: function () {
              jQuerREL.ajax({ 
                type: "POST",
                update: jQuerREL(" #alert_date "),
                success: function( data ) {
                  jQuerREL("#alert_date").html("");
                }
              });

              var rent_from = jQuerREL(" #rent_from ").val();
              var rent_until = jQuerREL(" #rent_until ").val();
             var week = jQuerREL(" #week ").val();
              jQuerREL.ajax({ 
                type: "POST",
                url: "index.php?option=com_realestatemanager&task=ajax_rent_calcualete"
                +"&bid=<?php echo $house->id; ?>&rent_from="+
                rent_from+"&rent_until="+rent_until+"&week="+week,
                data: { " #do " : " #1 " },
                update: jQuerREL(" #message-here "),
                success: function( data ) {
                  jQuerREL("#message-here").html(data);
                  jQuerREL("#calculated_price").val(data);
                }
              });
            }
          }); 

        });

    <!--///////////////////////////////////////////////////////////////////////////-->

    function rem_rent_request_submitbutton() {
      var form = document.rent_request_form;
      var datef = form.rent_from.value;
      var dateu = form.rent_until.value;
      var dayornight = "<?php echo $realestatemanager_configuration['special_price']['show']?>";
      var frep = datef.replace(/\//gi,"-").replace(/\s/gi,"-").replace(/\*/gi,"-");
      var urep = dateu.replace(/\//gi,"-").replace(/\s/gi,"-").replace(/\*/gi,"-");
      var re = /\n*\-\d\d\d\d/;
      var found = urep.match(re);
      if(found){
        var dfrom = (frep.split ('-').reverse ());
        var duntil = (urep.split ('-').reverse ());
      } else {
        var dfrom = (frep.split ('-'));
        var duntil = (urep.split ('-'));
      }

      var dmin=dfrom[0]*10000+dfrom[1]*100+dfrom[2]*1;
      var dmax=duntil[0]*10000+duntil[1]*100+duntil[2]*1;
      if (form.user_name.value == "") {
        document.getElementById('user_name_warning').innerHTML =
        "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_NAME; ?>";
        document.getElementById('user_name_warning').style.color = "red";
        document.getElementById('alert_name').style.borderColor = "red";
        document.getElementById('alert_name').style.color = "red";
      } else if (form.user_email.value == "" || !isValidEmail(form.user_email.value)) {
        document.getElementById('user_email_warning').innerHTML =
        "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_EMAIL; ?>";
        document.getElementById('user_email_warning').style.color = "red";
        document.getElementById('alert_mail').style.borderColor = "red";
        document.getElementById('alert_mail').style.color = "red";
      } else if (dmax<dmin) {
       document.getElementById('alert_date').innerHTML =
       "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_ALERT; ?>";
       document.getElementById('alert_date').style.borderColor = "red";
       document.getElementById('alert_date').style.color = "red";
     } else if (unavailableDates.indexOf(form.rent_until.value) >= 0
       || unavailableDates.indexOf(form.rent_from.value) >= 0) {
      document.getElementById('alert_date').innerHTML =
      "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_DATE; ?>";
      document.getElementById('alert_date').style.borderColor = "red";
      document.getElementById('alert_date').style.color = "red";
    } else if (dmax==dmin && dayornight == 0) {
     document.getElementById('alert_date').innerHTML =
     "<?php echo _REALESTATE_MANAGER_INFOTEXT_JS_RENT_REQ_ALERT; ?>";
     document.getElementById('alert_date').style.borderColor = "red";
     document.getElementById('alert_date').style.color = "red";
   }else {
    form.submit();
  }
}

</script>
<?php
} 
?>  
<script  type="text/javascript" charset="utf-8" async defer>
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



  function allreordering(){
    if(document.orderForm.order_direction.value=='asc')
      document.orderForm.order_direction.value='desc';
    else document.orderForm.order_direction.value='asc';

    document.orderForm.submit();
  }

</script>


<?php positions_rem($params->get('view01')); ?>

<div class="row-fluid">
  <div class="span9">
    <div id="rem_house_galery">
      <!-- ********************** begin add for show/hiden button "Edit house"___c************* -->

      <?php

      $is_edit_all_houses = false ;
      if (checkAccess_REM($realestatemanager_configuration['option_edit']['registrationlevel'], 'RECURSE', userGID_REM($my->id), $acl)) {
        $is_edit_all_houses = true ;
      }



      if ($my->id == $house->owner_id && $my->id != '' || $is_edit_all_houses):

        global $option;

        if ($option != 'com_realestatemanager') {
          $form_action = "index.php?option=" . $option .
          "&task=edit_house&Itemid=" . $Itemid ;

        }
        else
          $form_action = "index.php?option=com_realestatemanager&task=edit_house&Itemid=" . $Itemid;
        ?>
        <form action="<?php echo sefRelToAbs($form_action); ?>" method="post"
         name="show_add" enctype="multipart/form-data">
         <div id ="button">
          <input type="hidden" name="id" value="<?php echo $house->id; ?>"/>
          <input class="button" type="submit" name="submit" value="<?php
          echo _REALESTATE_MANAGER_LABEL_BUTTON_EDIT_HOUSE; ?>"/>
        </div>
      </form>
    <?php endif;?>
    <!-- ************************* end  add for show/hiden button "Edit house"***************** -->
    <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?> ">

      <?php if (trim($house->htitle)) { ?>
      <span class="col_text_2"><?php echo $house->htitle; ?></span>
      <?php } ?> 
      <?php if($params->get('show_pricerequest')){ ?>
      <div class="rem_house_price">
        Tarif à la semaine à partir de :
        <?php
        if ($realestatemanager_configuration['price_unit_show'] == '1') {
          if ($params->get('show_sale_separator')) {
            echo "<div class=\"pricemoney\">
            <span class=\"money\">" 
            . formatMoney($house->price, true, $realestatemanager_configuration['price_format']) . 
            "</span>";
            echo "<span class=\"price\">&nbsp;" . $house->priceunit . "</span></div>";
          } else {
            echo "<div class=\"pricemoney\"><span class=\"money\">" . $house->price . "</span>";
            echo "<span class=\"price\">&nbsp;" . $house->priceunit . "</span></div>";    
          }
        } else {
          if ($params->get('show_sale_separator')) {
            echo "<div class=\"pricemoney\"><span class=\"price\">" . $house->priceunit . "</span>";
            echo "&nbsp;<span class=\"money\">"
            . formatMoney($house->price, true, $realestatemanager_configuration['price_format']) 
            . "</span></div>";
          } else {
            echo "<div class=\"pricemoney\"><span class=\"price\">" . $house->priceunit . "</span>";
            echo "&nbsp;<span class=\"money\">" . $house->price . "</span></div>";
          }
        }    
        if($currencys_price){
          foreach ($currencys_price as $key => $row) {
            if ($house->priceunit != $key) {
              if ($realestatemanager_configuration['price_unit_show'] == '1') {
                if ($params->get('show_sale_separator')) {
                  echo "<div class=\"pricemoney\"><span class=\"money\">" 
                  . formatMoney($row, true, $realestatemanager_configuration['price_format']) . "</span>";
                  echo "<span class=\"price\">&nbsp;" . $key . "</span></div>";
                } else {
                  echo "<div class=\"pricemoney\"><span class=\"money\">" . $row . "</span>";
                  echo "<span class=\"price\">&nbsp;" . $key . "</span></div>";    
                }
              } else {
                if ($params->get('show_sale_separator')) {
                  echo "<div class=\"pricemoney\"><span class=\"price\">" . $key . "</span>";
                  echo "&nbsp;<span class=\"money\">" 
                  . formatMoney($row, true, $realestatemanager_configuration['price_format']) . 
                  "</span></div>";
                } else {
                  echo "<div class=\"pricemoney\"><span class=\"price\">" . $key . "</span>";
                  echo "&nbsp;<span class=\"money\">" . $row . "</span></div>";
                }
              }
            }
          }
        }
        ?>

      </div>
      <?php 
    } ?>
  </div>
  <div style="clear:both"></div>
  <div class="button_ppe">

    <span>
      <?php if ($params->get('show_input_print_pdf')) { ?>
      <a onclick="window.open(
        this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
        return false;" rel="nofollow"
        href='<?php echo JRoute::_("index.php?option=com_realestatemanager&task=view&id=$id&catid=$catid&Itemid=$Itemid&printItem=pdf&lang=$langContent")?>' title="PDF"  rel="nofollow">
        <i class="fa fa-file"></i>
      </a>
      <?php }
      ?>

      <?php if ($params->get('show_input_print_view')) { ?>
      <a onclick="window.open(
        this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
        return false;" rel="nofollow"
        href='<?php echo JRoute::_("index.php?option=com_realestatemanager&task=view&id=$id&catid=$catid&Itemid=$Itemid&amp;printItem=print&tmpl=component");?>'

        title="Print"  rel="nofollow">
        <i class="fa fa-print"></i>
      </a>
      <?php }
      ?>

      <?php if ($params->get('show_input_mail_to')) { 
        if (version_compare(JVERSION, '1.6', 'lt')) {?>
        <a href="<?php echo $mosConfig_live_site;
        ?>/index.php?option=com_mailto&amp;tmpl=component&amp;link=<?php $url = JFactory::getURI();
        echo base64_encode($url->toString()); ?>"
        title="E-mail"
        onclick="window.open(this.href,'win2','width=400,height=350,menubar=yes,resizable=yes'); return false;">
        <i class="fa fa-envelope"></i>
      </a>
      <?php }else{
        require_once JPATH_SITE . '/components/com_mailto/helpers/mailto.php';
        $url = JFactory::getURI();
        $url_c = $url->toString();
        $link = 'index.php?option=com_mailto&amp;tmpl=component&amp;Itemid='
        .$Itemid.'&link='.MailToHelper::addLink($url_c);?>
        <a href="<?php echo sefRelToAbs($link);?>"
          title="E-mail"
          onclick="window.open(this.href,'win2','width=400,height=350,menubar=yes,resizable=yes'); return false;">
          <?php
          if (version_compare(JVERSION, "1.6.0", "lt")) { ?>
          <i class="fa fa-envelope"></i>
          <?php }
          else { ?>
          <i class="fa fa-envelope"></i>
          <?php  } ?>
        </a>
        <?php       }
      }
      ?>
    </span>
  </div>

  <div class="rem_house_location">
    <i class="fa fa-map-marker"></i>
    <?php if (isset($house->hcountry) && trim($house->hcountry)) { ?>
    <span class="col_text_2"><?php echo $house->hcountry; ?></span>,

    <?php     } if (isset($house->hregion) && trim($house->hregion)) { ?>
    <span class="col_text_2"><?php echo $house->hregion; ?></span>,

    <?php     } if (isset($house->hcity) && trim($house->hcity)) { ?>
    <span class="col_text_2"><?php echo $house->hcity; ?></span>,

    <?php } if (isset($house->hzipcode) && trim($house->hzipcode)) { ?>
    <span class="col_text_2"><?php echo $house->hzipcode; ?></span>,

    <?php }  if (isset($house->hlocation) && trim($house->hlocation)) { ?>
    <span class="col_02"><?php echo $house->hlocation; ?></span>.
    <?php } ?>

  </div>
  <div style="clear:both"></div>

  <span  class="col_img">

    <?php

    //for local images
    $imageURL = ($house->image_link);

    if ($imageURL == '') $imageURL = _REALESTATE_MANAGER_NO_PICTURE_BIG;
    $file_name = rem_picture_thumbnail($imageURL,
     $realestatemanager_configuration['fotomain']['width'],
     $realestatemanager_configuration['fotomain']['high']);
    $file = $mosConfig_live_site . '/components/com_realestatemanager/photos/' . $file_name;
    echo '<div style="position:relative">';
    echo '<img alt="' . $house->htitle . '" title="' . $house->htitle . '" src="' . $file . '"  >';

    ?>  
    <!-- add wishlist marker -->
    <?php
    ?>
    <?php if ($params->get('show_add_to_wishlist')) { ?>
    <span class="fa-stack fa-lg i-wishlist i-wishlist-all"  >
      <?php 

      if (in_array($house->id, $params->get('wishlist'))) { ?>
      <i class="fa fa-star fa-stack-1x" id="icon<?php echo $house->id ?>" title="<?php echo _REALESTATE_MANAGER_LABEL_WISHLIST_REMOVE ?>" onclick="addToWishlist(<?php echo $house->id ?>, <?php echo $my->id ?>)"> </i> 
      <?php } else { ?> 
      <i class="fa fa-star-o fa-stack-1x" id="icon<?php echo $house->id ?>" title="<?php echo _REALESTATE_MANAGER_LABEL_WISHLIST_ADD ?>" onclick="addToWishlist(<?php echo $house->id ?>, <?php echo $my->id ?>)"></i>
      <?php } ?>
    </span>
    <?php } 
  echo '</div>';?> 
  <!-- end add wishlist marker -->

</span>

<div class="table_gallery table_07">
  <?php if (count($house_photos) > 0) { ?>
  <div class="gallery_img">
    <?php for ($i = 0;$i < count($house_photos);$i++) { ?>
    <div class="thumbnail viewHouses" 
    style="width: <?php echo $realestatemanager_configuration['fotogal']['width'];?>px; height: <?php 
    echo $realestatemanager_configuration['fotogal']['high'];?>px;" >
    <a href="<?php echo $mosConfig_live_site; ?>/components/com_realestatemanager/photos/<?php 
    echo $house_photos[$i]->main_img; ?>" data-lightbox="rem_roadtrip" title="photo" >
    <img alt="<?php echo $house->htitle; ?>" title="<?php echo $house->htitle; ?>" 
    src="./components/com_realestatemanager/photos/<?php 
    echo rem_picture_thumbnail($house_photos[$i]->main_img,
     $realestatemanager_configuration['fotogal']['width'],
     $realestatemanager_configuration['fotogal']['high']); ?>" style = "vertical-align:middle;" />
   </a>
 </div>
 <?php
}
?>
</div>
<?php }
?>
</div>
</div>
<!--<form action="<?php //echo sefRelToAbs($form_action);
?>" method="post" name="house">-->
<?php positions_rem($params->get('view02')); ?>

<div id="rem_house_property">
 <?php
 $listing_status[0] = _REALESTATE_MANAGER_OPTION_SELECT;
 $listing_status1 = explode(',', _REALESTATE_MANAGER_OPTION_LISTING_STATUS);
 $i = 1;
 foreach ($listing_status1 as $listing_status2) {
  $listing_status[$i] = $listing_status2;
  $i++;
}
if ($house->listing_status != 0) {
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_LISTING_STATUS; ?>:</span>
    <span class="col_text_2"><?php echo $listing_status[$house->listing_status]; ?></span>
  </div>
  <?php
}
?>

<?php
$property_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
$property_type1 = explode(',', _REALESTATE_MANAGER_OPTION_PROPERTY_TYPE);
$i = 1;
foreach ($property_type1 as $property_type2) {
  $property_type[$i] = $property_type2;
  $i++;
}
if ($house->property_type != 0) {
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_PROPERTY_TYPE; ?>:</span>
    <span class="col_text_2"><?php echo $property_type[$house->property_type]; ?></span>
  </div>
  <?php
}
?>

<?php if (trim($house->houseid)) { ?>
<div class="row_text">
  <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_PROPERTYID; ?>:</span>
  <span class="col_text_2"><?php echo $house->houseid; ?></span>
</div>
<?php
}
?>
<?php
$listing_type[0] = _REALESTATE_MANAGER_OPTION_SELECT;
$listing_type[1] = _REALESTATE_MANAGER_OPTION_FOR_RENT;
$listing_type[2] = _REALESTATE_MANAGER_OPTION_FOR_SALE;
if ($house->listing_type != 0) {
  ?>
  <div class="row_text">
    <span class="col_text_icon"></span>
    <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_LISTING_TYPE; ?>:</span>
    <span class="col_02"><?php echo $listing_type[$house->listing_type]; ?></span>
  </div>
  <?php
}
?>
<?php if ($realestatemanager_configuration['extra1'] == 1 && $house->extra1 != "") {
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA1; ?>:</span>
    <span class="col_text_2"><?php echo $house->extra1; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra2'] == 1 && $house->extra2 != "") {
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA2; ?>:</span>
    <span class="col_text_2"><?php echo $house->extra2; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra3'] == 1 && $house->extra3 != "") {
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA3; ?>:</span>
    <span class="col_text_2"><?php echo $house->extra3; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra4'] == 1 && $house->extra4 != "") {
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA4; ?>:</span>
    <span class="col_text_2"><?php echo $house->extra4; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra5'] == 1 && $house->extra5 != "") {
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA5; ?>:</span>
    <span class="col_text_2"><?php echo $house->extra5; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra6'] == 1 && $house->extra6 > 0) {
  $extra1 = explode(',', _REALESTATE_MANAGER_EXTRA6_SELECTLIST);
  $i = 1;
  foreach ($extra1 as $extra2) {
    $extra[$i] = $extra2;
    $i++;
  }
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA6; ?>:</span>
    <span class="col_text_2"><?php echo $extra[$house->extra6]; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra7'] == 1 && $house->extra7 > 0) {
  $extra1 = explode(',', _REALESTATE_MANAGER_EXTRA7_SELECTLIST);
  $i = 1;
  foreach ($extra1 as $extra2) {
    $extra[$i] = $extra2;
    $i++;
  }
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA7; ?>:</span>
    <span class="col_text_2"><?php echo $extra[$house->extra7]; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra8'] == 1 && $house->extra8 > 0) {
  $extra1 = explode(',', _REALESTATE_MANAGER_EXTRA8_SELECTLIST);
  $i = 1;
  foreach ($extra1 as $extra2) {
    $extra[$i] = $extra2;
    $i++;
  }
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA8; ?>:</span>
    <span class="col_text_2"><?php echo $extra[$house->extra8]; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra9'] == 1 && $house->extra9 > 0) {
  $extra1 = explode(',', _REALESTATE_MANAGER_EXTRA9_SELECTLIST);
  $i = 1;
  foreach ($extra1 as $extra2) {
    $extra[$i] = $extra2;
    $i++;
  }
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA9; ?>:</span>
    <span class="col_text_2"><?php echo $extra[$house->extra9]; ?></span>
  </div>
  <?php
}
if ($realestatemanager_configuration['extra10'] == 1 && $house->extra10 > 0) {
  $extra1 = explode(',', _REALESTATE_MANAGER_EXTRA10_SELECTLIST);
  $i = 1;
  foreach ($extra1 as $extra2) {
    $extra[$i] = $extra2;
    $i++;
  }
  ?>
  <div class="row_text">
    <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EXTRA10; ?>:</span>
    <span class="col_text_2"><?php echo $extra[$house->extra10]; ?></span>
  </div>
  <?php }
  ?>
  <!--add edocument -->
  <?php
  if ($params->get('show_edocsrequest') && $house->edok_link != null) {
    $session = JFactory::getSession();
    $sid = $session->getId();
    $session->set("ssmid", $sid);
    setcookie('ssd', $sid, time() + 60 * 60 * 24, "/");
    ?>
    <div class="row_text">
      <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_EDOCUMENT; ?>:</span>
      <span class="col_text_2">
        <a href="<?php 
        echo sefRelToAbs('index.php?option=com_realestatemanager&amp;task=mdownload&amp;id='
        . $house->id . '&amp;Itemid=' . $Itemid); ?>" target="blank">
        <?php echo _REALESTATE_MANAGER_LABEL_EDOCUMENT_DOWNLOAD; ?>
      </a>
    </span>
  </div>
  <?php
} //end if
?>
</div>
<div class="tabs_buttons">
  <!--list of the tabs-->
  <ul id="countrytabs" class="shadetabs">
    <li>
      <a href="#" rel="country1" class="selected"><?php echo _REALESTATE_MANAGER_TAB_DESCRIPTION; ?>
      </a>
    </li>
    <?php
    if (($params->get('show_location') && $params->get('show_locationtab_registrationlevel'))
      || ($params->get('street_view') && $params->get('street_view_registrationlevel'))) {
        ?>
        <li>
          <a href="#" rel="country2" onmouseup="setTimeout('initialize()',10)">
            <?php echo _REALESTATE_MANAGER_TAB_LOCATION; ?>
          </a>
        </li>
        <?php
      }
      ?>
      <?php
      if ($params->get('show_reviews_tab')) {
        if ($params->get('show_reviewstab_registrationlevel')) {
          ?>
          <li>
            <a href="#" rel="country4"><?php echo _REALESTATE_MANAGER_TAB_REVIEWS; ?></a>
          </li>
          <?php
        }
      }
      ?>
      <?php
      if ($params->get('calendar_show') && $house->listing_type == 1) {
        if ($params->get('calendar_option_registrationlevel','')) {
          ?>
          <li>
            <a href="#" rel="country5"><?php echo _REALESTATE_MANAGER_LABEL_CALENDAR_CALENDAR; ?></a>
          </li>
          <?php
        }
      }
      ?>
    </ul>
  </div>
  <?php positions_rem($params->get('view03')); ?>

  <!--begin tabs-->
  <div id="tabs">
    <div id="country1" class="tabcontent">
      <div class="rem_type_house">
        <?php if (isset($house->rooms) && trim($house->rooms)) { ?>
        <div class="row_text">
          <i class="fa fa-building-o"></i>
          <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_ROOMS; ?>:</span>
          <span class="col_text_2"><?php echo $house->rooms; ?></span>
        </div>
        <?php } if (isset($house->bathrooms) && trim($house->bathrooms)) { ?>
        <div class="row_text">
          <i class="fa fa-tint"></i>
          <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_BATHROOMS; ?>:</span>
          <span class="col_text_2"><?php echo $house->bathrooms; ?></span>
        </div>
        <?php } if (isset($house->bedrooms) && trim($house->bedrooms)) { ?>
        <div class="row_text">
          <i class="fa fa-inbox"></i>
          <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_BEDROOMS; ?>:</span>
          <span class="col_text_2"><?php echo $house->bedrooms; ?></span>
        </div>
        <?php
      }
      ?>
      <?php if (isset($house->year) && trim($house->year)) { ?>
      <div class="row_text">
        <i class="fa fa-calendar"></i>
        <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_BUILD_YEAR; ?>:</span>
        <span class="col_text_2"><?php echo $house->year; ?></span>
      </div>
      <?php }   ?>
      <?php if (isset($house->garages) && trim($house->garages)) { ?>
      <div class="row_text">
        <i class="fa fa-truck"></i>
        <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_GARAGES; ?>:</span>
        <span class="col_text_2"><?php echo $house->garages; ?></span>
      </div>
      <?php }
      if (isset($house->lot_size) && trim($house->lot_size)) {
        ?>
        <div class="row_text">
          <i class="fa fa-arrows-alt"></i>
          <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_LOT_SIZE; ?>:</span>
          <span class="col_text_2">
            <?php echo $house->lot_size; ?> <?php echo _REALESTATE_MANAGER_LABEL_SIZE_SUFFIX_AR; ?>
          </span>
        </div>
        <?php }
        if (isset($house->house_size) && trim($house->house_size)) {
          ?>
          <div class="row_text">
            <i class="fa fa-expand"></i>
            <span class="col_text_1"><?php echo _REALESTATE_MANAGER_LABEL_HOUSE_SIZE; ?>:</span>
            <span class="col_text_2">
              <?php echo $house->house_size; ?> <?php echo _REALESTATE_MANAGER_LABEL_SIZE_SUFFIX; ?>
            </span>
          </div>
          <?php }
////////////////////////////////////START show video\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
          if (!empty($videos)) {
            $youtubeCode = "";
            $videoSrc = array();
            $videoSrcHttp = "";
            $videoType = array();
            foreach($videos as $video) {
              if ($video->youtube) {
                $youtubeCode = $video->youtube;
              } else if ($video->src) {
                $videoSrc[] = $video->src;
                if($video->type)
                  $videoType[] = $video->type;
              }
            }?>
            <div class="row_06">
              <span class="realestate_video">
                <strong class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_VIDEO; ?>:</strong>
                <?php 
                if (!empty($youtubeCode)) { ?>
                <iframe width="420" height="315" frameborder="0" 
                src="//www.youtube.com/embed/<?php echo $youtubeCode ?>"></iframe> 
                <?php
              } else if (!empty($videoSrc) && empty($youtubeCode)) { ?>
              <video width="320" height="240" controls>
                <?php
                for ($i = 0;$i < count($videoSrc);$i++) {
                  if(!strstr($videoSrc[$i], "http") && $videoType) {
                    echo '<source src="' . $mosConfig_live_site . $videoSrc[$i] .'"'.
                    ' type="' . $videoType[$i] .'">';
                  }else{
                   echo '<source src="' . $videoSrc[$i] .'"'.
                   ' type="' . $videoType[$i] .'">';
                 }
               }
               if (!empty($tracks)) {
                for ($i = 0;$i < count($tracks);$i++) {
                  ($i==0)?$default='default="default"':$default='';
                  if (!strstr($tracks[$i]->src, "http")) {
                    echo '<track src="' . $mosConfig_live_site.$tracks[$i]->src . '"'.
                    ' kind="' . $tracks[$i]->kind .'"'. 
                    ' srclang="' . $tracks[$i]->scrlang .'"'.
                    ' label="' . $tracks[$i]->label . '" '.$default.'>';
                  }else{
                    echo '<track src="' .$src . '"'.
                    ' kind="' . $tracks[$i]->kind .'"'. 
                    ' srclang="' . $tracks[$i]->scrlang .'"'.
                    ' label="'.$tracks[$i]->label.'" '.$default.'>';
                  }
                }
              } ?>
            </video>
          </span>

          <?php
        } ?>
      </div>
      <?php
    }
////////////////////////////////////END show video\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    if (isset($house->description) && trim($house->description)) {
      ?>
      <div class="rem_house_desciption"><?php positions_rem($house->description); ?></div>
      <?php
    }
    ?>
  </div>
  <div class="table_tab_01 table_03">

    <!--       *******************************************************************         -->
    <?php 
    global $database, $realestatemanager_configuration;
    if($realestatemanager_configuration['special_price']['show']){
      $switchTranslateDayNight = _REALESTATE_MANAGER_RENT_SPECIAL_PRICE_PER_DAY;       
    }else{
      $switchTranslateDayNight = _REALESTATE_MANAGER_RENT_SPECIAL_PRICE_PER_NIGHT;    
    }
    $query = "select * from #__rem_rent_sal WHERE fk_houseid='$house->id'";
    $database->setQuery($query);
    $rentTerm = $database->loadObjectList();
    if(isset($rentTerm[0]->special_price)) { ?>
    <div class = "row_17">
      <span class="col_01"><?php echo _REALESTATE_MANAGER_RENT_SPECIAL_PRICE; ?>:</span> </br>

      <table class="adminlist adminlist_04">
        <tr>
          <th class="title" width = "15%" align ='center' ><?php 
          echo _REALESTATE_MANAGER_FROM; ?></th>
          <th class="title" width = "15%" align ='center' ><?php 
          echo _REALESTATE_MANAGER_TO; ?></th>
          <th class="title" width = "15%" align ='center'><?php 
          echo $switchTranslateDayNight; ?></th>
          <th class="title" width = "20%" align ='center' ><?php 
          echo _REALESTATE_MANAGER_LABEL_REVIEW_COMMENT; ?></th>
        </tr>
        <?php                                                
        $DateToFormat = str_replace("D",'d',
          (str_replace("M",'m',(str_replace('%','',
            $realestatemanager_configuration['date_format'])))));
        for ($i = 0; $i < count($rentTerm); $i++) {   
          $date_from = new DateTime($rentTerm[$i]->price_from);
          $date_to = new DateTime($rentTerm[$i]->price_to);
          ?>
          <tr>
            <td align ='center'><?php echo date_format($date_from, "$DateToFormat"); ?></td>
            <td align ='center'><?php echo date_format($date_to, "$DateToFormat"); ?></td>
            <?php
            if ($realestatemanager_configuration['sale_separator'] == '1') { ?>
            <td align ='center'><?php 
            echo formatMoney($rentTerm[$i]->special_price, true,
             $realestatemanager_configuration['price_format']); ?></td>
             <?php   } else { ?>
             <td align ='center'><?php echo $rentTerm[$i]->special_price; ?></td>
             <?php   }
             ?>

             <td align ='center'><?php echo $rentTerm[$i]->comment_price; ?></td> 
           </tr>
           <?php } ?>  
         </table>
       </div>
       <?php   } ?>

       <!--       *******************************************************************         -->
       <div class="table_country3 ">
        <?php 
        if (count($house_feature)) {
          ?>
          <div class="row_text">
            <div class="rem_features_title">
              <?php echo _REALESTATE_MANAGER_LABEL_FEATURED_MANAGER_FEATURE; ?>:
            </div>
            <span class="col_text_2">
              <?php 
              for ($i = 0; $i < count($house_feature); $i++) {
                if ($realestatemanager_configuration['manager_feature_category'] == 1) {
                  if ($i != 0) {
                    if ($house_feature[$i]->categories !== $house_feature[$i - 1]->categories)
                      echo "<div class='rem_features_category'>" . $house_feature[$i]->categories . "</div>";
                  } else 
                  echo "<div class='rem_features_category'>" . $house_feature[$i]->categories . "</div>";
                }
                echo "<span class='rem_features_name'><i class='fa  fa-check rem_fa'></i>"
                . $house_feature[$i]->name . "</span>";
                if ($i != count($house_feature)-1) {
                  if ($house_feature[$i]->categories == $house_feature[$i + 1]->categories);            
                }
                ?>


                <?php }
                ?>
              </span>
            </div>

            <?php }
            ?>
          </div>

        </div>
      </div><!--end of tab-->
      <div id="country2" class="tabcontent">
        <!--if we are given the coordinates, then display latitude, longitude and a map with a marker -->
        <?php if ($house->hlatitude && $house->hlongitude) {?> 
        <div class="table_latitude table_04">
          <?php
          if(($params->get('show_location') && $params->get('show_locationtab_registrationlevel'))){ ?>
          <div id="map_canvas" class="re_map_canvas re_map_canvas_02"></div>
          <?php
        }
        if($params->get('street_view') && $params->get('street_view_registrationlevel')){ ?>
        <div id="map_pano" class="re_map_canvas re_map_canvas_02"></div>
        <?php
      } ?>
    </div>
    <?php 
  } else
  echo _REALESTATE_MANAGER_LABEL_NO_LOCATION_AVAILIBLE;
  ?>
</div>
<!--tab for reviews-->
<div id="country4" class="tabcontent">
  <?php
//show the reviews
  if ($reviews = $house->getReviews()) {
    ?>
    <br />
    <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
      <?php echo _REALESTATE_MANAGER_LABEL_REVIEWS; ?> 
    </div>

    <div class="reviews_table table_06">
      <?php
      if ($reviews != null && count($reviews) > 0) {
        for ($m = 0, $n = count($reviews); $m < $n; $m++) {
          $review = $reviews[$m];
          if ($review->published != 0) {
            ?>
            <div class="box_comment">
              <div class="user_name"><?php echo $review->user_name; ?></div>
              <span class="arrow_up_comment"></span>
              <div class="head_comment">
                <div class="title_rating">
                  <h4 class="col_title_rev"><?php echo $review->title; ?></h4>
                  <span class="col_rating_rev">
                    <img src="./components/com_realestatemanager/images/rating-<?php
                    echo $review->rating; ?>.png" 
                    alt="<?php echo ($review->rating) / 2; ?>" border="0" align="right"/>
                  </span>
                </div>
                <div class="row_comment">
                  <?php echo $review->comment; ?>
                </div>
                <div class="date">
                  <span class="date_format">
                    <?php echo data_transform_rem($review->date); ?>
                  </span>
                </div>
              </div>
            </div>
            <?php
          }
        }
      }
      ?>
    </div>

    <?php
  } else{
    echo "<p>No reviews for house</p>";
  }

  if ($params->get('show_reviews')) {
    $reviews = $house->getReviews();
    ?><?php
    if ($params->get('show_inputreviews')) {
      ?><?php positions_rem($params->get('view07')); ?><div id="hidden_review">
        <form action="<?php echo sefRelToAbs("index.php?option="
        . $option . "&amp;task=review_house&amp;Itemid=" .
        $Itemid); ?>" method="post" name="review_house"> 
        <input type="hidden" name="user_name" value="<?php echo $my->username ?>">
        <input type="hidden" name="fk_userid" value="<?php echo $my->id ?>">
        <input type="hidden" name="user_email" value="<?php echo $my->email ?>">
        <div class="componentheading<?php echo $params->get('pageclass_sfx'); ?>">
          <hr />
          <?php echo _REALESTATE_MANAGER_LABEL_ADDREVIEW; ?>
        </div>

        <div class="add_table_review table_09">
          <div class="row_01"><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_TITLE; ?></div>
          <div class="row_02">
            <input class="inputbox" type="text" name="title" size="80" 
            value="<?php if (isset($_REQUEST["title"])) {
              echo $_REQUEST["title"]; } ?>" /> 
            </div>
            <div class="row_03"><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_COMMENT; ?></div>
            <div class="row_04">
              <?php
              $comm_val = "";
              if (isset($_REQUEST["comment"])) {
                $comm_val = protectInjectionWithoutQuote("comment",'','STRING');
              }
                            //editorArea( 'editor1',  $comm_val, 'comment', '410', '200', '60', '10' );
              ?>
              <textarea name="comment" cols="50" rows="8" ><?php echo $comm_val; ?></textarea>
            </div>

            <!-- #### RATING #### -->
            <?php if (version_compare(JVERSION, "3.0", "ge")): ?>
              <script type="text/javascript">
                os_img_path = '<?php echo $mosConfig_live_site . '/components/com_realestatemanager/images/'; ?>' ;
                jQuerREL(document).ready(function(){
                  jQuerREL('#star').raty({
                    starHalf: os_img_path+'star-half.png',
                    starOff: os_img_path+'star-off.png',
                    starOn: os_img_path+'star-on.png' 
                  });
                });
              </script>

              <div class="row_rating_j3">
                <span class="lable_rating"><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_RATING; ?></span>
                <span id="star"></span>
              </div>

            <?php else:
            ?>
            <div class="row_rating_j2">
              <span class="col_01"><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_RATING; ?></span>
              <br />
              <span>  
                <?php
                $k = 0;
                while ($k < 11) {
                  ?>
                  <input type="radio" name="rating" value="<?php echo $k; ?>" <?php
                  if (isset($_REQUEST["rating"]) && $_REQUEST["rating"] == $k) {
                    echo "CHECKED";
                  }
                  ?> alt="Rating" />
                  <img src="./components/com_realestatemanager/images/rating-<?php echo $k; ?>.png" 
                  alt="<?php echo ($k) / 2; ?>" border="0" /><br />
                  <?php
                  $k++;
                }
                ?>
              </span>
            </div>

          <?php endif; ?>

          <!--*************************   begin add antispam guest   ********************-->
          <?php if ($params->get('captcha_option')) {
            if ($params->get('captcha_option_registrationlevel') &&  ($my->id == 0)) {
              ?>
              <div  class="row_capcha">
                <!--*************************   begin insetr image   **********************-->
                <?php
            // begin create kod
                $st = "      ";
                $algoritm = mt_rand(1, 2);
                switch ($algoritm) {
                  case 1:
                  for ($j = 0; $j < 6; $j+= 2) {
                        $st = substr_replace($st, chr(mt_rand(97, 122)), $j, 1); //abc
                        $st = substr_replace($st, chr(mt_rand(50, 57)), $j + 1, 1); //23456789
                      }
                      break;
                      case 2:
                      for ($j = 0; $j < 6; $j+= 2) {
                        $st = substr_replace($st, chr(mt_rand(50, 57)), $j, 1); //23456789
                        $st = substr_replace($st, chr(mt_rand(97, 122)), $j + 1, 1); //abc
                      }
                      break;
                    }
            //**************   begin search in $st simbol 'o, l, i, j, t, f'   ***************************
                    $st_validator = "olijtf";
                    for ($j = 0; $j < 6; $j++) {
                      for ($i = 0; $i < strlen($st_validator); $i++) {
                        if ($st[$j] == $st_validator[$i]) {
                        $st[$j] = chr(mt_rand(117, 122)); //uvwxyz
                      }
                    }
                  }
            //**************   end search in $st simbol 'o, l, i, j, t, f'   *****************************
                  $session = JFactory::getSession();
                  $session->set('captcha_keystring', $st);
                  if (isset($_REQUEST['error']) && $_REQUEST['error'] != "")
                    echo "<font style='color:red'>" . $_REQUEST['error'] . "</font><br />";
                  $name_user = "";
                  if (isset($_REQUEST['name_user']))
                    $name_user = $_REQUEST['name_user'];
                  if (isset($_REQUEST["err_msg"]))
                    echo "<script> alert('Error: " . $_REQUEST["err_msg"] . "'); </script>\n";
                  echo "<br /><img src='" . JRoute::_( "index.php?option=com_realestatemanager&amp;task=secret_image&Itemid=" . $Itemid)."'
                  alt='CAPTCHA_picture'/><br/>";
                  ?>
                  <!--**********************   end insert image   *******************************-->
                </div>
                <div class="row_05"><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_KEYGUEST; ?></div>
                <div class="row_06">
                  <input class="inputbox" type="text" name="keyguest" size="6" maxlength="6" />
                </div>
                <?php
              }
            }
            ?>

            <!--****************************   end add antispam guest   ******************************-->
            <div  class="row_button_review">
              <span class="button_save"> 
                <!-- save review button-->
                <input class="button" type="button" value="<?php 
                echo _REALESTATE_MANAGER_LABEL_BUTTON_SAVE; ?>" onclick="review_submitbutton()"/>
              </span>
            </div>

          </div>

          <input type="hidden" name="fk_houseid" value="<?php echo $house->id; ?>" />
          <input type="hidden" name="catid" value="<?php $temp = $house->catid;
          echo $temp[0]; ?>" />
        </form>
      </div> 
      <!-- end <div id="hidden_review"> -->
        <?php
    } //end if($params->get('show_inputreviews'))
} // end if( $params->get('show_reviews'))
?>
</div>
<?php
/////////////////////////////////////////////START CALENDAR\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
if ($house->listing_type == 1) {
  if ($params->get('show_rentrequest') && $params->get('show_rentstatus') && $params->get('calendar_show')) {
    ?>
    <div id="country5" class="tabcontent">
      <div style="text-align: center;" >
        <?php
        if (isset($_POST['month']) && isset($_POST['year'])) {
          $month = $_POST['month'];
          $year = $_POST['year'];
          $calendar = PHP_realestatemanager::getCalendar($month, $year, $house->id);
        } else {
          $month = date("m", mktime(0, 0, 0, date('m'), 1, date('Y')));
          $year = date("Y", mktime(0, 0, 0, date('m'), 1, date('Y')));
          $calendar = PHP_realestatemanager::getCalendar($month, $year, $house->id);
        }
        ?>
        <h4><?php echo _REALESTATE_MANAGER_LABEL_CALENDAR_TITLE; ?></h4>
        <form action="" method="post" id="calendar" name="calendar" >    
          <select name="month" class="inputbox" onChange="form.submit()">
            <option value="1" <?php if ($month == '1') echo "selected" ?> >
              <?php echo JText::_('JANUARY'); ?>
            </option>
            <option value="2" <?php if ($month == '2') echo "selected" ?> >
              <?php echo JText::_('FEBRUARY'); ?>
            </option>
            <option value="3" <?php if ($month == '3') echo "selected" ?> >
              <?php echo JText::_('MARCH'); ?>
            </option>
            <option value="4" <?php if ($month == '4') echo "selected" ?> >
              <?php echo JText::_('APRIL'); ?>
            </option>
            <option value="5" <?php if ($month == '5') echo "selected" ?> >
              <?php echo JText::_('MAY'); ?>
            </option>
            <option value="6" <?php if ($month == '6') echo "selected" ?> >
              <?php echo JText::_('JUNE'); ?>
            </option>
            <option value="7" <?php if ($month == '7') echo "selected" ?> >
              <?php echo JText::_('JULY'); ?>
            </option>
            <option value="8" <?php if ($month == '8') echo "selected" ?> >
              <?php echo JText::_('AUGUST'); ?>
            </option>
            <option value="9" <?php if ($month == '9') echo "selected" ?> >
             <?php echo JText::_('SEPTEMBER'); ?>
           </option>
           <option value="10" <?php if ($month == '10') echo "selected" ?> >
             <?php echo JText::_('OCTOBER'); ?>
           </option>
           <option value="11" <?php if ($month == '11') echo "selected" ?> >
            <?php echo JText::_('NOVEMBER'); ?>
          </option>
          <option value="12" <?php if ($month == '12') echo "selected" ?> >
            <?php echo JText::_('DECEMBER'); ?>
          </option>
        </select>
        <select name="year" class="inputbox"  onChange="form.submit()">
          <option value="2012" <?php if ($year == '2012') echo "selected" ?> >2012</option>
          <option value="2013" <?php if ($year == '2013') echo "selected" ?> >2013</option>
          <option value="2014" <?php if ($year == '2014') echo "selected" ?> >2014</option>
          <option value="2015" <?php if ($year == '2015') echo "selected" ?> >2015</option>
          <option value="2016" <?php if ($year == '2016') echo "selected" ?> >2016</option>
          <option value="2017" <?php if ($year == '2017') echo "selected" ?> >2017</option>
        </select>
      </form>
      <div class="rem_tableC basictable">
        <div class="row_01">
          <span class="col_01"><?php echo $calendar->tab1; ?></span>
          <span class="col_02"><?php echo $calendar->tab2; ?></span>
          <span class="col_03"><?php echo $calendar->tab3; ?></span>
          <span class="col_03"><?php echo $calendar->tab4; ?></span>
        </div>
        <div class="row_02">
          <span class="col_01"><?php echo $calendar->tab21; ?></span>
          <span class="col_02"><?php echo $calendar->tab22; ?></span>
          <span class="col_02"><?php echo $calendar->tab23; ?></span>
          <span class="col_03"><?php echo $calendar->tab24; ?><br /></span>
        </div>
        <div class="calendar_notation row_03">
          <div class="row_calendar">
            <span class="label_calendar_available">
              <?php echo _REALESTATE_MANAGER_LABEL_CALENDAR_AVAILABLE; ?></span>
              <div class="calendar_available_notation"></div>
            </div>
            <div class="row_calendar">
              <span class="label_calendar_available">
                <?php echo _REALESTATE_MANAGER_LABEL_CALENDAR_NOT_AVAILABLE; ?></span>
                <div class="calendar_not_available_notation"></div>
              </div>
            </div>
          </div>
        </div>
      </div>   
      <?php
    }
  } 
/////////////////////////////////////////////END CALENDAR\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\   
  ?>
</div> <!--end all tabs -->

<script type="text/javascript">
  var countries=new ddtabcontent("countrytabs")
  countries.setpersist(true)
    countries.setselectedClassTarget("link") //"link" or "linkparent"
    countries.init()
  </script>

</div>

<div class="span3">
  <?php positions_rem($params->get('view05')); ?>
  <?php    if ($params->get('show_owner_line') ==1 && $params->get('show_owner_line') ==1) {
    ?>
    <div class="rem_house_contacts">
      <div id="rem_house_titlebox">
        <?php echo _REALESTATE_MANAGER_LABEL_CONTACT_AGENT ; ?>
      </div>    
      <?php if (isset($house->agent) && trim($house->agent)) { ?>
      <span class="col_02"><?php echo $house->agent; ?></span>
      <?php
    }
    ?>

    <?php
    if ($params->get('show_owner_line') && $house->ownername != '' || $house->owneremail != '') {
      if ($params->get('show_owner_registrationlevel')) {
        ?>
        <span class="col_02"><?php echo $house->ownername, '</br>', $house->owneremail; ?></span>
        <?php
      }
    }
    ?>

    <?php
    if ($params->get('show_contacts_line')) {
      if ($params->get('show_contacts_registrationlevel')) {
        if (isset($house->contacts) && trim($house->contacts)) {
          ?>
          <span class="col_02"><?php echo $house->contacts; ?></span>
          <?php
        }
      }
    }
    ?>
  </div>
  <?php 
} ?>
<?php 
if($house->listing_type != 0) {?>
<div class="rem_buying_house">
  <?php
  if ($params->get('show_pricerequest')) {
    ?>              
    <?php
  }
  ?>          

  <?php
  if ($house->listing_type == 1) {
   if ($params->get('show_rentrequest') && $params->get('show_rentstatus') && ($house->price > 0)) {
     ?>
     <?php
     if ($option != 'com_realestatemanager') {
      $form_action = "index.php?option=" . $option .
      "&task=save_rent_request&Itemid=" . $Itemid ;
    } else
    $form_action = "index.php?option=com_realestatemanager&amp;task=save_rent_request&amp;Itemid=" . $Itemid;
    ?>
    <div id="rem_house_titlebox">
      <?php echo _REALESTATE_MANAGER_LABEL_BOOK_NOW ; ?>
    </div>
    <form action="<?php echo sefRelToAbs($form_action); ?>" method="post" name="rent_request_form">  
      <div id="show_buying"> 
        <input type="hidden" name="bid[]" value="<?php echo $house->id; ?>" />
        <input type="hidden" name="houseid" id="houseid" value="<?php echo $house->id ?>" maxlength="80" />
        <input type="hidden" name="calculated_price" id="calculated_price" value="" maxlength="80" />
        <?php 
        global $my;
        if ($my->guest) {
          ?>
          <div class="row_01">
            <div id="user_name_warning"></div>
            <input class="inputbox" id="alert_name" type="text" name="user_name" size="38" 
            maxlength="80" placeholder="<?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_NAME ; ?>*" />
          </div>
          <div class="row_02">
            <div id="user_email_warning"></div>
            <input class="inputbox" id="alert_mail" type="text" name="user_email" size="30" 
            maxlength="80" placeholder="<?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_EMAIL; ?>*" />
          </div>
          <?php
        } else {
          ?>

          <div class="row_03">
            <div id="user_name_warning"></div>
            <input class="inputbox" id="alert_name"  type="text" name="user_name" size="38" 
            maxlength="80" value="<?php echo $my->name; ?>" 
            placeholder="<?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_NAME; ?>*" />
          </div>
          <div class="row_04">
            <div id="user_email_warning"></div>
            <input id="alert_mail" class="inputbox" type="text" name="user_email" size="30" 
            maxlength="80" value="<?php echo $my->email; ?>" 
            placeholder="<?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_EMAIL; ?>*" />
          </div>
          <?php
        }
        ?>


        <script type="text/javascript">
          Date.prototype.toLocaleFormat = function(format) {
            var f = {Y : this.getYear() + 1900,m : this.getMonth() + 1,d : this.getDate(),
              H : this.getHours(),M : this.getMinutes(),S : this.getSeconds()}
              for(k in f)
                format = format.replace('%' + k, f[k] < 10 ? "0" + f[k] : f[k]);
              return format;
            };
            window.onload = function ()
            {
              var today = new Date();
              var date = today.toLocaleFormat("<?php echo $realestatemanager_configuration['date_format'] ?>");
                   //fix later //first load date dug.
                   // document.getElementById('rent_from').value = date;
                   // document.getElementById('rent_until').value = date;
                 }; 
               </script>


               <div class="row_05">
                <?php
                //  editorArea('editor1', '', 'user_mailing', '400', '200', '30', '5');
                ?>
                <textarea name="user_mailing" cols="50" rows="8" placeholder="<?php 
                echo _REALESTATE_MANAGER_TAB_DESCRIPTION; ?>" ></textarea>
              </div>
              <div>
                <p class="warning">Note : Les locations fonctionnent à la semaine.</p>
                <p>Durée de réservation en semaine :</p>
                <select id="week">
                  <option value="1" selected>1</option> 
                  <option value="2" >2</option>
                  <option value="3">3</option>
                </select>
              </div>
              <div class="row_06">
                <p><?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_FROM; ?>:</p>
                <?php global $realestatemanager_configuration;?>
                <p><input type="text" id="rent_from" name="rent_from"></p>
              </div>
              <div class="row_07">
                <p><?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_UNTIL; ?>:</p>
                <p><input type="text" id="rent_until" name="rent_until"></p>
              </div>

            </div>
            <script>
              jQuerREL("#rent_from, #week").change(function() {
                var rent_from_val = jQuerREL("#rent_from").val();
                var week = jQuerREL("#week").val();
                  //var rent_until = jQuerREL("#rent_until");
                  if(week == 1)
                  {
                    var dataAsObject = jQuerREL("#rent_from").datepicker('getDate');
                    dataAsObject.setDate(dataAsObject.getDate() + 6);
                    var rent_until = new Date(dataAsObject);
                    var rent_until = getUntilDate(rent_until);
                    jQuerREL("#rent_until").val(rent_until);

                    jQuerREL("#rent_until").attr("disabled","disabled");
                    updatePrice();
                  }
                  else if (week == 2)
                  {
                   var dataAsObject = jQuerREL("#rent_from").datepicker('getDate');
                   dataAsObject.setDate(dataAsObject.getDate() + 13);
                   var rent_until = new Date(dataAsObject);
                   var rent_until = getUntilDate(rent_until);
                   jQuerREL("#rent_until").val(rent_until);
                   jQuerREL("#rent_until").attr("disabled","disabled");
                   updatePrice();
                 }
                 else
                 {
                   var dataAsObject = jQuerREL("#rent_from").datepicker('getDate');
                   dataAsObject.setDate(dataAsObject.getDate() + 20);
                   var rent_until = new Date(dataAsObject);
                   var rent_until = getUntilDate(rent_until);
                   jQuerREL("#rent_until").val(rent_until);
                   jQuerREL("#rent_until").attr("disabled","disabled");
                   updatePrice();
                 }

                //newdate.setDate(newdate.getDate() - 7)
                function updatePrice()
                {
                  var rent_from = jQuerREL(" #rent_from ").val();
                  var rent_until = jQuerREL(" #rent_until ").val();
                  var week = jQuerREL(" #week ").val();
                  jQuerREL.ajax({ 
                    type: "POST",
                    url: "index.php?option=com_realestatemanager&task=ajax_rent_calcualete"
                    +"&bid=<?php echo $house->id; ?>&rent_from="+
                    rent_from+"&rent_until="+rent_until+"&week="+week,
                    data: { " #do " : " #1 " },
                    update: jQuerREL(" #message-here "),
                    success: function( data ) {
                      jQuerREL("#message-here").html(data);
                      jQuerREL("#calculated_price").val(data);
                    }
                  });
                }


                function getUntilDate(dateObject) {
                  var d = new Date(dateObject);
                  var day = d.getDate();
                  var month = d.getMonth() + 1;
                  var year = d.getFullYear();
                  if (day < 10) {
                    day = "0" + day;
                  }
                  if (month < 10) {
                    month = "0" + month;
                  }
                  var date = day + "-" + month + "-" + year;

                  return date;
                };
              });
            </script>
            <div id="alert_date" name = "alert_date"> <span id="alert_date"> </span>  </div>
            <div id="price_1">
              <span><?php echo    _REALESTATE_MANAGER_LABEL_PRICE. ': '; ?></span>
              <span id="message-here"> </span> 
              <span><?php //echo $house->priceunit; ?></span>
            </div>
            <div id="message-here"> </div>

            <div id="captcha-block">   
              <!--*************************   begin add antispam guest   ********************-->
              <?php if ($params->get('captcha_option_booking')) {
                if ($params->get('captcha_option_booking_registrationlevel') &&  ($my->id == 0)) {
                  ?>
                  <div  class="row_capcha">
                    <!--*************************   begin insetr image   **********************-->
                    <?php
            // begin create kod
                    $st = "      ";
                    $algoritm = mt_rand(1, 2);
                    switch ($algoritm) {
                      case 1:
                      for ($j = 0; $j < 6; $j+= 2) {
                        $st = substr_replace($st, chr(mt_rand(97, 122)), $j, 1); //abc
                        $st = substr_replace($st, chr(mt_rand(50, 57)), $j + 1, 1); //23456789
                      }
                      break;
                      case 2:
                      for ($j = 0; $j < 6; $j+= 2) {
                        $st = substr_replace($st, chr(mt_rand(50, 57)), $j, 1); //23456789
                        $st = substr_replace($st, chr(mt_rand(97, 122)), $j + 1, 1); //abc
                      }
                      break;
                    }
            //**************   begin search in $st simbol 'o, l, i, j, t, f'   ***************************
                    $st_validator = "olijtf";
                    for ($j = 0; $j < 6; $j++) {
                      for ($i = 0; $i < strlen($st_validator); $i++) {
                        if ($st[$j] == $st_validator[$i]) {
                        $st[$j] = chr(mt_rand(117, 122)); //uvwxyz
                      }
                    }
                  }
            //**************   end search in $st simbol 'o, l, i, j, t, f'   *****************************
                  $session = JFactory::getSession();
                  $session->set('captcha_keystring', $st);
                  if (isset($_REQUEST['error']) && $_REQUEST['error'] != "")
                    echo "<font style='color:red'>" . $_REQUEST['error'] . "</font><br />";
                  $name_user = "";
                  if (isset($_REQUEST['name_user']))
                    $name_user = $_REQUEST['name_user'];
                  if (isset($_REQUEST["err_msg"]))
                    echo "<script> alert('Error: " . $_REQUEST["err_msg"] . "'); </script>\n";
                  echo "<br /><img src='" . JRoute::_( "index.php?option=com_realestatemanager&amp;task=secret_image")."'
                  alt='CAPTCHA_picture'/><br/>";
                  ?>
                  <!--**********************   end insert image   *******************************-->
                </div>
                <div class="row_05"><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_KEYGUEST; ?></div>
                <div class="row_06">
                  <input class="inputbox" type="text" name="keyguest" size="6" maxlength="6" />
                </div>

                <?php
              }
            }
            ?>

            <!--****************************   end add antispam guest   ******************************-->


            <?php
            if ($params->get('show_rentstatus') && $params->get('show_rentrequest') 
              && !$params->get('rent_save') && !$params->get('search_request')) {
                ?>
                <br />
                <input type="button" value="<?php echo _REALESTATE_MANAGER_LABEL_BUTTON_RENT_REQU ; ?>" 
                class="button" onclick="rem_rent_request_submitbutton()" />
                <br />
                <?php
              } else if ($params->get('show_rentstatus') && $params->get('show_rentrequest') && $params->get('rent_save') 
                && !$params->get('search_request')) {
                  ?>
                  <input type="button" class="button" value="<?php echo _REALESTATE_MANAGER_LABEL_BUTTON_RENT_REQU_SAVE; ?>" 
                  onclick="rem_rent_request_submitbutton()" />
                  <?php } else {
                    ?>
                    &nbsp;
                    <?php
                  }
                  ?>
                </div>


              </form>
              <?php
            } else
            echo "</form>";
          } else if ($house->listing_type == 2) {
            ?>
          </form>
          <?php
          if ($params->get('show_buyrequest') && $params->get('show_buystatus')) {
            global $option;
            if ($option != 'com_realestatemanager') {
              $form_action = "index.php?option=" . $option 
              . "&task=buying_request&Itemid=" 
              . $Itemid ;
            } else
            $form_action = "index.php?option=com_realestatemanager&amp;task=buying_request&amp;Itemid=" . $Itemid;
            ?>
            <div id="rem_house_titlebox">
              <?php echo _REALESTATE_MANAGER_LABEL_BUTTON_SEND_MESSAGE; ?>
            </div>
            <div id="show_buying">
              <form action="<?php echo sefRelToAbs($form_action); ?>" method="post" name="buying_request">
                <div class="table_08">
                  <?php
                  global $my;
                  if ($my->guest) {
                    ?>      
                    <div class="row_01">
                      <div id="customer_name_warning"></div>
                      <span class="col_02"><input id="alert_name_buy" class="inputbox" type="text" 
                        name="customer_name" size="38" maxlength="80" placeholder="<?php 
                        echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_NAME ; ?>*"/></span>
                      </div>
                      <div class="row_02">
                        <div id="customer_email_warning"></div>
                        <span class="col_02"><input id="alert_mail_buy" class="inputbox" type="text" 
                          name="customer_email" size="38" maxlength="80" placeholder="<?php 
                          echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_EMAIL; ?>*"/></span>
                        </div>

                        <?php
                      } else {
                        ?>
                        <div class="row_03">
                          <div id="customer_name_warning"></div>
                          <span class="col_02">
                            <input id="alert_name_buy"  class="inputbox" type="text" name="customer_name" size="38" 
                            maxlength="80" placeholder="<?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_NAME; ?>" 
                            value="<?php echo $my->name; ?> " /></span>
                          </div>
                          <div class="row_04">
                            <div id="customer_email_warning"></div>
                            <span class="col_02">
                              <input id="alert_mail_buy"  class="inputbox" type="text" name="customer_email" size="38" 
                              maxlength="80" placeholder="<?php echo _REALESTATE_MANAGER_LABEL_RENT_REQUEST_EMAIL; ?>" 
                              value="<?php echo $my->email; ?>"/></span>
                            </div>
                            <?php
                          }
                          ?>
                          <div class="row_05">
                            <div id="customer_phone_warning"></div>
                            <span class="col_02">
                              <input class="inputbox" type="text" id="customer_phone" name="customer_phone" 
                              size="38" maxlength="80" placeholder="<?php echo _REALESTATE_MANAGER_REQUEST_PHONE; ?>" />
                            </span>
                          </div>
                          <div class="row_06">
                            <textarea name="customer_comment" cols="50" rows="8" placeholder="<?php 
                            echo _REALESTATE_MANAGER_TAB_DESCRIPTION; ?>" ></textarea>        
                            <input type="hidden" name="bid[]" value="<?php echo $house->id; ?>" />
                          </div>

                          <!--*************************   begin add antispam guest   ********************-->
                          <?php if ($params->get('captcha_option_sendmessage')) {
                            if ($params->get('captcha_option_sendmessage_registrationlevel') &&  ($my->id == 0)) {
                              ?>
                              <div  class="row_capcha">
                                <!--*************************   begin insetr image   **********************-->
                                <?php
            // begin create kod
                                $st = "      ";
                                $algoritm = mt_rand(1, 2);
                                switch ($algoritm) {
                                  case 1:
                                  for ($j = 0; $j < 6; $j+= 2) {
                        $st = substr_replace($st, chr(mt_rand(97, 122)), $j, 1); //abc
                        $st = substr_replace($st, chr(mt_rand(50, 57)), $j + 1, 1); //23456789
                      }
                      break;
                      case 2:
                      for ($j = 0; $j < 6; $j+= 2) {
                        $st = substr_replace($st, chr(mt_rand(50, 57)), $j, 1); //23456789
                        $st = substr_replace($st, chr(mt_rand(97, 122)), $j + 1, 1); //abc
                      }
                      break;
                    }
            //**************   begin search in $st simbol 'o, l, i, j, t, f'   ***************************
                    $st_validator = "olijtf";
                    for ($j = 0; $j < 6; $j++) {
                      for ($i = 0; $i < strlen($st_validator); $i++) {
                        if ($st[$j] == $st_validator[$i]) {
                        $st[$j] = chr(mt_rand(117, 122)); //uvwxyz
                      }
                    }
                  }
            //**************   end search in $st simbol 'o, l, i, j, t, f'   *****************************
                  $session = JFactory::getSession();
                  $session->set('captcha_keystring', $st);
                  if (isset($_REQUEST['error']) && $_REQUEST['error'] != "")
                    echo "<font style='color:red'>" . $_REQUEST['error'] . "</font><br />";
                  $name_user = "";
                  if (isset($_REQUEST['name_user']))
                    $name_user = $_REQUEST['name_user'];
                  if (isset($_REQUEST["err_msg"]))
                    echo "<script> alert('Error: " . $_REQUEST["err_msg"] . "'); </script>\n";
                  echo "<br /><img src='" . JRoute::_( "index.php?option=com_realestatemanager&amp;task=secret_image")."'
                  alt='CAPTCHA_picture'/><br/>";
                  ?>
                  <!--**********************   end insert image   *******************************-->
                </div>
                <div class="row_05"><?php echo _REALESTATE_MANAGER_LABEL_REVIEW_KEYGUEST; ?></div>
                <div class="row_06">
                  <input class="inputbox" type="text" name="keyguest" size="6" maxlength="6" />
                </div>

                <?php
              }
            }
            ?>

            <!--****************************   end add antispam guest   ******************************-->

            <div class="row_07">
              <span class="col_01">
                <input type="button" value="<?php echo _REALESTATE_MANAGER_LABEL_BUTTON_SEND_MESSAGE; ?>" 
                class="button" onclick="buying_request_submitbutton()"/>
              </span> 
            </div>
          </div>
        </form>
      </div>
      <?php
    }
  } else
  echo "</form>";
  ?>

</div>
<?php } ?>
</div> <!-- end span3-->




</div>

<?php positions_rem($params->get('similaires')); ?>

<div>
  <?php
  mosHTML::BackButton($params, $hide_js);
  ?>
</div>
<!-- Modal -->
<a href="#aboutus" class="rem-button-about"></a>

<a href="#rem-modal-css" class="rem-overlay" id="rem-aboutus" style="display: none;"></a>
<div class="rem-popup">
  <div class="rem-modal-text">
    Please past text to modal
  </div>

  <a class="rem-close" title="Close" href="#rem-close"></a>
</div>