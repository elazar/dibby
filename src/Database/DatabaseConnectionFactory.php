<?php

namespace Elazar\Dibby\Database;

interface DatabaseConnectionFactory
{
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
