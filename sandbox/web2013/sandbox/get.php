<?php
/*
 * Kontrollime esimest (0) get parameetri nime, kui on üks mooduli defineeritud võtmetest, siis  eeldame, et sooviti välja kutsuda seda moodulit,
 * kui ei leia esimesele (0) parameetrile võtit, siis tagastame 404
 * 0 parameetri väärtus ja 1-n parameetri nimed ja väärtused on kasutamiseks selle mooduli sees
 * Igal moodulil on võimalik määrata loogiline hierarhia, et saaks siduda menüüdega
 * Kasutame singletoni, lisame kõik kasutatavad muutujad ja klassid sellesse?
 */
print_r($_GET);
echo '<p>';

$i=0;
foreach($_GET as $k=>$v){
	echo $i.' '.$k.' '.$_GET[$k].'<br>';
	$i++;
}

?>