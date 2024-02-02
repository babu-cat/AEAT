<?php

namespace babucat\AEAT;

/**
 *
 * @package AEAT182
 *
 */
class AEAT182RDeclarant extends AEAT182RBase {

  /**
   * Class construct
   *  @param $excercise integer
   *  @param $NIFDeclarant String
   *  @param $declarantName String
   *  @param $supportType String
   *  @param $contactPerson String
   *  @param $declarationID integer
   *  @param $complOrSust String
   *  @param $lastIdDeclaration integer
   *  @param $registerNumber integer (optional)
   *  @param $donationImport integer (optional)
   *  @param $nature integer
   *  @param $NIFprotected String (optional)
   *  @param $nameProtected String
   *  @param $electronicSign string
   *
   *  @todo Revisar que campos son obligatorios
   */
  function __construct($params) {
    parent::__construct(1, $params['exercise'], $params['NIFDeclarant']);
    $declarantNameValue = ( isset($params['declarantName']) ) ? $params['declarantName'] : '';
    $supportTypeValue = ( isset($params['supportType']) ) ? $params['supportType'] : '';
    $contactPersonValue = ( isset($params['contactPerson']) ) ? $params['contactPerson'] : '';
    $declarationIDValue = ( isset($params['declarationID']) ) ? $params['declarationID'] : '';
    $complosustValue = ( isset($params['complosust']) ) ? $params['complosust'] : '';
    $lastIdDeclarationValue = ( isset($params['lastIdDeclaration']) ) ? $params['lastIdDeclaration'] : '';
    $registerNumberValue = ( isset($params['registerNumber']) ) ? $params['registerNumber'] : '0';
    $donationsImportValue = ( isset($params['donationsImport']) ) ? $params['donationsImport'] : '0';
    $natureValue = ( isset($params['nature']) ) ? $params['nature'] : '';
    $NIFprotectedValue = ( isset($params['NIFprotected']) ) ? $params['NIFprotected'] : '';
    $nameProtectedValue = ( isset($params['nameProtected']) ) ? $params['nameProtected'] : '';
    $electronicSignValue = ( isset($params['electronicSign']) ) ? $params['electronicSign'] : '';
    $this->attributes['declarantName'] = array ('length' => 40, 'dataType' => 'TEXT', 'trimmable' => 0, 'value' => $declarantNameValue);
    $this->attributes['supportType'] = array ('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $supportTypeValue);
    $this->attributes['contactPerson'] = array ('length' => 49, 'dataType' => 'TEXT', 'trimmable' => 0, 'value' => $contactPersonValue);
    $this->attributes['declarationID'] = array ('length' => 13, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $declarationIDValue);
    $this->attributes['complosust'] = array ('length' => 2, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $complosustValue);
    $this->attributes['lastIdDeclaration'] = array ('length' => 13, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $lastIdDeclarationValue);
    $this->attributes['registerNumber'] = array ('length' => 9, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $registerNumberValue);
    $this->attributes['donationsImport'] = array ('length' => 15, 'dataType' => 'NUM', 'trimmable' => 1, 'value' => $donationsImportValue);
    $this->attributes['nature'] = array ('length' => 1, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $natureValue);
    $this->attributes['NIFprotected'] = array ('length' => 9, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $NIFprotectedValue);
    $this->attributes['nameProtected'] = array ('length' => 40, 'dataType' => 'TEXT', 'trimmable' => 0,'value' => $nameProtectedValue);
    $this->attributes['space'] = array ('length' => 28, 'dataType' => 'TEXT', 'trimmable' => 0, 'value' => '');
    $this->attributes['electronicSign'] = array ('length' => 13, 'dataType' => 'TEXT', 'trimmable' => 1, 'value' => $electronicSignValue);
  }

  /**
   * Update the values of donationImport and registerNumber from $declareds array
   *
   * @param $amount integer
   */
  function addDeclared ($amount){
      $this->attributes['registerNumber']['value']++;
      $this->attributes['donationsImport']['value'] += $amount;
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
      if ($class['dataType'] == 'NUM') {
        if ($attribute == 'declarationID') {
          $output .= str_pad($class['value'], $class['length'], '0', STR_PAD_RIGHT);
        }
        else {
          $output .= str_pad($class['value'], $class['length'], '0', STR_PAD_LEFT);
        }
      }
      else {
        $class['value'] = $this->normalizeAlphanumericFields($class['value']);
        $output .= $this->mb_str_pad($class['value'], $class['length']);
      }
    }

    if ( $onlyErrors && ( !empty($errors) || !empty($warnings) ) ) {
      return array($warnings, $errors);
    }

    if ($onlyErrors == false ) {
      return $output;
    }
  }

}
