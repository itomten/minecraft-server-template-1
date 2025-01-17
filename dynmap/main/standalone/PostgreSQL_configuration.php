<?php
ob_start();
require_once('PostgreSQL_funcs.php');
include('PostgreSQL_config.php');
include('PostgreSQL_access.php');
ob_end_clean();

session_start();

if(isset($_SESSION['userid'])) {
  $userid = $_SESSION['userid'];
}
else {
  $userid = '-guest-';
}

$loggedin = false;
if(strcmp($userid, '-guest-')) {
  $loggedin = true;
}

$content = getStandaloneFile('dynmap_config.json');

header('Content-type: application/json; charset=utf-8');

$json = json_decode($content);

if (!$loginenabled) {
	echo $content;
}
else if($json->loginrequired && !$loggedin) {
    echo "{ \"error\": \"login-required\" }";
}
else {
	$uid = '[' . strtolower($userid) . ']';
	$json->loggedin = $loggedin;
	$wcnt = count($json->worlds);
	$newworlds = array();
	for($i = 0; $i < $wcnt; $i++) {
		$w = $json->worlds[$i];
		if($w->protected) {
		    $ss = stristr($worldaccess[$w->name], $uid);
			if($ss !== false) {
				$newworlds[] = $w;
			}
			else {
				$w = null;
			}
		}
		else {
			$newworlds[] = $w;
		}
		if($w != null) {
			$mcnt = count($w->maps);
			$newmaps = array();
			for($j = 0; $j < $mcnt; $j++) {
				$m = $w->maps[$j];
				if($m->protected) {
				    $ss = stristr($mapaccess[$w->name . '.' . $m->prefix], $uid);
					if($ss !== false) {
						$newmaps[] = $m;
					}
				}
				else {
					$newmaps[] = $m;
				}
			}
			$w->maps = $newmaps;		
		}
	}
	$json->worlds = $newworlds;
	
	echo json_encode($json);
}
cleanupDb();
 
?>

