<?php 
function xml_node_to_array($node, $protect ="")
{
	if($node->hasChildNodes())
	{
		$result = array();
		foreach($node->childNodes as $subnode)
		{
			if($subnode->localName != "")
			{
				$result[trim($subnode->localName,$protect)] = xml_node_to_array($subnode,$protect);
				//echo($subnode->localName."<br/>");
			}
		}
		if(count($result)>0)
			return $result;
	}
	//echo($node->nodeValue."<br/>");
	return trim($node->nodeValue,$protect);
}

function thing_to_xml_node($thing,$node, $doc, $protect = "", $fields = NULL)
{
	if(is_object($thing) || is_array($thing))
	{
		foreach($thing as $field => $value)
		{
			if($fields == NULL || in_array($field,$fields))
			{
				if($field != "")
				{
					$subnode = $doc->createElement($protect.$field);
					thing_to_xml_node($value,$subnode,$doc, $protect, $fields);
					$node->appendChild($subnode);
				}
				else
				{
					$node->nodeValue = filt($value);
				}
			}
		}
	}
	else
	{
		$node->nodeValue = filt($thing);
	}
}

function filt($val)
{
	return str_replace(array('&','"',"'","&#13;","\r"),array('','','','',''),stripInvalidXml(remove_br($val)));
}

function normalizeField($data)
{
	return htmlspecialchars_decode(htmlentities(stripInvalidXml($data)), ENT_NOQUOTES);
}

/**
 * Removes invalid XML
 *
 * @access public
 * @param string $value
 * @return string
 */
function stripInvalidXml($value)
{
	$ret = "";
	$current;
	if (empty($value))
	{
		return $ret;
	}

	$length = strlen($value);
	for ($i=0; $i < $length; $i++)
	{
		$current = ord($value{$i});
		
		if (($current == 0x9) ||
				($current == 0xA) ||
				($current == 0xD) ||
				(($current >= 0x20) && ($current <= 0xD7FF)) ||
				(($current >= 0xE000) && ($current <= 0xFFFD)) ||
				(($current >= 0x10000) && ($current <= 0x10FFFF)))
		{
			$ret .= chr($current);
		}
		else
		{
			$ret .= " ";
		}
	}
	return $ret;
}

?>