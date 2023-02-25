<?php

declare(strict_types=1);

namespace PgFramework\Controller;

use PgFramework\Validator\Validator;
use PgFramework\HttpUtils\RequestUtils;
use Psr\Http\Message\ServerRequestInterface;

class AbstractController
{
    /**
     * Return filter Post body from an SPA or Web
     *
     * @param ServerRequestInterface $request
     * @param array $filter
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, array $filter = []): array
    {
        $params = RequestUtils::getPostParams($request);
        if (empty($params)) {
            return [];
        }
        return array_filter($params, function ($key) use ($filter) {
            return in_array($key, $filter);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Récupérer les queryParams de la request filtrer par les clés
     * du tableau $filter
     *
     * @param ServerRequestInterface $request
     * @param array $options
     * @param array|null $filter default to ['limit', 'offset', 'order', 'include']
     * @return array
     */
    protected function getQueryOption(ServerRequestInterface $request, array $options, ?array $filter = []): array
    {
        $queryOptions = $request->getQueryParams();
        if (!empty($queryOptions)) {
            if (empty($filter)) {
                $filter = ['limit', 'offset', 'order', 'include'];
            }
            array_walk($queryOptions, function ($value, $key) use (&$options, $filter) {
                if (in_array($key, $filter)) {
                    if ($key === 'include') {
                        $options[$key] = [$value];
                    } else {
                        $options[$key] = $value;
                    }
                }
            });
        }
        return $options;
    }

    /**
     * Get validator form fields
     *
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles()));
    }
}
