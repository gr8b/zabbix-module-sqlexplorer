<?php

namespace Modules\SqlExplorer\Helpers\Html;

class ScriptTag extends RawContent {

    public function __construct($content = null) {
        parent::__construct('script', true);
        $this->setAttribute('type', 'text/javascript');

        if (is_string($content)) {
            $this->setRawContent($content);
        }
    }

    public function setAttribute($attribute, $value) {
        if ($attribute === 'src') {
            $this->items = [];
        }

        return parent::setAttribute($attribute, $value);
    }

    public function setRawContent($value) {
        $this->setAttribute('src', null);

        return parent::setRawContent($value);
    }
}
