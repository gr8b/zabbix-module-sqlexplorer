<?php

namespace Modules\SqlExplorer\Helpers\Html;

use CTag;

class CFormField extends CTag {

    protected $instance;

    public function __construct($child) {
        $this->instance = $child;
    }

    public function toString($destroy = true) {
        return $this->instance->toString($destroy);
    }
}