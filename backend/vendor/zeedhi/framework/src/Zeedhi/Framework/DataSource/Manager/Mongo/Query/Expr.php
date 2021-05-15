<?php
namespace Zeedhi\Framework\DataSource\Manager\Mongo\Query;

class Expr {
    protected $query = array();
    protected $currentField;

    public function field($field) {
        $this->currentField = (string)$field;
        return $this;
    }

    public function getQuery() {
        return $this->query;
    }

    public function setQuery(array $query) {
        $this->query = $query;
    }

    public function equals($value) {
        return $this->operator('$eq', $value);
    }

    public function notEquals($value) {
        return $this->operator('$ne', $value);
    }

    public function lt($value) {
        return $this->operator('$lt', $value);
    }

    public function lte($value) {
        return $this->operator('$lte', $value);
    }

    public function gt($value) {
        return $this->operator('$gt', $value);
    }

    public function gte($value) {
        return $this->operator('$gte', $value);
    }

    public function in(array $values) {
        return $this->operator('$in', array_values($values));
    }

    public function nin(array $values) {
        return $this->operator('$nin', array_values($values));
    }

    public function range($start, $end) {
        return $this->operator('$gte', $start)->operator('$lte', $end);
    }

    public function operator($operator, $value) {
        if ($this->currentField) {
            $this->query[$this->currentField][$operator] = $value;
        } else {
            $this->query[$operator] = $value;
        }

        return $this;
    }

}