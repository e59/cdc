<?php

namespace Cdc\Menu\Renderer;

use \Knp\Menu\Renderer\ListRenderer as LR;
use \Knp\Menu\ItemInterface;
use \Knp\Menu\Matcher\MatcherInterface;

/**
 * Renders MenuItem tree as unordered list
 */
class ListRenderer extends LR {

    protected function renderItem(ItemInterface $item, array $options) {

        $extras = $item->getExtras();
        if (array_key_exists('allow', $extras)) {
            $allow = $extras['allow'];
        } else {
            $allow = true;
        }

        if (!$allow) {
            $item->setDisplayChildren(false);
            $item->setDisplay(false);
        }

        return parent::renderItem($item, $options);
    }

}
