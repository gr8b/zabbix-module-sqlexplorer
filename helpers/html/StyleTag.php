<?php

namespace Modules\SqlExplorer\Helpers\Html;

class StyleTag extends RawContent {

    public function __construct($content = null) {
        parent::__construct('style', true);
        $this->setAttribute('type', 'text/css');

        if (is_string($content)) {
            $this->setStyle($content);
        }
    }

    public function setStyle($value) {
        $value = preg_replace('/^\s+/m', '', $value);

        return parent::setRawContent($value);
    }
}
