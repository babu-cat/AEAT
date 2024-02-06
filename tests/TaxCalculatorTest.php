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
            'persona física no recurrencia < 250 ' => [AEAT182::NATURAL_PERSON,100.00,100.00,200.00,"50005","35","35,00 €","80,00 €","20,00 €","65,00 €","40,00 €","80,00 €","20,00 €","60,00 €","0,00 €","8,33 €"], // 9299
            'persona física no recurrencia += 250 ' => [AEAT182::NATURAL_PERSON,500.00,100.00,0.00,"20830","35","175,00 €","242,50 €","257,50 €","325,00 €","200,00 €","300,00 €","200,00 €","300,00 €","41,67 €","95,83 €"], // BO
            'persona jurídica no recurrencia' => [AEAT182::SOCIETIES,150.00,150.00,0.00,"48015","35","52,50 €","52,50 €","97,50 €","97,50 €","60,00 €","60,00 €","90,00 €","90,00 €","100,00 €","100,00 €"], // MAL DEFINIT
            'persona física recurrencia < 250' => [AEAT182::NATURAL_PERSON,25.00,22.00,22.00,"08023","35","101,50 €"], // MAL DEFINIT
            //'persona física recurrencia += 250' => [AEAT182::NATURAL_PERSON,260.00,255.00,250.00,"08023","35","101,50 €"], // MAL DEFINIT
            //'persona jurídica recurrencia' => [AEAT182::SOCIETIES,260.00,255.00,250.00,"08023","35","101,50 €"], // MAL DEFINIT
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

        // @todo: Check all taxCalculatorResult values
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
