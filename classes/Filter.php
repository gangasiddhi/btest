<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7540 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class FilterCore extends ObjectModel
{
	/** @var int id_tag */
	public		$id;

	/** @var boolean Filter Status */
	public 		$active;
	
 	/** @var string Filter Position */
	public 		$postion;
        
        /** @var boolean Filter Position */
        public          $mode;
        
        public 		$name;
        
        public 		$description;

        /** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;
        
	//protected	$fieldsRequired = array('id_lang', 'name');
	//protected	$fieldsSize = array('name' => 32);
	protected	$fieldsValidate = array('active' => 'isBool', 'position' => 'isUnsignedInt');

	protected	$fieldsRequiredLang = array('name');
	protected	$fieldsSizeLang = array('name' => 32);
	protected	$fieldsValidateLang = array('id_lang' => 'isUnsignedId', 'name' => 'isGenericName', 'description' => 'isString');

	protected 	$table = 'filter';
	protected 	$identifier = 'id_filter';
	
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_filter'] = ($this->id);
		$fields['active'] = (int)($this->active);
                $fields['mode'] = (int)($this->mode);
		$fields['position'] = pSQL($this->postion);
                $fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}
        
        public function delete()
	{
            parent::delete();
            if($this->deleteTagsAttachedToFilter())
                return true;
            return false;
        }
        
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		$fields = array();
                $languages = Language::getLanguages(false);
		//$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
                foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = (int)($this->id);
                        $fields[$language['id_lang']]['name'] = (isset($this->name[$language['id_lang']])) ? pSQL($this->name[$language['id_lang']], true) : '';
			$fields[$language['id_lang']]['description'] = (isset($this->description[$language['id_lang']])) ? pSQL($this->description[$language['id_lang']], true) : '';
                }
                return $fields;
	}
        
        public static function getAllFilters($id_lang)
        {
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT *
            FROM `'._DB_PREFIX_.'filter` f
            LEFT JOIN  `'._DB_PREFIX_.'filter_lang` fl ON (fl.`id_filter` = f.`id_filter`)   
            WHERE  fl.id_lang = '.$id_lang.' ORDER BY f.`position`');
        }
        
        public static function getFiltersAndTags($id_lang)
        {
			//Don't Delete
            /*$result =  Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT f.id_filter as id_filter,f.mode as filter_mode, fl.name as filter_name, ft.id_tag as id_tag , t.name as tag_name
            FROM `'._DB_PREFIX_.'filter` f
            LEFT JOIN  `'._DB_PREFIX_.'filter_lang` fl ON (fl.`id_filter` = f.`id_filter`) 
            LEFT JOIN  `'._DB_PREFIX_.'filter_tags` ft ON (ft.`id_filter` = f.`id_filter` AND ft.id_lang = '.$id_lang.') 
            LEFT JOIN  `'._DB_PREFIX_.'tag` t ON (t.`id_tag` = ft.`id_tag`) 
            WHERE  fl.id_lang = '.$id_lang.' AND f.active = 1  ORDER BY f.`position`');*/
            
			
			$result =  Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT f.id_filter as id_filter,f.mode as filter_mode, fl.name as filter_name, ft.id_tag as id_tag , t.name as tag_name,COUNT(pt.id_tag) NumberOfProducts
			FROM `'._DB_PREFIX_.'filter` f
            LEFT JOIN  `'._DB_PREFIX_.'filter_lang` fl ON (fl.`id_filter` = f.`id_filter`) 
            LEFT JOIN  `'._DB_PREFIX_.'filter_tags` ft ON (ft.`id_filter` = f.`id_filter` AND ft.id_lang = '.$id_lang.') 
            LEFT JOIN  `'._DB_PREFIX_.'tag` t ON (t.`id_tag` = ft.`id_tag`) 
			LEFT JOIN  `'._DB_PREFIX_.'product_tag` pt ON (pt.id_tag = t.id_tag)
			WHERE  fl.id_lang = '.$id_lang.' AND f.active = 1
			GROUP BY pt.id_tag
			ORDER BY COUNT(pt.id_tag) DESC
			');
				
            if($result) 
            {    foreach($result as $res)
                {
                    $return[$res['id_filter']]['filter_name'] = $res['filter_name'];
                    $return[$res['id_filter']]['filter_mode'] = $res['filter_mode'];
                    $return[$res['id_filter']]['tags'][$res['id_tag']] = $res['tag_name'];
                }
                return $return;
            }
            return false;
        }
        
        public function addTagsToFilter($tags, $id_lang)
        {
            if(!is_array($tags))
                return false;
            $data = '';
            foreach ($tags AS $id_tag)
              $data .= '('.(int)($this->id).','.(int)($id_tag).','.$id_lang.'),';;
            $data = rtrim($data, ',');
            return Db::getInstance()->Execute('
                INSERT INTO `'._DB_PREFIX_.'filter_tags` (`id_filter`, `id_tag`, id_lang) 
                VALUES '.$data);
        }
        
        public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->ExecuteS('
			SELECT `id_filter`, `position`
			FROM `'._DB_PREFIX_.'filter` 
			ORDER BY `position` ASC'
		))
			return false;

		foreach ($res AS $filter)
			if ((int)($filter['id_filter']) == (int)($this->id))
				$movedFilter = $filter;

		if (!isset($movedFilter) || !isset($position))
			return false;

		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
                echo 'UPDATE `'._DB_PREFIX_.'filter`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)($movedFilter['position']).' AND `position` <= '.(int)($position)
				: '< '.(int)($movedFilter['position']).' AND `position` >= '.(int)($position)).'
			';
		return (Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'filter`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)($movedFilter['position']).' AND `position` <= '.(int)($position)
				: '< '.(int)($movedFilter['position']).' AND `position` >= '.(int)($position)).'
			')
		AND Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'filter`
			SET `position` = '.(int)($position).'
			WHERE `id_filter` = '.(int)($movedFilter['id_filter']).'
			'));
	}
        
        public function getTagsAvailableForFilter()
        {
            $tagIds = $this->getTagsAttachedToFilter(true);
           //echo 'gfgf=='.$tagIds ;
            $result =  Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT *
            FROM `'._DB_PREFIX_.'tag` 
            WHERE `id_tag` NOT IN ('.$tagIds.')');
//           echo "<pre>"; print_R($result);echo "</pre>";
            if($result)
            {    
                foreach($result as $res)
                {
                    $return[$res['id_lang']][$res['id_tag']] = $res['name'];
                }
            //echo "<pre>"; print_R($return);echo "</pre>";
                return $return;
            }
            return false;
        }
         
        public function getTagsAttachedToFilter($list=false)
        {
            $result =  Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT *
            FROM `'._DB_PREFIX_.'filter_tags` ft
            LEFT JOIN  `'._DB_PREFIX_.'tag` t ON (t.`id_tag` = ft.`id_tag`)
            WHERE  ft.`id_filter` = '.$this->id);
            $tagIds ='';
            if($list && $result)
            {
                foreach($result as $res)
                {
                    $tagIds .= $res['id_tag'].',';
                }
                $tagIds = rtrim($tagIds, ',');
                return $tagIds;
            }
            if($result)
            {   
                foreach($result as $res)
                {
                    $return[$res['id_lang']][] = $res;
                }
                return $return;
            }
            return false;
            
        }
        
        public function  deleteTagsAttachedToFilter()
        {
            return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'filter_tags` WHERE `id_filter` = '.$this->id);
        }
}


