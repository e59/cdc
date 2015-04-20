<?php

namespace Cdc;

use \Knp\Menu\ItemInterface;
use \Knp\Menu\Matcher\Voter\VoterInterface;

class ControllerUriVoter implements VoterInterface {

    private $index;

    public function __construct($index) {
        $this->index = $index;
    }

    public function matchItem(ItemInterface $item) {

        if ($item->getExtra('index') == $this->index) {
            return true;
        }
        return null;
    }

}
