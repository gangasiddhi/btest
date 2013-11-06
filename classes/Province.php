<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Province
 *
 * @author avish
 */
class Province extends ObjectModel  {

	/** @var integer Country id which state belongs */
	public 		$id_state;
	
	/** @var string Name */
	public 		$name;

 	protected 	$fieldsRequired = array('id_state', 'name');
 	protected 	$fieldsSize = array('id_state' => 10, 'name' => 32);
 	protected 	$fieldsValidate = array('id_state' => 'isUnsignedId', 'name' => 'isGenericName');

	protected 	$table = 'province';
	protected 	$identifier = 'id_province';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_state'] = intval($this->id_state);
		$fields['name'] = pSQL($this->name);
		return $fields;
	}

	/**
	* Get a province name with its ID
	*
	* @param integer $id_province Country ID
	* @return string Province name
	*/
	static public function getProvinceNameById($id_province)
    {
	    $result = Db::getInstance()->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'province`
		WHERE `id_province` = '.intval($id_province));

        return $result['name'];
    }
}
?>
