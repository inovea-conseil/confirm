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
 * 		\defgroup   modConfirm     Module modConfirm
 *      \file       htdocs/core/modules/modConfirm.class.php
 *      \ingroup    modConfirm
 *      \brief      Description and activation file for module modConfirm
 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 * 		\class      modConfirm
 *      \brief      Description and activation class for module modConfirm
 */
class modConfirm extends DolibarrModules
{
	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	function modConfirm($DB)
	{
        global $langs, $conf;
		
        $this->db = $DB;
		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 80200;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'confirm';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "crm";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = 'confirm';
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Module permettant d'envoyer une confirmation de RDV par mail au client";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.9.0';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'confirm@confirm';

		// Defined if the directory /mymodule/includes/triggers/ contains triggers or not
		$this->module_parts = array(
				'triggers' => 1,
				'models' => 1, 
				);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array("/confirm");

		// Relative path to module style sheet if exists. Example: '/mymodule/css/mycss.css'.
		//$this->style_sheet = '/mymodule/mymodule.css.php';

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array("config.php@confirm");

		// Dependencies
		$this->depends = array('modAgenda');		// List of modules id that must be enabled if this module is enabled
		$this->conflictwith = array();	
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("confirm@confirm");

		// Constants
		$this->const = array(0 => array('CONFIRM_ADDON','chaine','jupiter','',0),
                             1 => array('CONFIRM_ADDON_PDF','chaine','langouste','', 0),
                             2 => array('CONFIRM_EMAIL_FROM','chaine', $conf->global->MAILING_EMAIL_FROM,'',0),
                             3 => array('CONFIRM_DIR_OUTPUT', 'chaine', DOL_DATA_ROOT.'/confirm', '', 0),
                             4 => array('CONFIRM_SUBPERMCATEGORY_FOR_DOCUMENTS', 'chaine', 'myactions', '', 0));

        $this->tabs = array();
        
        // Dictionnaries
        $this->dictionnaries = array();
        // Boxes
		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r = 0;
		$this->rights[$r][0] = 80201;
		$this->rights[$r][1] = 'Créer/Modifier les confirmations de RDV liées à ce compte';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'create';
               

		$r++;
		$this->rights[$r][0] = 80202;
		$this->rights[$r][1] = 'Voir les confirmations de RDV liées à ce compte';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'read';

		$r++;
		$this->rights[$r][0] = 80203;
		$this->rights[$r][1] = 'Supprimer les confirmations de RDV liées à ce compte';
		$this->rights[$r][2] = 'd';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'delete';
		
		$r++;
		$this->rights[$r][0] = 80204;
		$this->rights[$r][1] = 'Envoyer les confirmations de RDV par mail liées à ce compte';
		$this->rights[$r][2] = 's';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'send';  

 		$r++;
		$this->rights[$r][0] = 80205;
		$this->rights[$r][1] = 'Créer/Modifier les confirmations de RDV de tout le monde';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'create';
               

		$r++;
		$this->rights[$r][0] = 80206;
		$this->rights[$r][1] = 'Voir les confirmations de RDV de tout le monde';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'read';

		$r++;
		$this->rights[$r][0] = 80207;
		$this->rights[$r][1] = 'Supprimer les confirmations de RDV de tout le monde';
		$this->rights[$r][2] = 'd';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'delete';

		
		$r++;
		$this->rights[$r][0] = 80208;
		$this->rights[$r][1] = 'Envoyer les confirmations de RDV par mail de tout le monde';
		$this->rights[$r][2] = 's';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'allactions';
        $this->rights[$r][5] = 'send';    
                                                                 
		// Main menu entries
		$this->menu = array();			// List of menus to add
        
        $r = 0;       
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu=agenda',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('ConfirmedMenu'),
        	'mainmenu'=> 'agenda',
        	'leftmenu'=> 'confirm',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/confirm/confirm.php',
			'langs'=> 'confirm@confirm',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 200,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->confirm->allactions->read || $user->rights->confirm->myactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both  
        );
        
        $r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu=agenda,fk_leftmenu=confirm',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('MyActionsToConfirmedMenu'),
        	'mainmenu'=> '',
        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/confirm/confirm.php?status=tosend&filter=mine',
			'langs'=> 'confirm@confirm',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 201,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->confirm->myactions->create',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );

        $r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu=agenda,fk_leftmenu=confirm',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('MyActionsConfirmedMenu'),
        	'mainmenu'=> '',
        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/confirm/confirm.php?status=sent&filter=mine',
			'langs'=> 'confirm@confirm',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 202,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->confirm->myactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        ); 
        $r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu=agenda,fk_leftmenu=confirm',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('AllActionsToConfirmedMenu'),
        	'mainmenu'=> '',
        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/confirm/confirm.php?status=tosend',
			'langs'=> 'confirm@confirm',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 203,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->confirm->allactions->create',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );

        $r++;
        $this->menu[$r]=array(
            'fk_menu'=>'fk_mainmenu=agenda,fk_leftmenu=confirm',			// Put 0 if this is a top menu
        	'type'=> 'left',			// This is a Top menu entry
        	'titre'=> $langs->trans('AllActionsConfirmedMenu'),
        	'mainmenu'=> '',
        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
			'url'=> '/confirm/confirm.php?status=sent',
			'langs'=> 'confirm',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=> 204,
			'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=> '$user->rights->confirm->allactions->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=> '',
			'user'=> 2
        );                        
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function init()
	{              
        $sql = array();             
		
		$result = $this->load_tables();

		return $this->_init($sql);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function remove()
	{
		
		$sql = array();
		
		return $this->_remove($sql);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/confirm/sql/');
	}
}

?>
