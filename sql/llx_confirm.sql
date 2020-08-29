-- ============================================================================
-- Copyright (C) 2012 Mikael Carlavan  <mcarlavan@qis-network.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================

CREATE TABLE IF NOT EXISTS `llx_confirm`(
  `rowid`			    int(11) AUTO_INCREMENT,
  `ref`				    varchar(30) NOT NULL,  
  `fk_action`   	    int(11),   
  `conf_sent`		    int(11) DEFAULT 0 NOT NULL,
  `conf_built`		    int(11) DEFAULT 0 NOT NULL,    
  `userconf`		    int(11) DEFAULT 0 NOT NULL,
  `usersend`		    int(11) DEFAULT 0 NOT NULL,
  
  `phone`           varchar(30) NOT NULL,
  `address`         text,  
  
  `entity`			    int(11) DEFAULT 1 NOT NULL,  
  `datec`               datetime,
  `dates`               datetime,    
  `datef`               date, 
   	  
  `tms`			        timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `model_pdf`           varchar(255) NULL, 
  PRIMARY KEY (`rowid`)    
)ENGINE=innodb DEFAULT CHARSET=utf8;
