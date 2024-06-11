## SQL Explorer

![](doc/sqlexplorer.1.png)

Module allow to make queries to database and export result as `.csv` file. Queries can be saved and reused later.
Codemirror is used as query editor. It supports SQL syntax highlight and database table column names autocompletion.
Use *"Administration -> General -> GUI -> Limit for search and filter results"* to configure max rows count to be displayed,
export to `.csv` is done without limiting rows count.

[![Latest Release](https://img.shields.io/github/v/release/gr8b/zabbix-module-sqlexplorer)](https://github.com/gr8b/zabbix-module-sqlexplorer/releases)

### Export file format

All stored SQL queries can be exported as single `.txt` file. Format of one SQL query:
- A line with two dash characters at the beginning, followed by the query name as it is set in the dropdown. The query can contain multiple SQL-style comment lines, but only the first one is used as the query name in the dropdown.
- The SQL code itself, which can span multiple lines.
- A line with two dash characters, marking the end of the query definition.
- An empty line, while not required, is suggested to improve the readability of the export file.

_Note: Successfull import will replace all stored queries._

Example file `Z60.txt`:
```sql
-- All events closed by global correlation rule
SELECT repercussion.clock, repercussion.name, rootCause.clock, rootCause.name AS name
    FROM events repercussion
    JOIN event_recovery ON (event_recovery.eventid=repercussion.eventid)
    JOIN events rootCause ON (rootCause.eventid=event_recovery.c_eventid)
    WHERE event_recovery.c_eventid IS NOT NULL
    ORDER BY repercussion.clock ASC;
--

-- SNMP hosts unreachable
SELECT proxy.host AS proxy, hosts.host, interface.error, CONCAT('zabbix.php?action=host.edit&hostid=', hosts.hostid) AS goTo
    FROM hosts
    LEFT JOIN hosts proxy ON (hosts.proxy_hostid=proxy.hostid)
    JOIN interface ON (interface.hostid=hosts.hostid)
    WHERE LENGTH(interface.error) > 0
        AND interface.type=2;
--
```

### Safe mode

To activate safe mode, the `manifest.json` file must include a `connection` property that contains credentials string in the format `username:password`.
When mode is enabled, module database interactions will use credentials for all database queries.

Example:
```
{
    "connection": "zabbix:zabbix"
}
```

### Compatibility and Zabbix support

For Zabbix 6.4 and newer, up to 7.0, use `*-zabbix-6.4-7.0.zip` file for installation.
For Zabbix version 6.2 and older use `*-zabbix-5.0-6.2.zip` file to install.

### Development

Clone repository, run `make docker-init prepare` to build docker image and initialize nodejs modules, then can use `make dev-watch` to rebuild javascript automatically when `app.js` file is changed.

### Thanks

[Aigars Kadikis](https://github.com/aigarskadikis/) for great ideas, testing and interest in module.
