<?php
/* Copyright (C) 2024      Dolibarr Community
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * @var CommonObject $object
 * @var Translate $langs
 * @var DoliDB $db
 * @var User $user
 * @var string $action
 * @var string $triggersendname
 * @var string $actiontypecode
 */

if (empty($conf) || !is_object($conf)) {
    print "Error, template page can't be called as URL";
    exit(1);
}

$langs->load('whatsappdoc@whatsappdoc');

if ($action === 'sendbywhatsapp' && !GETPOST('cancel') && !GETPOST('modelselected')) {
    $phone = trim(GETPOST('whatsapp_phone', 'alphanohtml'));
    $message = GETPOST('whatsapp_message', 'restricthtml');

    if (empty($phone)) {
        setEventMessages($langs->trans('ErrorWhatsappPhoneRequired'), null, 'errors');
        $action = 'presendwhatsapp';
    }

    if ($action === 'sendbywhatsapp' && empty($message)) {
        $message = $langs->trans('WhatsappDefaultMessage');
    }

    if ($action === 'sendbywhatsapp' && !empty($phone)) {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
        $whatsappUrl = 'https://wa.me/' . rawurlencode($cleanPhone);
        if (!empty($message)) {
            $whatsappUrl .= '?text=' . rawurlencode($message);
        }

        if ($object instanceof CommonObject && method_exists($object, 'call_trigger') && !empty($triggersendname)) {
            $object->call_trigger($triggersendname, $user);
        }

        if (empty($conf->global->WHATSAPPDOC_DISABLE_AGENDA_LOG) && !empty($actiontypecode) && $object->id > 0) {
            if (method_exists($object, 'add_contact')) {
                $object->add_contact(0, 'SEND', 'external');
            }
            if (method_exists($object, 'add_actionline')) {
                $object->add_actionline($langs->transnoentities('SendWhatsapp'), '', $actiontypecode, '', '', 0, 0, 0, 0, '');
            }
        }

        header('Location: ' . $whatsappUrl);
        exit;
    }
}
