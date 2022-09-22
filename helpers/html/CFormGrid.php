<?php

namespace Modules\SqlExplorer\Helpers\Html;

use CFormList;

class CFormGrid {

    /** @property CFormList $instance */
    protected $instance;

    public function __construct() {
        $this->instance = new CFormList();
    }

    public function addItem(array $item) {
        $this->instance->addRow(array_shift($item), $item);

        return $this;
    }

    public function toString($destroy = false) {
        return $this->instance->toString($destroy);
    }
}