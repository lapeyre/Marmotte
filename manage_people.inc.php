<?php 

require_once('config.inc.php');
require_once('manage_sessions.inc.php');

/*
 function generateKey($annee, $nom,$prenom)
 {
return mb_strtolower(replace_accents(trim($annee.$nom.$prenom," '-")));
}
*/
function candidateExists($nom,$prenom)
{

	$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'";';

	$result = sql_request($sql);
	return	(mysql_num_rows($result) > 0);
}

function normalizeCandidat($data)
{
	global $candidat_prototypes;

	$data2 = (object) $data;

	if(!isset($data2->nom))
		$data2->nom = "";
	if(!isset($data2->prenom))
		$data2->prenom = "";

	foreach($candidat_prototypes as $field => $value)
		if(isset($data2->$field))
		if($data2->$field=="")
		$data2->$field = $value;

	return $data2;
}

function is_classe($report)
{
	return is_numeric($report->avis);
}

function is_auditionne($report)
{
	return is_classe($report) || $report->avis=="oral" || $report->avis="nonclasse";
}

function is_auditionneCR($report)
{
	global $concours_ouverts;
	return (strlen($report->concours)>=1 && substr($concours_ouverts[$report->concours],0,2)=="CR")
	&&(is_classe($report) || $report->avis=="oral" || $report->avis="nonclasse");
}


function updateCandidateFromRequest($request, $oldannee="")
{
	//rrr();
	global $fieldsIndividualAll;

	$data = (object) array();


	foreach($fieldsIndividualAll as  $field => $value)
		if (isset($request["field".$field]))
		$data->$field = nl2br(trim($request["field".$field]),true);

	$candidate = updateCandidateFromData($data);
	
	if(isset($request['previousnom']) && isset($request['previousprenom']) && ($request['previousnom']!= "" || $request['previousprenom'] != "") )
	{
		if(mysql_real_escape_string($request['previousnom']) != $candidate->nom || mysql_real_escape_string($request['previousprenom']) != $candidate->prenom)
		{
			$sql = "UPDATE ".reports_db." SET nom=\"".$candidate->nom."\", prenom=\"".$candidate->prenom."\" WHERE nom =\"".mysql_real_escape_string($request['previousnom'])."\" AND prenom=\"".mysql_real_escape_string($request['previousprenom'])."\"";
			sql_request($sql);
			$sql = "DELETE FROM ".people_db." WHERE nom =\"".mysql_real_escape_string($request['previousnom'])."\" AND prenom=\"".mysql_real_escape_string($request['previousprenom'])."\"";
			sql_request($sql);
		}
	}
	
	return $candidate;
}

function updateCandidateFromData($data)
{
	global $fieldsIndividualAll;
	
	$candidate = get_or_create_candidate($data );

	$sqlcore = "";

	$first = true;
	foreach($data as  $field => $value)
	{
		if(key_exists($field, $fieldsIndividualAll))
		{
			$sqlcore.=$first ? "" : ",";
			$sqlcore.=$field.'="'.mysql_real_escape_string($value).'" ';
			$first = false;
		}
	}
	$sql = "UPDATE ".people_db." SET ".$sqlcore." WHERE nom=\"".$data->nom."\" AND prenom=\"".$data->prenom."\";";

	sql_request($sql);

	return get_or_create_candidate($data );



}

function getAllCandidates()
{
	$sql = "SELECT * FROM ".people_db.";";
	$result=mysql_query($sql);
	if($result == false)
		throw new Exception("Failed to process sql query ".$sql);
	$rows = array();

	while ($row = mysql_fetch_object($result))
		$rows[] = $row;

	return $rows;
}

function annee_from_data($data, $pref = "")
{
	$annee = session_year(current_session_id());

	$champ1 = $pref."anneecandidature";
	$champ2 = $pref."annee_recrutement";
	if(isset($data->$champ1))
		$annee = $data->$champ1;
	else if(isset($data->$champ2))
		$annee = $data->$champ2;

	return $annee;
}

function add_candidate_to_database($data)
{
	global $fieldsIndividualAll;

	$sqlvalues = "";
	$sqlfields = "";
	$first = true;

	global $empty_individual;

	foreach($fieldsIndividualAll as $field => $desc)
	{
		$sqlfields .= ($first ? "" : ",") .$field;
		$sqlvalues .= ($first ? "" : ",") .'"'.(isset($data->$field) ? $data->$field : $empty_individual[$field]).'"';
		$first = false;
	}


	$sql = "INSERT INTO ".people_db." ($sqlfields) VALUES ($sqlvalues);";
	sql_request($sql);

	$sql2 = 'SELECT * FROM '.people_db.' WHERE nom="'.$data->nom.'" AND prenom="'.$data->prenom.'";';
	$result = sql_request($sql2);
	$candidate = mysql_fetch_object($result);
	if($candidate == false)
	{
		throw new Exception("Failed to add candidate with request <br/>".$sql2);
	}

	return $candidate;

}

/*
 * This function will always return a candidate,
* created if needed,
* or throw an exception
*/
function get_or_create_candidate_from_nom($nom, $prenom)
{
	try
	{

		mysql_query("LOCK TABLES ".people_db." WRITE;");


		$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'" ;';

		$result = sql_request($sql);

		$cdata = mysql_fetch_object($result);
		if($cdata == false)
		{
			$data = (object) array();
			$data->nom = $nom;
			$data->prenom = $prenom;
			add_candidate_to_database($data);
			$result = sql_request($sql);
			$cdata = mysql_fetch_object($result);
			if($cdata == false)
				throw new Exception("Failed to find candidate previously added<br/>".$sql);
		}

		mysql_query("UNLOCK TABLES");
		return normalizeCandidat($cdata);
	}
	catch(Exception $exc)
	{
		mysql_query("UNLOCK TABLES;");
		throw new Exception("Failed to add candidate from report:<br/>".$exc->getMessage());
	}
}

