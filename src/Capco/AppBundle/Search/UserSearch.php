<?php

namespace Capco\AppBundle\Search;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Enum\SortField;
use Capco\UserBundle\Entity\UserType;
use Capco\UserBundle\Repository\UserRepository;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Range;
use Elastica\Query\Term;

class UserSearch extends Search
{
    const SEARCH_FIELDS = ['username', 'username.std'];

    private $userRepo;
    private $eventSearch;

    public function __construct(Index $index, UserRepository $userRepo, EventSearch $eventSearch)
    {
        parent::__construct($index);
        $this->userRepo = $userRepo;
        $this->eventSearch = $eventSearch;
        $this->type = 'user';
    }

    public function getRegisteredUsers(
        int $pagination,
        int $page,
        string $sort = null,
        $type = null
    ): array {
        $boolQuery = new Query\BoolQuery();
        if (null !== $type && UserType::FILTER_ALL !== $type) {
            $boolQuery->addMust(new Term(['userType.id' => ['value' => $type->getId()]]));
        }
        $query = new Query();
        $query->setQuery($boolQuery);
        if ('activity' === $sort) {
            $query->setSort([
                'totalContributionsCount' => [
                    'order' => 'DESC'
                ]
            ]);
        } else {
            $query->addSort([
                'createdAt' => ['order' => 'DESC']
            ]);
        }

        if ($pagination > 0) {
            $query->setFrom(($page - 1) * $pagination)->setSize($pagination);
        }

        $resultSet = $this->index->getType('user')->search($query);

        return [
            'results' => $this->getHydratedResultsFromResultSet($this->userRepo, $resultSet),
            'totalCount' => $resultSet->getTotalHits()
        ];
    }

    public function getAllUsers(
        ?int $limit = null,
        ?int $first = null,
        ?array $orderBy = null,
        bool $showSuperAdmin = false
    ): array {
        $boolQuery = new Query\BoolQuery();
        if (!$showSuperAdmin) {
            $queryString = new Query\QueryString();
            $queryString->setQuery('ROLE_SUPER_ADMIN');
            $queryString->setFields(['roles']);
            $boolQuery->addMustNot($queryString);
        }
        $query = new Query();
        $query->setQuery($boolQuery);

        if ($first) {
            $query->setFrom($first);
        }

        if ($limit) {
            $query->setSize($limit);
        }

        if ($orderBy) {
            $query->addSort($this->getSort($orderBy));
        }

        $resultSet = $this->index->getType('user')->search($query);

        return [
            'results' => $this->getHydratedResultsFromResultSet($this->userRepo, $resultSet),
            'totalCount' => $resultSet->getTotalHits()
        ];
    }

    public function searchAllUsers(
        $terms = null,
        ?array $notInIds = [],
        bool $authorsOfEventOnly = false,
        bool $onlyUsers = false
    ): array {
        $query = new Query\BoolQuery();

        if ($terms && $authorsOfEventOnly) {
            $authorIds = $this->eventSearch->getAllIdsOfAuthorOfEvent($terms);
            $users = $this->getHydratedResults($this->userRepo, $authorIds);

            if ($onlyUsers) {
                return $users;
            }

            return [
                'users' => $users,
                'count' => \count($authorIds)
            ];
        }

        if ($terms) {
            $query = $this->searchTermsInMultipleFields(
                $query,
                self::SEARCH_FIELDS,
                $terms,
                'phrase_prefix'
            );
        }

        if (\count($notInIds) > 0) {
            $query = $this->searchNotInTermsForField($query, 'id', $notInIds);
        }

        $resultSet = $this->index->getType($this->type)->search($query);
        $users = $this->getHydratedResultsFromResultSet($this->userRepo, $resultSet);

        if ($onlyUsers) {
            return $users;
        }

        return [
            'users' => $users,
            'count' => $resultSet->getTotalHits()
        ];
    }

