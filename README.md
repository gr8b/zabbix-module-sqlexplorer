## SQL Explorer

![](doc/sqlexplorer.1.png)

Module allow to make queries to database and export result as `.csv` file. Queries can be saved and reused later.
Codemirror is used as query editor. It supports SQL syntax highlight and database table column names autocompletion.

### Compatibility and Zabbix support

Module is designed to work with Zabbix 5.0 and newer.

### Development

Clone repository, run `make docker-init prepare` to build docker image and initialize nodejs modules, then can use `make dev-watch` to rebuild javascript automatically when `app.js` file is changed.
