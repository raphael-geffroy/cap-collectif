<?php

namespace Capco\AppBundle\GraphQL\Resolver\Query;

use Psr\Log\LoggerInterface;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Enum\OrderDirection;
use GraphQL\Type\Definition\ResolveInfo;
use Capco\AppBundle\Search\ProjectSearch;
use Capco\AppBundle\GraphQL\QueryAnalyzer;
use Capco\AppBundle\Enum\ProjectOrderField;
use Capco\AppBundle\GraphQL\ConnectionBuilder;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Capco\AppBundle\GraphQL\Resolver\Traits\ResolverTrait;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class QueryProjectsResolver implements ResolverInterface
{
    use ResolverTrait;

    private $logger;
    private $projectSearch;
    private $queryAnalyzer;

    public function __construct(
        ProjectSearch $projectSearch,
        LoggerInterface $logger,
        QueryAnalyzer $queryAnalyzer
    ) {
        $this->logger = $logger;
        $this->projectSearch = $projectSearch;
        $this->queryAnalyzer = $queryAnalyzer;
    }

    public function __invoke(
        Argument $args,
        ?User $viewer,
        ResolveInfo $resolveInfo
    ): ConnectionInterface {
        $this->protectArguments($args);
        $this->queryAnalyzer->analyseQuery($resolveInfo);

        return $this->resolve($args, $viewer);
    }

    public function resolve(Argument $args, ?User $viewer = null): ConnectionInterface
    {
        try {
            $totalCount = 0;
            $paginator = new Paginator(function (int $offset, int $limit) use (
                $args,
                $viewer,
                &$totalCount
            ) {
                $term = $args->offsetExists('term') ? $args->offsetGet('term') : null;
                $orderBy = $args->offsetExists('orderBy')
                    ? $args->offsetGet('orderBy')
                    : [
                        'field' => ProjectOrderField::PUBLISHED_AT,
                        'direction' => OrderDirection::DESC
                    ];
                $onlyPublic = $args->offsetExists('onlyPublic')
                    ? $args->offsetGet('onlyPublic')
                    : false;

                $results = $this->projectSearch->searchProjects(
                    0,
                    1000,
                    $orderBy,
                    $term,
                    $this->getFilters($args)
                );
                // @TODO: this logic should be done in Elasticsearch not PHP
                // https://github.com/cap-collectif/platform/issues/8616
                $allResults = [];
                // @var Project $project
                if (!$onlyPublic) {
                    foreach ($results['projects'] as $project) {
                        if ($project instanceof Project && $project->canDisplay($viewer)) {
                            $allResults[] = $project;
                        }
                    }
                } else {
                    foreach ($results['projects'] as $project) {
                        if ($project instanceof Project && $project->isPublic()) {
                            $allResults[] = $project;
                        }
                    }
                }
                $totalCount = \count($allResults);

                return \array_slice($allResults, $offset, $limit);
            });
            $connection = $paginator->auto($args, $totalCount);
            $connection->setTotalCount($totalCount);

            return $connection;
        } catch (\RuntimeException $exception) {
            $this->logger->error(__METHOD__ . ' ' . $exception->getMessage(), [
                'exception' => $exception
            ]);
        }

        return ConnectionBuilder::empty();
    }

    private function getFilters(Argument $args): array
    {
        $filters = [];
        if ($args->offsetExists('status') && '' !== $args['status']) {
            $filters['projectStatus'] = $args->offsetGet('status');
        }
        if ($args->offsetExists('theme') && '' !== $args['theme']) {
            $filters['themes.id'] = $args->offsetGet('theme');
        }
        if ($args->offsetExists('type') && '' !== $args['type']) {
            $filters['projectType.id'] = $args->offsetGet('type');
        }
        if ($args->offsetExists('author') && '' !== $args['author']) {
            $filters['authors.id'] = GlobalId::fromGlobalId($args->offsetGet('author'))['id'];
        }
        if ($args->offsetExists('district') && '' !== $args['district']) {
            $filters['districts.district.id'] = $args->offsetGet('district');
        }
        if ($args->offsetExists('withEventOnly') && false !== $args['withEventOnly']) {
            $filters['withEventOnly'] = $args['withEventOnly'];
        }

        return $filters;
    }
}
