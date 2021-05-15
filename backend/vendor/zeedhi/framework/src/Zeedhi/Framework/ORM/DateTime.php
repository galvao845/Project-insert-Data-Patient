<?php
namespace Zeedhi\Framework\ORM;

class DateTime extends \DateTime{

    public function __toString() {
        return $this->format("d/m/Y H:i:s");
    }

    public static function createFromFormat($format, $time, $timezone = null) {
        $ext_dt = null;
        $originalClassObject = parent::createFromFormat($format, $time);
        if ($originalClassObject) {
            $ext_dt = new static();
            $ext_dt->setTimestamp($originalClassObject->getTimestamp());
        }

        return $ext_dt;
    }

} 