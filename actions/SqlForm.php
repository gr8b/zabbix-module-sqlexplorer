<?php

namespace Modules\SqlExplorer\Actions;

use DB;
use CUrl;
use CMessageHelper;
use CControllerResponseData;
use CControllerResponseRedirect;
use CSettingsHelper;
use Modules\SqlExplorer\Helpers\ProfileHelper as Profile;

class SqlForm extends BaseAction {

    protected function checkInput() {
        $fields = $this->getSqlFormValidationRules();

        return $this->validateInput($fields);
    }

    protected function getSqlFormValidationRules() {
        if ($this->getAction() === 'sqlexplorer.csv') {
            return [
                'fav' => 'int32',
                'query' => 'string|required|not_empty',
                'add_column_names' => 'in 0,1'
            ];
        }

        return [
            'fav' => 'int32',
            'name' => 'string',
            'query' => 'string',
            'add_column_names' => 'in 0,1',
            'preview' => 'in 0,1'
        ];
    }

    protected function doAction() {
        $data = [
            'fav' => 0,
            'tab_url' => Profile::getPersonal(Profile::KEY_TAB_URL, 0),
            'text_to_url' => Profile::getPersonal(Profile::KEY_TEXT_TO_URL, 1),
            'autoexec' => Profile::getPersonal(Profile::KEY_AUTOEXEC_SQL, 0),
            'name' => '',
            'query'	 => "\n\n\n",
            'add_column_names' => Profile::getPersonal(Profile::KEY_SHOW_HEADER, 0),
            'add_bom_csv' => Profile::getPersonal(Profile::KEY_BOM_CSV, 0),
            'force_single_line_csv' => Profile::getPersonal(Profile::KEY_SINGLE_LINE_CSV, 0),
            'stopwords' => Profile::getPersonal(Profile::KEY_STOP_WORDS, Profile::DEFAULT_STOP_WORDS)
        ];
        $this->getInputs($data, array_keys($data));

        if ($this->hasInput('query')) {
            $query = @base64_decode($data['query']);

            if ($query !== false) {
                $data['query'] = urldecode($query);
            }
        }

        $this->setResponse(
            $this->getAction() === 'sqlexplorer.csv'
                ? $this->getCsvResponse($data)
                : $this->getHtmlResponse($data)
        );
    }

    protected function getCsvResponse(array $data) {
        $error = null;
        $rows = $this->module->dbSelect($data['query'], $error);

        if ($error !== null) {
            $response = new CControllerResponseRedirect(
                (new CUrl('zabbix.php'))
                    ->setArgument('action', 'sqlexplorer.form')
                    ->getUrl()
            );
            $response->setFormData($data);

            if (version_compare(ZABBIX_VERSION, '6.0', '<')) {
                [$message] = clear_messages();
                $response->setMessageError($message['message']);
            }
            else {
                CMessageHelper::setErrorTitle(_('Query error'));
            }

            return $response;
        }

        if ($rows && $data['add_column_names']) {
            array_unshift($rows, array_keys($rows[0]));
        }

        if ($data['force_single_line_csv']) {
            foreach ($rows as &$row) {
                foreach ($row as &$col) {
                    $col = str_replace(["\r", "\n"], ['', ' '], $col);
                }
                unset($col);
            }
            unset($row);
        }

        $data = [
            'main_block' => ($data['add_bom_csv'] ? "\xef\xbb\xbf" : '').zbx_toCSV($rows)
        ];
        $response = new CControllerResponseData($data);
        $response->setFileName('query_export.csv');

        return $response;
    }

    protected function getHtmlResponse(array $data) {
        if ($this->hasInput('preview')) {
            $error = null;
            $rows = $this->module->dbSelect($data['query'], $error);

            if ($error === null) {
                $data['rows_limit'] = $this->getGuiSearchLimit();
                $data['rows_count'] = count($rows);

                if ($data['rows_count'] > $data['rows_limit']) {
                    $data['rows'] = array_slice($rows, 0, $data['rows_limit']);
                }

                $data['rows'] = $rows;
            }

            if (version_compare(ZABBIX_VERSION, '6.0', '<')) {
                show_messages();
            }
        }

        $data['csrf_token'] = [
            'sqlexplorer.form' => $this->getActionCsrfToken('sqlexplorer.form'),
            'sqlexplorer.csv' => $this->getActionCsrfToken('sqlexplorer.csv'),
            'sqlexplorer.config' => $this->getActionCsrfToken('sqlexplorer.config'),
            'sqlexplorer.queries' => $this->getActionCsrfToken('sqlexplorer.queries')
        ];
        $data['public_path'] = $this->module->getAssetsUrl();
        $data['database'] = $this->module->getDatabase();
        $queries = Profile::getQueries();
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

    public function getGuiSearchLimit() {
        if (version_compare(ZABBIX_VERSION, '5.2', '>=')) {
            return CSettingsHelper::get(CSettingsHelper::SEARCH_LIMIT);
        }

        return select_config()['search_limit'];
    }
}
