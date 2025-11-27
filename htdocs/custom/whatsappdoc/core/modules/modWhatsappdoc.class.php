<?php
/* Copyright (C) 2024      Dolibarr Community
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 *\file    htdocs/custom/whatsappdoc/core/modules/modWhatsappdoc.class.php
 *\ingroup whatsappdoc
 *\brief   Description and activation file for the module Whatsappdoc
 */

require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module Whatsappdoc.
 */
class modWhatsappdoc extends DolibarrModules
{
    /**
     * Constructor.
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;

        $this->numero = 501000; // Reserved id for this custom module
        $this->rights_class = 'whatsappdoc';

        $this->family = 'interface';
        $this->module_position = '99';

        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = 'WhatsappdocDescription';
        $this->descriptionlong = 'WhatsappdocDescription';

        $this->editor_name = 'Dolibarr Community';
        $this->editor_url = 'https://www.dolibarr.org';

        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
        $this->picto = 'whatsapp';

        $this->module_parts = array(
            'hooks' => array(
                'data' => array(
                    'propalcard',
                    'ordercard',
                    'invoicecard',
                    'globalcard'
                ),
            ),
            'css' => array(),
            'js' => array(),
        );

        $this->dirs = array('/whatsappdoc/temp');

        $this->config_page_url = array();

        $this->hidden = 0;
        $this->depends = array('socialnetworks');
        $this->requiredby = array();
        $this->conflictwith = array();

        $this->phpmin = array(7, 4);
        $this->need_dolibarr_version = array(21, 0);
        $this->langfiles = array('whatsappdoc@whatsappdoc');

        if (empty($conf->whatsappdoc)) {
            $conf->whatsappdoc = new stdClass();
        }

        $this->tabs = array();

        $this->dictionaries = array();

        $this->boxes = array();

        $this->rights = array();
        $this->menu = array();
    }
}
