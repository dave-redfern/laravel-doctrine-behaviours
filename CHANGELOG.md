Change Log
==========

2016-07-22
----------

Added:

 * IdentifiableWithTimestamps
 * MakeEntity command for building entity stubs + repository
 
BC Breaks:

 * Renamed ServiceProvider class to BehavioursServiceProvider, now loads commands
 * Renamed repositories config files to doctrine_repositories

2016-04-14
----------

Changed:

 * Added compiles() to provider to allow main classes to be compiled in production

2016-02-13
----------

Changed:

 * Fixed SluggableEventSubscriber missing interface

2015-11-29 - 2
--------------

Added:

 * config/repositories.php

Changed:

 * RepositoryServiceProvider is no longer abstract, has a config file

2015-11-29
----------

Added:

 * Sluggable

2015-11-28
----------

Added:

 * CanRenumberCollection
 * NumericallySortable
 * Publishable
 * Versionable

2015-11-25
----------

Initial commit.
