<?php

namespace Elazar\Dibby\Database;

interface DatabaseConnectionFactory
{
    /**
     * Returns a list of database driver names, generally for the user to
     * choose from during initial setup.
     *
     * @return string[] Zero or more driver names depending on environment and
     *         underlying library support
     */
    public function getAvailableDrivers(): array;

    /**
     * Returns a database connection for read operations. The nature of the
     * returned value is dependent on the underlying implementation.
     *
     * This method must actually establish a successful connection to the
     * database server or throw an exception if a connection attempt ultimately
     * fails.
     *
     * @throws \Elazar\Dibby\Exception
     * @return object|resource
     */
    public function getReadConnection();

    /**
     * Returns a database connection for write operations. The nature of the
     * returned value is dependent on the underlying implementation.
     *
     * This method must actually establish a successful connection to the
     * database server or throw an exception if a connection attempt ultimately
     * fails.
     *
     * @throws \Elazar\Dibby\Exception
     * @return object|resource
     */
    public function getWriteConnection();
}
