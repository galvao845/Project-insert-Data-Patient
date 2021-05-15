<?php
namespace Zeedhi\Framework\Util;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

class DefaultInflector implements Inflector {

    /**
     * {@inheritdoc}
     */
    public function classify($tableName) {
        return DoctrineInflector::classify(strtolower($tableName));
    }
}