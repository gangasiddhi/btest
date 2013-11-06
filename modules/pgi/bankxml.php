<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
/*if ($xml = simplexml_load_file(dirname(__FILE__).'/bank.xml'))
	{ $i = 1;
		global $cookie, $smarty;
		$xml_data = array();
		foreach($xml->children() as $child)
		  {
			if($child->getName() == 'bank'.$i)
			{
				$xml_data[$xml->getName()][$child->getName()] = $child ;
			}
			$i++;
		  }
		  foreach($xml_data as $xml_dat)
		  {
				$data = array();
				$data[] = $xml_dat;
		  }
		  print_r($xml_data);
		  echo "<br/>";
		  echo "<br/>";
		  echo "<br/>";
		   print_r($data);
		
	}*/
	$xmlfile = dirname(__FILE__).'/bank.xml';
	$xmlparser = xml_parser_create();

	// open a file and read data
	$fp = fopen($xmlfile, 'r');
	$xmldata = fread($fp, 4096);

	xml_parse_into_struct($xmlparser,$xmldata,$values);

	xml_parser_free($xmlparser);
	print_r($values);
	 echo "<br/>";
		  echo "<br/>";
		  echo "<br/>";
	$data = array();
	$i = 1;
	foreach ($values as $value)
	{
			if($value[tag] == 'BANK_ID')
				$bankid = $value[value];
			if($value[tag] == 'BANK_ID')
				continue;
			if($value[type] == 'complete')
			{
				if($value[tag] == 'CARDID' || $value[tag] == 'CARDIMAGE')
				{
					if($value[tag] == 'CARDID')
						$cardid = $value[value];
					$data['banks'][$bankid][cards][$cardid][strtolower($value[tag])] = $value[value];
				}
				else
					$data['banks'][$bankid][strtolower($value[tag])] = $value[value];
			}
	}
		print_r($data);
?>
