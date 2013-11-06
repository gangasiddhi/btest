<?php
/**
  * Import tab for admin panel, AdminImport.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
//include_once(PS_ADMIN_DIR.'/../classes/ExcelReader.php');

@ini_set('max_execution_time', 0);
define('MAX_LINE_SIZE', 4096);

define('UNFRIENDLY_ERROR', false); // Used for validatefields diying without user friendly error or not

// this value set the number of columns visible on each page
define('MAX_COLUMNS', 6);
// correct Mac error on eof
define('MAX_File_Size', 4000000);
@ini_set('auto_detect_line_endings', '1');

class AdminExport extends AdminTab
{
    public function __construct()
    {
        parent::__construct();
    }

    public function postProcess()
    {
        global $currentIndex;
        if (Tools::isSubmit('submitFile'))
        {
            if (!isset($_FILES['file']['tmp_name']) OR empty($_FILES['file']['tmp_name']))
				$this->_errors[] = $this->l('no file selected');
            elseif (!file_exists($_FILES['file']['tmp_name']) OR !@rename($_FILES['file']['tmp_name'], dirname(__FILE__).'/../import/shipping.csv'))
                $this->_errors[] = $this->l('Error move uploaded file');
            elseif ($_FILES['file']['size'] > 4000000)
    		    return Tools::displayError('file is too large').' ('.($file['size'] / 1000).Tools::displayError('KB').'). '.Tools::displayError('Maximum allowed:').' '.($maxFileSize / 1000).Tools::displayError('KB');
            else
            {
				Tools::redirectAdmin($currentIndex.'&token='.Tools::getValue('token').'&conf=18');
            }
        }
        parent::postProcess();
    }

    public function display()
    {
        if (!Tools::isSubmit('submitFile'))
            $this->displayForm();
    }

    public function displayForm($isMainTab = true)
    {
          global $currentIndex;
          parent::displayForm();

          echo '
                      <fieldset>
                         <legend><img src="../img/admin/export.gif" />'.$this->l('Export products to XML format').'</legend>
                         <a href="xml.php?products_xml">'.$this->l('Click To Export Product XML Format').'</a>
                      </fieldset>
                      <fieldset>
                         <legend><img src="../img/admin/export.gif" />'.$this->l('Export products to XML format').'</legend>
                          <form action="'.$currentIndex.'&token='.$this->token.'" method="POST" enctype="multipart/form-data">
                          <label class="clear">'.$this->l('Select a file').' </label>
                          <div class="margin-form">
                                <input name="file" type="file" /><br />
                          </div>
                          <div class="margin-form">
                                <input type="submit" name="submitFile" value="'.$this->l('Upload').'" class="button" />
                                <a href="xml.php?shipment_xml" style="margin:0 0 0 20px"> '.$this->l('Click To  Get Shipment XML Format').'</a>
                          </div>
                          </form>
                      </fieldset>';
    }

}
?>
