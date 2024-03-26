<?php
/*
MIT License

Copyright (c) 2024 mrfakename <https://github.com/fakerybakery/phpsearch>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

class PHPSearch {
    private $query = null;
    private $connection = null;
    private $stopwords = ['for ', ' and ', 'nor ', 'but ', ' yet', ' or ', ' your ', 'yourself', 'yourselves', ' not ', ' why ', ' do ', ' to ', ' need ', ' my ', ' i ', 'why ', ' me ', ' my ', ' mine ', '.com', '.org', '.net', '.info', '.live', '.online', '.gov', 'best ', ' best '];
    public function __construct($search_string = null, $connection = null) {
        $this->query = $search_string;
        $this->$connection = $connection;
    }
    public function setQuery($search_string) {
        if (empty($search_string)) {
            throw new Exception('Invalid search query passed');
            exit;
        }
        $this->query = $search_string;
    }
    public function setConn($connection) {
        if (!$connection) {
            throw new Exception('Invalid MySQLi connection passed');
            exit;
        }
        $this->connection = $connection;
    }
    private function clean($string) {
        $string = str_replace(' ', '_', $string);
        $string = preg_replace('/[^A-Za-z0-9\_]/', '', $string);
        $string = preg_replace('/_+/', '_', $string);
        return str_replace('_', ' ', $string);
    }
    private function parseQuery($query) {
        $query = strtolower($query);
        $query = trim($query);
        $query = $this->clean($query);
        foreach ($this->stopwords as $stopword) {
            $query = str_replace($stopword, ' ', $query);
        }
        $query = '%' . str_replace(' ', '%', $query) . '%';
        return $query;
    }
    public function search($table, ...$rows) {
        $query = $this->parseQuery($this->query);
        $sql = 'SELECT * FROM `' . mysqli_real_escape_string($this->connection, $table) . '` WHERE';
        $nums = 0;
        $searchArr = [];
        foreach($rows as $row) {
            $sql .= " `" . mysqli_real_escape_string($this->connection, $row) . "` LIKE ? OR";
            array_push($searchArr, $query);
            $nums++;
        }
        $sql = substr_replace($sql, '', -3);
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(str_repeat('s', $nums), ...$searchArr);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res;
    }
}
