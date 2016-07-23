<?php

return [

    /*
     * Add the Doctrine repositories you want bound to the container here.
     *
     * This allows you to type hint the repository in constructors and action methods
     * in controllers. The repositories can always be accessed by calling the entity
     * manager ->getRepository() method with the class instance as argument.
     *
     * The required parameters are:
     *  * repository - the repository class, can be EntityRepository::class
     *  * entity - the class name of the entity the repository is for
     *
     * The followng are optional:
     *  * alias - a shorter alias e.g. app.user.repository
     *  * tags - any tags to add to the reference in the container e.g. [ 'repository' ]
     *
     * E.g.:
     *
     * [
     *     'repository' => App\Repositories\AddressRepository::class,
     *     'entity'     => App\Entities\Address::class,
     *     'alias'      => 'app.address.repository',
     *     'tags'       => ['repository'],
     * ],
     */
    'repositories' => [

    ]

];