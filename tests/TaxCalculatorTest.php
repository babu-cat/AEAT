<?php

namespace babucat\tests\AEAT;

use babucat\AEAT\AEAT182;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaxCalculatorTest extends TestCase
{

    public static function taxCalculatorProvider(): array
    {
        return [
            'persona física no recurrencia < 250 ' => [AEAT182::NATURAL_PERSON,100.00,100.00,200.00,"50005","35","35,00 €","80,00 €","20,00 €","65,00 €","40,00 €","80,00 €","20,00 €","60,00 €","8,33 €","8,33 €"],
            'persona física no recurrencia += 250 ' => [AEAT182::NATURAL_PERSON,500.00,100.00,0.00,"20830","35","175,00 €","242,50 €","257,50 €","325,00 €","200,00 €","300,00 €","200,00 €","300,00 €","41,67 €","95,83 €"],
            'persona jurídica no recurrencia' => [AEAT182::SOCIETIES,150.00,150.00,0.00,"48015","35","52,50 €","52,50 €","97,50 €","97,50 €","60,00 €","60,00 €","90,00 €","90,00 €","12,50 €","12,50 €"],
            'persona física recurrencia < 250' => [AEAT182::NATURAL_PERSON,25.00,25.00,25.00,"28050","40","10,00 €","20,00 €","5,00 €","15,00 €","11,25 €","20,00 €","5,00 €","13,75 €","2,27 €","2,27 €"],
            'persona física recurrencia += 250' => [AEAT182::NATURAL_PERSON,300.00,250.00,250.00,"08023","40","120,00 €","180,00 €","120,00 €","180,00 €","135,00 €","222,50 €","77,50 €","165,00 €","27,27 €","77,27 €"],
            'persona jurídica recurrencia' => [AEAT182::SOCIETIES,622.00,622.00,622.00,"28010","40","248,80 €","248,80 €","373,20 €","373,20 €","311,00 €","311,00 €","311,00 €","311,00 €","124,40 €","124,40 €"]
        ];
    }

    /**
    * @test
    * @dataProvider taxCalculatorProvider
    */
    public function taxCalculator($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode,
                                  $expectedPercentage,$reductionMin, $reductionMax, $expectedActualAmountMin,$expectedActualAmountMax,
                                  $reductionMinNew, $reductionMaxNew, $expectedActualAmountMinNew, $expectedActualAmountMaxNew,
                                  $contributionMinNew, $contributionMaxNew)
    {
        $test = new AEAT182();
        
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode);

        $this->assertSame($expectedPercentage,$taxCalculatorResult['percentage']);
        $this->assertSame($reductionMin,$taxCalculatorResult['reduction_min']);
        $this->assertSame($reductionMax,$taxCalculatorResult['reduction_max']);
        $this->assertSame($expectedActualAmountMin,$taxCalculatorResult['actual_amount_min']);
        $this->assertSame($expectedActualAmountMax,$taxCalculatorResult['actual_amount_max']);
        $this->assertSame($reductionMinNew,$taxCalculatorResult['reduction_new_min']);
        $this->assertSame($reductionMaxNew,$taxCalculatorResult['reduction_new_max']);    
        $this->assertSame($expectedActualAmountMinNew,$taxCalculatorResult['actual_amount_min_new']);
        $this->assertSame($expectedActualAmountMaxNew,$taxCalculatorResult['actual_amount_max_new']);
        $this->assertSame($contributionMinNew,$taxCalculatorResult['contribution_new_min']);
        $this->assertSame($contributionMaxNew,$taxCalculatorResult['contribution_new_max']);
    }

    public static function taxCalculatorCatalanReliefProvider(): array
    {
        return [        
            'persona física catalana no recurrencia  < 250' => [AEAT182::NATURAL_PERSON,60.00,60.00,0.00,"08035","50","30,00 €","57,00 €","3,00 €","30,00 €","33,00 €","57,00 €","3,00 €","27,00 €","6,67 €","6,67 €"],
            'persona física catalana no recurrencia >= 250' => [AEAT182::NATURAL_PERSON,1500.00,2300.00,2300.00,"08023","50","750,00 €","817,50 €","682,50 €","750,00 €","825,00 €","925,00 €","575,00 €","675,00 €","166,67 €","238,89 €"],
            'persona física catalana recurrencia  < 250' => [AEAT182::NATURAL_PERSON,25.00,22.00,22.00,"43001","55","13,75 €","23,75 €","1,25 €","11,25 €","15,00 €","23,75 €","1,25 €","10,00 €","3,13 €","3,13 €"],
            'persona física catalana recurrencia >= 250' => [AEAT182::NATURAL_PERSON,300.00,300.00,300.00,"08032","55","165,00 €","225,00 €","75,00 €","135,00 €","180,00 €","267,50 €","32,50 €","120,00 €","37,50 €","106,25 €"],
        ];
    }

    /**
    * @test
    * @dataProvider taxCalculatorCatalanReliefProvider
    */
    public function taxCalculatorCatalanRelief($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode,
                                                $expectedPercentage,$reductionMin, $reductionMax, $expectedActualAmountMin,$expectedActualAmountMax,
                                                $reductionMinNew, $reductionMaxNew, $expectedActualAmountMinNew, $expectedActualAmountMaxNew,
                                                $contributionMinNew, $contributionMaxNew)

    {
        $test = new AEAT182(autonomousDeduction: true);
 
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode);

        $this->assertSame($expectedPercentage,$taxCalculatorResult['percentage']);
        $this->assertSame($reductionMin,$taxCalculatorResult['reduction_min']);
        $this->assertSame($reductionMax,$taxCalculatorResult['reduction_max']);
        $this->assertSame($expectedActualAmountMin,$taxCalculatorResult['actual_amount_min']);
        $this->assertSame($expectedActualAmountMax,$taxCalculatorResult['actual_amount_max']);
        $this->assertSame($reductionMinNew,$taxCalculatorResult['reduction_new_min']);
        $this->assertSame($reductionMaxNew,$taxCalculatorResult['reduction_new_max']);    
        $this->assertSame($expectedActualAmountMinNew,$taxCalculatorResult['actual_amount_min_new']);
        $this->assertSame($expectedActualAmountMaxNew,$taxCalculatorResult['actual_amount_max_new']);
        $this->assertSame($contributionMinNew,$taxCalculatorResult['contribution_new_min']);
        $this->assertSame($contributionMaxNew,$taxCalculatorResult['contribution_new_max']);
    }

}
