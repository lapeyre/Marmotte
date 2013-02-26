<?php 
require_once('config.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');


function displayImport()
{
	global $typeImports;

	?>
<form enctype="multipart/form-data" action="index.php" method="post">
<table>

<tr><td>
	<input type="hidden" name="type" value="evaluations"></input>
	<input	type="hidden" name="action" value="upload" />
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
		</td></tr><tr><td> <input name="uploadedfile"
		type="file" /> <br /> <input type="submit" value="Importer" />
		</td></tr>
	</table>
</form>

<?php 

}

function displaySecretaryImport()
{
	if(isSecretaire())
{
?>
<form enctype="multipart/form-data" action="index.php" method="post">
<table>
<tr><td>
		Type<select name="subtype">
			<?php
			global $typesRapports;
			foreach ($typesRapports as $ty => $value)
				echo "<option value=\"$ty\">".$value."</option>\n";
			?>
		</select>
		</td></tr>

<tr><td>
	<input type="hidden" name="type" value="evaluations"></input>
	<input	type="hidden" name="action" value="upload" />
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
		</td></tr><tr><td> <input name="uploadedfile"
		type="file" /> <br /> <input type="submit" value="Importer" />
		</td></tr>
	</table>
</form>
		<?php 
}
		
}

function displayExport()
{
	global $typeExports;

	echo "<ul>";

	foreach($typeExports as $idexp => $exp)
	{
		$expname= $exp["name"];
		$level = $exp["permissionlevel"];
		if (getUserPermissionLevel()>=$level)
		{
			echo "<li><a href=\"export.php?action=export&amp;type=$idexp\">";
			//echo "<img class=\"icon\" width=\"40\" height=\"40\" src=\"img/$idexp-icon-50px.png\" alt=\"$expname\"/></a>";
			echo "$expname</a></li>";
		}
	}
	echo "</ul>";
}
?>


<h2>Export</h2>
<p>Ce menu permet d'exporter l'ensemble des rapports de la sélection en
	cours dans différents formats. Pour une édition des rapports
	hors-ligne, choisir le format "csv".</p>

<?php displayExport();?>
	<hr/>
<h2>Import</h2>
<p>
Le formulaire suivant vous permet d'importer un rapport édité offline.
</p>
<?php 
	displaySecretaryImport();
	?>

	
<?php 
if(isSecretaire())
{
	?>
	<hr/>
	
	<h2>Import de rapports vierges</h2>
	
<p>
	Le formulaire ci-dessous permet d'injecter plusieurs rapports dans Marmotte, en partant d'un fichier
	excel fourni par le SGCN.<br /> Ces rapports pourront ensuite être édités en ligne par les rapporteurs.<br /><br />
	
	La procédure est la suivante.
		</p>
	
	<ul>
	<li>Choisissez les champs à importer puis cliquer sur
	<form enctype="multipart/form-data" action="export.php" method="post">
	<input type="checkbox" name="fields[]" value="nomprenom">Nom et prénom (dans le même champ)</input><br/>
	<input type="checkbox" name="fields[]" value="nom">Nom</input><br/>
	<input type="checkbox" name="fields[]" value="prenom">Prénom</input><br/>
	<input type="checkbox" name="fields[]" value="unite">Code unité</input><br/>
	<input type="checkbox" name="fields[]" value="grade">Grade</input><br/>
	<input type="checkbox" name="fields[]" value="rapporteur">Rapporteur1</input><br/>
	<input type="checkbox" name="fields[]" value="rapporteur2">Rapporteur2</input><br/>
	<input type="submit" name="bouton" value="Télécharger exemple" />
	<input type="hidden" name="type" value="exempleimportcsv"/> 
	<input type="hidden" name="action" value="export"/> 
	</form>
	.</li>
	<li>Pour chaque type de rapport, copiez les données depuis le fichier du SGCN dans le fichier exemple.</li>
	<li>Importez le fichier exemple dans Marmotte en utilisant le menu suivant:<br />
	</ul>
	<!--  Enfin utiliser de préférence l'encodage utf-8 pour les caractères accentués.<br/> -->
	<?php 
	displaySecretaryImport();
	?>
	<p>
	Vous pouvez supprimer les colonnes inutiles mais il est indispensable de
	laisser les intitulés des colonnes restantes tels quels.<br />
	</p>
<hr />


<?php 
try
{
	$sql = "SELECT * FROM ".units_db." LIMIT 0,5";
	$result = sql_request($sql);

	$rows = array();
	while ($row = mysql_fetch_object($result))
		$rows[] = $row;

	$csv_reports = compileUnitsAsCSV($rows);
	$filename = "csv/exemple_unites.csv";
	if($handle = fopen($filename, 'w'))
	{
		fwrite ($handle, $csv_reports);
		fclose($handle);
	}
	else
	{
		echo("Watchout: couldn't create exemple csv file ".$filename);
	}
}
catch(Exception $e)
{
	echo("Watchout: couldn't create exemple csv file ".$e->getMessage());
}

?>

<h2>Ajout de plusieurs unités</h2>
<p>
<p>
	Le formulaire ci-dessous permet d'injecter des unités dans la base de
	donnée.<br /> Les rapports sont envoyés sous forme de fichier csv.<br />
	Vous pouvez partir de <a href="csv/exemple_unites.csv">ce fichier
		exemple</a>.<br /> Vous pouvez supprimer les colonnes inutiles mais il
	est indispensable de laisser les intitulés des colonnes restantes tels
	quels.<br /> Les différentes entrées sont encadrées par des guillemets
	par conséquent les champs ne doivent pas contenir des guillements non
	échappés: il faut au préalabale de l'envoi remplacer chaque " par \".<br />
	<!--  Enfin utiliser de préférence l'encodage utf-8 pour les caractères accentués.<br/> -->
	Les données d'un labo avec le même code seront remplacées.
</p>
<form enctype="multipart/form-data" action="index.php" method="post"
	onsubmit="return confirm('Etes vous sur de vouloir uploader ce fichier labos?');">
	<p>
		<input type="hidden" name="type" value="unites" /> <input
			type="hidden" name="action" value="upload" /> <input type="hidden"
			name="MAX_FILE_SIZE" value="100000" /> Fichier csv: <input
			name="uploadedfile" type="file" /> <br /> <input type="submit"
			value="Ajouter unités" />
	</p>
</form>

<?php 
}
else
{
	?>

<p>Ce menu permet d'importer ou de mettre à jour des rapports.</p>

<?php displayImport();
}
?>

