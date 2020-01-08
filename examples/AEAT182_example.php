<?php

require_once 'AEAT182.php';

$declarant = array(
  "exercise" => "2018",
  "NIFDeclarant" => "G55522288",
  "declarantName" => "FUNDACION",
  "supportType"=>"T",
  "contactPerson" => "333524558LOPEZ PEREZ JAVIER",
  "declarationID"=>"1820000000000",
  "LastIdDeclaration"=>"0000000000000",
  "registerNumber"=>0,
  "donationImport"=>0,
  "nature"=>"1",
);

$model = new AEAT182();

$model->setDeclarant($declarant);

$declareds = array(
  'declared1' => array(
    "exercise" => $declarant['exercise'],
    "NIFDeclarant" => $declarant["NIFDeclarant"],
    "externalId"=>'1er declared',
    "NIFDeclared" => 'AAAAA',
    "declaredName" => 'AAAAA',
    "provinceCode" => '09',
    "key" => 'A',
    "deduction" => 'A',
    "donationImport" => 12345,
    "recurrenceDonations" => 'AAAAA',
    "nature" => 1,
  ),
  'declared2' => array(
    "exercise" => $declarant['exercise'],
    "NIFDeclarant" => $declarant["NIFDeclarant"],
    "NIFDeclared" => 'BBBBB',
    "declaredName" => 'BBBBB',
    "provinceCode" => '08',
    "key" => 'B',
    "deduction" => 'B',
    "donationImport" => 12345,
    "recurrenceDonations" => 'BBBBB',
    "nature" => 1,
  ),
  'declared3' => array(
    "exercise" => $declarant['exercise'],
    "NIFDeclarant" => $declarant["NIFDeclarant"],
    "externalId"=>'3er declared',
    "NIFDeclared" => 'AAAAA',
    "declaredName" => 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
    "provinceCode" => '090',
    "key" => 'A',
    "deduction" => 'A',
    "donationImport" => 12345,
    "recurrenceDonations" => 'AAAAA',
    "nature" => 1,
  ),
  'declared4' => array(
    "exercise" => $declarant['exercise'],
    "NIFDeclarant" => $declarant["NIFDeclarant"],
    "NIFDeclared" => 'BBBBBBBBBBBBBBB',
    "declaredName" => 'BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB',
    "provinceCode" => '08',
    "key" => 'B',
    "deduction" => 'B',
    "donationImport" => 12345,
    "recurrenceDonations" => 'BBBBB',
    "nature" => 1,
  ),
);

foreach ($declareds as $key => $declared) {
  $model->addDeclared($declared);
}
echo('<h2>'.'Aqui muestra un pequeño ejemplo de como seria utilitzar el SAVEFILE:'.'</h2>'."\r\n");
$model->saveFile(false);

$errors = $model->check182();

echo("\r\n".'<h2>'.'Aqui muestra un pequeño ejemplo de como seria utilitzar el CHECK182:'.'</h2>'."\r\n");
echo('<h4>'.'este es el array de los WARNING'.'</h4>'."\r\n");
print_r($errors[0]);
echo('<h4>'.'y este es el array de los ERRORES'.'</h4>'."\r\n");
print_r($errors[1]);
