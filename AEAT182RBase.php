<?php

/**
 *
 * @package AEAT182
 *
 */
abstract class AEAT182RBase {

  var $attributes = array();

  function __construct($rType, $exercise, $NIFDeclarant) {
    $this->attributes['registerType'] = array (
      'length' => 1,
      'dataType' => 'TEXT',
      'trimmable' => 1,
      'value' => $rType
    );
    $this->attributes['model'] = array (
      'length' => 3,
      'dataType' => 'TEXT',
      'trimmable' => 1,
      'value' => "182"
    );
    $this->attributes['exercise'] = array (
      'length' => 4,
      'dataType' => 'TEXT',
      'trimmable' => 1,
      'value' => $exercise
    );
    $this->attributes['NIFDeclarant'] = array (
      'length' => 9,
      'dataType' => 'TEXT',
      'trimmable' => 1,
      'value' => $NIFDeclarant
    );
  }

  /**
   * @return integer
   */
  function formatToMoney182($import) {
    return intval( floatval($import) * 100 );
  }

  /**
   * Based on https://www.php.net/manual/es/function.str-pad.php#116616
   */
  function mb_str_pad($input, $pad_length) {
    $diff = strlen($input) - mb_strlen($input);
    return str_pad($input, $pad_length + $diff);
  }

  /**
   * Replace the special characters not allowed on 182 presentation model
   *
   * @param string $value
   *
   * return string
   */

  function cleanSpecialChars($value) {

    $value = str_replace (
      array('á', 'à', 'ä', 'â', 'Á', 'À', 'Â', 'Ä', 'Ã', 'Å'),
      array('a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'A'),
      $value
    );

    $value = str_replace (
      array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
      array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
      $value
    );

    $value = str_replace (
      array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
      array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
      $value
    );

    $value = str_replace (
      array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô', 'Õ'),
      array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'O'),
      $value
    );

    $value = str_replace (
      array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
      array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
      $value
    );

    /*
    $value = str_replace (
      array('ñ', 'Ñ', 'ç', 'Ç'),
      array('n', 'N', 'c', 'C'),
      $value
    );
    */

    $value = str_replace (
      array('·', 'ª', 'º', 'Ý', 'Ÿ', 'Ž', ','),
      array('.', '.', '.', 'Y', 'Y', 'Z', ''),
      $value
    );

    return $value;
  }

  /**
   * Drop the excess spaces not allowed on 182 presentation model
   *
   * @param string $value
   *
   * return string
   */
  function normalizeSpaces($value) {

    $value = trim($value);
    $value = explode(" ",$value);
    $value = array_filter($value);
    $value = implode(" ", $value);

    return $value;
  }

  /**
   * Normalize the alphanumeric fields of the 182 presentation model
   *
   * @param string $value
   *
   * return string
   */
  function normalizeAlphanumericFields($value) {

    $value = $this->normalizeSpaces($value);
    $value = $this->cleanSpecialChars($value);

    return $value;
  }

}
