<?php
$miuviewAPI = 'http://oviir.eu/miuview-api';
$contactsAPI = 'http://oviir.eu/api/contacts';

if (!empty($_GET)) {
  header('Content-Type: text/json; charset=utf-8');
  switch ($_GET['api']) {
    case 'miuview':
      echo file_get_contents($miuviewAPI . '?' . $_SERVER['QUERY_STRING']);
      break;
    case 'contacts':
      echo file_get_contents($contactsAPI . '?' . $_SERVER['QUERY_STRING']);
      break;
    default:
      echo 'false';
  }
}
