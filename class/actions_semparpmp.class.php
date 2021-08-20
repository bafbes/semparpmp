<?php
/**
 * Created by PhpStorm.
 * User: lounes
 * Date: 05/08/21
 * Time: 11:38
 */
class Actionssemparpmp{
    function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
    {
        global $mysoc, $db, $langs, $conf, $user, $id;
        if  (get_class($object) == 'Propal' && $object->statut == Propal::STATUS_SIGNED)
        {
            if (!empty($conf->facture->enabled) && $user->rights->facture->creer)
            {
                print '<a class="butAction" href="'.dol_buildpath('/semparpmp/decompte_card.php',1).'?action=create&amp;origin='.$object->element.'&amp;originid='.$object->id.'&amp;socid='.$object->socid.'">'.$langs->trans("Créer un décompte").'</a>';
            }

        }
    }
}