<?php
// Support for meta_query (nested arrays, AND/OR, =, !=, >, <, >=, <=, numeric)
if (!function_exists('rentfetch_build_meta_query_sql')) {
function rentfetch_build_meta_query_sql($meta_query, $wpdb, $post_table = 'posts', &$join_count = 0) {
    $join = [];
    $where = [];
    $relation = 'AND';
    if ( isset($meta_query['relation']) ) {
        $relation = strtoupper($meta_query['relation']);
        unset($meta_query['relation']);
    }
    foreach ($meta_query as $mq) {
        if ( isset($mq['relation']) || isset($mq[0]) ) {
            // Nested meta_query
            $sub = rentfetch_build_meta_query_sql($mq, $wpdb, $post_table, $join_count);
            if ($sub['join']) $join = array_merge($join, $sub['join']);
            if ($sub['where']) $where[] = '(' . $sub['where'] . ')';
        } else if ( is_array($mq) && isset($mq['key']) ) {
            $join_count++;
            $alias = 'pm' . $join_count;
            $join[] = "INNER JOIN {$wpdb->postmeta} $alias ON $alias.post_id = {$wpdb->$post_table}.ID";
            $key = esc_sql($mq['key']);
            $compare = isset($mq['compare']) ? strtoupper($mq['compare']) : null;
            $value = isset($mq['value']) ? $mq['value'] : '';
            $type = isset($mq['type']) ? strtolower($mq['type']) : '';
            $meta_val = ($type === 'numeric') ? "$alias.meta_value+0" : "$alias.meta_value";

            // Determine compare if not set and value is array (WP_Query default is IN)
            if (!$compare && is_array($value)) {
                $compare = 'IN';
            } elseif (!$compare) {
                $compare = '=';
            }

            // Handle IN, NOT IN, BETWEEN, NOT BETWEEN, and scalar comparisons
            if (in_array($compare, ['IN', 'NOT IN'], true) && is_array($value)) {
                $esc_vals = array_map(function($v) use ($type, $wpdb) {
                    return ($type === 'numeric') ? (float)$v : esc_sql($v);
                }, $value);
                $val_list = ($type === 'numeric') ? implode(',', $esc_vals) : "'" . implode("','", $esc_vals) . "'";
                $where[] = "$alias.meta_key = '$key' AND $meta_val $compare ($val_list)";
            } elseif (in_array($compare, ['BETWEEN', 'NOT BETWEEN'], true) && is_array($value) && count($value) === 2) {
                $v1 = ($type === 'numeric') ? (float)$value[0] : esc_sql($value[0]);
                $v2 = ($type === 'numeric') ? (float)$value[1] : esc_sql($value[1]);
                if ($type === 'numeric') {
                    $where[] = "$alias.meta_key = '$key' AND $meta_val $compare $v1 AND $v2";
                } else {
                    $where[] = "$alias.meta_key = '$key' AND $meta_val $compare '" . esc_sql($value[0]) . "' AND '" . esc_sql($value[1]) . "'";
                }
            } elseif (in_array($compare, ['=', '!=', '>', '<', '>=', '<='], true)) {
                $val = ($type === 'numeric') ? (float)$value : esc_sql($value);
                if ($type === 'numeric') {
                    $where[] = "$alias.meta_key = '$key' AND $meta_val $compare $val";
                } else {
                    $where[] = "$alias.meta_key = '$key' AND $meta_val $compare '" . esc_sql($value) . "'";
                }
            } else {
                // Fallback: treat as string equality
                $where[] = "$alias.meta_key = '$key' AND $meta_val = '" . esc_sql($value) . "'";
            }
        }
    }
    return [
        'join' => $join,
        'where' => $where ? implode(" $relation ", $where) : ''
    ];
}
}
