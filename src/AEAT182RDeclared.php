<?php

namespace babucat\AEAT;

/**
 *
 * @package AEAT182
 *
 */
class AEAT182RDeclared extends AEAT182RBase {

  var $externalIdValue;


  /**
   * Class constructor.
   *
   * @param $exercise string
   * @param $NIFDeclarant string
   * @param $declarantName string Necesario para generación del fichero 993
   * @param $NIFDeclared string
   * @param $NIFRepresentative string
   * @param $declaredName string
   * @param $provinceCode string Es correspon als dos primers digits del codi postal
   * @param $key string integer
   * @param $deduction integer
   * @param $import integer
   * @param $donationKind string
   * @param $ACDeduction string
   * @param $ACDeductionNumber integer
   * @param $nature string
   * @param $revocation string
   * @param $exerciseRevocationDonative integer
   * @param $goodType string
   * @param $goodID string
   * @param $recurrenceDonations string
   * @param $space string
   *
   *  @todo Revisar que campos son obligatorios
   *
   */
  function __construct($params) {
    parent::__construct(2, $params['exercise'], $params['NIFDeclarant'], $params['declarantName']);

    $this->externalIdValue = ( isset($params['externalId']) ) ? $params['externalId'] : '';
    $NIFDeclaredValue = ( isset($params['NIFDeclared']) ) ? $params['NIFDeclared'] : '';
    $NIFRepresentativeValue = ( isset($params['NIFRepresentative']) ) ? $params['NIFRepresentative'] : '';
    $declaredNameValue = ( isset($params['declaredName']) ) ? $params['declaredName'] : '';
    $provinceCodeValue = ( isset($params['provinceCode']) ) ? $params['provinceCode'] : '';
    $keyValue = ( isset($params['key']) ) ? $params['key'] : '';
    $deductionValue = ( isset($params['deduction']) ) ? $params['deduction'] : '';
    $donationImportValue = ( isset($params['donationImport']) ) ? $params['donationImport'] : '';
    $donationKindValue = ( isset($params['donationKind']) ) ? $params['donationKind'] : '';
    $ACDeductionValue = ( isset($params['ACDeduction']) ) ? $params['ACDeduction'] : '';
    $ACDeductionNumberValue = ( isset($params['ACDeductionNumber']) ) ? $params['ACDeductionNumber'] : '';
    $natureValue = ( isset($params['nature']) ) ? $params['nature'] : '';
    $revocationValue = ( isset($params['revocation']) ) ? $params['revocation'] : '';
    $exerciseRevocationDonativeValue = ( isset($params['exerciseRevocationDonative']))? $params['exerciseRevocationDonative'] : '';
    $goodTypeValue = ( isset($params['goodType']) ) ? $params['goodType'] : '';
    $goodIDValue = ( isset($params['goodID']) ) ? $params['goodID'] : '';
    $recurrenceDonationsValue = ( isset($params['recurrenceDonations']) ) ? $params['recurrenceDonations'] : '';
    $this->attributes['NIFDeclared'] = array('length' => 9, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $NIFDeclaredValue);
    $this->attributes['NIFRepresentative'] =array('length' => 9, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $NIFRepresentativeValue);
    $this->attributes['declaredName'] = array('length' => 40, 'dataType' => 'TEXT', 'trimmable' => 0, 'value' => $declaredNameValue);
    $this->attributes['provinceCode'] = array('length' => 2, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $provinceCodeValue);
    $this->attributes['key'] = array('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $keyValue);
    $this->attributes['deduction'] = array('length' => 5, 'dataType' => 'NUM', 'trimmable' => 1, 'value' =>  $deductionValue);
    $this->attributes['donationImport'] = array('length' => 13, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $donationImportValue);
    $this->attributes['donationKind'] = array('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $donationKindValue);
    $this->attributes['ACDeduction'] = array('length' => 2, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $ACDeductionValue);
    $this->attributes['ACDeductionNumber'] = array('length' => 5, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $ACDeductionNumberValue);
    $this->attributes['nature'] = array('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $natureValue);
    $this->attributes['revocation'] = array('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $revocationValue);
    $this->attributes['exerciseRevocationDonative'] = array('length' => 4, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $exerciseRevocationDonativeValue);
    $this->attributes['goodType'] = array('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $goodTypeValue);
    $this->attributes['goodID'] = array('length' => 20, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $goodIDValue);
    $this->attributes['recurrenceDonations'] = array('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $recurrenceDonationsValue);
    $this->attributes['space'] = array('length' => 118, 'dataType' => 'TEXT', 'trimmable' => 'NO', 'value' => '');
  }

  /**
   * Return a string with the correct form to send this for an estate
   * @param $onlyErrors boolean
   * @param $attributes array
   *
   * @return array if $onlyErrors is true, string otherwise
   */
  function getOutput($onlyErrors=false) {

    $warnings = array();
    $errors = array();
    $output = '';

    foreach ($this->attributes as $attribute => $class) {
      if ( mb_strlen($class['value']) > $class['length'] ) {
        if ($class['trimmable'] == 1){
          $errors[] = array('VALUETOOLONG', 'El campo ' . $class['value'] . ' solo puede tener ' . $class['length'] . ' carácteres. Hace falta corregirlo para poder presentar el fichero 182.');
        }
        else {
          $warnings[] = array('VALUETOOLONG', 'El campo ' . $class['value'] . ' solo puede tener ' . $class['length'] . ' carácteres. NO hace falta corregirlo para poder presentar el fichero 182, se cortará el valor en su longitud permitida.');
          $class['value'] = mb_substr($class['value'], 0, $class['length']);
        }
      }
      if ($class['dataType'] == 'NUM'){
        $output .= str_pad($class['value'], $class['length'],'0', STR_PAD_LEFT);
      }
      else {
        $class['value'] = $this->normalizeAlphanumericFields($class['value']);
        $output .= $this->mb_str_pad($class['value'], $class['length']);
      }
    }

    if ($onlyErrors && ( !empty($errors) || !empty($warnings) )) {
      return array($warnings, $errors);
    }

    if ($onlyErrors == false ) {
      return $output;
    }
  }

  public function getOutput993() {
    $output = "";

    $donationImport = $this->attributes['donationImport']['value'];

    $output .= $this->normalizeAlphanumericFields($this->attributes['NIFDeclared']['value'],'993') . ';';
    $output .= ';';
    $output .= $this->normalizeAlphanumericFields($this->attributes['declaredName']['value'],'993') . ';';
    $output .= $this->attributes['NIFDeclarant']['value'] . ';';
    $output .= $this->normalizeAlphanumericFields($this->declarantName,993) . ';';
    $output .= substr_replace($donationImport, ',', strlen($donationImport)-2, 0);

    return $output;
  }
}
