<?php

/*
 * Description of Group
 *
 * @author Avish Websoft Pvt Ltd (Gangadhar K.M)
 */

class Group extends GroupCore
{
	/* Returns the  group name
	 * @param string $group_name, group name
	 * @param integer $id_lang , Language id
	 */

	public function getGroupIdByName($group_name, $id_lang)
	{
		$row = Db::getInstance()->getRow('
    		SELECT gl.`id_group`
    		FROM ' . _DB_PREFIX_ . 'group_lang gl
    		WHERE gl.`name` LIKE "' . ($group_name) . '" AND gl.`id_lang` =' . $id_lang
		);

		return $row['id_group'];
	}

	/* Returns the  group name
	 * @param string $groupId, group Id
	 * @param integer $id_lang , Language id
	 */

	public function getGroupNameById($groupId, $id_lang)
	{
		$row = Db::getInstance()->getRow('
    		SELECT gl.`name`
    		FROM ' . _DB_PREFIX_ . 'group_lang gl
    		WHERE gl.`id_group` = ' . ($groupId) . ' AND gl.`id_lang` =' . $id_lang
		);

		return $row['name'];
	}
	
	/* Remove all the customers form the given group.
	 * @param $group_id (Int), group id .
	 */

	public function deleteAllCustomersFromGroup($group_id)
	{
		return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_group` WHERE `id_group` = ' . (int) ($group_id));
	}

}

?>
