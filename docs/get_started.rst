Get started
===========

Installation
------------

Locally
~~~~~~~

Download the `automate.phar`_ file and store it somewhere on your computer.

Globally (manual)
~~~~~~~~~~~~~~~~~

You can run these commands to easily access ``automate`` from anywhere on
your system:

.. code-block:: bash

    $ sudo wget https://github.com/julienj/automate/raw/master/build/automate.phar -O /usr/local/bin/automate

or with curl:

.. code-block:: bash

    $ sudo curl https://github.com/julienj/automate/raw/master/build/automate.phar -o /usr/local/bin/automate

then:

.. code-block:: bash

    $ sudo chmod a+x /usr/local/bin/automate

Then, just run ``automate``.

Globally (Composer)
~~~~~~~~~~~~~~~~~~~

To install Automate, install Composer and issue the following command:

.. code-block:: bash

    $ ./composer.phar global require automate/automate @dev

Then, make sure you have ``~/.composer/vendor/bin`` in your ``PATH``, and
you're good to go:

.. code-block:: bash

    export PATH="$PATH:$HOME/.composer/vendor/bin"


Update
------

Locally
~~~~~~~

The ``self-update`` command tries to update ``automate`` itself:

.. code-block:: bash

    $ php automate.phar self-update

Globally (manual)
~~~~~~~~~~~~~~~~~

You can update ``automate`` through this command:

.. code-block:: bash

    $ sudo automate self-update




.. _automate.phar: https://github.com/julienj/automate/raw/master/build/automate.phar