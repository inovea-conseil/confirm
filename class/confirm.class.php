<?php
/* Copyright (C) 2012      Mikael Carlavan        <mcarlavan@qis-network.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * TODO : réécrire fonction fetch, créer variable $action
 * 
 */
 
/**
 *       \file       htdocs/confirm/class/confirm.class.php
 *       \ingroup    commercial
 *       \brief      File of class to manage appointments confirmations
 */
require_once(DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php');
require_once(DOL_DOCUMENT_ROOT."/comm/action/class/actioncomm.class.php");
require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");

dol_include_once("/confirm/core/modules/confirm/modules_confirm.php");

/**     \class      Confirm
 *	    \brief      Class to manage appointments confirmations
 */
class Confirm extends CommonObject
{
	var $db;
	var $error;
	var $errors=array();
	var $element='confirm';
	var $table_element = 'confirm';
	var $ismultientitymanaged = 2;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe


    var $id;    
    var $ref = '';
    var $fk_action = 0; //If of action
  
    
    var $conf_sent = 0; // 1=confirmation sent
    var $conf_built = 0; // 1=confirmation generated  
    
    var $userconf = 0; // Id of user who submit confirmation     
    var $usersend = 0; // Id of user who mail confirmation 
    
    var $phone;//Free phone
    var $address;

    
    var $entity = 0;    
    var $datec;//Confirmation date
    var $dates;//Send date
        
    var $datef;//idem       
    var $tms;           // Timestamp

    
    var $specimen = 0;
    var $action = null;   
    var $contact = null;
    
       
    /**
     *      Constructor
     *      @param      db      Database handler
     */
    function Confirm($db)
    {
        $this->db = $db;
    }

    /**
     *    Add a confirmation into database
     *    @param      user      	Object user making action
 	 *    @param      notrigger		1 = disable triggers, 0 = enable triggers
     *    @return     int         	Id of created event, < 0 if KO
     */
    function add($user)
    {
        global $langs, $conf;

		$now = dol_now();

		// Clean parameters
        if (empty($this->conf_sent))   $this->conf_sent = 0;
        if (empty($this->conf_built))   $this->conf_built = 0;        
        if ($this->fk_action < 0) $this->fk_action = 0;
        if ($this->userconf->id < 0) $this->userconf->id = 0;
        if ($this->usersend->id < 0) $this->usersend->id = 0;
                        
		$this->db->begin();
    
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."confirm";
        $sql.= "(ref,";
        $sql.= "fk_action,";        
        $sql.= "conf_sent,";
        $sql.= "conf_built,";        
        $sql.= "userconf,";
        $sql.= "usersend,";            
        $sql.= "phone,";        
        $sql.= "address,";               
        $sql.= "entity,";
        $sql.= "datec,";
        $sql.= "dates,";        
        $sql.= "datef";                                         
        $sql.= ") VALUES (";
        $sql.= "'".$this->ref."',";        
        $sql.= ($this->fk_action > 0 ? $this->fk_action : "0").",";
        $sql.= $this->conf_sent.",";
        $sql.= $this->conf_built.",";        
        $sql.= ($this->userconf->id > 0 ? $this->userconf->id : "0").",";
        $sql.= ($this->usersend->id > 0 ? $this->usersend->id : "0").",";          
        $sql.= "'".$this->db->escape($this->phone)."',";
        $sql.= "'".$this->db->escape($this->address)."',";              
        $sql.= $conf->entity.",";
        $sql.= (empty($this->datec) ? "NULL," : "'".$this->db->idate($this->datec)."',");
        $sql.= (empty($this->dates) ? "NULL," : "'".$this->db->idate($this->dates)."',");      
        $sql.= (empty($this->datef) ? "NULL" : "'".$this->db->idate($this->datef)."'");
        $sql.= ")";

        dol_syslog("Confirm::add sql=".$sql);
        $resql=$this->db->query($sql);
		if ($resql)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."confirm");
            
			$this->db->commit();
            
            $this->fetch($this->id);
            $this->ref = $this->getNextNumRef($this->action->societe);
            
            $sql = 'UPDATE '.MAIN_DB_PREFIX."confirm SET ref='".$this->ref."' WHERE rowid=".$this->id; 
            $this->db->query($sql);
                       
            return $this->id;
        }
        else
        {
			$this->error=$this->db->lasterror().' sql='.$sql;
			$this->db->rollback();
            return -1;
        }

    }

	/**
	*    Charge l'objet confirmation depuis la base
	*    @param      id      id de la confirmation a recuperer
	*/
	function fetch($id = 0, $fk_action = 0)
	{
		global $conf, $user, $langs;

		$sql = "SELECT c.rowid,";
        $sql.= " c.ref,";
        $sql.= " c.fk_action,";        
        $sql.= " c.conf_sent,";
        $sql.= " c.conf_built,";        
        $sql.= " c.userconf,";
        $sql.= " c.usersend,";           
        $sql.= " c.phone,";        
        $sql.= " c.address,";                   
        $sql.= " c.entity,";
        $sql.= " c.datec,";
        $sql.= " c.dates,";        
        $sql.= " c.datef,";                                
		$sql.= " c.tms";
		$sql.= " FROM ".MAIN_DB_PREFIX."confirm as c";
		if ($id > 0)
		{
			$sql.= " WHERE c.rowid=".$id;
		}
		else
		{
			if ($fk_action > 0)
			{
				$sql.= " WHERE c.fk_action=".$fk_action;
			}
			else
			{
				$this->error = $langs->trans('WrongParameter');
				return -1;
			}		
		}
		
		dol_syslog("Confirm::fetch sql=".$sql);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id        = $obj->rowid;

                $this->ref          = $obj->ref;
                $this->fk_action    = $obj->fk_action;                
                $this->conf_sent    = $obj->conf_sent;
                $this->conf_built   = $obj->conf_built;            
                
                $this->userconf     = new User($this->db);
                $this->userconf->id = $obj->userconf;    

                $this->usersend     = new User($this->db);
                $this->usersend->id = $obj->usersend;  
                            
                $this->phone    = $obj->phone;
                $this->address  = $obj->address;
  
                
                $this->entity       = $obj->entity;    
                $this->datec        = $this->db->jdate($obj->datec);
                $this->dates        = $this->db->jdate($obj->dates);
                                
                $this->datef        = $this->db->jdate($obj->datef);       
                $this->tms          = $this->db->jdate($obj->tms);
                
			    // Fetch action
				if ($this->fk_action > 0)
				{
					$action = new ActionComm($this->db); 
					$result = $action->fetch($this->fk_action);
				
					if ($result < 0)
					{
						$this->error=$langs->trans('ActionDoesNotExist');
						return -1;                    
					}
					else
					{             
						//Fetch society
						if ($action->societe->id > 0)
						{
							$soc = new Societe($this->db); 
							$result = $soc->fetch($action->societe->id);

							if ($result < 0)
							{
								$this->error = $langs->trans('SocietyDoesNotExist');
								return -1;                    
							}
							else
							{
								$action->societe = $soc;                            
							}                 
						} 
					
						//Fetch user todo
						 if ($action->usertodo->id > 0)
						 {
							$usertodo = new User($this->db); 
							$result = $usertodo->fetch($action->usertodo->id);
						
							if ($result < 0)
							{
								$this->error = $langs->trans('UserDoesNotExist');
								return -1;                    
							}
							else
							{
								$action->usertodo = $usertodo;                            
							}                 
						}                   
									   
						$this->action = $action;
						$this->action->phonecall = ($action->type_code == 'AC_TEL' ? true : false);  
														 
					}                 
				}

				//Fetch userconf
				if ($this->userconf->id > 0)
				{
					$userconf = new User($this->db); 
					$result = $userconf->fetch($this->userconf->id);
				
					if ($result < 0)
					{
						$this->error = $langs->trans('UserDoesNotExist');
						return -1;                     
					}
					else
					{
						$this->userconf = $userconf;                            
					}                        
				}

				//Fetch usersend
				if ($this->usersend->id > 0)
				{
					$usersend = new User($this->db); 
					$result = $usersend->fetch($this->usersend->id);
				
					if ($result < 0)
					{
						$this->error = $langs->trans('UserDoesNotExist');
						return -1;                    
					}
					else
					{
						$this->usersend = $usersend;                            
					}                        
				}
														   
				return $this->id;
			                                                                
			}
			else
			{
				$this->error=$langs->trans('ActionDoesNotExist');
				return 0;
			}
            
			$this->db->free($resql);
            
		}
		else
		{
			$this->error=$this->db->lasterror();
			return -1;
		}
	}

	/**
	*    Charge l'objet confirmation depuis la base
	*    @param      id      id de la confirmation a recuperer
	*/
	
	function fetch_from_action($id)
	{
		global $conf, $user, $langs;

		return $this->fetch(0, $id);
	}
	
	/**
	*    Supprime la confirmation de la base
	*    @return     int     <0 si ko, >0 si ok
	*/
	function delete()
    {
    	global $user, $conf, $langs;
    	
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."confirm";
        $sql.= " WHERE rowid=".$this->id;

        dol_syslog("Confirm::delete sql=".$sql, LOG_DEBUG);
        if ($this->db->query($sql))
        {
            return 1;
        }
        else
        {
        	$this->error=$this->db->lasterror()." sql=".$sql;
        	return -1;
        }
    }
    
	/**
	*    Supprime la confirmation de la base
	*    @return     int     <0 si ko, >0 si ok
	*/
	function delete_from_action($user)
    {
    	global $conf, $langs;
    	
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."confirm";
        $sql.= " WHERE fk_action=".$this->fk_action;

        dol_syslog("Confirm::delete_from_action sql=".$sql, LOG_DEBUG);
        if ($this->db->query($sql))
        {
            return 1;
        }
        else
        {
        	$this->error=$this->db->lasterror()." sql=".$sql;
        	return -1;
        }
    }    

	/**
 	 *    Met a jour confirmation en base.
 	 *    @return     	int     <0 si ko, >0 si ok
	 */
    function update($user)
    {
        global $conf;
        // Clean parameters

		// Clean parameters
        if (empty($this->conf_sent))   $this->conf_sent = 0;
        if (empty($this->conf_built))   $this->conf_built = 0;        
        if ($this->fk_action < 0) $this->fk_action = 0;
        
        if ($this->userconf->id < 0) $this->userconf->id = 0;
        if ($this->usersend->id < 0) $this->usersend->id = 0;
                
        if (empty($this->entity))      $this->entity = $conf->entity;
        
		$sql = "UPDATE ".MAIN_DB_PREFIX."confirm SET";
        $sql.= " ref = '".$this->ref."'";
        $sql.= ", fk_action = ".($this->fk_action > 0 ? $this->fk_action : "0")."";        
        $sql.= ", conf_sent = ".$this->conf_sent."";
        $sql.= ", conf_built = ".$this->conf_built."";        
        $sql.= ", userconf = ".($this->userconf->id > 0 ? $this->userconf->id : "0")."";
        $sql.= ", usersend = ".($this->usersend->id > 0 ? $this->usersend->id : "0")."";           
        $sql.= ", phone = '".$this->db->escape($this->phone)."'";        
        $sql.= ", address = '".$this->db->escape($this->address)."'";               
        $sql.= ", entity = ".$this->entity."";
        $sql.= ", datec = ".(empty($this->datec) ? "NULL" : "'".$this->db->idate($this->datec)."'");
        $sql.= ", dates = ".(empty($this->dates) ? "NULL" : "'".$this->db->idate($this->dates)."'");    
        $sql.= ", datef = ".(empty($this->datef) ? "NULL" : "'".$this->db->idate($this->datef)."'");                                                    
        $sql.= " WHERE rowid=".$this->id;

        
		dol_syslog("Confirm::update sql=".$sql);
        if ($this->db->query($sql))
        {
            return 1;
        }
        else
        {
        	$this->error=$this->db->error();
			dol_syslog("Confirm::update ".$this->error,LOG_ERR);
        	return -1;
    	}
    }

    
   /**
     *		Initialise an example of appointment confirmation with random values
     *		Used to build previews or test instances
     */
    function initAsSpecimen()
    {
        global $user, $outputlangs, $conf, $mysoc;


        // Initialize parameters
        $now = dol_now();
                            
        $this->id = 0;
        $this->ref = 'SPECIMEN';
        $this->specimen = 1;
		$this->fk_action = 0;
        
        $this->action->id = 0;
        $this->action->phonecall = false;
        $this->action->datep = $now; // Appointment date 
        $this->action->societe = $mysoc;
        $this->action->usertodo->id	= $user->id;
        
        $this->conf_sent    = 0;
        $this->conf_built   = 0;
        
        $this->userconf = new User($this->db);
        $this->userconf->id     = 0;   
    
        $this->phone    = $mysoc->phone;
        $this->address  = $mysoc->name ."\n". $mysoc->address ." \n". $mysoc->zip .' '. $mysoc->town;    
        
        $this->entity       = $conf->entity;    
        $this->datec = $now; // Confirmation date
        $this->dates = $now; // Send date                  
        $this->datef = $now; // Creation date for ref numbering 
                          
		$this->tms = $now;             
    }
    
    
    /**
     *      Return next reference of confirmation not already used (or last reference)
     *      according to numbering module defined into constant CONIFRM_ADDON
     *      @param	   soc  		           objet company
     *      @param     mode                    'next' for next value or 'last' for last value
     *      @return    string                  free ref or last ref
     */
    function getNextNumRef($soc, $mode='next')
    {
        global $conf, $db, $langs;
        
        $langs->load("confirm@confirm");

        // Clean parameters (if not defined or using deprecated value)
        if (empty($conf->global->CONIFRM_ADDON)){
            $conf->global->CONIFRM_ADDON = 'mod_confirm_pluton';
        }else if ($conf->global->CONIFRM_ADDON=='pluton'){
            $conf->global->CONIFRM_ADDON = 'mod_confirm_pluton';
        }else if ($conf->global->CONIFRM_ADDON == 'jupiter'){
            $conf->global->CONIFRM_ADDON = 'mod_confirm_jupiter';
        } 

        $included = false;

        $file = $conf->global->CONIFRM_ADDON.".php";
        $classname = $conf->global->CONIFRM_ADDON;
        
        // Include file with class
        $dir = '/confirm/core/modules/confirm/';

        // Include file with class
        $included = dol_include_once($dir.$file);

        if (! $included)
        {
            $this->error = $langs->trans('FailedToIncludeNumberingFile');
            return -1;
        }  

        $obj = new $classname();

        $numref = "";
        $numref = $obj->getNumRef($soc,$this,$mode);

        if ( $numref != "")
        {
            return $numref;
        }
        else
        {
            return false;
        }
    }        
}

?>
