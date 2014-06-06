<?php

/*
 * Helper functions for building a DataTables server-side processing SQL query
 *
 * The static functions in this class are just helper functions to help build
 * the SQL used in the DataTables demo server-side processing scripts. These
 * functions obviously do not represent all that can be done with server-side
 * processing, they are intentionally simple to show how it works. More complex
 * server-side processing operations will likely require a custom script.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

class FusionTablesSSP {
    var $fusion_base_url = 'https://www.googleapis.com/fusiontables/v1/query';

    function __construct($table, $key) {
        $this->key = $key;
        $this->table = $table;
    }

    function getDrawCount($reuest) {
        return intval($request['draw']);
    }

    function getColumns($request) {
        return $request['columns'];
    }

    function getOffset($request) {
        $start = 0;

        if (array_key_exists('start', $request) && ($request['start'] != '')) {
            $start = $request['start'];
        }

        return $start;
    }

    function getLimit($request) {
        $limit = 10;

        if (array_key_exists('length', $request) && ($request['length'] != '')) {
            $limit = $request['length'];
        }

        return $limit;
    }

    function getOrderBy($request, $columns) {
        if (array_key_exists('order', $request) && is_array($request['order']) && (count($request['order']) > 0)) {
            $orderByParts = array();

            foreach($request['order'] as $idx => $order) {
                $column = $columns[$order['column']];
                $orderByParts[] = $column['name'] .' '. $order['dir'];
            }

            return $orderByParts;
        } else {
            return array();
        }
    }

    function getSearchConditions($q, $isRegex, $columns) {
        $conditions = array();
        foreach($columns as $idx => $column) {
            if ($column['searchable'] == "true") {
                $conditions[] = $column['name'] .' CONTAINS IGNORING CASE \''. $q .'\'';
            }
        }
        return $conditions;
    }

    function getConditions($request, $columns) {
        if (array_key_exists('search', $request) && ($request['search']['value'] != "")) {
            // no support for regex search yet ..
            return $this->getSearchConditions($request['search']['value'], false, $columns);
        } else {
            return array();
        }
    }

    function buildQuery(
        $columns = array(),
        $conditions = array(),
        $group_by = array(),
        $order_by = array(),
        $offset = 0,
        $limit = 0
        ) {

        if (count($columns) > 0) {
            $columns_sql = join(',', $columns);
        } else {
            $columns_sql = "*";
        }

        if (count($conditions) > 0) {
            $conditions_sql = 'WHERE '. join(' AND ', $conditions);
        } else {
            $conditions_sql = "";
        }

        if (count($group_by) > 0) {
            $group_by_sql = "GROUP BY ". join(',', $group_by);
        } else {
            $group_by_sql = "";
        }

        if (count($order_by) > 0) {
            $order_by_sql = "ORDER BY ". join(',', $order_by);
        }

        if ($limit > 0) {
            $limit_sql = "LIMIT $limit";
        } else {
            $limit_sql = "";
        }

        return "SELECT $columns_sql FROM ". $this->table ." $conditions_sql $group_by_sql $order_by_sql OFFSET $offset $limit_sql";
    }

    function makeUrl($query) {
        $args = http_build_query(array('sql' => $query, 'key' => $this->key));
        return $this->fusion_base_url .'?'. $args;
    }

    function httpRequest($url) {
        return file_get_contents($url);
    }

    function getTotalRecordsCount() {
        // get total count
        $counts = json_decode($this->httpRequest($this->makeUrl(
            $this->buildQuery(
                array('COUNT()'),
                array(),
                array(),
                array(),
                0,
                0
            )
        )));

        return intval($counts->rows[0][0]);
    }

    function getFilteredRecordsCount($orderBy, $conditions) {
        // get total count
        $counts = json_decode($this->httpRequest($this->makeUrl(
            $this->buildQuery(
                array('COUNT()'),
                $conditions,
                array(),
                $orderBy,
                0,
                0
            )
        )));

        return intval($counts->rows[0][0]);
    }

    function getRecords($orderBy, $conditions, $offset, $limit) {
        // get total count
        $records = json_decode($this->httpRequest($this->makeUrl(
            $this->buildQuery(
                array('*'),
                $conditions,
                array(),
                $orderBy,
                $offset,
                $limit
            )
        )));

        return $records->rows ? $records->rows : array();
    }

    function execute($request) {
        $columns = $this->getColumns($request);
        $orderBy = $this->getOrderBy($request, $columns);
        $conditions = $this->getConditions($request, $columns);
        $offset = $this->getOffset($request);
        $limit = $this->getLimit($request);

        return array(
            'draw' => $this->getDrawCount($request),
            'recordsTotal' => $this->getTotalRecordsCount(),
            'recordsFiltered' => $this->getFilteredRecordsCount($orderBy, $conditions),
            'data' => $this->getRecords($orderBy, $conditions, $offset, $limit)
        );
    }
}
