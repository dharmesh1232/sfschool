<?php

function getContactID( $grade, $last, $first ) {
  $grade = trim( $grade );
  $first = trim( $first );
  $last  = trim( $last  );

  $query = "
SELECT     c.id
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE      s.grade = %1
AND        c.first_name = %2
AND        c.last_name  = %3
";
  $params = array( 1 => array( $grade, 'Integer' ),
            2 => array( $first, 'String'  ),
            3 => array( $last , 'String'  ),
  );
  return CRM_Core_DAO::singleValueQuery( $query, $params );
}

function setUnlimitedStatus( $contactID ) {
  $sql = "
UPDATE civicrm_value_school_information
SET    extended_care_status_2010 = %1
WHERE  entity_id = %2
";
  $params = array( 1 => array( 'Unlimited', 'String'  ),
            2 => array( $contactID , 'Integer' ),
  );
  CRM_Core_DAO::executeQuery( $sql, $params );
}

function readPaymentFile( $readFile, $writeFile, $globalID ) {
  $fdRead  = fopen( $readFile, "r" );
  if ( ! $fdRead ) {
    echo "Could not read input file: $readFile\n";
    exit( );
  }

  $fdWrite  = fopen( $writeFile, "w" );
  if ( ! $fdWrite ) {
    echo "Could not write output file: $writeFile\n";
    exit( );
  }

  // read first line
  // $header = fgetcsv( $fdRead );

  $count  = 0;
  while ( $fields = fgetcsv( $fdRead ) ) {

    // get contact id
    $contactID = getContactID( $fields[2], $fields[1], $fields[0] );
    if ( ! $contactID ) {
      echo "Could not retrieve valid Contact ID for: " . implode( ',', $fields ) . "\n";
      continue;
    }

    $blocksCharged = $fields[6];
    $details = "Spring 2014 - {$fields[3]}";

    $output = array(
      $globalID++,
      $contactID,
      "Charge",
      $details,
      "2014-04-17",
      $blocksCharged,
      'Activity Fee'
    );
    fputcsv( $fdWrite, $output );
  }

  fclose( $fdRead  );
  fclose( $fdWrite );
}

function initialize( ) {
  require_once '/home/lobo/www/d7/sites/school/civicrm.settings.php';

  require_once 'CRM/Core/Config.php';
  $config =& CRM_Core_Config::singleton( );

  require_once 'CRM/Core/Error.php';
}

function run( ) {
  initialize( );

  readPaymentFile(
    '/home/lobo/SFS/SFS/Fall2014Charges.csv',
    '/home/lobo/SFS/SFS/Fall2014Charges.sql',
    3384
  );
}

run( );
