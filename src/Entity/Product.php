<?php
namespace App\Entity;

use App\Service\Database\Database;
use App\Service\Monolog\Log;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{

    private array $attribute;
    private ?\Monolog\Logger $log;
    private ?\Doctrine\DBAL\Connection $conn;

    /**
     * Product constructor.
     * @param string[] $attribute
     */
    public function __construct(array $attribute)
    {
        $this->conn = Database::getInstance();
        $this->log = Log::getInstance();
        $this->attribute = $attribute;
    }


    public function save(): bool
    {
        $log = Log::getInstance();
        if(isset($this->attribute['наименование'])) {
            $entities_name = $this->attribute['наименование'];
            unset($this->attribute['наименование']);
        } else {
            $this->log->error("not fount name product");
            return false;
        }
        $key = array_keys($this->attribute);
        $values = array_values($this->attribute);
        try {
            if($this->insertFromArray('attributes', ['name'], $key) === false) {
                $this->log->error("не удалось произвести запись в таблицу аттрибутов");
                return false;
            }
            if($this->insertFromArray('options', ['name'], $values) === false) {
                $this->log->error("не удалось произвести запись в таблицу парамтеров");
                return false;
            }
            if($this->insertFromArray('entities', ['name'], [$entities_name]) === false) {
                $this->log->error("не удалось произвести запись в таблицу названий");
                return false;
            }
            $product=[];
            foreach ($this->attribute as $key => $value) {
                $product[] = [$entities_name, $key , $value];
            }
            if($this->insertProductFromArray('products', ['entities_id','attributes_id','options_id'], $product) === false) {
                $this->log->error("не удалось произвести запись в таблицу товаров");
                return false;
            }
        } catch (\Doctrine\DBAL\Driver\Exception | \Doctrine\DBAL\Exception $e) {
            Log::error($e);
            return false;
        }
        return true;
    }

    /**
     * @param string $tableName
     * @param array $column
     * @param array $values
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function insertFromArray(string $tableName, array $column, array $values):bool
    {
        $sql = "INSERT INTO ".$tableName;

        if(!empty($column)) {
            $sql .= " ( " . implode(',', $column) . " ) VALUES ";
        } else {
            $this->log->error("colunm is null");
            return false;
        }

        //prepare statement
        foreach ($values as $item => $value) {
            if(is_array($value)) {
                $valueToString = implode(',', array_fill(0, count($value), ' ?'));
                $sql .= $item === 0 ? ' ( ' . $valueToString . ')' : ', ( '. $valueToString .')';
            } else {
                $sql .= $item === 0 ? ' (?)' : ', (?)';
            }
        }
        if(count($column) > 1) {
            foreach($column as $key => &$value) {
                $value .= "=VALUES (" . $value . ")";
            }
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(',', $column);
        } else {
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(',', $column) . " = VALUES (" . implode(',', $column) . ")";
        }
        try {
        $stmt = $this->conn->prepare($sql);
        $i = 1;
        foreach ($values as $item => $value) {
            if(is_array($value)) {
                foreach($value as $key => $val) {
                    $stmt->bindValue($i, $val);
                    $i++;
                }
            } else {
                $stmt->bindValue($i, $value);
                $i++;
            }
        }
        $result = $stmt->executeQuery();
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
        return true;
    }

    private function insertProductFromArray(string $tableName, array $column, array $values): bool
    {
        $sql = "INSERT INTO ".$tableName;

        if(!empty($column)) {
            $sql .= " ( " . implode(',', $column) . " ) VALUES ";
        } else {
            $this->log->error("colunm is null");
            return false;
        }
        $array_select = " ((select ".$column[0]." from ".trim($column[0],'_id')." where name = ?),(select ".$column[1]." from ".trim($column[1],'_id')." where name = ?),(select ".$column[2]." from ".trim($column[2],'_id')." where name = ?))";
        if(is_array($values)) {
            $valueToString = implode(',', array_fill(0, count($values), $array_select));
            $sql .=  $valueToString;
        } else {
            $this->log->error("incorrect values");
            return false;
        }

        //prepare statement

        if(count($column) > 1) {
            foreach($column as $key => &$value) {
                $value .= "=VALUES (" . $value . ")";
            }
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(',', $column);
        } else {
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(',', $column) . " = VALUES (" . implode(',', $column) . ")";
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $i = 1;
            foreach ($values as $item => $value) {
                if(is_array($value)) {
                    foreach($value as $key => $val) {
                        $stmt->bindValue($i, $val);
                        $i++;
                    }
                } else {
                    $stmt->bindValue($i, $value);
                    $i++;
                }
            }
            $result = $stmt->executeQuery();
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }

        return true;
    }
}