<?php

namespace Yaoi\Schema\Structure;

use Yaoi\Schema\Properties;
use Yaoi\Schema\Schema;

interface ClassStructureContract
{
    /**
     * @param Properties|static $properties
     * @param Schema $ownerSchema
     */
    public static function setUpProperties($properties, Schema $ownerSchema);
}