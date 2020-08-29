<?php
/* Copyright (C) 2012      Mikael Carlavan        <contact@mika-carl.fr>
 *                                                http://www.mikael-carlavan.fr
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
 *	\file       htdocs/core/triggers/interface_010_modConfRDV_ConfRDVWorkflow.class.php
 *  \ingroup    agenda
 *  \brief      Trigger file for agenda module
 */


/**
 *	\class      InterfaceConfRDVWorkflow
 *  \brief      Class of triggered functions for ndfp module
 */
 
dol_include_once("/confirm/class/confirm.class.php");
 
class InterfaceConfirmWorkflow
{
    var $db;
    var $error;

    var $date;
    var $duree;
    var $texte;
    var $desc;

    /**
     *   Constructor.
     *   @param      DB      Database handler
     */
    function InterfaceConfirmWorkflow($DB)
    {
        $this->db = $DB;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "agenda";
        $this->description = "Triggers of confirm module.";
        $this->version = '1.5.4';                        // 'experimental' or 'dolibarr' or version
        $this->picto = 'confirm@confirm';
    }

    /**
     *   Return name of trigger file
     *   @return     string      Name of trigger file
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   Return description of trigger file
     *   @return     string      Description of trigger file
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Return version of trigger file
     *   @return     string      Version of trigger file
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      Function called when a Dolibarrr business event is done.
     *      All functions "run_trigger" are triggered if file is inside directory htdocs/includes/triggers
     *
     *      @param      action      Event code (COMPANY_CREATE, PROPAL_VALIDATE, ...)
     *      @param      object      Object action is done on
     *      @param      user        Object user
     *      @param      langs       Object langs
     *      @param      conf        Object conf
     *      @return     int         <0 if KO, 0 if no action are done, >0 if OK
     */
    function run_trigger($action, $object, $user, $langs, $conf)
    {

        if (empty($conf->agenda->enabled))
        {
           return 0;     // Module not active, we do nothing 
        } 

		if (is_object($langs))
		{
			$langs->load("other");
			$langs->load("confirm@confirm");
			$langs->load("agenda");
        }    

		if (is_object($object))
		{        
			// Actions
			if ($action == 'ACTION_CREATE')
			{
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

				if ($object->type_code == 'AC_TEL' || $object->type_code == 'AC_RDV')
				{
					// This action may need confirmation, insert it the confirmation table
					$confm = new Confirm($this->db);
	   
					$confm->id = 0;
					$confm->ref = 'PROV';
			
					$confm->fk_action = $object->id;
					$confm->conf_sent = 0;
					$confm->conf_built = 0;

					$confm->userconf->id = 0;
					$confm->usersend->id = 0;
				
					$confm->entity = $conf->entity;                        
				
					$confm->phone = '';
					$confm->address = '';
				  
				
					$confm->add($user);  
				
				}

			}
			elseif ($action == 'ACTION_DELETE')
			{
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

				// Remove confirmations of this action
				$confm = new Confirm($this->db);
				$confm->fk_action = $object->id;
			
				$confm->delete_from_action($user);
			}

		}
		return 0;
    }

}
?>
