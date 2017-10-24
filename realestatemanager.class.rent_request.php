<?php

if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 *
 * @package  RealEstateManager
 * @copyright 2012 Andrey Kvasnevskiy-OrdaSoft(akbet@mail.ru); Rob de Cleen(rob@decleen.com)
 * Homepage: http://www.ordasoft.com
 * @version: 3.0 Pro
 *
 *
 */
class mosRealEstateManager_rent_request extends JTable {

    /** @var int Primary key */
    var $id = null;

    /** @var int - the house id this rent is assosiated with */
    var $fk_houseid = null;

    /** @var datetime - since when this house is rent out */
    var $rent_from = null;

    /** @var datetime - when the house should be returned */
    var $rent_until = null;

    /** @var datetime - when the house realy was/is returned */
    var $rent_request = null;

    /** @var boolean */
    var $checked_out = null;

    /** @var time */
    var $checked_out_time = null;

    /** @var string - the user who rent this house */
    var $user_name = null;

    /** @var string â€“ the email of the user who rent this house */
    var $user_email = null;

    /** @var string â€“ the mail address of the user who rent this house */
    var $user_mailing = null;

    /** @var string â€“ the e-mail address of the user who rent this house if it's no user of the database */
    var $status = 0;
    var $fk_userid = null;

    /**
     * @param database - A database connector object
     */
    function __construct(&$db) {
        parent::__construct('#__rem_rent_request', 'id', $db);
    }

    // overloaded check function
    function check() {
        // check if house is already rent out
        $this->_db->setQuery("SELECT fk_rentid FROM #__rem_houses " . "\nWHERE id='$this->fk_houseid' AND fk_rentid = null");
        $xid = intval($this->_db->loadResult());
        if ($xid) {
            $this->_error = _HOUSE_RENT_OUT;
            return false;
        }
        return true;
    }

    /**
     * @return array â€“ name: the string of the user the house is rent to - e-mail: the e-mail address of the user
     */
    function getRentTo() {
        $retVal['name'] = null;
        $retVal['email'] = null;
        if ($this->fk_userid != null && $this->fk_userid != 0) {
            $this->_db->setQuery("SELECT name, email from #__users where id=$this->fk_userid");
            $help = $this->_db->loadRow();
            $retVal['name'] = $help[0];
            $retVal['email'] = $help[1];
        } else {
            $retVal['name'] = $user_name;
            $retVal['email'] = $user_email;
        }
        return $retVal;
    }

    //status codes
    //0: just inserted
    //1: accepted
    //2: not accepted
    function accept() {
        global $database, $my, $realestatemanager_configuration;
        if ($this->id == null) {
            return "Method called on a non instant object";
        }
        $this->checkout($my->id);
        //create new rent dataset
     
        // print_r($house);exit;
        //$house->load($this->fk_userid);
        //print_r($this);exit;
        //print_r($houseid);exit;
      
        $data = JFactory::getDBO();

        $query = "SELECT fk_houseid FROM #__rem_rent_request where fk_houseid = " . $this->fk_houseid;
        $data->setQuery($query);
        $h_assoc = $data->loadResult();
        if($assoc_rem = getAssociateHouses($h_assoc)){
            $assoc_rem = getAssociateHouses($h_assoc);
            $assoc_rem = explode(',', $assoc_rem);
        }else{
            $assoc_rem = explode(',', $h_assoc);
        }
      
        for($i = 0, $n = count($assoc_rem); $i < $n; $i++){
        
            $rent = new mosRealEstateManager_rent($database);            
            $house = new mosRealEstateManager($database);           
            //$house->checkout($my->id);
            $house->load($assoc_rem[$i]);
            $query = "SELECT * FROM #__rem_rent where fk_houseid = " . $assoc_rem[$i] . " AND rent_return IS  NULL ";
            $data->setQuery($query);
            $rentTerm = $data->loadObjectList();
            $rent_from = substr($this->rent_from, 0, 10);
            $rent_until = substr($this->rent_until, 0, 10);
            
            foreach ($rentTerm as $oneTerm){

                $oneTerm->rent_from = substr($oneTerm->rent_from, 0, 10);
                $oneTerm->rent_until = substr($oneTerm->rent_until, 0, 10);
                $returnMessage = checkRentDayNightREM (($oneTerm->rent_from),($oneTerm->rent_until), $rent_from, $rent_until, $realestatemanager_configuration);
              
                if($assoc_rem[$i] !== $oneTerm->id && strlen($returnMessage) > 0){                 
                    echo "<script> alert('$returnMessage'); window.history.go(-1); </script>\n";          
                    exit;
                }       
            } 
        
          $rent->rent_from = $this->rent_from;
          $rent->fk_houseid = $assoc_rem[$i];
          $rent->rent_until = $this->rent_until;
          $rent->user_name = $this->user_name;
          $rent->user_email = $this->user_email;
          $rent->user_mailing = $this->user_mailing;
          $rent->fk_userid = $this->fk_userid;
          //$rent->rent_from = date("Y-m-d H:i:s");
          $rent->rent_until = $this->rent_until;
          if (!$rent->check($rent)) {
              return $rent->getError();
          }
          if (!$rent->store()) {
              return $rent->getError();
          }
          $rent->checkin();
          //update house with rent id
          $house->fk_rentid = $rent->id;
          if (!$house->store()) {
              return $house->getError();
          }
          //$house->checkin();
          $this->status = 1;
          if (!$this->store()) {
              return $this->getError();
          }
          $house->checkin();
          
      }
      return null;
    }
    
