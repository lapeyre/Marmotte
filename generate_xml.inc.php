<?php 

require_once('manage_sessions.inc.php');
require_once('manage_unites.inc.php');

function getReportsAsXMLArray($filter_values, $sort_criteria)
{
	global $fieldsAll;
	$rows = filterSortReports($filter_values, $sort_criteria);

	//to map id_session s to session nicknames
	$sessions = sessionArrays();
	$units = unitsList();

	$docs = array();
	foreach($rows as $row)
		$docs[] = rowToXMLDoc($row, $sessions,$units);
		
	return $docs;
}

function implode_with_keys($assoc,$inglue=':',$outglue=','){
    $res=array();
    foreach($assoc as $tk=>$tv){
        $res[] = $tk.$inglue.$tv;
    }
    return implode($outglue,$res);
}

function getReportsAsXML($filter_values, $sort_criteria = "")
{
	global $fieldsAll;
	global $filters;
	
	$doc = new DOMDocument();
	$root = $doc->createElement("rapports");
	$rows = filterSortReports($filter_values, $sort_criteria);
	
	if(isset($filter_values['id_edit']))
		$root->setAttribute('id_edit',$filter_values['id_edit']);
		
	//foreach($filters as $filter => $data)
		//$root->setAttribute($filter,$filter_values[$filter]);

	//to map id_session s to session nicknames
	$sessions = sessionArrays();
	$units = unitsList();

	foreach($rows as $row)
	{
		$elem = createXMLReportElem($row, $sessions,$units,$doc);
		$root->appendChild($elem);
	}

	$doc->appendChild($root);
	return $doc;
}


function appendLeaf($fieldname, $fieldvalue, DOMDocument $doc, DOMElement $node)
{
	$leaf = $doc->createElement($fieldname);
	$leaf->appendChild($doc->createCDATASection ($fieldvalue));
	$node->appendChild($leaf);
}


function EnteteDroit($row, $units)
{
	global $enTetesDroit;
	global $typesRapportsToEnteteDroit;
	global $avis_classement;
	global $avis_candidature;
	
	$result = "";
	
	if( ($row->type == "Promotion") && ($row->grade != "CR2"))
	{
		$result = $enTetesDroit['PromotionDR'];
		$result .= $avis_classement[$row->avis].'<br/>';
		$result .= $row->nom." ".$row->prenom.'<br/>';
		if(array_key_exists($row->unite,$units))
			$result .= " ".$row->unite." (".$units[$row->unite]->nickname.")";
		else
			$result .= " ".$row->unite;
		return $result;
	}

	if(array_key_exists($row->type, $typesRapportsToEnteteDroit))
	{
		$type = $typesRapportsToEnteteDroit[$row->type];
		if(array_key_exists($type, $enTetesDroit))
		{
			$result = $enTetesDroit[$type];
			if($type == 'Individu')
			{
				$result .= $row->nom." ".$row->prenom."<br/>";
				if(array_key_exists($row->unite,$units))
					$result .= " ".$row->unite." (".$units[$row->unite]->nickname.")";
				else
					$result .= " ".$row->unite;
			}
			else if($type == 'Concours')
			{
				$result .= $avis_candidature[$row->avis];
				$result .= "<br/>";
				$result .= $row->nom." ".$row->prenom;
			}
			else if($type == 'Unite')
			{
				if(array_key_exists($row->unite,$units))
				{
					$unit = $units[$row->unite];
					$result .= " ".$row->unite." (".$unit->nickname.")<br/>".$unit->directeur;
				}
				else
				{
					$result .= " ".$row->unite;
				}
			}
			else if($type == 'Ecole')
			{
				$unit = $row->unite;
				if(array_key_exists($row->unite,$units))
					$unit = $units[$row->unite]->nickname;
				$result .= $row->ecole." "."<br/>".$row->prenom." ".$row->nom." (".$unit.") ";
			}
			return $result;
		}
	}
	return "";
}

function EnteteGauche($row)
{
	global $typesRapportsToEnteteGauche;
	$result = $typesRapportsToEnteteGauche[$row->type];
	if($row->type == "Promotion")
	{
		$oldgrade = $row->grade;
		$newgrade = "CR1";
		if( $oldgrade == "DR2")
			$newgrade = "DR1";
		if( $oldgrade == "DR1")
			$newgrade = "DRCE1";
		if( $oldgrade == "DRCE1")
			$newgrade = "DRCE2";
		$result .= "<br/>".$newgrade;
	}
	if($row->type == "Candidature")
	{
		$result .= "<br/>".$row->concours;
	}
	return $result;
}

