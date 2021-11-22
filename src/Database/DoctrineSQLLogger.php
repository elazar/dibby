<?php

namespace Elazar\Dibby\Database;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Types\Type;
use Psr\Log\LoggerInterface;

class DoctrineSQLLogger implements SQLLogger
{
    private ?string $sql = null;

    /**
     * @var list<mixed>|array<string, mixed>|null
     */
    private ?array $params = null;

    /**
     * @var array<int, Type|int|string|null>|array<string, Type|int|string|null>|null
     */
    private ?array $types = null;

    private ?float $start = null;

    public function __construct(
        private LoggerInterface $logger,
    ) { }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->sql = $sql;
        $this->params = $params;
        $this->types = $types;
        $this->start = hrtime(true);
    }

    public function stopQuery()
    {
        $this->logger->debug('Executed query', [
            'sql' => $this->sql,
            'params' => $this->params,
            'types' => $this->types,
            'runtime' => (int) ((hrtime(true) - $this->start) / 10**6),
        ]);
    }
}
