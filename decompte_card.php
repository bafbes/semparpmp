<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       decompte_card.php
 *        \ingroup    semparpmp
 *        \brief      Page to create/edit/view decompte
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
dol_include_once('/semparpmp/class/decompte.class.php');
dol_include_once('/semparpmp/lib/semparpmp_decompte.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("semparpmp@semparpmp", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'decomptecard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');

$origin = GETPOST('origin', 'alpha');
$originid = (GETPOST('originid', 'int') ? GETPOST('originid', 'int') : GETPOST('origin_id', 'int')); // For backward compatibility

//$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Decompte($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->semparpmp->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('decomptecard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
    if (GETPOST('search_' . $key, 'alpha')) $search[$key] = GETPOST('search_' . $key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.


$permissiontoread = $user->rights->semparpmp->decompte->read;
$permissiontoadd = $user->rights->semparpmp->decompte->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->semparpmp->decompte->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->semparpmp->decompte->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->semparpmp->decompte->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->semparpmp->multidir_output[isset($object->entity) ? $object->entity : 1];

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, $object->element, $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

//if (empty($permissiontoread)) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
    $error = 0;

    $backurlforlist = dol_buildpath('/semparpmp/decompte_list.php', 1);

    if (empty($backtopage) || ($cancel && empty($id))) {
        if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
            if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
            else $backtopage = dol_buildpath('/semparpmp/decompte_card.php', 1) . '?id=' . ($id > 0 ? $id : '__ID__');
        }
    }

    $triggermodname = 'SEMPARPMP_DECOMPTE_MODIFY'; // Name of trigger action code to execute when we modify record
    // Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
    if ($action == 'add' && !empty($permissiontoadd))
    {
        foreach ($object->fields as $key => $val)
        {
            if ($object->fields[$key]['type'] == 'duration') {
                if (GETPOST($key.'hour') == '' && GETPOST($key.'min') == '') continue; // The field was not submited to be edited
            } else {
                if (!GETPOSTISSET($key)) continue; // The field was not submited to be edited
            }
            // Ignore special fields
            if (in_array($key, array('rowid', 'entity', 'import_key'))) continue;
            if (in_array($key, array('date_creation', 'tms', 'fk_user_creat', 'fk_user_modif'))) {
                if (!in_array(abs($val['visible']), array(1, 3))) continue; // Only 1 and 3 that are case to create
            }

            // Set value to insert
            if (in_array($object->fields[$key]['type'], array('text', 'html'))) {
                $value = GETPOST($key, 'restricthtml');
            } elseif ($object->fields[$key]['type'] == 'date') {
                $value = dol_mktime(12, 0, 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));	// for date without hour, we use gmt
            } elseif ($object->fields[$key]['type'] == 'datetime') {
                $value = dol_mktime(GETPOST($key.'hour', 'int'), GETPOST($key.'min', 'int'), GETPOST($key.'sec', 'int'), GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'), 'tzuserrel');
            } elseif ($object->fields[$key]['type'] == 'duration') {
                $value = 60 * 60 * GETPOST($key.'hour', 'int') + 60 * GETPOST($key.'min', 'int');
            } elseif (preg_match('/^(integer|price|real|double)/', $object->fields[$key]['type'])) {
                $value = price2num(GETPOST($key, 'alphanohtml')); // To fix decimal separator according to lang setup
            } elseif ($object->fields[$key]['type'] == 'boolean') {
                $value = ((GETPOST($key) == '1' || GETPOST($key) == 'on') ? 1 : 0);
            } elseif ($object->fields[$key]['type'] == 'reference') {
                $tmparraykey = array_keys($object->param_list);
                $value = $tmparraykey[GETPOST($key)].','.GETPOST($key.'2');
            } else {
                $value = GETPOST($key, 'alphanohtml');
            }
            if (preg_match('/^integer:/i', $object->fields[$key]['type']) && $value == '-1') $value = ''; // This is an implicit foreign key field
            if (!empty($object->fields[$key]['foreignkey']) && $value == '-1') $value = ''; // This is an explicit foreign key field

            //var_dump($key.' '.$value.' '.$object->fields[$key]['type']);
            $object->$key = $value;
            if ($val['notnull'] > 0 && $object->$key == '' && !is_null($val['default']) && $val['default'] == '(PROV)')
            {
                $object->$key = '(PROV)';
            }
            if ($val['notnull'] > 0 && $object->$key == '' && is_null($val['default']))
            {
                $error++;
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv($val['label'])), null, 'errors');
            }
        }

        // Fill array 'array_options' with data from add form
        if (!$error) {
            $ret = $extrafields->setOptionalsFromPost(null, $object);
            if ($ret < 0) $error++;
        }

        if (!$error)
        {
            if (1)
            {
                // Creation OK
                if ($action == 'add') {
                    if ($socid > 0) $object->socid = GETPOST('socid', 'int');
                    $selectedLines = GETPOST('toselect', 'array');
                    // If creation from another object of another module (Example: origin=propal, originid=1)
                    if (!empty($origin) && !empty($originid)) {
                        // Parse element/subelement (ex: project_task)
                        $element = $subelement = $origin;

                        $element = 'comm/propal';
                        $subelement = 'propal';

                        $object->origin = $origin;
                        $object->origin_id = $originid;

                        // Possibility to add external linked objects with hooks
                        $object->linked_objects[$object->origin] = $object->origin_id;


                        if (is_array($_POST['other_linked_objects']) && !empty($_POST['other_linked_objects'])) {
                            $object->linked_objects = array_merge($object->linked_objects, $_POST['other_linked_objects']);
                        }

                        $id = $object->create($user); // This include class to add_object_linked() and add add_contact()

                        if ($id > 0) {
                            dol_include_once('/' . $element . '/class/' . $subelement . '.class.php');

                            $classname = ucfirst($subelement);
                            $srcobject = new $classname($db);

                            dol_syslog("Try to find source object origin=" . $object->origin . " originid=" . $object->origin_id . " to add lines or deposit lines");
                            $result = $srcobject->fetch($object->origin_id);

                            $typeamount = GETPOST('typedeposit', 'aZ09');
                            $valuedeposit = price2num(GETPOST('valuedeposit', 'alpha'), 'MU');


                            if (1) {
                                if ($result > 0) {
                                    $lines = $srcobject->lines;
                                    if (empty($lines) && method_exists($srcobject, 'fetch_lines')) {
                                        $srcobject->fetch_lines();
                                        $lines = $srcobject->lines;
                                    }


                                    $fk_parent_line = 0;
                                    $num = count($lines);

                                    for ($i = 0; $i < $num; $i++) {
                                        if (!in_array($lines[$i]->id, $selectedLines)) continue; // Skip unselected lines

                                        $label = (!empty($lines[$i]->label) ? $lines[$i]->label : '');
                                        $desc = (!empty($lines[$i]->desc) ? $lines[$i]->desc : $lines[$i]->libelle);

                                        if ($lines[$i]->subprice < 0 && empty($conf->global->INVOICE_KEEP_DISCOUNT_LINES_AS_IN_ORIGIN)) {
                                            // Negative line, we create a discount line
                                            $discount = new DiscountAbsolute($db);
                                            $discount->fk_soc = $object->socid;
                                            $discount->amount_ht = abs($lines[$i]->total_ht);
                                            $discount->amount_tva = abs($lines[$i]->total_tva);
                                            $discount->amount_ttc = abs($lines[$i]->total_ttc);
                                            $discount->tva_tx = $lines[$i]->tva_tx;
                                            $discount->fk_user = $user->id;
                                            $discount->description = $desc;
                                            $discountid = $discount->create($user);
                                            if ($discountid > 0) {
                                                $result = $object->insert_discount($discountid); // This include link_to_invoice
                                            } else {
                                                setEventMessages($discount->error, $discount->errors, 'errors');
                                                $error++;
                                                break;
                                            }
                                        } else {
                                            // Positive line

                                            // Date start
                                            $date_creation = false;
                                            if ($lines[$i]->date_debut_prevue)
                                                $date_creation = $lines[$i]->date_debut_prevue;
                                            if ($lines[$i]->date_debut_reel)
                                                $date_creation = $lines[$i]->date_debut_reel;
                                            if ($lines[$i]->date_creation)
                                                $date_creation = $lines[$i]->date_creation;

                                            // Date end
                                            $tms = false;
                                            if ($lines[$i]->date_fin_prevue)
                                                $tms = $lines[$i]->date_fin_prevue;
                                            if ($lines[$i]->date_fin_reel)
                                                $tms = $lines[$i]->date_fin_reel;
                                            if ($lines[$i]->tms)
                                                $tms = $lines[$i]->tms;

                                            // Reset fk_parent_line for no child products and special product
                                            if (($lines[$i]->product_type != 9 && empty($lines[$i]->fk_parent_line)) || $lines[$i]->product_type == 9) {
                                                $fk_parent_line = 0;
                                            }

                                            // Extrafields
                                            if (method_exists($lines[$i], 'fetch_optionals')) {
                                                $lines[$i]->fetch_optionals();
                                                $array_options = $lines[$i]->array_options;
                                            }

                                            $tva_tx = $lines[$i]->tva_tx;

                                            $result = $object->addline(
                                                $desc, $lines[$i]->subprice, $lines[$i]->qty, $tva_tx,
                                                 $date_creation, $tms, 0,
                                                'HT', 0, $lines[$i]->rang, $object->origin, $lines[$i]->rowid,
                                                $fk_parent_line, $label, $array_options,
                                                $lines[$i]->fk_unit
                                            );

                                            if ($result > 0) {
                                                $lineid = $result;
                                            } else {
                                                $lineid = 0;
                                                $error++;
                                                break;
                                            }

                                            // Defined the new fk_parent_line
                                            if ($result > 0 && $lines[$i]->product_type == 9) {
                                                $fk_parent_line = $result;
                                            }
                                        }
                                    }
                                } else {
                                    setEventMessages($srcobject->error, $srcobject->errors, 'errors');
                                    $error++;
                                }
                            }

                            // Now we create same links to contact than the ones found on origin object
                            /* Useless, already into the create
                            if (! empty($conf->global->MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN))
                            {
                                $originforcontact = $object->origin;
                                $originidforcontact = $object->origin_id;
                                if ($originforcontact == 'shipping')     // shipment and order share the same contacts. If creating from shipment we take data of order
                                {
                                    $originforcontact=$srcobject->origin;
                                    $originidforcontact=$srcobject->origin_id;
                                }
                                $sqlcontact = "SELECT code, fk_socpeople FROM ".MAIN_DB_PREFIX."element_contact as ec, ".MAIN_DB_PREFIX."c_type_contact as ctc";
                                $sqlcontact.= " WHERE element_id = ".$originidforcontact." AND ec.fk_c_type_contact = ctc.rowid AND ctc.element = '".$db->escape($originforcontact)."'";

                                $resqlcontact = $db->query($sqlcontact);
                                if ($resqlcontact)
                                {
                                    while($objcontact = $db->fetch_object($resqlcontact))
                                    {
                                        //print $objcontact->code.'-'.$objcontact->fk_socpeople."\n";
                                        $object->add_contact($objcontact->fk_socpeople, $objcontact->code);
                                    }
                                }
                                else dol_print_error($resqlcontact);
                            }*/

                            // Hooks
                            $parameters = array('objFrom' => $srcobject);
                            $reshook = $hookmanager->executeHooks('createFrom', $parameters, $object, $action); // Note that $action and $object may have been
                            // modified by hook
                            if ($reshook < 0) {
                                setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
                                $error++;
                            }
                        } else {
                            setEventMessages($object->error, $object->errors, 'errors');
                            $error++;
                        }
                    } else {   // If some invoice's lines coming from page
                        $id = $object->create($user);

                        for ($i = 1; $i <= $NBLINES; $i++) {
                            if ($_POST['idprod' . $i]) {
                                $product = new Product($db);
                                $product->fetch($_POST['idprod' . $i]);
                                $startday = dol_mktime(12, 0, 0, $_POST['date_start' . $i . 'month'], $_POST['date_start' . $i . 'day'], $_POST['date_start' . $i . 'year']);
                                $endday = dol_mktime(12, 0, 0, $_POST['date_end' . $i . 'month'], $_POST['date_end' . $i . 'day'], $_POST['date_end' . $i . 'year']);
                                $result = $object->addline($product->description, $product->price, $_POST['qty' . $i], $product->tva_tx, $_POST['idprod' . $i], $startday, $endday, 0, 0, '', $product->price_ttc, $product->type, -1, 0, '', 0, 0, null, 0, '', 0, 100, '', $product->fk_unit);
                            }
                        }
                    }
                }
                if ($id > 0 && !$error)
                {
                    $db->commit();

                    // Define output language
                    if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE) && count($object->lines))
                    {
                        $outputlangs = $langs;
                        $newlang = '';
                        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
                        if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
                        if (!empty($newlang)) {
                            $outputlangs = new Translate("", $conf);
                            $outputlangs->setDefaultLang($newlang);
                            $outputlangs->load('products');
                        }
                        $model = $object->model_pdf;
                        $ret = $object->fetch($id); // Reload to get new records

                        $result = $object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
                        if ($result < 0) setEventMessages($object->error, $object->errors, 'errors');
                    }

                    header('Location: '.$_SERVER["PHP_SELF"].'?id='.$id);
                    exit();
                } else {
                    $db->rollback();
                    $action = 'create';
                    $_GET["origin"] = $_POST["origin"];
                    $_GET["originid"] = $_POST["originid"];
                    setEventMessages($object->error, $object->errors, 'errors');
                }
                $urltogo = $backtopage ? str_replace('__ID__', $result, $backtopage) : $backurlforlist;
                $urltogo = preg_replace('/--IDFORBACKTOPAGE--/', $object->id, $urltogo); // New method to autoselect project after a New on another form object creation
                header("Location: ".$urltogo);
                exit;
            } else {
                // Creation KO
                if (!empty($object->errors)) setEventMessages(null, $object->errors, 'errors');
                else setEventMessages($object->error, null, 'errors');
                $action = 'create';
            }
        } else {
            $action = 'create';
        }
    }
    elseif ($action == 'addline' && GETPOST('submitforalllines', 'alpha') && GETPOST('vatforalllines', 'alpha') !== '') {
        // Define vat_rate
        $vat_rate = (GETPOST('vatforalllines') ? GETPOST('vatforalllines') : 0);
        $vat_rate = str_replace('*', '', $vat_rate);
        $localtax1_rate = get_localtax($vat_rate, 1, $object->thirdparty, $mysoc);
        $localtax2_rate = get_localtax($vat_rate, 2, $object->thirdparty, $mysoc);
        foreach ($object->lines as $line) {
            $result = $object->updateline($line->id, $line->desc, $line->subprice, $line->qty, $line->remise_percent, $line->date_start, $line->date_end, $vat_rate, $localtax1_rate, $localtax2_rate, 'HT', $line->info_bits, $line->product_type, $line->fk_parent_line, 0, $line->fk_fournprice, $line->pa_ht, $line->label, $line->special_code, $line->array_options, $line->situation_percent, $line->fk_unit, $line->multicurrency_subprice);
        }
    }
    elseif ($action == 'addline' && $usercancreate)		// Add a new line
    {
        $langs->load('errors');
        $error = 0;

        // Set if we used free entry or predefined product
        $predef = '';
        $product_desc = (GETPOST('dp_desc', 'none') ?GETPOST('dp_desc', 'restricthtml') : '');
        $price_ht = price2num(GETPOST('price_ht'), 'MU', 2);
        $price_ht_devise = price2num(GETPOST('multicurrency_price_ht'), 'CU', 2);
        $prod_entry_mode = GETPOST('prod_entry_mode', 'alpha');
        if ($prod_entry_mode == 'free')
        {
            $idprod = 0;
            $tva_tx = (GETPOST('tva_tx', 'alpha') ? GETPOST('tva_tx', 'alpha') : 0);
        } else {
            $idprod = GETPOST('idprod', 'int');
            $tva_tx = '';
        }

        $qty = GETPOST('qty'.$predef);
        $remise_percent = GETPOST('remise_percent'.$predef);

        // Extrafields
        $extralabelsline = $extrafields->fetch_name_optionals_label($object->table_element_line);
        $array_options = $extrafields->getOptionalsFromPost($object->table_element_line, $predef);
        // Unset extrafield
        if (is_array($extralabelsline)) {
            // Get extra fields
            foreach ($extralabelsline as $key => $value) {
                unset($_POST["options_".$key.$predef]);
            }
        }

        if (empty($idprod) && ($price_ht < 0) && ($qty < 0)) {
            setEventMessages($langs->trans('ErrorBothFieldCantBeNegative', $langs->transnoentitiesnoconv('UnitPriceHT'), $langs->transnoentitiesnoconv('Qty')), null, 'errors');
            $error++;
        }
        if (!$prod_entry_mode)
        {
            if (GETPOST('type') < 0 && !GETPOST('search_idprod'))
            {
                setEventMessages($langs->trans('ErrorChooseBetweenFreeEntryOrPredefinedProduct'), null, 'errors');
                $error++;
            }
        }
        if ($prod_entry_mode == 'free' && empty($idprod) && GETPOST('type') < 0) {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Type')), null, 'errors');
            $error++;
        }
        if (($prod_entry_mode == 'free' && empty($idprod) && (($price_ht < 0 && empty($conf->global->FACTURE_ENABLE_NEGATIVE_LINES)) || $price_ht == '') && $price_ht_devise == '') && $object->type != Facture::TYPE_CREDIT_NOTE) 	// Unit price can be 0 but not ''
        {
            if ($price_ht < 0 && empty($conf->global->FACTURE_ENABLE_NEGATIVE_LINES))
            {
                $langs->load("errors");
                if ($object->type == $object::TYPE_DEPOSIT) {
                    // Using negative lines on deposit lead to headach and blocking problems when you want to consume them.
                    setEventMessages($langs->trans("ErrorLinesCantBeNegativeOnDeposits"), null, 'errors');
                } else {
                    setEventMessages($langs->trans("ErrorFieldCantBeNegativeOnInvoice", $langs->transnoentitiesnoconv("UnitPriceHT"), $langs->transnoentitiesnoconv("CustomerAbsoluteDiscountShort")), null, 'errors');
                }
                $error++;
            } else {
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("UnitPriceHT")), null, 'errors');
                $error++;
            }
        }
        if ($qty == '') {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Qty')), null, 'errors');
            $error++;
        }
        if ($prod_entry_mode == 'free' && empty($idprod) && empty($product_desc)) {
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('Description')), null, 'errors');
            $error++;
        }
        if ($qty < 0) {
            $langs->load("errors");
            setEventMessages($langs->trans('ErrorQtyForCustomerInvoiceCantBeNegative'), null, 'errors');
            $error++;
        }

        if (!$error && !empty($conf->variants->enabled) && $prod_entry_mode != 'free') {
            if ($combinations = GETPOST('combinations', 'array')) {
                //Check if there is a product with the given combination
                $prodcomb = new ProductCombination($db);

                if ($res = $prodcomb->fetchByProductCombination2ValuePairs($idprod, $combinations)) {
                    $idprod = $res->fk_product_child;
                } else {
                    setEventMessages($langs->trans('ErrorProductCombinationNotFound'), null, 'errors');
                    $error++;
                }
            }
        }

        if (!$error && ($qty >= 0) && (!empty($product_desc) || !empty($idprod))) {
            $ret = $object->fetch($id);
            if ($ret < 0) {
                dol_print_error($db, $object->error);
                exit();
            }
            $ret = $object->fetch_thirdparty();

            // Clean parameters
            $date_start = dol_mktime(GETPOST('date_start'.$predef.'hour'), GETPOST('date_start'.$predef.'min'), GETPOST('date_start'.$predef.'sec'), GETPOST('date_start'.$predef.'month'), GETPOST('date_start'.$predef.'day'), GETPOST('date_start'.$predef.'year'));
            $date_end = dol_mktime(GETPOST('date_end'.$predef.'hour'), GETPOST('date_end'.$predef.'min'), GETPOST('date_end'.$predef.'sec'), GETPOST('date_end'.$predef.'month'), GETPOST('date_end'.$predef.'day'), GETPOST('date_end'.$predef.'year'));
            $price_base_type = (GETPOST('price_base_type', 'alpha') ? GETPOST('price_base_type', 'alpha') : 'HT');

            // Define special_code for special lines
            $special_code = 0;
            // if (empty($_POST['qty'])) $special_code=3; // Options should not exists on invoices

            // Ecrase $pu par celui du produit
            // Ecrase $desc par celui du produit
            // Ecrase $tva_tx par celui du produit
            // Ecrase $base_price_type par celui du produit
            // Replaces $fk_unit with the product's
            if (!empty($idprod))
            {
                $prod = new Product($db);
                $prod->fetch($idprod);

                $label = ((GETPOST('product_label') && GETPOST('product_label') != $prod->label) ? GETPOST('product_label') : '');

                // Search the correct price into loaded array product_price_by_qty using id of array retrieved into POST['pqp'].
                $pqp = (GETPOST('pbq', 'int') ? GETPOST('pbq', 'int') : 0);

                $datapriceofproduct = $prod->getSellPrice($mysoc, $object->thirdparty, $pqp);

                $pu_ht = $datapriceofproduct['pu_ht'];
                $pu_ttc = $datapriceofproduct['pu_ttc'];
                $price_min = $datapriceofproduct['price_min'];
                $price_base_type = $datapriceofproduct['price_base_type'];
                $tva_tx = $datapriceofproduct['tva_tx'];
                $tva_npr = $datapriceofproduct['tva_npr'];

                $tmpvat = price2num(preg_replace('/\s*\(.*\)/', '', $tva_tx));
                $tmpprodvat = price2num(preg_replace('/\s*\(.*\)/', '', $prod->tva_tx));

                // if price ht was forced (ie: from gui when calculated by margin rate and cost price). TODO Why this ?
                if (!empty($price_ht) || $price_ht === '0')
                {
                    $pu_ht = price2num($price_ht, 'MU');
                    $pu_ttc = price2num($pu_ht * (1 + ($tmpvat / 100)), 'MU');
                } // On reevalue prix selon taux tva car taux tva transaction peut etre different
                // de ceux du produit par defaut (par exemple si pays different entre vendeur et acheteur).
                elseif ($tmpvat != $tmpprodvat)
                {
                    if ($price_base_type != 'HT')
                    {
                        $pu_ht = price2num($pu_ttc / (1 + ($tmpvat / 100)), 'MU');
                    } else {
                        $pu_ttc = price2num($pu_ht * (1 + ($tmpvat / 100)), 'MU');
                    }
                }

                $desc = '';

                // Define output language
                if (!empty($conf->global->MAIN_MULTILANGS) && !empty($conf->global->PRODUIT_TEXTS_IN_THIRDPARTY_LANGUAGE)) {
                    $outputlangs = $langs;
                    $newlang = '';
                    if (empty($newlang) && GETPOST('lang_id', 'aZ09'))
                        $newlang = GETPOST('lang_id', 'aZ09');
                    if (empty($newlang))
                        $newlang = $object->thirdparty->default_lang;
                    if (!empty($newlang)) {
                        $outputlangs = new Translate("", $conf);
                        $outputlangs->setDefaultLang($newlang);
                        $outputlangs->load('products');
                    }

                    $desc = (!empty($prod->multilangs [$outputlangs->defaultlang] ["description"])) ? $prod->multilangs [$outputlangs->defaultlang] ["description"] : $prod->description;
                } else {
                    $desc = $prod->description;
                }

                if (!empty($product_desc) && !empty($conf->global->MAIN_NO_CONCAT_DESCRIPTION)) $desc = $product_desc;
                else $desc = dol_concatdesc($desc, $product_desc, '', !empty($conf->global->MAIN_CHANGE_ORDER_CONCAT_DESCRIPTION));

                // Add custom code and origin country into description
                if (empty($conf->global->MAIN_PRODUCT_DISABLE_CUSTOMCOUNTRYCODE) && (!empty($prod->customcode) || !empty($prod->country_code))) {
                    $tmptxt = '(';
                    // Define output language
                    if (!empty($conf->global->MAIN_MULTILANGS) && !empty($conf->global->PRODUIT_TEXTS_IN_THIRDPARTY_LANGUAGE)) {
                        $outputlangs = $langs;
                        $newlang = '';
                        if (empty($newlang) && GETPOST('lang_id', 'alpha'))
                            $newlang = GETPOST('lang_id', 'alpha');
                        if (empty($newlang))
                            $newlang = $object->thirdparty->default_lang;
                        if (!empty($newlang)) {
                            $outputlangs = new Translate("", $conf);
                            $outputlangs->setDefaultLang($newlang);
                            $outputlangs->load('products');
                        }
                        if (!empty($prod->customcode))
                            $tmptxt .= $outputlangs->transnoentitiesnoconv("CustomCode").': '.$prod->customcode;
                        if (!empty($prod->customcode) && !empty($prod->country_code))
                            $tmptxt .= ' - ';
                        if (!empty($prod->country_code))
                            $tmptxt .= $outputlangs->transnoentitiesnoconv("CountryOrigin").': '.getCountry($prod->country_code, 0, $db, $outputlangs, 0);
                    } else {
                        if (!empty($prod->customcode))
                            $tmptxt .= $langs->transnoentitiesnoconv("CustomCode").': '.$prod->customcode;
                        if (!empty($prod->customcode) && !empty($prod->country_code))
                            $tmptxt .= ' - ';
                        if (!empty($prod->country_code))
                            $tmptxt .= $langs->transnoentitiesnoconv("CountryOrigin").': '.getCountry($prod->country_code, 0, $db, $langs, 0);
                    }
                    $tmptxt .= ')';
                    $desc = dol_concatdesc($desc, $tmptxt);
                }

                $type = $prod->type;
                $fk_unit = $prod->fk_unit;
            } else {
                $pu_ht = price2num($price_ht, 'MU');
                $pu_ttc = price2num(GETPOST('price_ttc'), 'MU');
                $tva_npr = (preg_match('/\*/', $tva_tx) ? 1 : 0);
                $tva_tx = str_replace('*', '', $tva_tx);
                if (empty($tva_tx)) $tva_npr = 0;
                $label = (GETPOST('product_label') ? GETPOST('product_label') : '');
                $desc = $product_desc;
                $type = GETPOST('type');
                $fk_unit = GETPOST('units', 'alpha');
                $pu_ht_devise = price2num($price_ht_devise, 'MU');
            }


            $price2num_pu_ht = price2num($pu_ht);
            $price2num_remise_percent = price2num($remise_percent);
            $price2num_price_min = price2num($price_min);
            if (empty($price2num_pu_ht)) $price2num_pu_ht = 0;
            if (empty($price2num_remise_percent)) $price2num_remise_percent = 0;
            if (empty($price2num_price_min)) $price2num_price_min = 0;

            if ($usercanproductignorepricemin && (!empty($price_min) && ($price2num_pu_ht * (1 - $price2num_remise_percent / 100) < $price2num_price_min))) {
                $mesg = $langs->trans("CantBeLessThanMinPrice", price(price2num($price_min, 'MU'), 0, $langs, 0, 0, - 1, $conf->currency));
                setEventMessages($mesg, null, 'errors');
            } else {
                // Insert line
                $result = $object->addline($desc, $pu_ht, $qty, $tva_tx, $localtax1_tx, $localtax2_tx, $idprod, $remise_percent, $date_start, $date_end, 0, $info_bits, '', $price_base_type, $pu_ttc, $type, - 1, $special_code, '', 0, GETPOST('fk_parent_line'), $fournprice, $buyingprice, $label, $array_options, $_POST['progress'], '', $fk_unit, $pu_ht_devise);

                if ($result > 0)
                {
                    // Define output language and generate document
                    if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
                    {
                        $outputlangs = $langs;
                        $newlang = '';
                        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
                        if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
                        if (!empty($newlang)) {
                            $outputlangs = new Translate("", $conf);
                            $outputlangs->setDefaultLang($newlang);
                            $outputlangs->load('products');
                        }
                        $model = $object->model_pdf;
                        $ret = $object->fetch($id); // Reload to get new records

                        $result = $object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
                        if ($result < 0) setEventMessages($object->error, $object->errors, 'errors');
                    }

                    unset($_POST['prod_entry_mode']);

                    unset($_POST['qty']);
                    unset($_POST['type']);
                    unset($_POST['remise_percent']);
                    unset($_POST['price_ht']);
                    unset($_POST['multicurrency_price_ht']);
                    unset($_POST['price_ttc']);
                    unset($_POST['tva_tx']);
                    unset($_POST['product_ref']);
                    unset($_POST['product_label']);
                    unset($_POST['product_desc']);
                    unset($_POST['fournprice']);
                    unset($_POST['buying_price']);
                    unset($_POST['np_marginRate']);
                    unset($_POST['np_markRate']);
                    unset($_POST['dp_desc']);
                    unset($_POST['idprod']);
                    unset($_POST['units']);

                    unset($_POST['date_starthour']);
                    unset($_POST['date_startmin']);
                    unset($_POST['date_startsec']);
                    unset($_POST['date_startday']);
                    unset($_POST['date_startmonth']);
                    unset($_POST['date_startyear']);
                    unset($_POST['date_endhour']);
                    unset($_POST['date_endmin']);
                    unset($_POST['date_endsec']);
                    unset($_POST['date_endday']);
                    unset($_POST['date_endmonth']);
                    unset($_POST['date_endyear']);

                    unset($_POST['situations']);
                    unset($_POST['progress']);
                } else {
                    setEventMessages($object->error, $object->errors, 'errors');
                }

                $action = '';
            }
        }
    } elseif ($action == 'updateline' && $usercancreate && !GETPOST('cancel', 'alpha'))
    {
        if (!$object->fetch($id) > 0)	dol_print_error($db);
        $object->fetch_thirdparty();

        // Clean parameters
        $date_creation = '';
        $tms = '';
        $date_creation = dol_mktime(GETPOST('date_starthour'), GETPOST('date_startmin'), GETPOST('date_startsec'), GETPOST('date_startmonth'), GETPOST('date_startday'), GETPOST('date_startyear'));
        $date_tms = dol_mktime(GETPOST('date_endhour'), GETPOST('date_endmin'), GETPOST('date_endsec'), GETPOST('date_endmonth'), GETPOST('date_endday'), GETPOST('date_endyear'));
        $description = dol_htmlcleanlastbr(GETPOST('product_desc', 'restricthtml') ? GETPOST('product_desc', 'restricthtml') : GETPOST('desc', 'restricthtml'));
        $pu_ht = price2num(GETPOST('price_ht'), '', 2);
        $qty = GETPOST('qty');


        // Extrafields
        $extralabelsline = $extrafields->fetch_name_optionals_label($object->table_element_line);
        $array_options = $extrafields->getOptionalsFromPost($object->table_element_line);
        // Unset extrafield
        if (is_array($extralabelsline)) {
            // Get extra fields
            foreach ($extralabelsline as $key => $value) {
                unset($_POST["options_".$key]);
            }
        }

        // Define special_code for special lines
        $special_code = GETPOST('special_code');
        if (!GETPOST('qty')) $special_code = 3;

        $line = new DecompteLine($db);
        $line->fetch(GETPOST('lineid', 'int'));

        if ($object->type == Facture::TYPE_CREDIT_NOTE && $object->situation_cycle_ref > 0)
        {
            // in case of situation credit note
            if (GETPOST('progress') >= 0)
            {
                $mesg = $langs->trans("CantBeNullOrPositive");
                setEventMessages($mesg, null, 'warnings');
                $error++;
                $result = -1;
            } elseif (GETPOST('progress') < $line->situation_percent) // TODO : use a modified $line->get_prev_progress($object->id) result
            {
                $mesg = $langs->trans("CantBeLessThanMinPercent");
                setEventMessages($mesg, null, 'warnings');
                $error++;
                $result = -1;
            }
        } elseif (GETPOST('progress') < $percent)
        {
            $mesg = '<div class="warning">'.$langs->trans("CantBeLessThanMinPercent").'</div>';
            setEventMessages($mesg, null, 'warnings');
            $error++;
            $result = -1;
        }

        // Check minimum price
        $productid = GETPOST('productid', 'int');
        if (!empty($productid))
        {
            $product = new Product($db);
            $product->fetch($productid);

            $type = $product->type;

            $price_min = $product->price_min;
            if ((!empty($conf->global->PRODUIT_MULTIPRICES) || !empty($conf->global->PRODUIT_CUSTOMER_PRICES_BY_QTY_MULTIPRICES)) && !empty($object->thirdparty->price_level))
                $price_min = $product->multiprices_min [$object->thirdparty->price_level];

            $label = ((GETPOST('update_label') && GETPOST('product_label')) ? GETPOST('product_label') : '');

            // Check price is not lower than minimum (check is done only for standard or replacement invoices)
            if ($usercanproductignorepricemin && (($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_REPLACEMENT) && $price_min && (price2num($pu_ht) * (1 - price2num(GETPOST('remise_percent')) / 100) < price2num($price_min)))) {
                setEventMessages($langs->trans("CantBeLessThanMinPrice", price(price2num($price_min, 'MU'), 0, $langs, 0, 0, - 1, $conf->currency)), null, 'errors');
                $error++;
            }
        } else {
            $type = GETPOST('type');
            $label = (GETPOST('product_label') ? GETPOST('product_label') : '');

            // Check parameters
            if (GETPOST('type') < 0) {
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Type")), null, 'errors');
                $error++;
            }
        }
        if ($qty < 0) {
            $langs->load("errors");
            setEventMessages($langs->trans('ErrorQtyForCustomerInvoiceCantBeNegative'), null, 'errors');
            $error++;
        }
        if ((empty($productid) && (($pu_ht < 0 && empty($conf->global->FACTURE_ENABLE_NEGATIVE_LINES)) || $pu_ht == '') && $pu_ht_devise == '') && $object->type != Facture::TYPE_CREDIT_NOTE) 	// Unit price can be 0 but not ''
        {
            if ($pu_ht < 0 && empty($conf->global->FACTURE_ENABLE_NEGATIVE_LINES))
            {
                $langs->load("errors");
                if ($object->type == $object::TYPE_DEPOSIT) {
                    // Using negative lines on deposit lead to headach and blocking problems when you want to consume them.
                    setEventMessages($langs->trans("ErrorLinesCantBeNegativeOnDeposits"), null, 'errors');
                } else {
                    setEventMessages($langs->trans("ErrorFieldCantBeNegativeOnInvoice", $langs->transnoentitiesnoconv("UnitPriceHT"), $langs->transnoentitiesnoconv("CustomerAbsoluteDiscountShort")), null, 'errors');
                }
                $error++;
            } else {
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("UnitPriceHT")), null, 'errors');
                $error++;
            }
        }


        // Update line
        if (!$error) {
            if (empty($usercancreatemargin))
            {
                foreach ($object->lines as &$line)
                {
                    if ($line->id == GETPOST('lineid'))
                    {
                        $fournprice = $line->fk_fournprice;
                        $buyingprice = $line->pa_ht;
                        break;
                    }
                }
            }

            $result = $object->updateline(GETPOST('lineid', 'int'), $description, $pu_ht, $qty, price2num(GETPOST('remise_percent', 'alpha')),
                $date_start, $date_end, $vat_rate, $localtax1_rate, $localtax2_rate, 'HT', $info_bits, $type,
                GETPOST('fk_parent_line', 'int'), 0, $fournprice, $buyingprice, $label, $special_code, $array_options, price2num(GETPOST('progress', 'alpha')),
                GETPOST('units', 'alpha'), $pu_ht_devise);

            if ($result >= 0) {
                if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
                    // Define output language
                    $outputlangs = $langs;
                    $newlang = '';
                    if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09'))
                        $newlang = GETPOST('lang_id', 'aZ09');
                    if ($conf->global->MAIN_MULTILANGS && empty($newlang))
                        $newlang = $object->thirdparty->default_lang;
                    if (!empty($newlang)) {
                        $outputlangs = new Translate("", $conf);
                        $outputlangs->setDefaultLang($newlang);
                        $outputlangs->load('products');
                    }

                    $ret = $object->fetch($id); // Reload to get new records
                    $object->generateDocument($object->model_pdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
                }

                unset($_POST['qty']);
                unset($_POST['type']);
                unset($_POST['productid']);
                unset($_POST['remise_percent']);
                unset($_POST['price_ht']);
                unset($_POST['multicurrency_price_ht']);
                unset($_POST['price_ttc']);
                unset($_POST['tva_tx']);
                unset($_POST['product_ref']);
                unset($_POST['product_label']);
                unset($_POST['product_desc']);
                unset($_POST['fournprice']);
                unset($_POST['buying_price']);
                unset($_POST['np_marginRate']);
                unset($_POST['np_markRate']);

                unset($_POST['dp_desc']);
                unset($_POST['idprod']);
                unset($_POST['units']);

                unset($_POST['date_starthour']);
                unset($_POST['date_startmin']);
                unset($_POST['date_startsec']);
                unset($_POST['date_startday']);
                unset($_POST['date_startmonth']);
                unset($_POST['date_startyear']);
                unset($_POST['date_endhour']);
                unset($_POST['date_endmin']);
                unset($_POST['date_endsec']);
                unset($_POST['date_endday']);
                unset($_POST['date_endmonth']);
                unset($_POST['date_endyear']);

                unset($_POST['situations']);
                unset($_POST['progress']);
            } else {
                setEventMessages($object->error, $object->errors, 'errors');
            }
        }
    } elseif ($action == 'updatealllines' && $usercancreate && $_POST['all_percent'] == $langs->trans('Modifier'))	// Update all lines of situation invoice
    {
        if (!$object->fetch($id) > 0) dol_print_error($db);
        if (GETPOST('all_progress') != "")
        {
            $all_progress = GETPOST('all_progress', 'int');
            foreach ($object->lines as $line)
            {
                $percent = $line->get_prev_progress($object->id);
                if (floatval($all_progress) < floatval($percent)) {
                    $mesg = $langs->trans("Line").' '.$i.' : '.$langs->trans("CantBeLessThanMinPercent");
                    setEventMessages($mesg, null, 'warnings');
                    $result = -1;
                } else $object->update_percent($line, $_POST['all_progress']);
            }
        }
    } elseif ($action == 'updateline' && $usercancreate && $_POST['cancel'] == $langs->trans("Cancel")) {
        header('Location: '.$_SERVER["PHP_SELF"].'?facid='.$id); // To show again edited page
        exit();
    } // Outing situation invoice from cycle
    elseif ($action == 'confirm_situationout' && $confirm == 'yes' && $usercancreate)
    {
        $object->fetch($id, '', '', '', true);

        if (in_array($object->statut, array(Facture::STATUS_CLOSED, Facture::STATUS_VALIDATED))
            && $object->type == Facture::TYPE_SITUATION
            && $usercancreate
            && !$objectidnext
            && $object->is_last_in_cycle()
            && $usercanunvalidate
        )
        {
            $outingError = 0;
            $newCycle = $object->newCycle(); // we need to keep the "situation behavior" so we place it on a new situation cycle
            if ($newCycle > 1)
            {
                // Search credit notes
                $lastCycle = $object->situation_cycle_ref;
                $lastSituationCounter = $object->situation_counter;
                $linkedCreditNotesList = array();

                if (count($object->tab_next_situation_invoice) > 0) {
                    foreach ($object->tab_next_situation_invoice as $next_invoice) {
                        if ($next_invoice->type == Facture::TYPE_CREDIT_NOTE
                            && $next_invoice->situation_counter == $object->situation_counter
                            && $next_invoice->fk_facture_source == $object->id
                        )
                        {
                            $linkedCreditNotesList[] = $next_invoice->id;
                        }
                    }
                }

                $object->situation_cycle_ref = $newCycle;
                $object->situation_counter = 1;
                $object->situation_final = 0;
                if ($object->update($user) > 0)
                {
                    $errors = 0;
                    if (count($linkedCreditNotesList) > 0)
                    {
                        // now, credit note must follow
                        $sql = 'UPDATE '.MAIN_DB_PREFIX.'facture ';
                        $sql .= ' SET situation_cycle_ref='.$newCycle;
                        $sql .= ' , situation_final=0';
                        $sql .= ' , situation_counter='.$object->situation_counter;
                        $sql .= ' WHERE rowid IN ('.implode(',', $linkedCreditNotesList).')';

                        $resql = $db->query($sql);
                        if (!$resql) $errors++;

                        // Change each progression persent on each lines
                        foreach ($object->lines as $line)
                        {
                            // no traitement for special product
                            if ($line->product_type == 9)  continue;


                            if (!empty($object->tab_previous_situation_invoice))
                            {
                                // search the last invoice in cycle
                                $lineIndex = count($object->tab_previous_situation_invoice) - 1;
                                $searchPreviousInvoice = true;
                                while ($searchPreviousInvoice)
                                {
                                    if ($object->tab_previous_situation_invoice[$lineIndex]->type == Facture::TYPE_SITUATION || $lineIndex < 1)
                                    {
                                        $searchPreviousInvoice = false; // find, exit;
                                        break;
                                    } else {
                                        $lineIndex--; // go to previous invoice in cycle
                                    }
                                }


                                $maxPrevSituationPercent = 0;
                                foreach ($object->tab_previous_situation_invoice[$lineIndex]->lines as $prevLine)
                                {
                                    if ($prevLine->id == $line->fk_prev_id)
                                    {
                                        $maxPrevSituationPercent = max($maxPrevSituationPercent, $prevLine->situation_percent);
                                    }
                                }


                                $line->situation_percent = $line->situation_percent - $maxPrevSituationPercent;

                                if ($line->update() < 0) $errors++;
                            }
                        }
                    }

                    if (!$errors)
                    {
                        setEventMessages($langs->trans('Updated'), '', 'mesgs');
                        header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
                    } else {
                        setEventMessages($langs->trans('ErrorOutingSituationInvoiceCreditNote'), array(), 'errors');
                    }
                } else {
                    setEventMessages($langs->trans('ErrorOutingSituationInvoiceOnUpdate'), array(), 'errors');
                }
            } else {
                setEventMessages($langs->trans('ErrorFindNextSituationInvoice'), array(), 'errors');
            }
        }
    } // add lines from objectlinked
    elseif ($action == 'import_lines_from_object'
        && $usercancreate
        && $object->statut == Facture::STATUS_DRAFT
        && ($object->type == Facture::TYPE_STANDARD || $object->type == Facture::TYPE_REPLACEMENT || $object->type == Facture::TYPE_DEPOSIT || $object->type == Facture::TYPE_PROFORMA || $object->type == Facture::TYPE_SITUATION))
    {
        $fromElement = GETPOST('fromelement');
        $fromElementid = GETPOST('fromelementid');
        $importLines = GETPOST('line_checkbox');

        if (!empty($importLines) && is_array($importLines) && !empty($fromElement) && ctype_alpha($fromElement) && !empty($fromElementid))
        {
            if ($fromElement == 'commande')
            {
                dol_include_once('/'.$fromElement.'/class/'.$fromElement.'.class.php');
                $lineClassName = 'OrderLine';
            } elseif ($fromElement == 'propal')
            {
                dol_include_once('/comm/'.$fromElement.'/class/'.$fromElement.'.class.php');
                $lineClassName = 'PropaleLigne';
            }
            $nextRang = count($object->lines) + 1;
            $importCount = 0;
            $error = 0;
            foreach ($importLines as $lineId)
            {
                $lineId = intval($lineId);
                $originLine = new $lineClassName($db);
                if (intval($fromElementid) > 0 && $originLine->fetch($lineId) > 0)
                {
                    $originLine->fetch_optionals();
                    $desc = $originLine->desc;
                    $pu_ht = $originLine->subprice;
                    $qty = $originLine->qty;
                    $txtva = $originLine->tva_tx;
                    $txlocaltax1 = $originLine->localtax1_tx;
                    $txlocaltax2 = $originLine->localtax2_tx;
                    $fk_product = $originLine->fk_product;
                    $remise_percent = $originLine->remise_percent;
                    $date_start = $originLine->date_start;
                    $date_end = $originLine->date_end;
                    $ventil = 0;
                    $info_bits = $originLine->info_bits;
                    $fk_remise_except = $originLine->fk_remise_except;
                    $price_base_type = 'HT';
                    $pu_ttc = 0;
                    $type = $originLine->product_type;
                    $rang = $nextRang++;
                    $special_code = $originLine->special_code;
                    $origin = $originLine->element;
                    $origin_id = $originLine->id;
                    $fk_parent_line = 0;
                    $fk_fournprice = $originLine->fk_fournprice;
                    $pa_ht = $originLine->pa_ht;
                    $label = $originLine->label;
                    $array_options = $originLine->array_options;
                    if ($object->type == Facture::TYPE_SITUATION) {
                        $situation_percent = 0;
                    } else {
                        $situation_percent = 100;
                    }
                    $fk_prev_id = '';
                    $fk_unit = $originLine->fk_unit;
                    $pu_ht_devise = $originLine->multicurrency_subprice;

                    $res = $object->addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1, $txlocaltax2, $fk_product, $remise_percent, $date_start, $date_end, $ventil, $info_bits, $fk_remise_except, $price_base_type, $pu_ttc, $type, $rang, $special_code, $origin, $origin_id, $fk_parent_line, $fk_fournprice, $pa_ht, $label, $array_options, $situation_percent, $fk_prev_id, $fk_unit, $pu_ht_devise);

                    if ($res > 0) {
                        $importCount++;
                    } else {
                        $error++;
                    }
                } else {
                    $error++;
                }
            }

            if ($error)
            {
                setEventMessages($langs->trans('ErrorsOnXLines', $error), null, 'errors');
            }
        }
    }