function createXMLReportElem($row, $sessions, $units, DOMDocument $doc)
{
	global $fieldsAll;
	global $typesRapports;
	global $typesRapportsToEnteteGauche;
	global $enTetesDroit;
	global $typesRapportsToEnteteDroit;
	global $typesRapportsToCheckboxes;
	global $typesRapportsToCheckboxesTitles;
	global $typesRapportsToFormula;
	
	if(!$sessions)
		$sessions = sessionArrays();

	if(!$units)
		$units = unitsList();


	$rapportElem = $doc->createElement("rapport");

	$rapportElem->setAttribute("id",$row->id);
	$rapportElem->setAttribute("id_origine",$row->id_origine);


	$fieldsspecial = array('unite','date','type');

	//On ajoute une formule à la fin du rapport si nécessaire
	if(array_key_exists($row->type,$typesRapportsToFormula))
	{
		$formulas = $typesRapportsToFormula[$row->type];
		
		if(array_key_exists($row->avis,$formulas))
		{
			$formula = $formulas[$row->avis];
			$row->rapport .= "<br/><br/>".$formula;
			
		}
	}
	
	
	//On ajoute tous les fields pas spéciaux
	foreach ($fieldsAll as $fieldID => $title)
		if(!in_array($fieldID,$fieldsspecial))
		appendLeaf($fieldID, $row->$fieldID, $doc, $rapportElem);


	//On ajoute le type du rapport et le pretty print
	appendLeaf("type", $row->type, $doc, $rapportElem);

	//On ajoute les entete gauche et droit
	appendLeaf("entetegauche", EnteteGauche($row), $doc, $rapportElem);
	appendLeaf("entetedroit", EnteteDroit($row,$units), $doc, $rapportElem);


	//On ajoute le nickname du labo
	$value = "";
	if(array_key_exists($row->unite,$units))
		$value = $row->unite." (".$units[$row->unite]->nickname.")";
	else
		$value = $row->unite;
	appendLeaf("unite", $value, $doc, $rapportElem);

	//On ajoute la date du jour
	appendLeaf("date", date("j/n/Y",strtotime($row->date)), $doc, $rapportElem);

	

	//On ajoute les cases à choix multiple, si nécessaires
	if(array_key_exists($row->type,$typesRapportsToCheckboxes))
	{
		$checkBoxes = $typesRapportsToCheckboxes[$row->type];
		$checkBoxesTitle = $typesRapportsToCheckboxesTitles[$row->type];

		$leaf = $doc->createElement("checkboxes");
		$leaf->setAttribute("titre", $checkBoxesTitle);

		foreach($checkBoxes as $avis => $intitule)
		{
			$subleaf = $doc->createElement("checkbox");
			$subleaf->appendChild($doc->createCDATASection ($intitule));
			$subleaf->setAttribute("mark", ($avis == $row->avis) ? "checked" : "unchecked");
			$leaf->appendChild($subleaf);
		}

		$rapportElem->appendChild($leaf);
	}


	//On ajoute le nickname de la session
	appendLeaf("session", $sessions[$row->id_session], $doc, $rapportElem);


	//On ajoute le numero de la section
	appendLeaf("section_nb", section_nb, $doc, $rapportElem);

	//On ajoute l'intitulé de la section
	appendLeaf("section_intitule", section_intitule, $doc, $rapportElem);

	//On ajoute le nom et le tire du signataire
	appendLeaf("signataire", president, $doc, $rapportElem);
	appendLeaf("signataire_titre", president_titre, $doc, $rapportElem);

	return $rapportElem;

}

function rowToXMLDoc($row, $sessions = null, $units = null)
{
	$doc = new DOMDocument();
	$elem = createXMLReportElem($row, $sessions, $units, $doc);
	$doc->appendChild($elem);
	return $doc;
}

function XMLToHTML(DOMDocument $doc)
{
	$xsl = new DOMDocument();
	$xsl->load('xslt/html2.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);
	$html = $proc->transformToXML($doc);
	return $html;
}

//Returns the name of the file
function filename_from_node(DOMNode $node)
{
	global $typesRapportsUnites;

	$nom = "";
	$prenom = "";
	$grade = "";
	$unite = "";
	$type = "";
	$session = "Session";
	$avis = "";

	foreach($node->childNodes as $child)
	{
		switch($child->nodeName)
		{
			case "nom": $nom = mb_convert_case(replace_accents($child->nodeValue), MB_CASE_TITLE); break;
			case "prenom": $prenom = mb_convert_case(replace_accents($child->nodeValue), MB_CASE_TITLE); break;
			case "grade": $grade = $child->nodeValue; break;
			case "unite": $unite = $child->nodeValue; break;
			case "type": $type = $child->nodeValue; break;
			case "session": $session = $child->nodeValue; break;
			case "avis": $avis = $child->nodeValue; break;
		}
	}

	if($type == "Promotion")
	{
		switch($grade)
		{
			case "CR2": $grade = "CR1"; break;
			case "DR2": $grade = "DR1"; break;
			case "DR1": $grade = "DRCE1"; break;
			case "DRCE1": $grade = "DRCE2"; break;
		}
		$grade .= " - ".$avis;
	}

	if($type == "Evaluation-Vague" || $type == "Evaluation-MiVague")
		$type .=  " - ".mb_convert_case($avis,MB_CASE_TITLE);
	
	if(array_key_exists($type,$typesRapportsUnites))
		return $session." - ".$type." - ".$unite;
	else
		return $session." - ".$type." - ".$grade." - ".$nom."_".$prenom;
}

?>