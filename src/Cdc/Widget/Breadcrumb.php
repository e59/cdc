<?php

namespace Cdc\Widget;

class Breadcrumb {

    use \Nette\SmartObject;

    public static function render(\Knp\Menu\ItemInterface $menu = null, array $options = array(), $index = null, $lastItem = null) {
        if (!$menu) {
            return null;
        }
        $matcher = new \Knp\Menu\Matcher\Matcher;
        $voter = new \Cdc\ControllerUriVoter($index);
        $matcher->addVoter($voter);
        $renderer = new \Cdc\Menu\Renderer\Breadcrumb\ListRenderer($matcher, $options);
        $renderer->setLast($lastItem);
        return $renderer->render($menu, $options);
    }

}