function get_or_create_candidate($data)
{
	$data = normalizeCandidat($data);

	return get_or_create_candidate_from_nom($data->nom,$data->prenom);
}

function change_candidate_property($annee,$nom,$prenom, $property_name, $newvalue)
{

	$data = (object) array($property_name => $newvalue);

	change_candidate_properties($annee,$nom,$prenom, $data);
}

function change_candidate_properties($annee,$nom,$prenom, $data)
{
	$data = (object) $data;
	$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'";';
	$result = sql_request($sql);

	$candidate = mysql_fetch_object($result);
	if($candidate == false)
	{
		$data = (object) array();
		$data->nom = $nom;
		$data->prenom = $prenom;
		$data->anneecandidature = $annee;
		$candidate = get_or_create_candidate($data);
	}

	foreach($data as $property_name => $newvalue)
		if(!property_exists($candidate,$property_name))
		throw new Exception("No property '".$property_name."' in candidate object");

	$sqlcore = "";
	$first = true;

	$sql = "UPDATE ".people_db." SET ";
	foreach($candidate as  $field => $value)
	{
		if (isset($candidate->$field) && isset($data->$field))
		{
			$sql .=$first ? "" : ",";
			$sql .= " ".$field.'="'.mysql_real_escape_string(trim($data->$field)).'" ';
			$first = false;
		}
	}

	$sql .= ' WHERE nom="'.$candidate->nom.'" AND prenom="'.$candidate->prenom.'";';

	sql_request($sql);

}

function is_associated_directory($candidate, $directory)
{
	return ($candidate->nom == "" || strpos(norm_name($directory), norm_name($candidate->nom) ) != false ) && ( $candidate->prenom == "" || strpos(norm_name($directory), norm_name($candidate->prenom) )  != false );
}

function get_people_directory($candidate, $session, $create_directory_if_nexists = false)
{
	global $dossiers_candidats;
	$basedir = $dossiers_candidats."/".$session."/".$candidate->nom."_".$candidate->prenom."/";


	if($create_directory_if_nexists && !is_dir($basedir))
	{
		echo "Creating directory ".$basedir."<br/>";
		$result = mkdir($basedir,0700, true);
		if(!$result)
			echo "Failed to create directory ".$basedir."<br/>";
	}
	
	return $basedir;
}

function find_people_files($candidate, $force, $session, $create_directory_if_nexists = false, $directories = NULL)
{
	global $dossiers_candidats;
	if($candidate->nom == "" && $candidate->prenom == "")
		return array();

	$basedir = get_people_directory($candidate, $session, false);
	
	if($force && !is_dir($basedir))
	{
		if($directories == NULL)
			$directories = get_directories_list($session);
		foreach($directories as $directory)
		{
			if( is_associated_directory($candidate, $directory) )
			{
				echo "Renaming '".$directory . "' to '". $basedir."'<br/>";
				rename($directory,$basedir);
				/*
				$dir = str_replace($dossiers_candidats,"",$directory);
				echo "Changing candidate dir for ".$directory." <br/>";
				change_candidate_property($candidate->anneecandidature, $candidate->nom, $candidate->prenom, $fieldID, $dir);
				$basedir = $directory;
				$candidate->$fieldID = $directory;
				*/
				break;
			}
		}
		//echo "No directory found for ".$candidate->nom." ".$candidate->nom." <br/>";
		//change_candidate_property($candidate->anneecandidature, $candidate->nom, $candidate->prenom, $fieldID, "");
	}

	$basedir = get_people_directory($candidate, $session, $create_directory_if_nexists);
	
	if ( is_dir($basedir) )
	{
		$handle = opendir($basedir);
		if($handle != false)
		{
			$files = array();
			while(1)
			{
				$file = readdir($handle);
				if($file === false)
					break;
				if($file != "." && $file != "..")
				{
					$filenames[] = $file;
					foreach($filenames as $file)
					{
						$timestamp = filemtime($basedir."/".$file);
						if($timestamp != false)
							$files[date("d/m/Y - h:i:s",$timestamp).$file]=$file;
					}
				}
			}
			closedir($handle);

			return $files;
		}
	}
	else
		echo "No directory found<br/>";


	return array();
}

function get_directories_list($session)
{
	global $dossiers_candidats;

	$directories = array();
	$files = glob($dossiers_candidats . "/".$session."/*" );

	foreach($files as $file)
	{
		if(is_dir($file))
		{
			$directories[]= $file;
		}
	}

	return $directories;
}

function link_files_to_candidates()
{
	throw string("Not implemented anymore");

	/*global $dossiers_candidats;

	echo "Linking files to candidates<br/>";

	$candidates = getAllCandidates();

	$directories = get_directories_list();

	$nb = 0;
	foreach($candidates as $candidate)
	{
		echo "Linking files to candidate ".$candidate->nom." <br/>";
		try
		{
			find_people_files($candidate, true, current_session(), $false, $directories);
			$nb++;
		}
		catch(Exception $e)
		{
			echo $e."<br/>";
		}
	}
	echo "Found files for ".$nb. "/".count($candidates)."  candidates<br/>";
	*/
}

function norm_name($nom)
{
	$nom = replace_accents($nom);
	return strtoupper(str_replace(array(" ","'","-"), array("_","_","_"),$nom));
}



?>