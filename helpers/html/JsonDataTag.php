<?php

namespace Modules\SqlExplorer\Helpers\Html;

class JsonDataTag extends RawContent {

    public function __construct($id = null, $content = null) {
        parent::__construct('script', true);
        $this->setAttribute('type', 'text/json');

        if ($id !== null) {
            $this->setId($id);
        }

        if ($content !== null) {
            $this->setData($content);
        }
    }

    public function setData($value) {
        $value = json_encode($value);

        return parent::setRawContent($value);
    }
}
