
<?php 

require_once("header.inc.php");
require_once("authbar.inc.php");
require_once('display_report.inc.php');
require_once('display_reports.inc.php');
require_once('manage_filters_and_sort.inc.php');

?>

<script type="text/javascript">
function alertSize() {
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		myWidth = window.innerWidth; myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth ||document.documentElement.clientHeight ) ) {
		myWidth = document.documentElement.clientWidth; myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		myWidth = document.body.clientWidth; myHeight = document.body.clientHeight;
	}
	window.alert( 'Width = ' + myWidth + ' and height = ' + myHeight );
}
function getScrollXY() {
	var scrOfX = 0, scrOfY = 0;
	if( typeof( window.pageYOffset ) == 'number' ) {
		scrOfY = window.pageYOffset; scrOfX = window.pageXOffset;
	} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		scrOfY = document.body.scrollTop; scrOfX = document.body.scrollLeft;
	} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		scrOfY = document.documentElement.scrollTop; scrOfX = document.documentElement.scrollLeft;
	}
	window.alert( 'Horizontal scrolling = ' + scrOfX + '\nVertical scrolling = ' + scrOfY );
}


</script>

<?php 
function alertText($text)
{
	echo $text."\n";
	echo
	"<script>
		alert(\"".str_replace(array("\"","<br/>","<p>","</p>"),array("'","\\n","\\n","\\n"), $text)."\")
			</script>";
}
?>

<div class="large">

	<!-- 
	<div class="header">
		<h2><span>Comité National de la Recherche Scientifique</span></h2>
		<h1>Interface de saisie des prérapports</h1>
	</div>
 -->
	<div class="content">


		<?php 
		require_once('manage_sessions.inc.php');
		require_once('manage_unites.inc.php');
		require_once('manage_rapports.inc.php');
		require_once('manage_people.inc.php');
		require_once('db.inc.php');
		require_once("upload.inc.php");



		$id_rapport = isset($_REQUEST["id"]) ? mysql_real_escape_string($_REQUEST["id"]) : -1;
		$id_origine = isset($_REQUEST["id_origine"]) ? mysql_real_escape_string($_REQUEST["id_origine"]) : 0;
		$id_toupdate = isset($_REQUEST["id_toupdate"]) ? mysql_real_escape_string($_REQUEST["id_toupdate"]) : 0;

		$action = isset($_REQUEST["action"]) ? mysql_real_escape_string($_REQUEST["action"]) : "";

		if(isset($_REQUEST["reset_filter"]))
			resetFilterValuesExceptSession();

		if(isset($_REQUEST["reset_tri"]))
			resetOrder();

		function scrollToId($id)
		{

			echo('
					<script type="text/javascript">');

			echo('
					document.getElementById("'.$id.'").scrollIntoView();');
			/*
			 echo('
			 		var elt = document.getEleme	ntById( '.$id.' );
			 		var top = (	return elt.offsetTop + ( elt.offsetParent ? elt.offsetParent.documentOffsetTop() : 0 )) - ( window.innerHeight / 2 );
			 		window.scrollTo( 0, top );
			 		');
			*/
			echo('		</script>');

		}
		function displayReports($centralid = 0)
		{

			displaySummary(getCurrentFiltersList(), getFilterValues(), getSortingValues());

			if($centralid != 0 && $centralid != -1)
			{
				$id  = getIDOrigine($centralid);
				scrollToId('t'.$id);
			}

		};

		function editWithRedirect($id)
		{
			?>
			<script type="text/javascript">
			window.location = "index.php?action=edit&id=<?php echo $id;?>"
			</script>
			<?php 
		}

		function viewWithRedirect($id)
		{
			?>
					<script type="text/javascript">
					window.location = "index.php?action=read&id=<?php echo $id;?>"
					</script>
					<?php 
		}
				

		function displayWithRedirects($id = 0)
		{
			?>
							<script type="text/javascript">
							window.location = "index.php?action=view&id=<?php echo $id;?>"
							</script>
							<?php 
				}
				
				
		try
		{
			switch($action)
			{
				case 'updateconfig':
					put_raw_config($_REQUEST['fieldconfig']);
					include 'admin.inc.php';
					break;
					
				case 'delete':
					$next = next_report($id_rapport);
					$before = deleteReport($id_rapport, true);
					echo "<p>Deleted report ".$id_rapport."</p>\n";
					unset($_REQUEST['id']);
					unset($_REQUEST['id_origine']);
//					displayWithRedirects( ($before != -1) ? $before : $next);
					if($next != -1)
						displayWithRedirects($next);
					else
						displayReports();
					break;

				case 'change_statut':
					if(isset($_REQUEST["new_statut"]))
					{
						$filterValues = getFilterValues();
						$new_statut =  mysql_real_escape_string($_REQUEST["new_statut"]);
						change_statuts($new_statut, $filterValues);
						$filterValues['statut']	 = $new_statut;
						displaySummary(getCurrentFiltersList(), $filterValues, getSortingValues());
					}
					break;
				case 'view':
					displayReports($id_rapport);
					break;
				case 'deleteCurrentSelection':
					deleteCurrentSelection();
					displayReports();
					break;
				case 'edit':
					editReport($id_rapport);
					break;
				case 'read':
					viewReport($id_rapport);
					break;
				case 'history':
					historyReport($id_origine);
					break;
				case 'upload':
					$result= process_upload();
					alertText($result);
					displayReports();
					break;

				case 'view':
					displayWithRedirects($next);
					//viewWithRedirect($next);
					break;
						
				case 'update':

					$next = next_report($id_origine);
					$previous = previous_report($id_origine);


					if(isset($_REQUEST["read"]))
					{
						viewWithRedirect($id_origine);
					}
					else if(isset($_REQUEST["edit"]))
					{
						editWithRedirect($id_origine);
					}
					else if(isset($_REQUEST["editnext"]))
					{
						editWithRedirect($next);
					}
					else if(isset($_REQUEST["viewnext"]))
					{
						viewWithRedirect($next);
					}
					else if(isset($_REQUEST["editprevious"]))
					{
						editWithRedirect($previous);
					}
					else if(isset($_REQUEST["viewprevious"]))
					{
						viewWithRedirect($previous);
					}
					else if(isset($_REQUEST["retourliste"]))
					{
						unset($_REQUEST["id_origine"]);
						unset($_REQUEST["id"]);
						displayWithRedirects($id_origine);
					}
					else if(isset($_REQUEST["deleteandeditnext"]))
					{
						$before = deleteReport($id_origine, false);
						if($before != -1)
							editWithRedirect($before);
						else if($next != -1)
							editWithRedirect($next);
						else
							displayWithRedirects();
					}
					else if(isset($_REQUEST['ajoutfichier']) && isset($_REQUEST['uploaddir']))
					{
						$directory = $_REQUEST['uploaddir'];
						echo 
							process_upload(
									$directory,
						 		get_or_create_candidate(
						 			getReport($id_origine)
						 		)
						  	)
						;
						editReport($id_origine);
					}
					else if(isset($_REQUEST['suppressionfichier']))
					{
						if(isset($_REQUEST['deletedfile']))
						{
							$file = $_REQUEST['deletedfile'];
							if(!isSecretaire() && !is_picture($file))
								throw new Exception("You are allowed to delete images only, not documents of type '".$suffix."'");
							unlink($file);
						}
						editReport($id_origine);
					}
					else
					{
						$done = false;

						foreach($concours_ouverts as $concours => $nom)
						{
							if(isset($_REQUEST['importconcours'.$concours]))
							{
								$done = true;
								$newreport = update_report_from_concours($id_origine,$concours, getLogin());
								editWithRedirect($newreport->id);
								break;
									
							}
						}


						if(!$done)
						{
							$report = addReportFromRequest($id_origine,$_REQUEST);

							if(isset($_REQUEST["submitandeditnext"]))
							{
								editWithRedirectReport($next);
							}
							else if(isset($_REQUEST["submitandviewnext"]))
							{
								viewWithRedirect($next);
							}
							else if(isset($_REQUEST["submitandkeepediting"]))
							{
								editWithRedirect($report->id);
							}
							else if(isset($_REQUEST["submitandkeepviewing"]))
							{
								viewWithRedirect($report->id);
							}
						}
					}

					break;
				case 'change_current_session':
					if(isset($_REQUEST["current_session"]))
						$_SESSION['current_session'] = $_REQUEST["current_session"];
					displayWithRedirects();
					break;
				case 'new':
					if (isset($_REQUEST["type"]))
					{
						$type = $_REQUEST["type"];
						$report = newReport($type);
						displayEditableReport($report);
					}
					else
					{
						throw new Exception("Cannot create new document because no type_eval provided");
					}
					break;
				case'newpwd':
				case 'adminnewpwd':
					if (isset($_REQUEST["oldpwd"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
					{
						$old = mysql_real_escape_string($_REQUEST["oldpwd"]);
						$pwd1 = mysql_real_escape_string($_REQUEST["newpwd1"]);
						$pwd2 = mysql_real_escape_string($_REQUEST["newpwd2"]);
						$login = mysql_real_escape_string($_REQUEST["login"]);
						$envoiparemail = isset($_REQUEST["envoiparemail"])  ? mysql_real_escape_string($_REQUEST["envoiparemail"]) : false;

						if (($pwd1==$pwd2))
						{
							if (changePwd($login,$old,$pwd1,$pwd2,$envoiparemail))
								echo "<p><strong>Mot de passe modifié avec succès.</strong></p>";
						}
						else
							throw new Exception("Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>");
					}
					else
						throw new Exception("Erreur :</strong> Vous n'avez fourni les informations nécessaires pour modifier votre mot de passe, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
					include 'admin.inc.php';

					break;
				case 'admin':
					if (isSecretaire())
						include "admin.inc.php";
					else
						throw new Exception("<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
					break;
				case 'admindeleteaccount':
					if (isSecretaire())
					{
						if (isset($_REQUEST["login"]))
						{
							$login = $_REQUEST["login"];
							deleteUser($login);
							include "admin.inc.php";
						}
						else
							throw new Exception("<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer un utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
					}
					else
						throw new Exception("<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
				case 'infosrapporteur':
					if (isBureauUser())
					{
						if (isset($_REQUEST["login"]) and isset($_REQUEST["permissions"]))
						{
							global  $concours_ouverts;
							$login = $_REQUEST["login"];
							$permissions = $_REQUEST["permissions"];
							$sousjury = "";
							foreach($concours_ouverts as $concours => $nom)
								if(isset($_REQUEST["sousjury".$concours]))
									$sousjury .= $_REQUEST["sousjury".$concours];

							changeUserInfos($login,$permissions,$sousjury);
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour modifier les droits de cet utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
						}
						include "admin.inc.php";
						scrollToId('infosrapporteur');
					}
					else
					{
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
					break;
				case 'checkpwd':
					if(isset($_REQUEST["password"]))
					{
						$password = $_REQUEST["password"];
						checkPasswords($password);
					}
					break;
				case 'adminnewaccount':
					if (isSecretaire())
					{
						if (isset($_REQUEST["email"]) and isset($_REQUEST["description"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
						{
							$desc = $_REQUEST["description"];
							$pwd1 = $_REQUEST["newpwd1"];
							$pwd2 = $_REQUEST["newpwd2"];
							$login = $_REQUEST["login"];
							$email = $_REQUEST["email"];
							$envoiparemail = $_REQUEST["envoiparemail"] === 'on';
							if (($pwd1==$pwd2))
								echo "<p><strong>".createUser($login,$pwd2,$desc, $email, $envoiparemail)."</p></strong>";
							else
								echo "<p><strong>Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>";
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer un utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
						}
						include "admin.inc.php";
					}
					else
					{
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
					break;
				case 'admindeletesession':
					if (isset($_REQUEST["sessionid"]))
						deleteSession(mysql_real_escape_string($_REQUEST["sessionid"]), isset($_REQUEST["supprimerdossiers"]));
					else
						throw new Exception("Vous n'avez fourni toutes les informations nécessaires pour supprimer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.");
					include "admin.inc.php";
					break;
				case 'changepwd':
					include "changePwd.inc.php";
					break;
				case 'ajoutlabo':
					if(isset($_REQUEST["nickname"]) and isset($_REQUEST["code"]) and isset($_REQUEST["fullname"]) and isset($_REQUEST["directeur"]))
					{
						addUnit(
						mysql_real_escape_string($_REQUEST["nickname"]),
						 mysql_real_escape_string($_REQUEST["code"]),
						 mysql_real_escape_string($_REQUEST["fullname"]),
						 mysql_real_escape_string($_REQUEST["directeur"])
						 );
						echo "Added unit \"".mysql_real_escape_string($_REQUEST["nickname"])."\"<br/>";
					}
					else
					{
						echo "Cannot process action ajoutlabo: missing data<br/>";
					}
					include "unites.php";
					break;
				case 'deletelabo':
					if(isset($_REQUEST["unite"]))
					{
						deleteUnit(mysql_real_escape_string($_REQUEST["unite"]));
						echo "Deleted unit \"".mysql_real_escape_string($_REQUEST["unite"])."\"<br/>";
					}
					else
					{
						echo "Cannot process action ajoutlabo: missing data<br/>";
					}
					include "admin.inc.php";
					break;
				case 'mailing':
				case 'email_rapporteurs':
					include 'mailing.inc.php';
					break;
				case 'createhtpasswd':
					createhtpasswd();
					displayWithRedirects();
					include "admin.inc.php";
					break;
				case 'trouverfichierscandidats':
					link_files_to_candidates();
					include "admin.inc.php";
					break;
				case 'creercandidats':
					creercandidats();
					include "admin.inc.php";
					break;
				case 'injectercandidats':
					injectercandidats();
					include "admin.inc.php";
					break;
				case "displayunits":
					include "unites.php";
					break;
				case "displaystats":
					include "stats.php";
					break;
				case "displayimportexport":
					include "import_export.php";
					break;
				case "";
				default:
					if(substr($action,0,3)=="set")
					{
						$fieldId = substr($action,3);
						$newvalue = isset($_REQUEST['new'.$fieldId]) ? mysql_real_escape_string($_REQUEST['new'.$fieldId]) : "";
						$newid = change_report_property($id_toupdate, $fieldId, $newvalue);
						displayWithRedirects($newid);
					}
					else
					{
						echo get_config("welcome_message");
						displayWithRedirects();
					}
					break;
			}
		}
		catch(Exception $exc)
		{
			$text = 'Impossible d\'exécuter l\'action "'.$action.'"<br/>Exception: '.$exc->getMessage();
			alertText($text);
		}
		?>
	</div>
</div>
