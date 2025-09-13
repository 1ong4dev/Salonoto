<?php
class Database
{
    private const HOST = 'localhost';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const DBNAME = 'doansalon';

    /**
     * Kết nối CSDL
     */
    private static function Connect()
    {
        $connect = new mysqli(self::HOST, self::USERNAME, self::PASSWORD, self::DBNAME);
        if ($connect->connect_error) {
            die('Connection failed: ' . $connect->connect_error);
        }
        return $connect;
    }

    // ========================
    // SELECT thông thường
    // ========================
    public static function GetData($query, $format = [])
    {
        if (is_array($format)) {
            $connect = self::Connect();
            $resQuery = $connect->query($query);

            if (!$resQuery) {
                die('Invalid query: ' . $connect->error);
            }

            $arr = [];
            if ($resQuery->num_rows > 0) {
                while ($row = $resQuery->fetch_assoc()) {
                    $arr[] = $row;
                }

                // Trả về giá trị theo key hoặc index
                if (isset($format['cell'])) {
                    $formatRow = $format['row'] ?? 0;
                    $formatKey = is_numeric($format['cell']) ? array_keys($arr[$formatRow])[$format['cell']] : $format['cell'];
                    return $arr[$formatRow][$formatKey] ?? null;
                }

                // Trả về dòng dữ liệu tại index
                if (isset($format['row'])) {
                    return $arr[$format['row']];
                }
            }

            $connect->close();
            return $arr;
        }
        return [];
    }

    // ========================
    // SELECT có phân trang
    // ========================
    public static function GetDataWithPagination($query, $offset = 10, $page = 1)
    {
        $countAll = self::GetData('SELECT count(*) FROM categories', ['cell' => 0]);
        $start = ($page - 1) * $offset;
        $data = self::GetData($query . " LIMIT $start, $offset");
        $end = $start + count($data);

        return [
            'data'        => $data,
            'start'       => $start + 1,
            'end'         => $end,
            'countAll'    => $countAll,
            'page_number' => ceil($countAll / $offset),
        ];
    }

    // ========================
    // INSERT, UPDATE, DELETE
    // ========================
    public static function NonQuery($query)
    {
        $connect = self::Connect();
        $result = $connect->query($query);
        $connect->close();
        return $result === true;
    }

    // ========================
    // INSERT và trả về ID vừa tạo
    // ========================
    public static function NonQueryId($query)
    {
        $connect = self::Connect();
        $result = $connect->query($query);
        if ($result) {
            $lastId = $connect->insert_id;
            $connect->close();
            return $lastId;
        } else {
            die('Error: ' . $connect->error);
        }
    }

    // ========================
    // Transaction
    // ========================
    public static function BeginTransaction()
    {
        $connect = self::Connect();
        $connect->autocommit(false);
        return $connect;
    }

    public static function Commit($connect)
    {
        if ($connect) {
            $connect->commit();
            $connect->autocommit(true);
            $connect->close();
        }
    }

    public static function Rollback($connect)
    {
        if ($connect) {
            $connect->rollback();
            $connect->autocommit(true);
            $connect->close();
        }
    }

    // ========================
    // NonQuery trong transaction
    // ========================
    public static function NonQueryTrans($connect, $query)
    {
        if (!$connect->query($query)) {
            throw new Exception($connect->error);
        }
        return true;
    }

    // ========================
    // NonQueryId trong transaction
    // ========================
    public static function NonQueryIdTrans($connect, $query)
    {
        if ($connect->query($query)) {
            return $connect->insert_id;
        } else {
            throw new Exception($connect->error);
        }
    }
}
?>
