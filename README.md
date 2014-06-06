DataTablesFusionTablesSSP
=========================

[Fusion Tables](https://support.google.com/fusiontables/answer/2571232) Server side processing class for [DataTables](http://datatables.net/)

Installation
------------

Copy the files and be sure to edit api.php with the necessary details. You will need a key to access Google Fusion Tables and you will need the name of the table you want to query. Additional configuration is done in Javascript. Do not forget to edit that. The columns are important, and be sure to give the columns the same name as in your fusion table.

Caveats
-------

1. The query engine of Google Fusion only allows AND clauses in the where part of the query statement. This means you can effectively only search on one of the columns (If you make more columns sortable, all colums must match the search query).
2. There is no support for regex search queries yet

Contributing
------------

As per usual with GitHub. Fork, make changes and make a pull request.

Contact
-------

Please contact me (bje at dds dot nl) for any questions.
