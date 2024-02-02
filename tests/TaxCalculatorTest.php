<?php

namespace babucat\tests\AEAT;

use babucat\AEAT\AEAT182;
use PHPUnit\Framework\TestCase;

final class TaxCalculatorTest extends TestCase
{
    /** @test */
    public function taxCalculator()
    {
        $test = new AEAT182();
        
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence(AEAT182::NATURAL_PERSON,260.00,255.00,270.00,"08023");

        $this->assertSame('35',$taxCalculatorResult['percentage']);
        $this->assertSame('101,50 €',$taxCalculatorResult['actual_amount_min']);
    }

    /** @test */
    public function taxCalculatorCatalanRelief()
    {
        $test = new AEAT182(NULL,NULL,NULL,NULL,NULL,NULL,NULL,true);
        
        $taxCalculatorResult = $test->getDeductionPercentAndDonationsRecurrence(AEAT182::NATURAL_PERSON,260.00,255.00,270.00,"08023");

        $this->assertSame('35',$taxCalculatorResult['percentage']);
        $this->assertSame('62,50 €',$taxCalculatorResult['actual_amount_min']);
    }

}
