<?php
/**
 * @copyright Copyright &copy; Arno Slatius 2015
 * @package yii2-nestable
 * @version 1.0
 */

namespace slatiusa\nestable;

use \creocoder\nestedsets\NestedSetsBehavior;

/**
 * Create nestable lists using drag & drop for Yii 2.0.
 * Based on jquery.nestable.js plugin.
 * @see http://dbushell.github.io/Nestable/
 *
 * @author Arno Slatius <a.slatius@gmail.com>
 * @since 1.0
 */
class NestableBehavior extends NestedSetsBehavior
{
    /**
    * Wrapper function to be able to use the protected method of the NestedSetsBehavior
    *
    * @param integer $value
    * @param integer $depth
    */
    public function nodeMove($value, $depth) {
        $this->node = $this->owner;
        parent::moveNode($value, $depth);
    }
}