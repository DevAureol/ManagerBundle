ManagerBundle
==============

The ManagerBundle is a Bundle for automatically create manager files according to Entity.

========
COMMAND 
========
  aureol_manager:generate:basemanager  <BundleName(required)>                                     ====  Command for create a Manager folder with a BaseManager file with the followings action: 
                                                                                                    - PersistAndFlush ($entity)
                                                                                                    - findAll
                                                                                                    - find ($id, $lockmode = null, $lockversion = null)
                                                                                                    - findBy (array $criteria, array $orderBy = null, $limit = null, $offset = null)

  aureol_manager:generate:manager <BundleName(required)> <EntityName(optionnal)> <Base(option)>   ====  Command for create Manager file according to EntityName.
                                                                                                        If no EntityName, the command generate manager for all Entity;
                                                                                                        If add Base option, the command generate BaseManager ( if no EntityName ) 
