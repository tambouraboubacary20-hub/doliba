<?php
/* Copyright (C) 2024      Dolibarr Community
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * @var string $action
 * @var string $defaulttopic
 * @var CommonObject $object
 * @var Conf $conf
 * @var Translate $langs
 */

if (empty($conf) || !is_object($conf)) {
    print "Error, template page can't be called as URL";
    exit(1);
}

if ($action !== 'presendwhatsapp') {
    return;
}

dol_include_once('/core/lib/files.lib.php');

$langs->load('whatsappdoc@whatsappdoc');

$object->fetch_thirdparty();
$ref = dol_sanitizeFileName($object->ref);
$diroutput = $diroutput ?? '';
$fileparams = dol_most_recent_file($diroutput . '/' . $ref, preg_quote($ref, '/') . '[^\-]+');
$file = isset($fileparams['fullname']) ? $fileparams['fullname'] : null;

$outputlangs = $langs;
if (getDolGlobalInt('MAIN_MULTILANGS')) {
    $newlang = GETPOST('lang_id', 'aZ09');
    if (empty($newlang) && is_object($object->thirdparty)) {
        $newlang = $object->thirdparty->default_lang;
    }
    if (!empty($newlang)) {
        $outputlangs = new Translate('', $conf);
        $outputlangs->setDefaultLang($newlang);
        $outputlangs->loadLangs(array('main', 'whatsappdoc@whatsappdoc'));
    }
}

$forcebuilddoc = true;
if (!empty($forcebuilddoc) && (!$file || !is_readable($file)) && method_exists($object, 'generateDocument')) {
    $hidedetails ??= 0;
    $hidedesc ??= 0;
    $hideref ??= 0;

    $result = $object->generateDocument($object->model_pdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
    if ($result > 0) {
        $fileparams = dol_most_recent_file($diroutput . '/' . $ref, preg_quote($ref, '/') . '[^\-]+');
        $file = isset($fileparams['fullname']) ? $fileparams['fullname'] : null;
    }
}

$relativefile = '';
if (!empty($fileparams['relativename'])) {
    $relativefile = $fileparams['relativename'];
} elseif (!empty($file)) {
    $relativefile = preg_replace('/^' . preg_quote(DOL_DATA_ROOT, '/') . '\//', '', $file);
}

$modulepart = $object->element;
if ($modulepart === 'facture') {
    $modulepart = 'invoice';
}

$downloadUrl = '';
if (!empty($relativefile)) {
    $downloadUrl = dol_buildpath('/document.php', 1) . '?modulepart=' . urlencode($modulepart) . '&file=' . urlencode($relativefile);
}

$defaultPhone = '';
if (!empty($object->thirdparty) && !empty($object->thirdparty->socialnetworks['whatsapp'])) {
    $defaultPhone = $object->thirdparty->socialnetworks['whatsapp'];
} elseif (!empty($object->thirdparty) && !empty($object->thirdparty->phone_mobile)) {
    $defaultPhone = $object->thirdparty->phone_mobile;
} elseif (!empty($object->thirdparty) && !empty($object->thirdparty->phone)) {
    $defaultPhone = $object->thirdparty->phone;
}

$defaultMessage = $langs->trans('WhatsappDefaultBody', $object->ref, $downloadUrl);

print '<div id="formmailbeforetitle" name="formmailbeforetitle"></div>';
print '<div class="clearboth"></div>';
print '<br>';
print load_fiche_titre($langs->trans('SendWhatsapp'));
print dol_get_fiche_head(array(), '', '', -1);

print '<form class="tagtable noborder" method="POST" action="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="sendbywhatsapp">';

print '<div class="fichecenter">';
print '<table class="border centpercent">';

print '<tr><td class="fieldrequired">' . $langs->trans('WhatsappPhone') . '</td>';
print '<td><input type="text" class="minwidth300" name="whatsapp_phone" value="' . dol_escape_htmltag($defaultPhone) . '"></td></tr>';

print '<tr><td>' . $langs->trans('LinkedDocument') . '</td><td>';
if ($downloadUrl) {
    print '<a href="' . dol_escape_htmltag($downloadUrl) . '" target="_blank">' . dol_escape_htmltag($relativefile) . '</a>';
} else {
    print $langs->trans('NoDocument');
}
print '</td></tr>';

print '<tr><td class="fieldrequired">' . $langs->trans('WhatsappMessage') . '</td>';
print '<td><textarea class="quatrevingtpercent" name="whatsapp_message" rows="4">' . dol_escape_htmltag($defaultMessage) . '</textarea></td></tr>';

print '</table>';
print '</div>';

print '<div class="center">';
print '<input type="submit" class="button" name="senditwhatsapp" value="' . dol_escape_htmltag($langs->trans('SendWhatsapp')) . '">';
print '&nbsp;';
print '<input type="submit" class="button button-cancel" name="cancel" value="' . dol_escape_htmltag($langs->trans('Cancel')) . '">';
print '</div>';

print '</form>';
print dol_get_fiche_end();
