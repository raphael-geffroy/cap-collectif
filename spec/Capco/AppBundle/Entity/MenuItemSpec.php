<?php

namespace spec\Capco\AppBundle\Entity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MenuItemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Capco\AppBundle\Entity\MenuItem');
    }
}
