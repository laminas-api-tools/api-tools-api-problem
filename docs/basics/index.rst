.. _basics.index:

LaminasHal Basics
============

LaminasHal allows you to create RESTful JSON APIs that adhere to
:ref:`Hypermedia Application Language <laminashal.hal-primer>`. For error
handling, it uses :ref:`API-Problem <laminashal.error-reporting>`.

The pieces you need to implement, work with, or understand are:

- Writing event listeners for the various ``Laminas\ApiTools\Hal\Resource`` events,
  which will be used to either persist resources or fetch resources from
  persistence.

- Writing routes for your resources, and associating them with resources and/or
  ``Laminas\ApiTools\Hal\ResourceController``.

- Writing metadata describing your resources, including what routes to associate
  with them.

All API calls are handled by ``Laminas\ApiTools\Hal\ResourceController``, which in
turn composes a ``Laminas\ApiTools\Hal\Resource`` object and calls methods on it. The
various methods of the controller will return either
``Laminas\ApiTools\Hal\ApiProblem`` results on error conditions, or, on success, a
``Laminas\ApiTools\Hal\HalResource`` or ``Laminas\ApiTools\Hal\HalCollection`` instance; these
are then composed into a ``Laminas\ApiTools\Hal\View\RestfulJsonModel``.

If the MVC detects a ``Laminas\ApiTools\Hal\View\RestfulJsonModel`` during rendering,
it will select ``Laminas\ApiTools\Hal\View\RestfulJsonRenderer``. This, with the help
of the ``Laminas\ApiTools\Hal\Plugin\HalLinks`` plugin, will generate an appropriate
payload based on the object composed, and ensure the appropriate Content-Type
header is used.

If a ``Laminas\ApiTools\Hal\HalCollection`` is detected, and the renderer determines
that it composes a ``Laminas\Paginator\Paginator`` instance, the ``HalLinks``
plugin will also generate pagination relational links to render in the payload.
