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
   * @param float $amountTwoYearBefore
   * @param string $cp
   * 
   * @return array[percentage,recurrence,reduction,actual_amount_min,actual_amount_max,reduction_new,actual_amount_min_new,actual_amount_max_new,contribution_new_min,contribution_new_max]
   */
  public function getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $cp) {

    // Ley 49/2002, de 23 de diciembre, de régimen fiscal de las entidades sin fines lucrativos y de los incentivos fiscales al mecenazgo.

    // [Artículo 19. Deducción de la cuota del Impuesto sobre la Renta de las Personas Físicas](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a19)
    // [Artículo 20. Deducción de la cuota del Impuesto sobre Sociedades](https://www.boe.es/buscar/act.php?id=BOE-A-2002-25039&p=20191228&tn=1#a20)

    $deduction_amount = 0;
    $deduction_amount_new = 0;
    $deducted_amount = 0;
    $deducted_amount_min = 0;
    $deducted_amount_max = 0;
    $deducted_amount_new = 0;
    $deducted_amount_new_min = 0;
    $deducted_amount_new_max = 0;
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
      $min_current_deduction = $donationsRecurrence == 1 ? '40' : '35' ;
      $max_current_deduction = 80 ;
      if ($amountThisYear <= 150) {
        $deducted_amount_max = $amountThisYear * intval($max_current_deduction) * 0.01;
        $deducted_amount_min = $amountThisYear * intval($min_current_deduction) * 0.01;
      }
      else{
        $partial_amount = $amountThisYear - 150;
        if ($donationsRecurrence == 1) {
           $deduction_amount = '40';
        }
        else {
           $deduction_amount = '35';
        }
        $deducted_amount_min = $amountThisYear * $min_current_deduction * 0.01;
        $deducted_amount_max = (150 * $max_current_deduction * 0.01) + ($partial_amount * intval($min_current_deduction) * 0.01);
      }
      
      //Bloque relativo a la nueva normativa para 2024 de personas físicas
      $min_current_deduction_new = $donationsRecurrence == 1 ? 45 : 40 ;
      $max_current_deduction_new = 80 ;
      if ($amountThisYear <= 250) {
        $deducted_amount_new_max = $amountThisYear * $max_current_deduction_new * 0.01;
        $deducted_amount_new_min = $amountThisYear * $min_current_deduction_new * 0.01;
      }
      else{
        $partial_amount_new = $amountThisYear - 250;
        if ($donationsRecurrence == 1) {
          $deduction_amount_new = '45';
        }
        else {
          $deduction_amount_new = '40';
        }
        $deducted_amount_new_min = $amountThisYear * $min_current_deduction_new * 0.01;
        $deducted_amount_new_max = (250 * $max_current_deduction_new * 0.01) + ($partial_amount_new * intval($min_current_deduction_new) * 0.01);
      }      
      // Fin bloque relativo a la nueva normativa para 2024 de personas físicas

      //Si el declarante pertenece a una provincia catalana, se le añade la deducción del 15% del tramo autonómico
      if ($this->autonomousDeduction && self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA)) 
      {
        $deducted_amount_min += $amountThisYear * 15 * 0.01;
        $deducted_amount_max += $amountThisYear * 15 * 0.01;
        $deducted_amount_new_min += $amountThisYear * 15 * 0.01;
        $deducted_amount_new_max += $amountThisYear * 15 * 0.01;
        $min_current_deduction += 15 ;
        $max_current_deduction += 15 ;
        $min_current_deduction_new += 15;
        $max_current_deduction_new += 15;      
      }
    }
    elseif ($contactType == self::SOCIETIES) {
      if ($donationsRecurrence == 1) {
        $min_current_deduction = '40';
        $deducted_amount_min = $deducted_amount_max = $amountThisYear * 40 * 0.01;
      }
      else {
        $min_current_deduction = '35';
        $deducted_amount_min = $deducted_amount_max = $amountThisYear * 35 * 0.01;
      }

      //Bloque relativo a la nueva normativa para 2024 de personas jurídicas
      if ($donationsRecurrence == 1) {
        $deduction_amount_new = '50';
        $deducted_amount_new_min = $deducted_amount_new_max = $amountThisYear * 50 * 0.01;
      }
      else {
        $deduction_amount_new = '40';
        $deducted_amount_new_min = $deducted_amount_new_max = $amountThisYear * 40 * 0.01;
      }    
    // Fin bloque relativo a la nueva normativa para 2024 de personas jurídicas
    }
    else {
      return array();
    }

      // Inicio bloque cálculo de nueva contribución para 2024 para que el coste real sea el mismo que con la normativa anterior
      //Bloque relativo a la nueva normativa para 2024 de personas físicas
      if ($contactType == self::NATURAL_PERSON) {      
      //Porcentaje de deducción mínimo según nueva normativa
      $new_deduction_min = $donationsRecurrence == 1 ? 45 : 40 ;
      $new_deduction_max = 80 ;
      //Si le resulta aplicable el tramo de deducción autonómica, la deducción mínima se incrementa un 15%
      if ($this->autonomousDeduction && self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA)) {
        $new_deduction_min += 15 ;
        $new_deduction_max += 15 ;
      }

      //$constant = self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA) ? 20 : 5 ;
      $constant_max = 100 / (100 - $new_deduction_max);
      $constant_min = 100 / (100 - $new_deduction_min);
     
      // Si el importe no supera los 150€, solo se calcula la nueva contribución con la desgravación mínima, que se incrementa un 5% . En caso contrario, se aplica la nueva fórmula
      if ($amountThisYear <= 150) {
        $contribution_new_max = (($deducted_amount_new_min - $deducted_amount_min)*100)/(100-$new_deduction_min);
        $contribution_new_min = 0;
      }else{
        $old_partial_amount = $amountThisYear-150;
        $eq_max = (($old_partial_amount-(($min_current_deduction*0.01)*$old_partial_amount))-($old_partial_amount-(0.01*$new_deduction_max)*$old_partial_amount))*$constant_max;
        //Suma de aportación último año + resultado de la fórmula con la desgravación máxima
        $eq_amountThisYear_max = $eq_max + $amountThisYear;
        
        //Cálculos relativos a la desgravación mínima
        $contribution_new_min_increase = (($amountThisYear - $deducted_amount_min)*100)/(100-$new_deduction_min);
        $contribution_new_min = $contribution_new_min_increase - $amountThisYear;

        //Cálculos relativos a la desgravación máxima
        if($eq_amountThisYear_max <= 250){
          $contribution_new_max = $eq_max;
        }else{
          //Import fix per als primers 250€
          $contribucion_new_less_250_max = 250 * (0.01*(100-$max_current_deduction_new));
          //Import real per restant fins a igualar aportació de 2023
          $diff_actual_amount_max = ($amountThisYear - $deducted_amount_max) - $contribucion_new_less_250_max;

          //calcular nuevos porcentajes de deducción a partir de la nueva cantidad (suma de aportación último año + resultado de la fórmula)
          if($eq_amountThisYear_max > 250)
          {
            if ($donationsRecurrence == 1) {
              $deduction_amount_partial = '45';
            }
            else {
              $deduction_amount_partial = '40';
            }

            if ($this->autonomousDeduction && self::isAutonomousCommunityProvince(substr( $cp, 0, 2 ),self::ACC_CATALONIA)) {
              $deduction_amount_partial += 15 ;
            }
          }
          $contribucion_new_max_more_250 = $diff_actual_amount_max * 100 / (100 - $new_deduction_min);
          $contribution_new_max_total = 250 + $contribucion_new_max_more_250;
          $contribution_new_max = $contribution_new_max_total - $amountThisYear;
        }
      }
      // Fin bloque relativo a la nueva normativa para 2024 de personas físicas
    }
    elseif ($contactType == self::SOCIETIES) {
      //Bloque relativo a la nueva normativa para 2024 de personas Jurídicas
      //Cálculo del porcentaje de la aportación que asume el contribuyente
      $contribution_new_max_total = ($amountThisYear - $deducted_amount) * 100 / (100-$deduction_amount_new);
      $contribution_new_max = $contribution_new_min = $contribution_new_max_total - $amountThisYear;
      // Fin bloque relativo a la nueva normativa para 2024 de personas jurídicas
    }

    // Fin bloque cálculo de nueva contribución para 2024 para que el coste real sea el mismo que con la normativa anterior
    $actualAmount = $amountThisYear - $deducted_amount;
    $actualAmountNew = $amountThisYear - $deducted_amount_new;
    return array( 'percentage' => $min_current_deduction, 
                  'recurrence' => $donationsRecurrence,   
                  'reduction_min' => strval(number_format($deducted_amount_min, 2, ',', ' ')) . ' €', 
                  'reduction_max' => strval(number_format($deducted_amount_max, 2, ',', ' ')) . ' €', 
                  'actual_amount_min' => strval(number_format($amountThisYear - $deducted_amount_max, 2, ',', ' ')) . ' €', //coste mínimo para el donante aplicando la desgravación máxima
                  'actual_amount_max' => strval(number_format($amountThisYear - $deducted_amount_min, 2, ',', ' ')) . ' €', //todo: coste máximo para el donante aplicando la desgravación mínima
                  'reduction_new_min' => strval(number_format($deducted_amount_new_min, 2, ',', ' ')) . ' €', 
                  'reduction_new_max' => strval(number_format($deducted_amount_new_max, 2, ',', ' ')) . ' €', 
                  'actual_amount_min_new' => strval(number_format($amountThisYear - $deducted_amount_new_max, 2, ',', ' ')) . ' €' , //coste mínimo para el donante aplicando la desgravación máxima
                  'actual_amount_max_new' => strval(number_format($amountThisYear - $deducted_amount_new_min, 2, ',', ' ')) . ' €' , //todo: coste máximo para el donante aplicando la desgravación mínima      
                  'contribution_new_min' => strval(number_format($contribution_new_min, 2, ',', ' ')) . ' €',           
                  'contribution_new_max' => strval(number_format($contribution_new_max, 2, ',', ' ')) . ' €'           
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
