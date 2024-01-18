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
   * @param string $cp
   * 
   * @return array[percentage,recurrence,reduction,actual_amount,reduction_new,actual_amount_new,contribution_new]
   */
  static public function getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $cp) {

    // Ley 49/2002, de 23 de diciembre, de régimen fiscal de las entidades sin fines lucrativos y de los incentivos fiscales al mecenazgo.

    // [Artículo 19. Deducción de la cuota del Impuesto sobre la Renta de las Personas Físicas](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a19)
    // [Artículo 20. Deducción de la cuota del Impuesto sobre Sociedades](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a20)

    $donationsRecurrence = 0;

    //TODO Con la normativa de 2024, la recurrencia solo tendrá en cuenta los dos últimos años
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

      //Bloque relativo a la nueva normativa para 2024 de personas físicas
      if ($amountThisYear <= 250) {
        $deduction_amount_new = '80';
        $deducted_amount_new = $amountThisYear * 80 * 0.01;
      }
      else{
        $partial_amount_new = $amountThisYear - 250;
        if ($donationsRecurrence == 1) {
          $deduction_amount_new = '45';
        }
        else {
          $deduction_amount_new = '40';
        }
        $deducted_amount_new = (250 * 80 * 0.01) + ($partial_amount_new * intval($deduction_amount_new) * 0.01);
      }      
      // Fin bloque relativo a la nueva normativa para 2024 de personas físicas

      //Si el declarante pertenece a una provincia catalana, se le añade la deducción del 15% del tramo autonómico
      if (self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA)) 
      {
        $deducted_amount += $amountThisYear * 15 * 0.01;
        $deducted_amount_new += $amountThisYear * 15 * 0.01; //Importe según normativa 2024
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

      //Bloque relativo a la nueva normativa para 2024 de personas jurídicas
      if ($donationsRecurrence == 1) {
        $deduction_amount_new = '50';
        $deducted_amount_new = $amountThisYear * 50 * 0.01;
      }
      else {
        $deduction_amount_new = '40';
        $deducted_amount_new = $amountThisYear * 40 * 0.01;
      }    
    // Fin bloque relativo a la nueva normativa para 2024 de personas jurídicas
    }
    else {
      return array();
    }

      // Inicio bloque cálculo de nueva contribución para 2024 para que el coste real sea el mismo que con la normativa anterior
      //Bloque relativo a la nueva normativa para 2024 de personas físicas
      if ($contactType == self::NATURAL_PERSON) {
      $old_deduction = self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA) ? $deduction_amount + 15 : $deduction_amount;
      $new_reduction = self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA) ? 95 : 80 ;
      $constant = self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA) ? 20 : 5 ;
     
      // Si el importe no supera los 150€, no hay ningún cambio. En caso contrario, se aplica la nueva fórmula
      if ($amountThisYear <= 150) {
        $contribution_new = 0;
      }else{
        $old_partial_amount = $amountThisYear-150;
        $eq = (($old_partial_amount-(($old_deduction*0.01)*$old_partial_amount))-($old_partial_amount-(0.01*$new_reduction)*$old_partial_amount))*$constant;
        //Suma de aportación último año + resultado de la fórmula
        $eq_amountThisYear = $eq + $amountThisYear;

        if($eq_amountThisYear <= 250){
          $contribution_new = $eq;
        }else{
          //Import fix per als primers 250€
          $contribucion_new_less_250 = 250 * (0.01*(100-$new_reduction));

          //Import real per restant fins a igualar aportació de 2023
          $diff_actual_amount = ($amountThisYear - $deducted_amount) - $contribucion_new_less_250;

          //calcular nuevos porcentajes de deducción a partir de la nueva cantidad (suma de aportación último año + resultado de la fórmula)
          if($eq_amountThisYear > 250)
          {
            if ($donationsRecurrence == 1) {
              $deduction_amount_partial = '45';
            }
            else {
              $deduction_amount_partial = '40';
            }

            if (self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA)) {
              $deduction_amount_partial += 15 ;
            }
          }
          $contribucion_new_more_250 = $diff_actual_amount * 100 / (100 - $deduction_amount_partial);
          $contribution_new_total = 250 + $contribucion_new_more_250;
          $contribution_new = $contribution_new_total - $amountThisYear;
        }
      }
      // Fin bloque relativo a la nueva normativa para 2024 de personas físicas
    }
    elseif ($contactType == self::SOCIETIES) {
      //Bloque relativo a la nueva normativa para 2024 de personas Jurídicas
      //Cálculo del porcentaje de la aportación que asume el contribuyente
      $contribution_new_total = ($amountThisYear - $deducted_amount) * 100 / (100-$deduction_amount_new);
      $contribution_new = $contribution_new_total - $amountThisYear;
      // Fin bloque relativo a la nueva normativa para 2024 de personas jurídicas
    }

    // Fin bloque cálculo de nueva contribución para 2024 para que el coste real sea el mismo que con la normativa anterior
    $actualAmount = $amountThisYear - $deducted_amount;
    $actualAmountNew = $amountThisYear - $deducted_amount_new;

    return array( 'percentage' => $deduction_amount , 
                  'recurrence' => $donationsRecurrence,   
                  'reduction' => strval(number_format($deducted_amount, 2, ',', ' ')) . ' €', 
                  'actual_amount' => strval(number_format($amountThisYear - $deducted_amount, 2, ',', ' ')) . ' €',
                  'reduction_new' => strval(number_format($deducted_amount_new, 2, ',', ' ')) . ' €', 
                  'actual_amount_new' => strval(number_format($amountThisYear - $deducted_amount_new, 2, ',', ' ')) . ' €' ,          
                  'contribution_new' => strval(number_format($contribution_new, 2, ',', ' ')) . ' €'           
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

}
