<?php

namespace UIS\Core\DB;

use DB;

class BufferInsert
{
    protected $db = null;
    protected $insertData = [];
    protected $insertDataSize = 0;
    protected $bufferMaxSize = 500;
    protected $tableName = null;
    protected $columns = null;
    protected $updateColumns = [];

    public function __construct($tableName, $columns, $updateColumns = [], $connection = null)
    {
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->updateColumns = $updateColumns;
        $this->db = $connection === null ? DB::connection() : $connection;
    }

    public function insert($data)
    {
        $this->insertData[] = $data;
        $this->insertDataSize++;

        if ($this->insertDataSize >= $this->bufferMaxSize) {
            $this->flush();
        }
    }

    public function flush()
    {
        $tableName = $this->db->getTablePrefix().$this->tableName;
        $sql = "INSERT INTO {$tableName} ( `".implode('`, `', $this->columns).'` ';

        $questionMarks = [];
        $bindings = [];
        foreach ($this->insertData as $data) {
            $questionMarks[] = '('.$this->placeholders('?', sizeof($data)).')';
            $bindings = array_merge($bindings, $data);
        }
        $sql .= ') VALUES '.implode(', ', $questionMarks).$this->createUpdateSql();
        $this->db->insert($sql, $bindings);
    }

    protected function placeholders($text, $count = 0, $separator = ', ')
    {
        $result = [];
        if ($count > 0) {
            for ($x = 0; $x < $count; $x++) {
                $result[] = $text;
            }
        }

        return implode($separator, $result);
    }

    protected function createUpdateSql()
    {
        if (empty($this->updateColumns)) {
            return '';
        }
        $updateSql = ' ON  DUPLICATE KEY UPDATE ';
        foreach ($this->updateColumns as $column) {
            $updateSql .= " `{$column}` = VALUES(`{$column}`), ";
        }
        $updateSql = trim($updateSql, ', ');

        return $updateSql;
    }
}
