<?php

session_start();

require_once("utils.inc.php");

require_once("db.inc.php");
require_once("manage_users.inc.php");


require_once 'generate_pdf.inc.php';
require_once 'generate_zip.inc.php';

require_once 'header.inc.php';
require_once("authbar.inc.php");


?>
<div class="large">
	<div class="content">
		<?php 
		try
		{
			//load xml file
			$doc = new DOMDocument("1.0","utf-8");
			$result = $doc->load('reports/reports.xml');

			if($result === false)
				throw new Exception("Failed to load reports/reports.xml");

			$reports = $doc->getElementsByTagName("rapport");


			$next_report = NULL;
			$filenames = array();



			if(isset($_REQUEST['zip_file']))
			{
				$filename = $_REQUEST['zip_file'];
				echo '<p>Le fichier zip contenant tous les pdf:<br/> <a href="'.$filename.'">'.$filename.'</a>.</p>'."\n";
			}

			echo "<table>";

			foreach($reports as $report)
			{
				if(!$report->hasAttributes())
				{
					continue;
				}

				$is_done = $report->hasAttribute('done');

				$filename = $report->getAttribute('filename').".pdf";
				$filenames['reports/'.$filename] = $filename;

				if(!$is_done)
				{
					echo '<tr><td>'.$filename.'</td>';
					if($next_report == NULL)
					{
						$next_report = $report;
						echo '<td><font color="red">Processing...</font></td></tr>'."\n";
					}
					else
					{
						echo '<td>Todo</td></tr>'."\n";
					}

				}
				//echo if($report->attributes->getNamedItem('status') == '')
			}
			foreach($reports as $report)
			{
				if(!$report->hasAttributes())
				{
					continue;
				}

				if($report->hasAttribute('done'))
				{
					$filename = $report->getAttribute('filename').".pdf";
					echo '<tr><td><a href="reports/'.$filename.'">'.$filename.'</a></td>'."\n";
					echo '<td><font>Done</font></td></tr>'."\n";
				}
				//echo if($report->attributes->getNamedItem('status') == '')
			}


			echo "</table>\n";

			if($next_report != NULL)
			{

					$xsl = new DOMDocument("1.0","UTF-8");
					$type = $next_report->getAttribute('type');
					$xsl_path = type_to_xsl($type);
					$xsl->load($xsl_path);

					$proc = new XSLTProcessor();
					$proc->importStyleSheet($xsl);

					if($type=="Classement")
					{
						echo $xsl_path;
						//return;
					}
					
					$filename = 'reports/'.$next_report->getAttribute('filename').".pdf";

					$subreport = new DOMDocument("1.0","UTF-8");
					$node = $subreport->importNode($next_report,true);
					$subreport->appendChild($node);
					$html = $proc->transformToXML($subreport);


					$pdf = HTMLToPDF($html);
					$pdf->Output($filename,"F");

					$next_report->setAttribute('done','');

					$doc->save('reports/reports.xml');

					?>
		<script>window.location = 'create_reports.php'</script>
		<?php
			}
			else
			{



				if(!isset($_REQUEST['zip_file']))
					try
					{
						$filenames = array();

						foreach($reports as $report)
						{
							if($report->hasAttribute('done'))
							{
								$filename = $report->getAttribute('filename').".pdf";
								$filenames['reports/'.$filename] = $filename;
							}
						}
							
						$filename = zip_files($filenames,'reports/reports.zip');
						?>
		<script>window.location = 'create_reports.php<?php echo "?zip_file=".$filename;?>'</script>
		<?php
					}
					catch(Exception $exc)
			{
				echo "Failed to generate zip file: ".$exc->getMessage();
			}
	}

}
catch(Exception $e)
{
	echo "Failed to generate pdfs:  <br/>\n".$e;
}
?>
	</div>
</div>
</body>
</html>
