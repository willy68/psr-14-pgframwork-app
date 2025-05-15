<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use PgFramework\Database\Doctrine\Bridge\DebugStackInterface;

class DoctrineCollector extends DataCollector implements Renderable, AssetProvider
{
    protected DebugStackInterface $debugStack;

    /**
     * DoctrineCollector constructor.
     * @param DebugStackInterface $debugStack
     */
    public function __construct(DebugStackInterface $debugStack)
    {
        $this->debugStack = $debugStack;
    }

    /**
     * @return array
     */
    public function collect(): array
    {
		$queries = array();
		$totalExecTime = 0;
		foreach ($this->debugStack->queries as $q) {
			$queries[] = array(
				'sql' => $q['sql'],
				'params' => (object) $this->getParameters($q['params'] ?? []),
				'duration' => $q['executionMS'],
				'duration_str' => $this->getDataFormatter()->formatDuration($q['executionMS'])
			);
			$totalExecTime += $q['executionMS'];
		}

		return array(
			'nb_statements' => count($queries),
			'accumulated_duration' => $totalExecTime,
			'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($totalExecTime),
			'statements' => $queries
		);
    }

	/**
	 * Returns an array of parameters used with the query
	 *
	 * @param $params
	 * @return array
	 */
	public function getParameters($params) : array
	{
		return array_map(function ($param) {
			if (is_string($param)) {
				return htmlentities($param, ENT_QUOTES, 'UTF-8', false);
			} elseif (is_array($param)) {
				return '[' . implode(', ', $this->getParameters($param)) . ']';
			} elseif (is_numeric($param)) {
				return strval($param);
			} elseif ($param instanceof \DateTimeInterface) {
				return $param->format('Y-m-d H:i:s');
			} elseif (is_object($param)) {
				return json_encode($param);
			}
			return $param ?: '';
		}, $params);
	}

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'doctrine';
    }

    /**
     * @return array
     */
    public function getWidgets(): array
    {
        return array(
            "database" => array(
                "icon" => "arrow-right",
                "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map" => "doctrine",
                "default" => "[]"
            ),
            "database:badge" => array(
                "map" => "doctrine.nb_statements",
                "default" => 0
            )
        );
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return array(
            'css' => 'widgets/sqlqueries/widget.css',
            'js' => 'widgets/sqlqueries/widget.js'
        );
    }
}
