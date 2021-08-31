<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/decompte.class.php
 * \ingroup     semparpmp
 * \brief       This file is a CRUD class file for Decompte (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Decompte
 */
class Decompte extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'semparpmp';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'decompte';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'semparpmp_decompte';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for decompte. Must be the part after the 'object_' into object_decompte.png
	 */
	public $picto = 'decompte@semparpmp';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'maxwidth200', 'wordbreak', 'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'help'=>"Help text", 'showoncombobox'=>'1',),
		'amount' => array('type'=>'price', 'label'=>'Amount', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text for amount",),
		'qty' => array('type'=>'real', 'label'=>'Qty', 'enabled'=>'1', 'position'=>45, 'notnull'=>0, 'visible'=>1, 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp', 'help'=>"Help text for quantity",),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)', 'label'=>'ThirdParty', 'enabled'=>'1', 'position'=>50, 'notnull'=>-1, 'visible'=>1, 'index'=>1, 'help'=>"LinkToThirparty",),
		'fk_project' => array('type'=>'integer:Project:projet/class/project.class.php:1', 'label'=>'Project', 'enabled'=>'1', 'position'=>52, 'notnull'=>-1, 'visible'=>-1, 'index'=>1,),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>600, 'notnull'=>0, 'visible'=>0,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Valid&eacute;', '9'=>'Annul&eacute;'),),
		'der_decompte' => array('type'=>'boolean', 'label'=>'dernier décompte', 'enabled'=>'1', 'position'=>20, 'notnull'=>0, 'visible'=>1,),
		'date_init' => array('type'=>'date', 'label'=>'date initiale', 'enabled'=>'1', 'position'=>25, 'notnull'=>0, 'visible'=>1,),
		'date_fin' => array('type'=>'date', 'label'=>'date finale', 'enabled'=>'1', 'position'=>26, 'notnull'=>0, 'visible'=>1,),
	);
	public $rowid;
	public $ref;
	public $label;
	public $amount;
	public $qty;
	public $fk_soc;
	public $fk_project;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $last_main_doc;
	public $import_key;
	public $model_pdf;
	public $status;
	public $der_decompte;
	public $date_init;
	public $date_fin;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	 public $table_element_line = 'semparpmp_decompteline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	 public $fk_element = 'fk_decompte';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	 public $class_element_line = 'DecompteLine';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	 protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	 protected $childtablesoncascade = array('semparpmp_decompteline');

	// /**
	//  * @var DecompteLine[]     Array of subtable lines
	//  */
	 public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->semparpmp->decompte->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs))
		{
			foreach ($this->fields as $key => $val)
			{
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval']))
				{
					foreach ($val['arrayofkeyval'] as $key2 => $val2)
					{
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

    /**
     *  Add an invoice line into database (linked to product/service or not).
     *  Les parametres sont deja cense etre juste et avec valeurs finales a l'appel
     *  de cette methode. Aussi, pour le taux tva, il doit deja avoir ete defini
     *  par l'appelant par la methode get_default_tva(societe_vendeuse,societe_acheteuse,produit)
     *  et le desc doit deja avoir la bonne valeur (a l'appelant de gerer le multilangue)
     *
     *  @param    	string		$desc            	Description of line
     *  @param    	double		$pu_ht              Unit price without tax (> 0 even for credit note)
     *  @param    	double		$qty             	Quantity
     *  @param    	double		$txtva           	Force Vat rate, -1 for auto (Can contain the vat_src_code too with syntax '9.9 (CODE)')
     *  @param		double		$txlocaltax1		Local tax 1 rate (deprecated, use instead txtva with code inside)
     *  @param		double		$txlocaltax2		Local tax 2 rate (deprecated, use instead txtva with code inside)
     *  @param    	int			$fk_product      	Id of predefined product/service
     *  @param    	double		$remise_percent  	Percent of discount on line
     *  @param    	int			$date_start      	Date start of service
     *  @param    	int			$date_end        	Date end of service
     *  @param    	int			$ventil          	Code of dispatching into accountancy
     *  @param    	int			$info_bits			Bits of type of lines
     *  @param    	int			$fk_remise_except	Id discount used
     *  @param		string		$price_base_type	'HT' or 'TTC'
     *  @param    	double		$pu_ttc             Unit price with tax (> 0 even for credit note)
     *  @param		int			$type				Type of line (0=product, 1=service). Not used if fk_product is defined, the type of product is used.
     *  @param      int			$rang               Position of line
     *  @param		int			$special_code		Special code (also used by externals modules!)
     *  @param		string		$origin				Depend on global conf MAIN_CREATEFROM_KEEP_LINE_ORIGIN_INFORMATION can be 'orderdet', 'propaldet'..., else 'order','propal,'....
     *  @param		int			$origin_id			Depend on global conf MAIN_CREATEFROM_KEEP_LINE_ORIGIN_INFORMATION can be Id of origin object (aka line id), else object id
     *  @param		int			$fk_parent_line		Id of parent line
     *  @param		int			$fk_fournprice		Supplier price id (to calculate margin) or ''
     *  @param		int			$pa_ht				Buying price of line (to calculate margin) or ''
     *  @param		string		$label				Label of the line (deprecated, do not use)
     *  @param		array		$array_options		extrafields array
     *  @param      int         $situation_percent  Situation advance percentage
     *  @param      int         $fk_prev_id         Previous situation line id reference
     *  @param 		string		$fk_unit 			Code of the unit to use. Null to use the default one
     *  @param		double		$pu_ht_devise		Unit price in foreign currency
     *  @param		string		$ref_ext		    External reference of the line
     *  @return    	int             				<0 if KO, Id of line if OK
     */
    public function addline(
        $desc,
        $pu_ht,
        $qty,
        $txtva,
        $date_creation = '',
        $tms = '',
        $price_base_type = 'HT',
        $pu_ttc = 0,
        $rang = -1,
        $origin = '',
        $origin_id = 0,
        $fk_parent_line = 0,
        $label = '',
        $array_options = 0,
        $fk_unit = null
    ) {
        // Deprecation warning
        if ($label) {
            dol_syslog(__METHOD__.": using line label is deprecated", LOG_WARNING);
            //var_dump(debug_backtrace(false));exit;
        }

        global $mysoc, $conf, $langs;

        dol_syslog(get_class($this)."::addline id=$this->id,desc=$desc,pu_ht=$pu_ht,qty=$qty,txtva=$txtva, date_creation=$date_creation,tms=$tms,price_base_type=$price_base_type,pu_ttc=$pu_ttc,fk_unit=$fk_unit", LOG_DEBUG);

        if ($this->statut == self::STATUS_DRAFT)
        {
            include_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';

            // Clean parameters
            if (empty($qty)) $qty = 0;
            if (empty($rang)) $rang = 0;
            if (empty($txtva)) $txtva = 0;
            if (empty($fk_parent_line) || $fk_parent_line < 0) $fk_parent_line = 0;

            $qty = price2num($qty);
            $pu_ht = price2num($pu_ht);
            $pu_ttc = price2num($pu_ttc);
            if (!preg_match('/\((.*)\)/', $txtva)) {
                $txtva = price2num($txtva); // $txtva can have format '5.0(XXX)' or '5'
            }

            if ($price_base_type == 'HT')
            {
                $pu = $pu_ht;
            } else {
                $pu = $pu_ttc;
            }

            $this->db->begin();



            // Clean vat code
            $reg = array();
            $vat_src_code = '';
            if (preg_match('/\((.*)\)/', $txtva, $reg))
            {
                $vat_src_code = $reg[1];
                $txtva = preg_replace('/\s*\(.*\)/', '', $txtva); // Remove code into vatrate.
            }

            // Calcul du total TTC et de la TVA pour la ligne a partir de
            // qty, pu, remise_percent et txtva
            // TRES IMPORTANT: C'est au moment de l'insertion ligne qu'on doit stocker
            // la part ht, tva et ttc, et ce au niveau de la ligne qui a son propre taux tva.

            $tabprice = calcul_price_total($qty, $pu, $remise_percent=0, $txtva, $txlocaltax1=0, $txlocaltax2=0, 0, $price_base_type, $info_bits='', $product_type='', $mysoc, $localtaxes_type='', $situation_percent=0, $this->multicurrency_tx, $pu_ht_devise=0);

            $total_ht  = $tabprice[0];
            $total_tva = $tabprice[1];
            $total_ttc = $tabprice[2];
            $pu_ht = $tabprice[3];



            // Rank to use
            $ranktouse = $rang;
            if ($ranktouse == -1)
            {
                $rangmax = $this->line_max($fk_parent_line);
                $ranktouse = $rangmax + 1;
            }

            // Insert line
            $this->line = new DecompteLine($this->db);

            $this->line->context = $this->context;

            $this->line->fk_decompte = $this->id;
            $this->line->label = $label; // deprecated
            $this->line->desc = $desc;

            $this->line->qty = $qty; // For credit note, quantity is always positive and unit price negative
            $this->line->subprice = $pu_ht; // For credit note, unit price always negative, always positive otherwise

            $this->line->vat_src_code = $vat_src_code;
            $this->line->tva_tx = $txtva;

            $this->line->total_ht = $total_ht; // For credit note and if qty is negative, total is negative
            $this->line->total_ttc = $total_ttc; // For credit note and if qty is negative, total is negative
            $this->line->total_tva = $total_tva; // For credit note and if qty is negative, total is negative

            $this->line->tms = $tms;
            $this->line->rang = $ranktouse;

            $this->line->fk_parent_line = $fk_parent_line;
            $this->line->origin = $origin;
            $this->line->origin_id = $origin_id;
            $this->line->fk_unit = $fk_unit;



            if (is_array($array_options) && count($array_options) > 0) {
                $this->line->array_options = $array_options;
            }

            $result = $this->line->insert();
            if ($result > 0)
            {
                // Reorder if child line
                if (!empty($fk_parent_line)) $this->line_order(true, 'DESC');


                if ($result > 0)
                {
                    $this->db->commit();
                    return $this->line->id;
                } else {
                    $this->error = $this->db->lasterror();
                    $this->db->rollback();
                    return -1;
                }
            } else {
                $this->error = $this->line->error;
                $this->errors = $this->line->errors;
                $this->db->rollback();
                return -2;
            }
        } else {
            dol_syslog(get_class($this)."::addline status of invoice must be Draft to allow use of ->addline()", LOG_ERR);
            return -3;
        }
    }

    /**
     *  Update a detail line
     *
     *  @param     	int			$rowid           	Id of line to update
     *  @param     	string		$desc            	Description of line
     *  @param     	double		$pu              	Prix unitaire (HT ou TTC selon price_base_type) (> 0 even for credit note lines)
     *  @param     	double		$qty             	Quantity
     *  @param     	double		$remise_percent  	Percentage discount of the line
     *  @param     	int		    $date_start      	Date de debut de validite du service
     *  @param     	int		    $date_end        	Date de fin de validite du service
     *  @param     	double		$txtva          	VAT Rate (Can be '8.5', '8.5 (ABC)')
     * 	@param		double		$txlocaltax1		Local tax 1 rate
     *  @param		double		$txlocaltax2		Local tax 2 rate
     * 	@param     	string		$price_base_type 	HT or TTC
     * 	@param     	int			$info_bits 		    Miscellaneous informations
     * 	@param		int			$type				Type of line (0=product, 1=service)
     * 	@param		int			$fk_parent_line		Id of parent line (0 in most cases, used by modules adding sublevels into lines).
     * 	@param		int			$skip_update_total	Keep fields total_xxx to 0 (used for special lines by some modules)
     * 	@param		int			$fk_fournprice		Id of origin supplier price
     * 	@param		int			$pa_ht				Price (without tax) of product when it was bought
     * 	@param		string		$label				Label of the line (deprecated, do not use)
     * 	@param		int			$special_code		Special code (also used by externals modules!)
     *  @param		array		$array_options		extrafields array
     * 	@param      int         $situation_percent  Situation advance percentage
     * 	@param 		string		$fk_unit 			Code of the unit to use. Null to use the default one
     * 	@param		double		$pu_ht_devise		Unit price in currency
     * 	@param		int			$notrigger			disable line update trigger
     *  @param		string		$ref_ext		    External reference of the line
     *  @return    	int             				< 0 if KO, > 0 if OK
     */
    public function updateline($rowid, $desc, $pu, $qty, $date_creation, $tms, $txtva, $price_base_type = 'HT', $fk_parent_line = 0, $label = '',  $array_options = 0, $fk_unit = null,  $notrigger = 0)
    {
        global $conf, $user;
        // Deprecation warning
        if ($label) {
            dol_syslog(__METHOD__.": using line label is deprecated", LOG_WARNING);
        }

        include_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';

        global $mysoc, $langs;

        dol_syslog(get_class($this)."::updateline rowid=$rowid, desc=$desc, pu=$pu, qty=$qty, date_creation=$date_creation, tms=$tms, txtva=$txtva, price_base_type=$price_base_type,  fk_parent_line=$fk_parent_line , fk_unit=$fk_unit", LOG_DEBUG);

        if ($this->brouillon)
        {

            $this->db->begin();

            // Clean parameters
            if (empty($qty)) $qty = 0;
            if (empty($fk_parent_line) || $fk_parent_line < 0) $fk_parent_line = 0;

            $qty			= price2num($qty);
            $pu 			= price2num($pu);
            if (!preg_match('/\((.*)\)/', $txtva)) {
                $txtva = price2num($txtva); // $txtva can have format '5.0(XXX)' or '5'
            }


            // Calculate total with, without tax and tax from qty, pu, remise_percent and txtva
            // TRES IMPORTANT: C'est au moment de l'insertion ligne qu'on doit stocker
            // la part ht, tva et ttc, et ce au niveau de la ligne qui a son propre taux tva.

            // Clean vat code
            $reg = array();
            $vat_src_code = '';
            if (preg_match('/\((.*)\)/', $txtva, $reg))
            {
                $vat_src_code = $reg[1];
                $txtva = preg_replace('/\s*\(.*\)/', '', $txtva); // Remove code into vatrate.
            }

            $tabprice = calcul_price_total($qty, $pu, $remise_percent=0, $txtva, $txlocaltax1=0, $txlocaltax2=0, 0, $price_base_type, $info_bits=0, $type='', $mysoc, $localtaxes_type='', $situation_percent=0, $this->multicurrency_tx, $pu_ht_devise=0);

            $total_ht  = $tabprice[0];
            $total_tva = $tabprice[1];
            $total_ttc = $tabprice[2];
            $pu_ht  = $tabprice[3];
            $pu_tva = $tabprice[4];
            $pu_ttc = $tabprice[5];


            // Old properties: $price, $remise (deprecated)
            $price = $pu;
            $remise = 0;

            //Fetch current line from the database and then clone the object and set it in $oldline property
            $line = new DecompteLine($this->db);
            $line->fetch($rowid);
            $line->fetch_optionals();

            $staticline = clone $line;

            $line->oldline = $staticline;
            $this->line = $line;
            $this->line->context = $this->context;

            // Reorder if fk_parent_line change
            if (!empty($fk_parent_line) && !empty($staticline->fk_parent_line) && $fk_parent_line != $staticline->fk_parent_line)
            {
                $rangmax = $this->line_max($fk_parent_line);
                $this->line->rang = $rangmax + 1;
            }

            $this->line->id = $rowid;
            $this->line->rowid				= $rowid;
            $this->line->label				= $label;
            $this->line->desc = $desc;
            $this->line->qty = $qty; // For credit note, quantity is always positive and unit price negative

            $this->line->tva_tx = $txtva;

            $this->line->subprice			= $pu_ht; // For credit note, unit price always negative, always positive otherwise
            $this->line->date_creation = $date_creation;
            $this->line->tms			= $tms;
            $this->line->total_ht			= $total_ht; // For credit note and if qty is negative, total is negative
            $this->line->total_tva			= $total_tva;
            $this->line->total_ttc			= $total_ttc;
            $this->line->fk_parent_line = $fk_parent_line;
            $this->line->fk_unit = $fk_unit;


            if (is_array($array_options) && count($array_options) > 0) {
                // We replace values in this->line->array_options only for entries defined into $array_options
                foreach ($array_options as $key => $value) {
                    $this->line->array_options[$key] = $array_options[$key];
                }
            }

            $result = $this->line->update($user, $notrigger);
            if ($result > 0)
            {
                // Reorder if child line
                if (!empty($fk_parent_line)) $this->line_order(true, 'DESC');

                // Mise a jour info denormalisees au niveau facture
                $this->update_price(1);
                $this->db->commit();
                return $result;
            } else {
                $this->error = $this->line->error;
                $this->db->rollback();
                return -1;
            }
        } else {
            $this->error = "Invoice statut makes operation forbidden";
            return -2;
        }
    }

    /**
     *	Delete line in database
     *
     *	@param		int		$rowid		Id of line to delete
     *	@return		int					<0 if KO, >0 if OK
     */
    public function deleteline($rowid)
    {
        global $user;

        dol_syslog(get_class($this)."::deleteline rowid=".$rowid, LOG_DEBUG);

        if (!$this->brouillon)
        {
            $this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
            return -1;
        }

        $this->db->begin();


        dol_syslog(get_class($this)."::deleteline", LOG_DEBUG);

        $line = new DecompteLine($this->db);

        $line->context = $this->context;

        // For triggers
        $result = $line->fetch($rowid);
        if (!($result > 0)) dol_print_error($this->db, $line->error, $line->errors);

        if ($line->delete($user) > 0)
        {
            $result = $this->update_price(1);

            if ($result > 0)
            {
                $this->db->commit();
                return 1;
            } else {
                $this->db->rollback();
                $this->error = $this->db->lasterror();
                return -1;
            }
        } else {
            $this->db->rollback();
            $this->error = $line->error;
            return -1;
        }
    }


    /**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetch_lines();

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		if (property_exists($object, 'ref')) $object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		if (property_exists($object, 'label')) $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		if (property_exists($object, 'status')) { $object->status = self::STATUS_DRAFT; }
		if (property_exists($object, 'date_creation')) { $object->date_creation = dol_now(); }
		if (property_exists($object, 'date_modification')) { $object->date_modification = null; }
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0)
		{
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option)
			{
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey]))
				{
					//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error)
		{
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0)
			{
				$error++;
			}
		}

		if (!$error)
		{
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid)
			{
				if ($this->copy_linked_contact($object, 'external') < 0)
					$error++;
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) $this->fetch_lines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch_lines()
	{
        global $langs, $conf;
        // phpcs:enable
        $this->lines = array();

        $sql = 'SELECT l.rowid, l.fk_decompte, l.label as custom_label, l.description, l.amount, l.qty, l.tva_tx,';
        $sql .= ' l.subprice,';
        $sql .= ' l.rang, ';
        $sql .= ' l.date_creation as date_creation, l.tms as tms,';
        $sql .= ' l.total_ht, l.total_tva, l.total_ttc,';
        $sql .= ' l.fk_unit';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'semparpmp_decompteline as l';
        $sql .= ' WHERE l.fk_decompte = '.$this->id;
        $sql .= ' ORDER BY l.rowid';

        dol_syslog(get_class($this).'::fetch_lines', LOG_DEBUG);
        $result = $this->db->query($sql);
        if ($result)
        {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num)
            {
                $objp = $this->db->fetch_object($result);
                $line = new DecompteLine($this->db);

                $line->id               = $objp->rowid;
                $line->rowid = $objp->rowid; // deprecated
                $line->fk_decompte       = $objp->fk_decompte;
                $line->label            = $objp->custom_label; // deprecated
                $line->desc             = $objp->description; // Description line
                $line->description      = $objp->description; // Description line
                $line->ref              = $objp->product_ref; // Ref product
                $line->product_ref      = $objp->product_ref; // Ref product
                $line->libelle          = $objp->product_label; // deprecated
                $line->product_label = $objp->product_label; // Label product
                $line->product_desc     = $objp->product_desc; // Description product
                $line->qty              = $objp->qty;
                $line->subprice         = $objp->subprice;

                $line->tva_tx           = $objp->tva_tx;
                $line->date_creation       = $this->db->jdate($objp->date_creation);
                $line->tms         = $this->db->jdate($objp->tms);
                $line->date_creation       = $this->db->jdate($objp->date_creation);
                $line->tms         = $this->db->jdate($objp->tms);
                $line->total_ht         = $objp->total_ht;
                $line->total_tva        = $objp->total_tva;
                $line->total_ttc        = $objp->total_ttc;
                $line->rang = $objp->rang;
                $line->fk_unit = $objp->fk_unit;

                $line->fetch_optionals();


                $this->lines[$i] = $line;

                $i++;
            }
            $this->db->free($result);
            return 1;
        } else {
            $this->error = $this->db->error();
            return -3;
        }
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key.' IN ('.$this->db->sanitize($this->db->escape($value)).')';
				} else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num))
			{
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED)
		{
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->decompte->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->decompte->decompte_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) $sql .= ", date_validation = '".$this->db->idate($now)."'";
			if (!empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = ".$user->id;
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql)
			{
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger)
			{
				// Call trigger
				$result = $this->call_trigger('DECOMPTE_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error)
		{
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref))
			{
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'decompte/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'decompte/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->semparpmp->dir_output.'/decompte/'.$oldref;
				$dirdest = $conf->semparpmp->dir_output.'/decompte/'.$newref;
				if (!$error && file_exists($dirsource))
				{
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest))
					{
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->semparpmp->dir_output.'/decompte/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry)
						{
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error)
		{
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error)
		{
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->semparpmp_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'DECOMPTE_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->semparpmp_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'DECOMPTE_CANCEL');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->semparpmp->semparpmp_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'DECOMPTE_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("Decompte").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/semparpmp/decompte_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink')
		{
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip))
		{
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				$label = $langs->trans("ShowDecompte");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) $result .= $this->ref;

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('decomptedao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("semparpmp@semparpmp");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql .= ' WHERE t.rowid = '.$id;
		$result = $this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
        return $this->fetch_lines();

    }

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("semparpmp@semparpmp");

		if (empty($conf->global->SEMPARPMP_DECOMPTE_ADDON)) {
			$conf->global->SEMPARPMP_DECOMPTE_ADDON = 'mod_decompte_standard';
		}

		if (!empty($conf->global->SEMPARPMP_DECOMPTE_ADDON))
		{
			$mybool = false;

			$file = $conf->global->SEMPARPMP_DECOMPTE_ADDON.".php";
			$classname = $conf->global->SEMPARPMP_DECOMPTE_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."core/modules/semparpmp/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1')
				{
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 0;

		$langs->load("semparpmp@semparpmp");

		if (!dol_strlen($modele)) {
			$modele = 'standard_decompte';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->DECOMPTE_ADDON_PDF)) {
				$modele = $conf->global->DECOMPTE_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/semparpmp/doc/";

		if ($includedocgeneration && !empty($modele)) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class DecompteLine. You can also remove this and generate a CRUD class for lines objects.
 */
class DecompteLine extends CommonObjectLine
{
	// To complete with content of an object DecompteLine
	// We should have a field rowid, fk_decompte and position

    /**
     * @var string ID to identify managed object
     */
    public $element = 'decompteline';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'semparpmp_decompteline';

    public $oldline;

    //! From llx_facturedet
    //! Id facture
    public $fk_decompte;
    //! Id parent line
    public $fk_parent_line;

    //! Description ligne
    public $desc;


    public $rang = 0;


    public $origin;
    public $origin_id;


    public $date_start;
    public $date_end;



    /**
     *	Load invoice line from database
     *
     *	@param	int		$rowid      id of invoice line to get
     *	@return	int					<0 if KO, >0 if OK
     */
    public function fetch($rowid)
    {
        $sql = 'SELECT fd.rowid, fd.fk_decompte, fd.label as custom_label, fd.description, fd.amount, fd.qty, fd.tva_tx,';
        $sql .= ' fd.subprice,';
        $sql .= ' fd.date_creation as date_creation, fd.tms as tms,';
        $sql .= ' fd.total_ht, fd.total_tva, fd.total_ttc, fd.rang,';
        $sql .= ' fd.fk_unit, fd.fk_user_creat, fd.fk_user_modif,';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'semparpmp_decompteline as fd';
        $sql .= ' WHERE fd.rowid = '.$rowid;

        $result = $this->db->query($sql);
        if ($result)
        {
            $objp = $this->db->fetch_object($result);

            $this->rowid = $objp->rowid;
            $this->id = $objp->rowid;
            $this->fk_decompte = $objp->fk_decompte;
            $this->fk_parent_line = $objp->fk_parent_line;
            $this->label				= $objp->custom_label;
            $this->desc					= $objp->description;
            $this->qty = $objp->qty;
            $this->subprice = $objp->subprice;
            $this->tva_tx = $objp->tva_tx;
            $this->date_creation			= $this->db->jdate($objp->date_creation);
            $this->tms				= $this->db->jdate($objp->tms);
            $this->total_ht				= $objp->total_ht;
            $this->total_tva			= $objp->total_tva;
            $this->total_ttc			= $objp->total_ttc;
            $this->rang					= $objp->rang;


            $this->ref = $objp->product_ref; // deprecated

            $this->product_ref = $objp->product_ref;
            $this->product_label		= $objp->product_label;
            $this->product_desc			= $objp->product_desc;

            $this->fk_unit = $objp->fk_unit;
            $this->fk_user_creat		= $objp->fk_user_creat;
            $this->fk_user_author = $objp->fk_user_author;

            $this->db->free($result);

            return 1;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     *	Insert line into database
     *
     *	@param      int		$notrigger		                 1 no triggers
     *  @param      int     $noerrorifdiscountalreadylinked  1=Do not make error if lines is linked to a discount and discount already linked to another
     *	@return		int						                 <0 if KO, >0 if OK
     */
    public function insert($notrigger = 0, $noerrorifdiscountalreadylinked = 0)
    {
        global $langs, $user, $conf;

        $error = 0;


        dol_syslog(get_class($this)."::insert rang=".$this->rang, LOG_DEBUG);

        // Clean parameters
        $this->desc = trim($this->desc);
        if (empty($this->tva_tx)) $this->tva_tx = 0;
        if (empty($this->rang)) $this->rang = 0;
        if (empty($this->subprice)) $this->subprice = 0;
        if (empty($this->fk_parent_line)) $this->fk_parent_line = 0;

        $this->db->begin();

        // Insertion dans base de la ligne
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'semparpmp_decompteline';
        $sql .= ' (fk_decompte, label, description, qty,';
        $sql .= ' tva_tx,';
        $sql .= ' subprice, ';
        $sql .= ' date_creation, tms,';
        $sql .= ' rang,';
        $sql .= ' total_ht, total_tva, total_ttc,';
        $sql .= ' fk_unit, fk_user_creat, fk_user_modif';
        $sql .= ')';
        $sql .= " VALUES (".$this->fk_decompte.",";
        $sql .= " ".(!empty($this->label) ? "'".$this->db->escape($this->label)."'" : "null").",";
        $sql .= " '".$this->db->escape($this->desc)."',";
        $sql .= " ".price2num($this->qty).",";
        $sql .= " ".price2num($this->tva_tx).",";
        $sql .= " ".price2num($this->subprice).",";
        $sql .= " ".(!empty($this->date_creation) ? "'".$this->db->idate($this->date_creation)."'" : "null").",";
        $sql .= " ".(!empty($this->tms) ? "'".$this->db->idate($this->tms)."'" : "null").",";
        $sql .= " ".$this->rang.',';
        $sql .= " ".price2num($this->total_ht).",";
        $sql .= " ".price2num($this->total_tva).",";
        $sql .= " ".price2num($this->total_ttc).",";
        $sql .= " ".(!$this->fk_unit ? 'NULL' : $this->fk_unit);
        $sql .= ", ".$user->id;
        $sql .= ", ".$user->id;
        $sql .= ')';

        dol_syslog(get_class($this)."::insert", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.'semparpmp_decompteline');
            $this->rowid = $this->id; // For backward compatibility

            if (!$error)
            {
                $result = $this->insertExtraFields();
                if ($result < 0)
                {
                    $error++;
                }
            }


            if (!$notrigger)
            {
                // Call trigger
                $result = $this->call_trigger('LINEBILL_INSERT', $user);
                if ($result < 0)
                {
                    $this->db->rollback();
                    return -2;
                }
                // End call triggers
            }

            $this->db->commit();
            return $this->id;
        } else {
            $this->error = $this->db->lasterror();
            $this->db->rollback();
            return -2;
        }
    }

    /**
     *	Update line into database
     *
     *	@param		User	$user		User object
     *	@param		int		$notrigger	Disable triggers
     *	@return		int					<0 if KO, >0 if OK
     */
    public function update($user = '', $notrigger = 0)
    {
        global $user, $conf;

        $error = 0;


        // Clean parameters
        $this->desc = trim($this->desc);
        if (empty($this->tva_tx)) $this->tva_tx = 0;


        $this->db->begin();

        // Update line in database
        $sql = "UPDATE ".MAIN_DB_PREFIX."semparpmp_decompteline SET";
        $sql .= " description='".$this->db->escape($this->desc)."'";
        $sql .= ", label=".(!empty($this->label) ? "'".$this->db->escape($this->label)."'" : "null");
        $sql .= ", subprice=".price2num($this->subprice)."";
        $sql .= ", tva_tx=".price2num($this->tva_tx)."";
        $sql .= ", qty=".price2num($this->qty);
        $sql .= ", date_creation=".(!empty($this->date_creation) ? "'".$this->db->idate($this->date_creation)."'" : "null");
        $sql .= ", tms=".(!empty($this->tms) ? "'".$this->db->idate($this->tms)."'" : "null");
        if (!empty($this->rang)) $sql .= ", rang=".$this->rang;
        $sql .= ", fk_unit=".(!$this->fk_unit ? 'NULL' : $this->fk_unit);
        $sql .= ", fk_user_modif =".$user->id;


        $sql .= " WHERE rowid = ".$this->rowid;

        dol_syslog(get_class($this)."::update", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql)
        {
            if (!$error)
            {
                $this->id = $this->rowid;
                $result = $this->insertExtraFields();
                if ($result < 0)
                {
                    $error++;
                }
            }

            if (!$error && !$notrigger)
            {
                // Call trigger
                $result = $this->call_trigger('LINEBILL_UPDATE', $user);
                if ($result < 0)
                {
                    $this->db->rollback();
                    return -2;
                }
                // End call triggers
            }
            $this->db->commit();
            return 1;
        } else {
            $this->error = $this->db->error();
            $this->db->rollback();
            return -2;
        }
    }

    /**
     * 	Delete line in database
     *  TODO Add param User $user and notrigger (see skeleton)
     *
     *	@return	    int		           <0 if KO, >0 if OK
     */
    public function delete()
    {
        global $user;

        $this->db->begin();

        // Call trigger
        $result = $this->call_trigger('LINEBILL_DELETE', $user);
        if ($result < 0)
        {
            $this->db->rollback();
            return -1;
        }
        // End call triggers

        // extrafields
        $result = $this->deleteExtraFields();
        if ($result < 0)
        {
            $this->db->rollback();
            return -1;
        }

        $sql = "DELETE FROM ".MAIN_DB_PREFIX."semparpmp_decompteline WHERE rowid = ".$this->rowid;
        dol_syslog(get_class($this)."::delete", LOG_DEBUG);
        if ($this->db->query($sql))
        {
            $this->db->commit();
            return 1;
        } else {
            $this->error = $this->db->error()." sql=".$sql;
            $this->db->rollback();
            return -1;
        }
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     *	Update DB line fields total_xxx
     *	Used by migration
     *
     *	@return		int		<0 if KO, >0 if OK
     */
    public function update_total()
    {
        // phpcs:enable
        $this->db->begin();
        dol_syslog(get_class($this)."::update_total", LOG_DEBUG);


        // Mise a jour ligne en base
        $sql = "UPDATE ".MAIN_DB_PREFIX."semparpmp_decompteline SET";
        $sql .= " total_ht=".price2num($this->total_ht)."";
        $sql .= ",total_tva=".price2num($this->total_tva)."";
        $sql .= ",total_ttc=".price2num($this->total_ttc)."";
        $sql .= " WHERE rowid = ".$this->rowid;

        dol_syslog(get_class($this)."::update_total", LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql)
        {
            $this->db->commit();
            return 1;
        } else {
            $this->error = $this->db->error();
            $this->db->rollback();
            return -2;
        }
    }
}
