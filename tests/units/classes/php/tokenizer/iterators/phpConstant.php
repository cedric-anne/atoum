<?php

namespace atoum\atoum\tests\units\php\tokenizer\iterators;

use atoum\atoum;

require_once __DIR__ . '/../../../../runner.php';

class phpConstant extends atoum\test
{
    public function testClass()
    {
        $this
            ->testedClass
                ->isSubClassOf(atoum\php\tokenizer\iterator::class)
        ;
    }
}