    public function searchUsers(
        $terms = null,
        ?array $fields = [],
        ?array $notInIds = [],
        bool $onlyUsers = false
    ): array {
        $query = new Query\BoolQuery();

        if ($terms) {
            $query = $this->searchTermsInMultipleFields($query, $fields, $terms, 'best_fields');
        }
        if (\count($notInIds) > 0) {
            $query = $this->searchNotInTermsForField($query, 'id', $notInIds);
        }

        $resultSet = $this->index->getType($this->type)->search($query, 300);
        $users = $this->getHydratedResultsFromResultSet($this->userRepo, $resultSet);

        if ($onlyUsers) {
            return $users;
        }

        return [
            'users' => $users,
            'count' => $resultSet->getTotalHits()
        ];
    }

    public function getContributorByProject(Project $project, int $offset, int $limit): array
    {
        $nestedQuery = new Query\Nested();
        $nestedQuery->setPath('contributionsCountByProject');

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(
            new Term(['contributionsCountByProject.project.id' => $project->getId()])
        );
        $boolQuery->addMust(new Range('contributionsCountByProject.count', ['gt' => 0]));

        $nestedQuery->setQuery($boolQuery);

        $query = new Query($nestedQuery);
        $query->setSort([
            'contributionsCountByProject.count' => [
                'order' => 'desc',
                'nested_filter' => [
                    'term' => ['contributionsCountByProject.project.id' => $project->getId()]
                ]
            ]
        ]);

        $query
            ->setSource(['id'])
            ->setFrom($offset)
            ->setSize($limit);
        $resultSet = $this->index->getType($this->type)->search($query);
        $users = $this->getHydratedResultsFromResultSet($this->userRepo, $resultSet);

        return [
            'results' => $users,
            'totalCount' => $resultSet->getTotalHits()
        ];
    }

    public function getContributorByStep(AbstractStep $step, int $offset, int $limit): array
    {
        $nestedQuery = new Query\Nested();
        $nestedQuery->setPath('contributionsCountByStep');

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Term(['contributionsCountByStep.step.id' => $step->getId()]));
        $boolQuery->addMust(new Range('contributionsCountByStep.count', ['gt' => 0]));

        $nestedQuery->setQuery($boolQuery);

        $query = new Query($nestedQuery);

        $query->setSort([
            'createdAt' => [
                'order' => 'desc'
            ]
        ]);

        // Unstable sort by top contributors.
        // It will be used in the future for projects counters.
        // $query->setSort([
        //     'contributionsCountByStep.count' => [
        //         'order' => 'desc',
        //         'nested_filter' => [
        //             'term' => ['contributionsCountByStep.step.id' => $step->getId()],
        //         ],
        //     ],
        // ]);

        $query
            ->setSource(['id'])
            ->setFrom($offset)
            ->setSize($limit);

        $resultSet = $this->index->getType($this->type)->search($query);
        $users = $this->getHydratedResultsFromResultSet($this->userRepo, $resultSet);

        return [
            'results' => $users,
            'totalCount' => $resultSet->getTotalHits()
        ];
    }

    public function getAllContributors(int $offset, int $limit): array
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Range('totalContributionsCount', ['gt' => 0]));

        $query = new Query($boolQuery);
        $query->setSort([
            'totalContributionsCount' => [
                'order' => 'desc'
            ]
        ]);

        $query
            ->setFrom($offset)
            ->setSize($limit)
            ->setSource(['id']);

        $resultSet = $this->index->getType('user')->search($query);

        return [
            'results' => $this->getHydratedResultsFromResultSet($this->userRepo, $resultSet),
            'totalCount' => $resultSet->getTotalHits()
        ];
    }

    private function getSort(array $order): array
    {
        switch ($order['field']) {
            case SortField::CREATED_AT:
                $sortField = SortField::SORT_FIELD[SortField::CREATED_AT];

                break;
            default:
                throw new \RuntimeException("Unknow order: ${order}");
        }

        return [$sortField => ['order' => $order['direction']]];
    }
}
