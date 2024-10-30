<?php

namespace mFrame\Axis;

use mFrame\Pattern\Factory;
use mframe\Database\SQL;
class Model extends Factory {

    protected string $requiredDB = "primary";
    protected mixed $db = null;

    protected array $fields;
    protected bool $protect = false;

    protected string $table = "";

    protected string $id;

    public function run(){
        if(is_null($this->db)){
            $db = new SQL();
            $this->db = $db->pull($this->requiredDB);
        }

        $this->setID();
    }

    protected function setID() : void {
        if(!empty($this->table)){
            $query = $this->db->query("DESCRIBE " . $this->table);
            $query->execute();
            $results = $query->fetchAll();
            foreach($results as $result){
                $this->fields[] = $result["Field"];
                if(end($result) == "primary"){
                    $this->id = $result[0];
                    break;
                }
                continue;
            }
        }
    }

    public function select(mixed $columns) : string {
        $statement = "SELECT ";

        if(is_string($columns)){
            if($columns == "count"){
                $statement .= "COUNT(*)";
            } elseif ($columns == "*"){
                $statement .= "*";
            } elseif(in_array($columns, $this->fields)){
                $statement .= $columns;
            } else {
                terminate("Attempt to request invalid field by name. Shutting down");
            }
        }

        if(is_array($columns)){
            foreach($columns as $column){
                if(is_array($column)){
                    $statement .= $column[0] . " AS " . $column[1] . ", ";
                } else {
                    $statement .= $column . ", ";
                }
            }

            $statement = rtrim($statement, ", ");
        }

        return $statement . $this->setTarget("select");
    }

    public function insert(array $columnAndValues) : string {
        $statement = "";
        if(!empty($columnAndValues)){
            $statement .= "INSERT ". $this->setTarget("insert");
            foreach($columnAndValues as $column => $value){
                $statement .= "(".$column.", ".$value."), ";
            }
            return rtrim($statement, ", ");
        }

        return $statement;
    }

    public function update(array $columnAndValues) : string {
        $statement = "";
        if(!empty($columnAndValues)){
            $statement .= "UPDATE " . $this->setTarget("update") . " SET ";
            foreach($columnAndValues as $column => $value){
                $statement .= "`".$column."` = '".$value."', ";
            }
            $statement = rtrim($statement, ", ");
        }
        return $statement;
    }

    public function delete() : string {
        $this->protect = true;
        return "DELETE " . $this->setTarget("delete");
    }

    public function truncate() : string {
        $this->protect = true;
        return "TRUNCATE " . $this->setTarget("truncate");
    }

    public function setClause(array $conditions, bool $stacked = false) : string {
        $statement = "WHERE ";
        if(!empty($conditions)){
            foreach($conditions as $condition){
                $something = $condition[0];
                $operand = $condition[1];
                $value = $condition[2];

                if($stacked){
                    $logical = $condition[3];
                    $statement .= "( " . $something ." " . $operand . " " . $value . ") " . $logical;
                } else {
                    $statement .= $something ." " . $operand . " " . $value . " ";
                }

                $statement = rtrim($statement, " ");
            }
        } else {
             $statement .= "1";
        }

        return $statement;
    }

    public function setLimit(int $max, int $page = 0) : string {
        $limit = "LIMIT " . $max;

        if($page >= 1){
            $limit .= "," . $page;
        }

        return $limit;
    }

    public function setGroup(mixed $segment) : string {
        $group = "GROUP BY ";
        if(is_array($segment)){
            $group .= implode(",", $segment);
        } else{
            $group .= $segment;
        }

        return $group;
    }

    private function setTarget(string $request) : string {
        $r = strtolower($request);

        switch ($r) {
            case "insert": $a = "INTO "; break;
            case "select":
            case "delete": $a = "FROM "; break;
            default: $a = " ";
        }

        return $a . $this->table . " ";
    }

    public function obtain(String $statement) : mixed {
        $query = $this->db->prepare($statement);
        $query->execute();
        return $query->fetchAll();
    }

}