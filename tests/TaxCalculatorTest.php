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
            'persona física no recurrencia < 250 ' => [AEAT182::NATURAL_PERSON,100.00,200.00,"50005","40","40,00 €","80,00 €","20,00 €","60,00 €"],
            'persona física no recurrencia += 250 ' => [AEAT182::NATURAL_PERSON,500.00,0.00,"20830","40","200,00 €","300,00 €","200,00 €","300,00 €"],
            'persona jurídica no recurrencia' => [AEAT182::SOCIETIES,150.00,0.00,"48015","40","60,00 €","60,00 €","90,00 €","90,00 €"],
            'persona física recurrencia < 250' => [AEAT182::NATURAL_PERSON,25.00,25.00,"28050","45","11,25 €","20,00 €","5,00 €","13,75 €"],
            'persona física recurrencia += 250' => [AEAT182::NATURAL_PERSON,300.00,250.00,"08023","45","135,00 €","222,50 €","77,50 €","165,00 €"],
            'persona jurídica recurrencia' => [AEAT182::SOCIETIES,622.00,622.00,"28010","50","311,00 €","311,00 €","311,00 €","311,00 €"]
        ];
    }

    /**
    * @test
    * @dataProvider taxCalculatorProvider
    */
    public function taxCalculator($contactType, $amountThisYear, $amountLastYear, $postalCode,
                                  $expectedPercentage,$reductionMin, $reductionMax, $expectedActualAmountMin,$expectedActualAmountMax)
    {
        $test = new AEAT182();
        
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $postalCode);
        $this->assertSame($expectedPercentage,$taxCalculatorResult['percentage']);
        $this->assertSame($reductionMin,$taxCalculatorResult['reduction_min']);
        $this->assertSame($reductionMax,$taxCalculatorResult['reduction_max']);
        $this->assertSame($expectedActualAmountMin,$taxCalculatorResult['actual_amount_min']);
        $this->assertSame($expectedActualAmountMax,$taxCalculatorResult['actual_amount_max']);
    }

    public static function taxCalculatorCatalanReliefProvider(): array
    {
        return [        
            'persona física catalana no recurrencia  < 250' => [AEAT182::NATURAL_PERSON,60.00,0.00,"08035","55","33,00 €","57,00 €","3,00 €","27,00 €"],
            'persona física catalana no recurrencia >= 250' => [AEAT182::NATURAL_PERSON,1500.00,2300.00,"08023","55","825,00 €","925,00 €","575,00 €","675,00 €"],
            'persona física catalana recurrencia  < 250' => [AEAT182::NATURAL_PERSON,25.00,22.00,"43001","60","15,00 €","23,75 €","1,25 €","10,00 €"],
            'persona física catalana recurrencia >= 250' => [AEAT182::NATURAL_PERSON,300.00,300.00,"08032","60","180,00 €","267,50 €","32,50 €","120,00 €"],
        ];
    }

    /**
    * @test
    * @dataProvider taxCalculatorCatalanReliefProvider
    */
    public function taxCalculatorCatalanRelief($contactType, $amountThisYear, $amountLastYear, $postalCode,
                                                $expectedPercentage,$reductionMin, $reductionMax, $expectedActualAmountMin,$expectedActualAmountMax)

    {
        $test = new AEAT182(autonomousDeduction: true);
 
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $postalCode);

        $this->assertSame($expectedPercentage,$taxCalculatorResult['percentage']);
        $this->assertSame($reductionMin,$taxCalculatorResult['reduction_min']);
        $this->assertSame($reductionMax,$taxCalculatorResult['reduction_max']);
        $this->assertSame($expectedActualAmountMin,$taxCalculatorResult['actual_amount_min']);
        $this->assertSame($expectedActualAmountMax,$taxCalculatorResult['actual_amount_max']);
    }

}