    function decline() {
    
        if ($this->id == null) {
            return "Method called on a non instant object";
        }
        
        $this->status = 2;
        if (!$this->store()) {
            return $this->getError();
        }
        return null;
    }

    function toXML3($xmlDoc) {
    
        //create and append name element
        $retVal = $xmlDoc->createElement("rentrequest");
        $fk_userid = $xmlDoc->createElement("fk_userid");
        $fk_userid->appendChild($xmlDoc->createTextNode($this->fk_userid));
        $retVal->appendChild($fk_userid);
        $rent_from = $xmlDoc->createElement("rent_from");
        $rent_from->appendChild($xmlDoc->createTextNode($this->rent_from));
        $retVal->appendChild($rent_from);
        $rent_until = $xmlDoc->createElement("rent_until");
        $rent_until->appendChild($xmlDoc->createTextNode($this->rent_until));
        $retVal->appendChild($rent_until);
        $rent_request = $xmlDoc->createElement("rent_retquest");
        $rent_request->appendChild($xmlDoc->createTextNode($this->rent_request));
        $retVal->appendChild($rent_request);
        $user_name = $xmlDoc->createElement("user_name");
        $user_name->appendChild($xmlDoc->createTextNode($this->user_name));
        $retVal->appendChild($user_name);
        $user_email = $xmlDoc->createElement("user_email");
        $user_email->appendChild($xmlDoc->createTextNode($this->user_email));
        $retVal->appendChild($user_email);
        $user_mailing = $xmlDoc->createElement("user_mailing");
        $user_mailing->appendChild($xmlDoc->createTextNode($this->user_mailing));
        $retVal->appendChild($user_mailing);
        $status = $xmlDoc->createElement("status");
        $status->appendChild($xmlDoc->createTextNode($this->status));
        $retVal->appendChild($status);
        return $retVal;
    }

    function toXML2() {
        $retVal = "<rentrequest>\n";
        $retVal.= "<fk_userid>" . $this->fk_userid . "</fk_userid>\n";
        $retVal.= "<rent_from>" . $this->rent_from . "</rent_from>\n";
        $retVal.= "<rent_until>" . $this->rent_until . "</rent_until>\n";
        $retVal.= "<rent_request>" . $this->rent_request . "</rent_request>\n";
        $retVal.= "<user_name>" . $this->user_name . "</user_name>\n";
        $retVal.= "<user_email>" . $this->user_email . "</user_email>\n";
        $retVal.= "<user_mailing><![CDATA[" . $this->user_mailing . "]]></user_mailing>\n";
        $retVal.= "<status>" . $this->status . "</status>\n";
        $retVal.= "</rentrequest>\n";
        return $retVal;
    }

}

