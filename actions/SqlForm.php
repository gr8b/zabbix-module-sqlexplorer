<?php

namespace Modules\SqlExplorer\Actions;

use DB;
use CUrl;
use CMessageHelper;
use CControllerResponseData;
use CControllerResponseRedirect;
use CProfile;

class SqlForm extends BaseAction {

    protected function checkInput() {
        $fields = $this->getValidationRules();

        return $this->validateInput($fields);
    }

    protected function getValidationRules() {
        if ($this->getAction() === 'sqlexplorer.csv') {
            return [
                'fav' => 'int32',
                'query' => 'string|required|not_empty',
                'add_column_names' => 'in 1'
            ];
        }

        return [
            'fav' => 'int32',
            'name' => 'string',
            'query' => 'string',
            'add_column_names' => 'in 1',
            'preview' => 'in 1'
        ];
    }

    protected function doAction() {
        $data = [
            'fav' => 0,
            'name' => '',
            'query'	 => "\n\n\n",
            'add_column_names' => 0
        ];
        $this->getInputs($data, array_keys($data));

        $this->setResponse(
            $this->getAction() === 'sqlexplorer.csv'
                ? $this->getCsvResponse($data)
                : $this->getHtmlResponse($data)
        );
    }

    protected function getCsvResponse(array $data) {
        $cursor = DBselect($data['query']);

        if ($cursor === false) {
            $response = new CControllerResponseRedirect(
                (new CUrl('zabbix.php'))
                    ->setArgument('action', 'sqlexplorer.form')
                    ->getUrl()
            );
            $response->setFormData($this->getInputAll());

            if (version_compare(ZABBIX_VERSION, '6.0', '<')) {
                [$message] = clear_messages();
                $response->setMessageError($message['message']);
            }
            else {
                CMessageHelper::setErrorTitle(_('Query error'));
            }

            return $response;
        }

        $rows = DBfetchArray($cursor);

        if ($rows && $data['add_column_names']) {
            array_unshift($rows, array_keys($rows[0]));
        }

        $data = [
            'main_block' => zbx_toCSV($rows)
        ];
        $response = new CControllerResponseData($data);
        $response->setFileName('query_export.csv');

        return $response;
    }

    protected function getHtmlResponse(array $data) {
        if ($this->hasInput('preview')) {
            $cursor = DBselect($data['query']);
            $data['rows'] = $cursor === false ? [] : DBfetchArray($cursor);

            if ($data['rows'] && $data['add_column_names']) {
                array_unshift($data['rows'], array_keys($data['rows'][0]));
            }

            if (version_compare(ZABBIX_VERSION, '6.0', '<')) {
                show_messages();
            }
        }

        $data['public_path'] = $this->module->getAssetsUrl();
        $data['database'] = $this->module->getDatabase();
        $queries = array_map('json_decode', CProfile::getArray(StoredSql::QUERIES_PROFILE_KEY, []));
        $queries = array_map('get_object_vars', $queries);
        $data['queries'] = array_merge([['title' => '', 'query' => "\n\n\n"]], array_values($queries));

        $data['db_schema'] = [];
        foreach (DB::getSchema() as $table => $schema) {
            $data['db_schema'][$table] = [];

            foreach ($schema['fields'] as $field => $field_schema) {
                // https://codemirror.net/docs/ref/#autocomplete.Completion
                $info = $schema['key'] === $field ? _('Primary key') : '';
                $data['db_schema'][$table][] = [
                    'label' => $field,
                    'info' => $info
                ];
            }
        };

        $response = new CControllerResponseData($data);
        $response->setTitle(_('SQL Explorer'));

        return $response;
    }
}
