<?php

namespace Cdc\Widget;

class Menu {

    use \Nette\SmartObject;

    public static function render(\Knp\Menu\ItemInterface $menu = null, array $options = array(), $index = null) {
        if (!$menu) {
            return null;
        }
        $matcher = new \Knp\Menu\Matcher\Matcher;
        $voter = new \Cdc\ControllerUriVoter($index);
        $matcher->addVoter($voter);
        $renderer = new \Cdc\Menu\Renderer\ListRenderer($matcher);
        return $renderer->render($menu, $options);
    }

}
