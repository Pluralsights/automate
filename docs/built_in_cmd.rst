Built in command
================

Init
----

To start using Automate in your project, all you need is a .automate/config.json file. This file describes the deployment configuration of your project.

Command ``automate  init`` create default config file :

.. code-block:: bash

    $ php automate.phar init

or (globally install):

.. code-block:: bash

    $ automate init


You can now edit the configuration file ``.automate/config.yml``

Deploy
------

Command ``automate deploy`` start the deployment.

.. code-block:: bash

    $ php automate.phar deploy

or (globally install):

.. code-block:: bash

    $ automate deploy

Configuration options :

.. code-block:: yaml

    remotes:
        host1:
            host: 'host1.com'
            user: 'user'
            rsa: './.automate/host1.rsa' # or password: 'mypassword'
            groups: 'web' # Server group. value can be an array : ['group1', 'group2']
            master: true  # you must have one and only one master
        host2:
            host: 'host2.com'
            user: 'user'
            rsa: './.automate/host2.rsa'
            groups: 'web'
            master: true

    deployment:
        group: 'web'  # deploys sources on all servers in this group
        from:  './'   # local path
        to: '/home/wwwroot/myproject' # remote path
        max_release:  3  # number of releases on the server
        symlink_dir: 'current' # current folder name
        releases_dir: 'releases' # releases folder name
        shared_dir: 'shared' # shared folder name
        strategy : 'ftp' # ftp or targz
        excludes: # list of items to exclude from deployment
            - '.idea'
            - 'vendor'
            - 'test'

        shared: # shard folders
            - 'flux'
            - 'upload'

        hooks: # hooks
            on_deploy:
                - { name: 'remote:run', params: { command: 'php app/console cache:clear', group: 'web' } }


.. note::

    There are 3 hooks :
        * pre_deploy
        * on_deploy
        * post_deploy


    you can run multiple tasks for each hook. You must enter the task name and its parameters.


Unlock
------

