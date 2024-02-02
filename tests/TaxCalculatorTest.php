<?php

namespace babucat\tests\AEAT;

use babucat\AEAT\AEAT182;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaxCalculatorTest extends TestCase
{

    public static function taxCalculatorProvider(): array
    {
        // @todo: Add complete testing set

        return [
            'persona física no recurrencia ' => [AEAT182::NATURAL_PERSON,260.00,255.00,270.00,"08023","35","101,50 €"], // BO
            'persona jurídica no recurrencia' => [AEAT182::SOCIETIES,260.00,255.00,270.00,"08023","35","101,50 €"], // MAL DEFINIT
            'persona física recurrencia' => [AEAT182::NATURAL_PERSON,260.00,255.00,250.00,"08023","35","101,50 €"], // MAL DEFINIT
            'persona jurídica recurrencia' => [AEAT182::SOCIETIES,260.00,255.00,250.00,"08023","35","101,50 €"], // MAL DEFINIT
        ];
    }

    /**
    * @test
    * @dataProvider taxCalculatorProvider
    */
    public function taxCalculator($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode,
                                  $expectedPercentage,$expectedActualAmountMin)
    {
        $test = new AEAT182();
        
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode);

        // @todo: Check all taxCalculatorResult values
        $this->assertSame($expectedPercentage,$taxCalculatorResult['percentage']);
        $this->assertSame($expectedActualAmountMin,$taxCalculatorResult['actual_amount_min']);
    }

    public static function taxCalculatorCatalanReliefProvider(): array
    {
        // @todo: Add complete testing set

        return [
            'persona física recurrencia catalana' => [AEAT182::NATURAL_PERSON,260.00,255.00,250.00,"08023","35","101,50 €"], // MAL DEFINIT
            'persona física recurrencia no catalana' => [AEAT182::NATURAL_PERSON,260.00,255.00,250.00,"28001","35","101,50 €"], // MAL DEFINIT
            'persona física no recurrencia catalana' => [AEAT182::NATURAL_PERSON,260.00,255.00,270.00,"08023","35","62,50 €"], // BO
            'persona física no recurrencia no catalana' => [AEAT182::NATURAL_PERSON,260.00,255.00,270.00,"28001","35","101,50 €"], // BO
        ];
    }

    /**
    * @test
    * @dataProvider taxCalculatorCatalanReliefProvider
    */
    public function taxCalculatorCatalanRelief($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode,
                                               $expectedPercentage,$expectedActualAmountMin)
    {
        $test = new AEAT182(NULL,NULL,NULL,NULL,NULL,NULL,NULL,true);
 
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence($contactType, $amountThisYear, $amountLastYear, $amountTwoYearBefore, $postalCode);

        // @todo: Check all taxCalculatorResult values
        $this->assertSame($expectedPercentage,$taxCalculatorResult['percentage']);
        $this->assertSame($expectedActualAmountMin,$taxCalculatorResult['actual_amount_min']);
    }

}
