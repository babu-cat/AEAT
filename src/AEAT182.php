<?php

namespace babucat\AEAT;

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
  var $exercise, $NIF_Declarant, $socialReason, $phone, $contactPerson, $autonomousDeduction;

  function __construct ($model='182', $exercise=null, $NIF_Declarant=null, $socialReason=null, $phone=null, $contactPerson=null, $declared=null, $autonomousDeduction=false) {
    $this->exercise = $exercise;
    $this->NIF_Declarant = $NIF_Declarant;
    $this->socialReason = $socialReason;
    $this->phone = $phone;
    $this->contactPerson = $contactPerson;
    $this->declarant;    
    $this->declareds = array();
    $this->autonomousDeduction = $autonomousDeduction;
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
   * @param float $amountThisYear
   * @param float $amountLastYear
   * @param string $cp
   * @param string $euro
   * 
   * @return array[percentage,recurrence,reduction,actual_amount_min,actual_amount_max]
   */
  public function getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $cp, $euro = TRUE) {

    // Ley 49/2002, de 23 de diciembre, de régimen fiscal de las entidades sin fines lucrativos y de los incentivos fiscales al mecenazgo.

    // [Artículo 19. Deducción de la cuota del Impuesto sobre la Renta de las Personas Físicas](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a19)
    // [Artículo 20. Deducción de la cuota del Impuesto sobre Sociedades](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a20)

    // [Real Decreto-ley 6/2023, de 19 de diciembre, por el que se aprueban medidas urgentes para la ejecución del Plan de Recuperación, Transformación y Resiliencia en materia de servicio público de justicia, función pública, régimen local y mecenazgo.](https://www.boe.es/buscar/act.php?id=BOE-A-2023-25758#lc)

    // Artículo 19. Deducción de la cuota del Impuesto sobre la Renta de las Personas Físicas.
    // Artículo 20. Deducción de la cuota del Impuesto sobre Sociedades.

    $deducted_amount_min = 0;
    $deducted_amount_max = 0;
    $deduction_percentage = 0;
    $donationsRecurrence = 0;

    if ( ($amountLastYear > 0) &&
         ($amountThisYear >= $amountLastYear) ) {
      $donationsRecurrence = 1;
    }
    else {
      $donationsRecurrence = 2;
    }

    if ($contactType == self::NATURAL_PERSON) {

      $min_current_deduction = $donationsRecurrence == 1 ? 45 : 40 ;
      $max_current_deduction = 80 ;
      if ($amountThisYear <= 250) {
        $deduction_percentage = '80';
        $deducted_amount_max = floatval($amountThisYear) * $max_current_deduction * 0.01;
        $deducted_amount_min = floatval($amountThisYear) * $min_current_deduction * 0.01;
      }
      else {
        $deducted_amount_max = 200 + ( $amountThisYear - 250 ) * $min_current_deduction * 0.01; // 200 = 250 * 80 * 0.01
        $deducted_amount_min = $amountThisYear * $min_current_deduction * 0.01;
        if ($donationsRecurrence = 1) {
          $deduction_percentage = '45';
        }
        else {
          $deduction_percentage = '40';
        }
      }      

      //Si el declarante pertenece a una provincia catalana, se le añade la deducción del 15% del tramo autonómico
      if ($this->autonomousDeduction && self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA)) 
      {
        $deducted_amount_min += floatval($amountThisYear) * 15 * 0.01;
        $deducted_amount_max += floatval($amountThisYear) * 15 * 0.01;
        $min_current_deduction += 15 ;
        $max_current_deduction += 15 ;
      }
    }
    elseif ($contactType == self::SOCIETIES) {

      $min_current_deduction = $donationsRecurrence == 1 ? 50 : 40 ;
      $max_current_deduction = $min_current_deduction ;

      if ($donationsRecurrence == 1) {
        $deducted_amount_min = $deducted_amount_max = floatval($amountThisYear) * 50 * 0.01;
        $deduction_percentage = '50';
      }
      else {
        $deducted_amount_min = $deducted_amount_max = floatval($amountThisYear) * 40 * 0.01;
        $deduction_percentage = '40';
      }    

    }
    else {
      return array();
    }

    if ($euro) {
      $euroSufix = ' €';
    }
    else {
      $euroSufix = '';
    }

    return array( 'percentage' => strval($deduction_percentage), 
                  'recurrence' => $donationsRecurrence,   
                  'reduction_min' => strval(number_format($deducted_amount_min, 2, ',', '')) . $euroSufix, 
                  'reduction_max' => strval(number_format($deducted_amount_max, 2, ',', '')) . $euroSufix, 
                  'actual_amount_min' => strval(number_format(floatval($amountThisYear) - $deducted_amount_max, 2, ',', '')) . $euroSufix, //coste mínimo para el donante aplicando la desgravación máxima
                  'actual_amount_max' => strval(number_format(floatval($amountThisYear) - $deducted_amount_min, 2, ',', '')) . $euroSufix, //todo: coste máximo para el donante aplicando la desgravación mínima
                );
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

  /**
   * Check if is spanish postal code
   *
   * @param string $postalCode
   *
   * @return boolean
   *
   */
  static function checkPostalCode ($postalCode) {
    if ( preg_match('/^[0-9]{5}$/i', $postalCode) ) {
      $postalCode = intval( mb_substr($postalCode,0,2) );
      if ( $postalCode >= 1 && $postalCode <= 52 ) {
        return true;
      }
    }
    else {
      return false;
    }
  }

  /**
   * Returns total donations amount
   *
   * @return float
   *
   */  public function getImport() {
    return floatval($this->declarant->attributes['donationsImport']['value'] / 100);
  }

  /**
   * Returns total number of contributors
   *
   * @return integer
   *
   */  public function getContributorsQty() {
    return $this->declarant->attributes['registerNumber']['value'];
   }

}