// Actions when linking object each other
    include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

// Actions when printing a doc from card
    include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

// Action to move up and down lines of object
//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

// Action to build doc
    include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

    if ($action == 'set_thirdparty' && $permissiontoadd) {
        $object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
    }
    if ($action == 'classin' && $permissiontoadd) {
        $object->setProject(GETPOST('projectid', 'int'));
    }

// Actions to send emails
    $triggersendname = 'SEMPARPMP_DECOMPTE_SENTBYMAIL';
    $autocopy = 'MAIN_MAIL_AUTOCOPY_DECOMPTE_TO';
    $trackid = 'decompte' . $object->id;
    include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}


/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Decompte");
$help_url = '';
llxHeader('', $title, $help_url);

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


// Part to create
if ($action == 'create') {
    print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Decompte")), '', 'object_' . $object->picto);

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="token" value="' . newToken() . '">';
    print '<input type="hidden" name="action" value="add">';
    if ($backtopage) print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';

    print dol_get_fiche_head(array(), '');

    // Set some default values
    //if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

    print '<table class="border centpercent tableforfieldcreate">' . "\n";

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

    // Load objectsrc
    $remise_absolue = 0;
    if (!empty($origin) && !empty($originid)) {
        // Parse element/subelement (ex: project_task)
        $element = $subelement = $origin;
        $regs = array();
        if (preg_match('/^([^_]+)_([^_]+)/i', $origin, $regs)) {
            $element = $regs[1];
            $subelement = $regs[2];
        }

        if ($element == 'project') {
            $projectid = $originid;

            if (empty($cond_reglement_id)) {
                $cond_reglement_id = $soc->cond_reglement_id;
            }
            if (empty($mode_reglement_id)) {
                $mode_reglement_id = $soc->mode_reglement_id;
            }
            if (!$remise_percent) {
                $remise_percent = $soc->remise_percent;
            }
            if (!$dateinvoice) {
                // Do not set 0 here (0 for a date is 1970)
                $dateinvoice = (empty($dateinvoice) ? (empty($conf->global->MAIN_AUTOFILL_DATE) ? -1 : '') : $dateinvoice);
            }
        } else {
            // For compatibility
            if ($element == 'order' || $element == 'commande') {
                $element = $subelement = 'commande';
            }
            if ($element == 'propal') {
                $element = 'comm/propal';
                $subelement = 'propal';
            }
            if ($element == 'contract') {
                $element = $subelement = 'contrat';
            }
            if ($element == 'shipping') {
                $element = $subelement = 'expedition';
            }

            dol_include_once('/' . $element . '/class/' . $subelement . '.class.php');

            $classname = ucfirst($subelement);
            $objectsrc = new $classname($db);
            $objectsrc->fetch($originid);
            if (empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines'))
                $objectsrc->fetch_lines();
            $objectsrc->fetch_thirdparty();

            $projectid = (!empty($projectid) ? $projectid : $objectsrc->fk_project);
            $ref_client = (!empty($objectsrc->ref_client) ? $objectsrc->ref_client : (!empty($objectsrc->ref_customer) ? $objectsrc->ref_customer : ''));
            $ref_int = (!empty($objectsrc->ref_int) ? $objectsrc->ref_int : '');

            // only if socid not filled else it's allready done upper
            if (empty($socid))
                $soc = $objectsrc->thirdparty;

            $dateinvoice = (empty($dateinvoice) ? (empty($conf->global->MAIN_AUTOFILL_DATE) ? -1 : '') : $dateinvoice);

            if ($element == 'expedition') {
                $ref_client = (!empty($objectsrc->ref_customer) ? $objectsrc->ref_customer : '');

                $elem = $subelem = $objectsrc->origin;
                $expeoriginid = $objectsrc->origin_id;
                dol_include_once('/' . $elem . '/class/' . $subelem . '.class.php');
                $classname = ucfirst($subelem);

                $expesrc = new $classname($db);
                $expesrc->fetch($expeoriginid);

                $cond_reglement_id = (!empty($expesrc->cond_reglement_id) ? $expesrc->cond_reglement_id : (!empty($soc->cond_reglement_id) ? $soc->cond_reglement_id : 1));
                $mode_reglement_id = (!empty($expesrc->mode_reglement_id) ? $expesrc->mode_reglement_id : (!empty($soc->mode_reglement_id) ? $soc->mode_reglement_id : 0));
                $fk_account = (!empty($expesrc->fk_account) ? $expesrc->fk_account : (!empty($soc->fk_account) ? $soc->fk_account : 0));
                $remise_percent = (!empty($expesrc->remise_percent) ? $expesrc->remise_percent : (!empty($soc->remise_percent) ? $soc->remise_percent : 0));
                $remise_absolue = (!empty($expesrc->remise_absolue) ? $expesrc->remise_absolue : (!empty($soc->remise_absolue) ? $soc->remise_absolue : 0));

                //Replicate extrafields
                $expesrc->fetch_optionals();
                $object->array_options = $expesrc->array_options;
            } else {
                $cond_reglement_id = (!empty($objectsrc->cond_reglement_id) ? $objectsrc->cond_reglement_id : (!empty($soc->cond_reglement_id) ? $soc->cond_reglement_id : 0));
                $mode_reglement_id = (!empty($objectsrc->mode_reglement_id) ? $objectsrc->mode_reglement_id : (!empty($soc->mode_reglement_id) ? $soc->mode_reglement_id : 0));
                $fk_account = (!empty($objectsrc->fk_account) ? $objectsrc->fk_account : (!empty($soc->fk_account) ? $soc->fk_account : 0));
                $remise_percent = (!empty($objectsrc->remise_percent) ? $objectsrc->remise_percent : (!empty($soc->remise_percent) ? $soc->remise_percent : 0));
                $remise_absolue = (!empty($objectsrc->remise_absolue) ? $objectsrc->remise_absolue : (!empty($soc->remise_absolue) ? $soc->remise_absolue : 0));

                if (!empty($conf->multicurrency->enabled)) {
                    if (!empty($objectsrc->multicurrency_code)) $currency_code = $objectsrc->multicurrency_code;
                    if (!empty($conf->global->MULTICURRENCY_USE_ORIGIN_TX) && !empty($objectsrc->multicurrency_tx)) $currency_tx = $objectsrc->multicurrency_tx;
                }

                // Replicate extrafields
                $objectsrc->fetch_optionals();
                $object->array_options = $objectsrc->array_options;
            }
        }
    } else {
        $cond_reglement_id = $soc->cond_reglement_id;
        $mode_reglement_id = $soc->mode_reglement_id;
        $fk_account = $soc->fk_account;
        $remise_percent = $soc->remise_percent;
        $remise_absolue = 0;
        $dateinvoice = (empty($dateinvoice) ? (empty($conf->global->MAIN_AUTOFILL_DATE) ? -1 : '') : $dateinvoice); // Do not set 0 here (0 for a date is 1970)

        if (!empty($conf->multicurrency->enabled) && !empty($soc->multicurrency_code)) $currency_code = $soc->multicurrency_code;
    }

    // Lines from source (TODO Show them also when creating invoice from template invoice)
    if (!empty($origin) && !empty($originid) && is_object($objectsrc)) {

        print "\n<!-- " . $classname . " info -->";
        print "\n";
        print '<input type="hidden" name="amount"         value="' . $objectsrc->total_ht . '">' . "\n";
        print '<input type="hidden" name="total"          value="' . $objectsrc->total_ttc . '">' . "\n";
        print '<input type="hidden" name="tva"            value="' . $objectsrc->total_tva . '">' . "\n";
        print '<input type="hidden" name="origin"         value="' . $objectsrc->element . '">';
        print '<input type="hidden" name="originid"       value="' . $objectsrc->id . '">';

        $newclassname = 'CommercialProposal';


        print '<tr><td>' . $langs->trans($newclassname) . '</td><td colspan="2">' . $objectsrc->getNomUrl(1);
        // We check if Origin document (id and type is known) has already at least one invoice attached to it
        $objectsrc->fetchObjectLinked($originid, $origin, '', 'facture');
        if (is_array($objectsrc->linkedObjects['facture']) && count($objectsrc->linkedObjects['facture']) >= 1) {
            setEventMessages('WarningBillExist', null, 'warnings');
            echo ' (' . $langs->trans('LatestRelatedBill') . ' ' . end($objectsrc->linkedObjects['facture'])->getNomUrl(1) . ')';
        }
        echo '</td></tr>';
        print '<tr><td>' . $langs->trans("Montant de l'offre") . '</td><td colspan="2">' . price($objectsrc->total_ht) . '</td></tr>';
        if ($mysoc->localtax2_assuj == "1" || $objectsrc->total_localtax2 != 0)        // Localtax2
        {
            print '<tr><td>' . $langs->transcountry("AmountLT2", $mysoc->country_code) . '</td><td colspan="2">' . price($objectsrc->total_localtax2) . "</td></tr>";
        }
        print '<tr><td>' . $langs->trans("Montant de l'offre") . '</td><td colspan="2">' . price($objectsrc->total_ttc) . "</td></tr>";

        if (!empty($conf->multicurrency->enabled)) {
            print '<tr><td>' . $langs->trans('MulticurrencyAmountHT') . '</td><td colspan="2">' . price($objectsrc->multicurrency_total_ht) . '</td></tr>';
            print '<tr><td>' . $langs->trans('MulticurrencyAmountTTC') . '</td><td colspan="2">' . price($objectsrc->multicurrency_total_ttc) . "</td></tr>";
        }
    }

    print '</table>' . "\n";

    print dol_get_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button" name="add" value="' . dol_escape_htmltag($langs->trans("CreateDraft")) . '">';
    print '&nbsp; ';
    print '<input type="' . ($backtopage ? "submit" : "button") . '" class="button button-cancel" name="cancel" value="' . dol_escape_htmltag($langs->trans("Cancel")) . '"' . ($backtopage ? '' : ' onclick="javascript:history.go(-1)"') . '>'; // Cancel for create does not post form if we don't know the backtopage
    print '</div>';


    if (!empty($origin) && !empty($originid) && is_object($objectsrc)) {
        print '<br>';

        $title = $langs->trans('ProductsAndServices');
        print load_fiche_titre($title);

        print '<table class="noborder centpercent">';

        $objectsrc->printOriginLinesList('', $selectedLines);

        print '</table>';
    }
    print '</form>';

    //dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
    print load_fiche_titre($langs->trans("Decompte"), '', 'object_' . $object->picto);

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="token" value="' . newToken() . '">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="id" value="' . $object->id . '">';
    if ($backtopage) print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';

    print dol_get_fiche_head();

    print '<table class="border centpercent tableforfieldedit">' . "\n";

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

    print '</table>';

    print dol_get_fiche_end();

    print '<div class="center"><input type="submit" class="button button-save" name="save" value="' . $langs->trans("Save") . '">';
    print ' &nbsp; <input type="submit" class="button button-cancel" name="cancel" value="' . $langs->trans("Cancel") . '">';
    print '</div>';

    print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
    $res = $object->fetch_optionals();

    $head = decomptePrepareHead($object);
    print dol_get_fiche_head($head, 'card', $langs->trans("Decompte"), -1, $object->picto);

    $formconfirm = '';

    // Confirmation to delete
    if ($action == 'delete') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteDecompte'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
    }
    // Confirmation to delete line
    if ($action == 'deleteline') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
    }
    // Clone confirmation
    if ($action == 'clone') {
        // Create an array for form
        $formquestion = array();
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
    }

    // Confirmation of action xxxx
    if ($action == 'xxx') {
        $formquestion = array();
        /*
        $forcecombo=0;
        if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
        $formquestion = array(
            // 'text' => $langs->trans("ConfirmClone"),
            // array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
            // array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
            // array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
        );
        */
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
    }

    // Call Hook formConfirm
    $parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
    $reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
    if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
    elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

    // Print form confirm
    print $formconfirm;


    // Object card
    // ------------------------------------------------------------
    $linkback = '<a href="' . dol_buildpath('/semparpmp/decompte_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

    $morehtmlref = '<div class="refidno">';
    /*
     // Ref customer
     $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
     $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
     // Thirdparty
     $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
     // Project
     if (! empty($conf->projet->enabled))
     {
     $langs->load("projects");
     $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
     if ($permissiontoadd)
     {
     //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
     $morehtmlref .= ' : ';
     if ($action == 'classify') {
     //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
     $morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
     $morehtmlref .= '<input type="hidden" name="action" value="classin">';
     $morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
     $morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
     $morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
     $morehtmlref .= '</form>';
     } else {
     $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
     }
     } else {
     if (! empty($object->fk_project)) {
     $proj = new Project($db);
     $proj->fetch($object->fk_project);
     $morehtmlref .= ': '.$proj->getNomUrl();
     } else {
     $morehtmlref .= '';
     }
     }
     }*/
    $morehtmlref .= '</div>';


    dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border centpercent tableforfield">' . "\n";

    // Common attributes
    //$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
    //unset($object->fields['fk_project']);				// Hide field already shown in banner
    //unset($object->fields['fk_soc']);					// Hide field already shown in banner
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';
    // Other attributes
    $cols = 2;
    //include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

    print '</table>';

    print '</div>';
    print '<div class="fichehalfright">';
    print '<div class="ficheaddleft">';

    print '<!-- amounts -->' . "\n";
    print '<table class="border bordertop tableforfield centpercent">';

    $sign = 1;
    if (!empty($conf->global->INVOICE_POSITIVE_CREDIT_NOTE_SCREEN) && $object->type == $object::TYPE_CREDIT_NOTE) {
        $sign = -1; // We invert sign for output
    }


    // Amount
    print '<tr><td class="titlefieldmiddle">' . $langs->trans('AmountHT') . '</td>';
    print '<td class="nowrap amountcard">' . price($sign * $object->amount, 1, '', 1, -1, -1, $conf->currency) . '</td></tr>';

    // Total with tax
    print '<tr><td>' . $langs->trans('AmountTTC') . '</td><td class="nowrap amountcard">' . price($sign * $object->amount, 1, '', 1, -1, -1, $conf->currency) . '</td></tr>';

    print '</table>';


    $nbrows = 8;
    $nbcols = 3;

    // Other attributes. Fields from hook formObjectOptions and Extrafields.
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

    print '</table>';
    print '</div>';
    print '</div>';

    print '<div class="clearboth"></div>';

    print dol_get_fiche_end();


    /*
     * Lines
     */

    // Lines
    $result = $object->getLinesArray();

    print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid')).'" method="POST">
	<input type="hidden" name="token" value="' . newToken().'">
	<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
	<input type="hidden" name="mode" value="">
	<input type="hidden" name="id" value="' . $object->id.'">
	';

    if (!empty($conf->use_javascript_ajax) && $object->statut == 0) {
        include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
    }

    print '<div class="div-table-responsive-no-min">';
    print '<table id="tablelines" class="noborder noshadow" width="100%">';
    // Show object lines
    if (!empty($object->lines)) {
        $ret = $object->printObjectLines($action, $mysoc, $soc, $lineid, 1);
    }

    // Form to add new line
    if ($object->status == 0 && $action != 'valid' && $action != 'editline') {
        if ($action != 'editline' && $action != 'selectlines') {
            // Add free products/services
            $object->formAddObjectLine(1, $mysoc, $soc);

            $parameters = array();
            $reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
        }
    }

    print "</table>\n";
    print "</div>";

    print "</form>\n";

    print dol_get_fiche_end();

    /*	if (!empty($object->table_element_line))
        {
            // Show object lines
            $result = $object->getLinesArray();

            print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
            <input type="hidden" name="token" value="' . newToken().'">
            <input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
            <input type="hidden" name="mode" value="">
            <input type="hidden" name="id" value="' . $object->id.'">
            ';

            if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
                include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
            }

            print '<div class="div-table-responsive-no-min">';
            if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
            {
                print '<table id="tablelines" class="noborder noshadow" width="100%">';
            }

            if (!empty($object->lines))
            {
                $object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
            }

            // Form to add new line
            if ($object->status == 0 && $permissiontoadd && $action != 'selectlines')
            {
                if ($action != 'editline')
                {
                    // Add products/services form
                    $object->formAddObjectLine(1, $mysoc, $soc);

                    $parameters = array();
                    $reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
                }
            }

            if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
            {
                print '</table>';
            }
            print '</div>';

            print "</form>\n";
        }*/


    // Buttons for actions

    if ($action != 'presend' && $action != 'editline') {
        print '<div class="tabsAction">' . "\n";
        $parameters = array();
        $reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
        if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

        if (empty($reshook)) {
            // Send
            if (empty($user->socid)) {
                print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle');
            }

            // Back to draft
            if ($object->status == $object::STATUS_VALIDATED) {
                print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes', '', $permissiontoadd);
            }

            print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit', '', $permissiontoadd);

            // Validate
            if ($object->status == $object::STATUS_DRAFT) {
                if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
                    print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_validate&confirm=yes', '', $permissiontoadd);
                } else {
                    $langs->load("errors");
                    //print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes', '', 0);
                    print '<a class="butActionRefused" href="" title="' . $langs->trans("ErrorAddAtLeastOneLineFirst") . '">' . $langs->trans("Validate") . '</a>';
                }
            }

            // Clone
            print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $object->socid . '&action=clone&object=scrumsprint', '', $permissiontoadd);

            /*
            if ($permissiontoadd)
            {
                if ($object->status == $object::STATUS_ENABLED) {
                    print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=disable">'.$langs->trans("Disable").'</a>'."\n";
                } else {
                    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=enable">'.$langs->trans("Enable").'</a>'."\n";
                }
            }
            if ($permissiontoadd)
            {
                if ($object->status == $object::STATUS_VALIDATED) {
                    print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=close">'.$langs->trans("Cancel").'</a>'."\n";
                } else {
                    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=reopen">'.$langs->trans("Re-Open").'</a>'."\n";
                }
            }
            */

            // Delete (need delete permission, or if draft, just need create/modify permission)
            print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete', '', $permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd));
        }
        print '</div>' . "\n";
    }


    // Select mail models is same action as presend
    if (GETPOST('modelselected')) {
        $action = 'presend';
    }

    if ($action != 'presend') {
        print '<div class="fichecenter"><div class="fichehalfleft">';
        print '<a name="builddoc"></a>'; // ancre

        $includedocgeneration = 0;

        // Documents
        if ($includedocgeneration) {
            $objref = dol_sanitizeFileName($object->ref);
            $relativepath = $objref . '/' . $objref . '.pdf';
            $filedir = $conf->semparpmp->dir_output . '/' . $object->element . '/' . $objref;
            $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
            $genallowed = $user->rights->semparpmp->decompte->read; // If you can read, you can build the PDF to read content
            $delallowed = $user->rights->semparpmp->decompte->write; // If you can create/edit, you can remove a file on card
            print $formfile->showdocuments('semparpmp:Decompte', $object->element . '/' . $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
        }

        // Show links to link elements
        $linktoelem = $form->showLinkToObjectBlock($object, null, array('decompte'));
        $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


        print '</div><div class="fichehalfright"><div class="ficheaddleft">';

        $MAXEVENT = 10;

        $morehtmlright = '<a href="' . dol_buildpath('/semparpmp/decompte_agenda.php', 1) . '?id=' . $object->id . '">';
        $morehtmlright .= $langs->trans("SeeAll");
        $morehtmlright .= '</a>';

        // List of actions on element
        include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
        $formactions = new FormActions($db);
        $somethingshown = $formactions->showactions($object, $object->element . '@' . $object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

        print '</div></div></div>';
    }

    //Select mail models is same action as presend
    if (GETPOST('modelselected')) $action = 'presend';

    // Presend form
    $modelmail = 'decompte';
    $defaulttopic = 'InformationMessage';
    $diroutput = $conf->semparpmp->dir_output;
    $trackid = 'decompte' . $object->id;

    include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
