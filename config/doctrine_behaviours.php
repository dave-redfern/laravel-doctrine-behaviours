<?php

use Somnambulist\Doctrine\Contracts;
use Somnambulist\Doctrine\Traits;

return [

    /*
     * Adds shortcuts to the make:entity command allowing traits/interfaces to be generated
     * with the entity. Add entries by specifying a name, and then the options:
     *
     * 'option_name' => [
     *     'alias'      => '',
     *     'contract'   => Interface\Contract\ClassName,
     *     'trait'      => Trait\ClassName,
     * ],
     *
     * Alias can be null, and a shortcut will not be created. contract and trait are both
     * required.
     *
     * If the option should not be used with another option e.g. because it is an aggregate
     * of interfaces e.g.: globally_trackable cannot be used with identifiable, blamable,
     * nameable, timestampable and universally_identifiable; then add blocked_by and give
     * the names of the options that it cannot be used with:
     *
     * 'option_name' => [
     *     // ...
     *     'blocked_by' => ['globally_trackable', 'trackable',],
     * ],
     *
     * Note that the alias must be unique, and certain letters are already taken:
     *  * h, q, V, v, vv, vvv, n
     */
    'behaviour_mappings' => [
        /*
         * Aggregate Behaviours
         */
        'globally_trackable' => [
            'alias'      => 'g',
            'contract'   => Contracts\GloballyTrackable::class,
            'trait'      => Traits\GloballyTrackable::class,
            'blocked_by' => ['identifiable', 'trackable'],
        ],
        'trackable' => [
            'alias'      => 't',
            'contract'   => Contracts\Trackable::class,
            'trait'      => Traits\Trackable::class,
            'blocked_by' => ['globally_trackable', 'identifiable'],
        ],

        /*
         * Individual Behaviours
         */
        'activatable' => [
            'alias'      => 'a',
            'contract' => Contracts\Activatable::class,
            'trait'    => Traits\Activatable::class,
        ],
        'blamable' => [
            'alias'      => 'b',
            'contract'   => Contracts\Blamable::class,
            'trait'      => Traits\Blamable::class,
            'blocked_by' => ['globally_trackable', 'trackable'],
        ],
        'identifiable' => [
            'alias'      => 'i',
            'contract'   => Contracts\Identifiable::class,
            'trait'      => Traits\Identifiable::class,
            'blocked_by' => ['globally_trackable', 'trackable'],
        ],
        'nameable' => [
            'alias'      => 'd',
            'contract'   => Contracts\Nameable::class,
            'trait'      => Traits\Nameable::class,
            'blocked_by' => ['globally_trackable', 'trackable'],
        ],
        'numerically_sortable' => [
            'alias'      => 'o',
            'contract' => Contracts\NumericallySortable::class,
            'trait'    => Traits\NumericallySortable::class,
        ],
        'publishable' => [
            'alias'      => 'p',
            'contract' => Contracts\Publishable::class,
            'trait'    => Traits\Publishable::class,
        ],
        'sluggable' => [
            'alias'      => 's',
            'contract' => Contracts\Sluggable::class,
            'trait'    => Traits\Sluggable::class,
        ],
        'uuid' => [
            'alias'      => 'u',
            'contract'   => Contracts\UniversallyIdentifiable::class,
            'trait'      => Traits\UniversallyIdentifiable::class,
            'blocked_by' => ['globally_trackable'],
        ],
        'timestampable' => [
            'alias'      => 'y',
            'contract'   => Contracts\Timestampable::class,
            'trait'      => Traits\Timestampable::class,
            'blocked_by' => ['globally_trackable', 'trackable'],
        ],
        'versionable' => [
            'alias'      => 'z',
            'contract' => Contracts\Versionable::class,
            'trait'    => Traits\Versionable::class,
        ],
    ],
];
