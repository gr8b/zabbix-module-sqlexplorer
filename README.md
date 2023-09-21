## SQL Explorer

![](doc/sqlexplorer.1.png)

Module allow to make queries to database and export result as `.csv` file. Queries can be saved and reused later.
Codemirror is used as query editor. It supports SQL syntax highlight and database table column names autocompletion.
Use *"Administration -> General -> GUI -> Limit for search and filter results"* to configure max rows count to be displayed,
export to `.csv` is done without limiting rows count.

### Compatibility and Zabbix support

For Zabbix 6.4 and newer, up to 7.0, use `*-zabbix-6.4-7.0.zip` file for installation.
For Zabbix version 6.2 and older use `*-zabbix-5.0-6.2.zip` file to install.

### Development

Clone repository, run `make docker-init prepare` to build docker image and initialize nodejs modules, then can use `make dev-watch` to rebuild javascript automatically when `app.js` file is changed.

### Thanks

[Aigars Kadikis](https://github.com/aigarskadikis/) for great ideas, testing and interest in module.