<?php
    
    require_once(realpath(dirname(__FILE__).'/../../').'/config/config.inc.php');
    $dir_name = $_SERVER['DOCUMENT_ROOT'].'/'._MODULE_DIR_.basename(dirname(__FILE__)).'/slides/';

    $id = intval(Tools::getValue('id'));
    $path = "slides/";
    $dir_handle = @opendir($path)or die("Unable to open folder");
    
    //List files in images directory
    //$file_count=0;
    while (false !== ($file = readdir($dir_handle)))
      {
        if($file == 'slide_0'.$id.'.jpg'){
             unlink($path.$file)or die("Unable to delete file");
        }
        
        /*$old_file=$path.$file;
        $new_file=$path.'slide_0'.$file_count.'.jpg';        
        rename($old_file, $new_file); 
        $file_count++;*/
      }
      closedir($path);
      //sleep(5);
      $num_files = count(glob($dir_name.'*.jpg'));
      $file_count=0;
      $dir_handle1 = @opendir($path)or die("Unable to open folder");
      
        while (false !== ($file1 = readdir($dir_handle1)))
           {
            if($num_files>$file_count){
                if($file1 !='.' && $file1 !='..'){
                    $old_file=$path.$file1;
                    $new_file=$path.'slide_0'.$file_count.'.jpg';        
                    rename($old_file, $new_file) or die("Unable to rename old file=".$old_file ."==new file==".$new_file);
                    $file_count++;
                }
            }
            
           }
       echo 'out='.$num_files .'=c='.$file_count.'==newC='.$count;
    //echo $count;
    closedir($path);

   // $module = new $class_name();
   // echo $module->_getFormItem(intval($id), true);