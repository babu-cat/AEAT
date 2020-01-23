<?php

require_once 'AEAT182RDeclarant.php';
require_once 'AEAT182RDeclared.php';

class AEAT182 {

  const AEAT182_EOL = "\r\n";

  var $declarant;
  var $declareds = array();
  var $exercise, $NIF_Declarant, $socialReason, $phone, $contactPerson;

  function __construct ($exercise=null, $NIF_Declarant=null, $socialReason=null, $phone=null, $contactPerson=null, $declared=null) {
    $this->exercise = $exercise;
    $this->NIF_Declarant = $NIF_Declarant;
    $this->socialReason = $socialReason;
    $this->phone = $phone;
    $this->contactPerson = $contactPerson;
    $this->declarant;
    $this->declareds = array();
  }

  /**
   * Declarant catches the information from the class
   *
   * @param $params array ['exercise' => string,
   *                       'NIFDeclarant => string,
   *                       'declarantName' => string
   *                       'supportType' => string,
   *                       'contactPerson' => string
   *                       'declarationID' => string
   *                       'complOrSust' => string
   *                       'lastIdDeclaration' => integer
   *                       'registerNumber' => integer
   *                       'donationImport' => integer
   *                       'nature' => string
   *                       'NIFprotected' => string
   *                       'nameProtected' => string
   *                       'space' => string
   *                       'electronicSign' => string];
   */

  public function setDeclarant($params) {
    $this->declarant = new AEAT182RDeclarant($params);
  }

  /**
   * Declared catches the information from the class
   *
   * @param $params array ['exercise' => string
   *                       'NIFDeclarant' => string
   *                       'NIFDeclared' => string
   *                       'NIFRepresentative' => string
   *                       'declaredName' => string
   *                       'provinceCode' => string
   *                       'key' => string
   *                       'deduction' => integer
   *                       'import' => integer
   *                       'donationKind' => string
   *                       'ACDeduction' => string
   *                       'ACDeductionNumber' => integer
   *                       'nature' => string
   *                       'revocation' => string
   *                       'exerciseRevocationDonative' => integer
   *                       'goodType' => string
   *                       'goodID' => string
   *                       'recurrenceDonations' => string
   *                       'space' => string
   */
  public function addDeclared($params) {
    $this->declareds[] = new AEAT182RDeclared($params);
    $this->declarant->addDeclared($params['donationImport']);
  }

  /**
   * @param boolean $onlyErrors
   *
   * @return array if $onlyErrors is true, string otherwise
   */
  private function getOutput($onlyErrors=false) {
    $output = array();
    $warnings = array();
    $errors = array();

    $output['declarant'] = $this->declarant->getOutput($onlyErrors);

    foreach($this->declareds as $declared) {
      if( !empty($declared->externalIdValue) ) {
        $output[$declared->externalIdValue] = $declared->getOutput($onlyErrors);
      }
      else {
        $output[] = $declared->getOutput($onlyErrors);
      }
    }
    if ($onlyErrors == true) {
      $output = array_filter($output);
      foreach ($output as $key => $error) {
        $warnings[$key] = $error[0];
        $errors[$key] = $error[1];
      }
      return array ($warnings, $errors);
    }
    else {
      return implode(self::AEAT182_EOL, $output);
    }
  }

  /**
   * Output catches the information from the getOutput
   *
   * @param boolean $download
   */
  public function saveFile($download = true) {
    $output = '';
    $output = utf8_decode($this->getOutput());

    if ($download == true) {
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Pragma: private');
      header('Content-Disposition: attachment; filename=182.182');
      header('Content-Length: ' . strlen($output));
      header('Content-Type: application/octet-stream; charset=iso-8859-1');
      echo $output;
      exit;
    }
    else {
      echo $output;
    }
  }

  /**
   * Output catches the information from the getOutput(true)
   *
   * @return array
   */
  public function check182() {
    $output = array();
    $output= $this->getOutput(true);

    if ( !empty($output) ) {
      return $output;
    }
  }
  
}
