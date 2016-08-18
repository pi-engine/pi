<?php

namespace GeoIp2\Record;

/**
 * Contains data for the continent record associated with an IP address
 *
 * This record is returned by all location services and databases.
 *
 * @property string|null $code A two character continent code like "NA" (North
 * America) or "OC" (Oceania). This attribute is returned by all location
 * services and databases.
 *
 * @property int|null $geonameId The GeoName ID for the continent. This
 * attribute is returned by all location services and databases.
 *
 * @property string|null $name Returns the name of the continent based on the
 * locales list passed to the constructor. This attribute is returned by all location
 * services and databases.
 *
 * @property array|null $names An array map where the keys are locale codes
 * and the values are names. This attribute is returned by all location
 * services and databases.
 */
class Continent extends AbstractPlaceRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = array(
        'code',
        'geonameId',
        'names'
    );
}
