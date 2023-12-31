<?php

require_once 'AEAT182RDeclarant.php';
require_once 'AEAT182RDeclared.php';

class AEAT182 {

  const AEAT182_EOL = "\r\n";
  const NATURAL_PERSON = 1;
  const SOCIETIES = 2;

  // Autonomous Community Codes
  const ACC_CATALONIA = '09';

  // Province Codes
  const PROVINCE_BARCELONA = '08';
  const PROVINCE_GIRONA = '17';
  const PROVINCE_LLEIDA = '25';
  const PROVINCE_TARRAGONA = '43';

  var $declarant;
  var $declareds = array();
  var $exercise, $NIF_Declarant, $socialReason, $phone, $contactPerson;

  function __construct ($model='182', $exercise=null, $NIF_Declarant=null, $socialReason=null, $phone=null, $contactPerson=null, $declared=null) {

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
   *                       'declarantName' => string Necesario para la generación del fichero 993
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

    foreach ($this->declareds as $declared) {
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
        if ( !empty($error[0]) ) {
          $warnings[$key] = $error[0];
        }
        if ( !empty($error[1]) ) {
          $errors[$key] = $error[1];
        }
      }
      return array ($warnings, $errors);
    }
    else {
      return implode(self::AEAT182_EOL, $output);
    }
  }

    /**
   *
   * @return array if $onlyErrors is true, string otherwise
   */
  private function getOutput993() {

    $output = array();
    $warnings = array();
    $errors = array();

    // CSV headers for 993
    $output[] = "NIF del donant;NIF del Representant;COGNOM1 COGNOM2 NOM;NIF de la entitat receptora;NOM o RAO SOCIAL de la entitat receptora;Import en euros";

    foreach ($this->declareds as $declared) {
      if( !empty($declared->externalIdValue) ) {
        $output[$declared->externalIdValue] = $declared->getOutput993();
      }
      else {
        $output[] = $declared->getOutput993();
      }
    }

    return implode(self::AEAT182_EOL, $output);
  }

  /**
   * Output catches the information from the getOutput
   *
   * @param boolean $download
   * @param string $mod
   */
  public function saveFile($download = true, $mod = '182') {
    $output = '';
    if ($mod == '993') {
      $output = $this->getOutput993();
    }
    else {
      $output = $this->getOutput();
    }
    $output = mb_convert_encoding($output, 'ISO-8859-1', 'UTF-8');
    $filename = ( $mod == '993' ) ? '993.csv' : '182.182'; 

    if ($download == true) {
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Pragma: private');
      header('Content-Disposition: attachment; filename=' . $filename);
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

  /**
   *
   * @param int $contactType {NATURAL_PERSON, SOCIETIES}
   * @param decimal $amountThisYear
   * @param decimal $amountLastYear
   * @param decimal $amountTwoYearBefore
   *
   * @return array[percentage,recurrence]
   */
  static public function getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $cp) {

    // Ley 49/2002, de 23 de diciembre, de régimen fiscal de las entidades sin fines lucrativos y de los incentivos fiscales al mecenazgo.

    // [Artículo 19. Deducción de la cuota del Impuesto sobre la Renta de las Personas Físicas](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a19)
    // [Artículo 20. Deducción de la cuota del Impuesto sobre Sociedades](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a20)

    $donationsRecurrence = 0;

    if ( ($amountLastYear > 0) &&
         ($amountTwoYearBefore > 0) &&
         ($amountThisYear >= $amountLastYear) &&
         ($amountLastYear >= $amountTwoYearBefore) ) {
      $donationsRecurrence = 1;
    }
    else {
      $donationsRecurrence = 2;
    }

    if ($contactType == self::NATURAL_PERSON) {
      if ($amountThisYear <= 150) {
        $deduction_amount = '80';
        $deducted_amount = $amountThisYear * 80 * 0.01;
      }
      else{
        $partial_amount = $amountThisYear - 150;
        if ($donationsRecurrence == 1) {
          $deduction_amount = '40';
        }
        else {
          $deduction_amount = '35';
        }
        $deducted_amount = (150 * 80 * 0.01) + ($partial_amount * intval($deduction_amount) * 0.01);
      }
      //Si el declarante pertenece a una provincia catalana, se le añade la deducción del 15% del tramo autonómico
      if (self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA)) 
      {
        $deducted_amount += $amountThisYear * 15 * 0.01;
      }
    }
    elseif ($contactType == self::SOCIETIES) {
      if ($donationsRecurrence == 1) {
        $deduction_amount = '40';
        $deducted_amount = $amountThisYear * 40 * 0.01;
      }
      else {
        $deduction_amount = '35';
        $deducted_amount = $amountThisYear * 35 * 0.01;
      }
    }
    else {
      return array();
    }
    return array('percentage' => $deduction_amount , 'recurrence' => $donationsRecurrence, 'reduction' => strval(number_format($deducted_amount, 2, ',', ' ')) . ' €', 'actual_amount' => strval(number_format($amountThisYear - $deducted_amount, 2, ',', ' ')) . ' €');
  }

  /**
   *
   * @param int $provinceCode
   * @param int $autonomousCommunityCode
   *
   * @return boolean
   */
  static public function isAutonomousCommunityProvince($provinceCode, $autonomousCommunityCode) {
    if ($autonomousCommunityCode == self::ACC_CATALONIA ) {
      return in_array($provinceCode,[
        self::PROVINCE_BARCELONA,
        self::PROVINCE_GIRONA,
        self::PROVINCE_LLEIDA,
        self::PROVINCE_TARRAGONA
      ]);
    }
    else {
      return false;
    }
  }
}
