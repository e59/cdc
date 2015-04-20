<?php

namespace Cdc\Menu\Renderer\Breadcrumb;

use \Nette\Utils\Arrays as A;

class ListRenderer extends \Knp\Menu\Renderer\ListRenderer {

    /**
     *
     * @var \Knp\Menu\ItemInterface
     */
    private $last;

    /**
     *
     * @param \Knp\Menu\ItemInterface|array $item
     */
    public function setLast($item) {
        if (!is_array($item)) {
            $item = array($item);
        }

        $this->last = $item;

        $l = end($item);

        if ($l) {
            $l->setCurrent(true);
        }
    }

    public function render(\Knp\Menu\ItemInterface $item, array $options = array()) {

        $root = $item->getRoot();

        $clean = A::get($options, 'breadcrumb_clean_labels', false);

        $itemIterator = new \Knp\Menu\Iterator\RecursiveItemIterator($root);
        $iterator = new \RecursiveIteratorIterator($itemIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $node) {
            $extras = $node->getExtras();
            if (array_key_exists('allow', $extras)) {
                $allow = $extras['allow'];
            } else {
                $allow = true;
            }

            if (!$allow || !$this->matcher->isCurrent($node) && !$this->matcher->isAncestor($node)) {
                $node->getParent()->removeChild($node);
            } else {

                if ($clean) {
                    $node->setLabel(strip_tags($node->getLabel()));
                }

                $node->getParent()->removeChild($node);
                $node->setParent(null);
                $root->addChild($node);
            }
        }

        if ($this->last) {
            foreach ($this->last as $l) {
                $root->addChild($l);
            }
        }

        return parent::render($root, $options);
    }

}
