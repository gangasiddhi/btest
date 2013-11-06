<?php
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

global $cookie;

if (isset($_GET['AddNewTag']) && $_GET['AddNewTag'] == 1)
{
    if($_GET['name'] && $_GET['id_lang'])
    {
        $result = Tag::addSingleTag($_GET['name'], $_GET['id_lang']);
        if(is_array($result))
        {
            if($cookie->id_lang == $_GET['id_lang'])
                die('{"tagId" : '.$result['id'].', "tagName" : "'.$result['name'].'"}');
            else
                die(true);
        }
        else
            die('{"hasError" : '.$result.'}');
            //die($result);
    }
    else 
    {
         die('{"hasError" : "Name and Language field empty"}');
    }
}

if (Tools::getIsset('AddNewFilter') && Tools::getValue('AddNewFilter'))
{
      if(Tools::getValue('name')) 
      {
          $filter = new TagsFilter();
          $filter->id_lang = Tools::getValue('id_lang');
          $filter->name = Tools::getValue('name');
          
          if($filter->add())
          {
              die(true);
          }
          else
          {
              die(false);
          }
      }
}

?>
