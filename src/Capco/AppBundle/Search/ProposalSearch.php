<?php

namespace Capco\AppBundle\Search;

use Capco\AppBundle\Elasticsearch\ElasticsearchPaginatedResult;
use Capco\AppBundle\Enum\OrderDirection;
use Capco\AppBundle\Enum\ProposalsState;
use Capco\AppBundle\Enum\ProposalTrashedStatus;
use Capco\AppBundle\Repository\ProposalRepository;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\Result;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;

class ProposalSearch extends Search
{
    public const SEARCH_FIELDS = [
        'title',
        'author.username.std',
        'author.username',
        'title.std',
        'reference',
        'reference.std',
        'body',
        'body.std',
        'object',
        'object.std',
        'teaser',
        'teaser.std'
    ];

    private $proposalRepo;

    public function __construct(Index $index, ProposalRepository $proposalRepo)
    {
        parent::__construct($index);
        $this->proposalRepo = $proposalRepo;
        $this->type = 'proposal';
    }

    public function searchProposals(
        int $limit,
        $terms,
        array $providedFilters,
        int $seed,
        ?string $cursor,
        ?string $order = null
    ): ElasticsearchPaginatedResult
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery = $this->searchTermsInMultipleFields(
            $boolQuery,
            self::SEARCH_FIELDS,
            $terms,
            'phrase_prefix'
        );
        $stateTerms = [];
        $filters = $this->getFilters($providedFilters);
        foreach ($filters as $key => $value) {
            $term = new Term([$key => ['value' => $value]]);
            if (
                \in_array($key, ['draft', 'published', 'trashed'], true) &&
                (isset($providedFilters['state']) &&
                    ProposalsState::ALL === $providedFilters['state'])
            ) {
                $stateTerms[] = $term;
            } else {
                $boolQuery->addFilter($term);
            }
        }

        if (\count($stateTerms) > 0) {
            $boolQuery->addFilter((new Query\BoolQuery())->addShould($stateTerms));
        }

        if ('random' === $order) {
            $query = $this->getRandomSortedQuery($boolQuery, $seed);
            $query->setSort(['_score' => new \stdClass(), 'id' => new \stdClass()]);
        } else {
            $query = new Query($boolQuery);
            if ($order) {
                $query->setSort([
                    $this->getSort(
                        $order,
                        $providedFilters['collectStep'] ?? $providedFilters['selectionStep']
                    ),
                    ['id' => new \stdClass()]
                ]);
            }
        }

        $this->applyCursor($query, $cursor);
        $query->setSource(['id'])->setSize($limit);
        $resultSet = $this->index->getType($this->type)->search($query);
        $ids = [];
        $cursors = [];
        foreach ($resultSet as $result) {
            $ids[] = $result->getData()['id'];
            $cursors[] = $result->getParam('sort');
        }
        $proposals = $this->getHydratedResults($this->proposalRepo, $ids);

        return new ElasticsearchPaginatedResult($proposals, $cursors, $resultSet->getTotalHits());
    }

    public static function findOrderFromFieldAndDirection(string $field, string $direction): string
    {
        switch ($field) {
            case 'VOTES':
                if (OrderDirection::ASC === $direction) {
                    $order = 'least-votes';
                } else {
                    $order = 'votes';
                }

                break;
            case 'PUBLISHED_AT':
                if (OrderDirection::ASC === $direction) {
                    $order = 'old-published';
                } else {
                    $order = 'last-published';
                }

                break;
            case 'CREATED_AT':
                if (OrderDirection::ASC === $direction) {
                    $order = 'old';
                } else {
                    $order = 'last';
                }

                break;
            case 'COMMENTS':
                $order = 'comments';

                break;
            case 'COST':
                if (OrderDirection::ASC === $direction) {
                    $order = 'cheap';
                } else {
                    $order = 'expensive';
                }

                break;
            default:
                $order = 'random';

                break;
        }

        return $order;
    }

    public function searchProposalsVotesCount(array $ids, array $filterArray = []): array
    {
        $boolQuery = new Query\BoolQuery();
        $filters = $this->getFilters($filterArray);
        foreach ($filters as $key => $value) {
            $boolQuery->addFilter(new Term([$key => ['value' => $value]]));
        }
        $boolQuery->addFilter(new Query\Terms('id', $ids));
        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setSource(['id', 'votesCountByStep', 'votesCount'])->setSize(\count($ids));
        $resultSet = $this->index->getType($this->type)->search($query);

        return array_map(static function (Result $result) {
            return $result->getData();
        }, $resultSet->getResults());
    }

    private function getSort(string $order, string $stepId): array
    {
        switch ($order) {
            case 'old':
                $sortField = 'createdAt';
                $sortOrder = 'asc';

                break;
            case 'last':
                $sortField = 'createdAt';
                $sortOrder = 'desc';

                break;
            case 'old-published':
                $sortField = 'publishedAt';
                $sortOrder = 'asc';

                break;
            case 'last-published':
                $sortField = 'publishedAt';
                $sortOrder = 'desc';

                break;
            case 'votes':
                return [
                    'votesCountByStep.count' => [
                        'order' => 'desc',
                        'nested_path' => 'votesCountByStep',
                        'nested_filter' => ['term' => ['votesCountByStep.step.id' => $stepId]]
                    ],
                    'createdAt' => ['order' => 'desc']
                ];

            case 'least-votes':
                return [
                    'votesCountByStep.count' => [
                        'order' => 'asc',
                        'nested_path' => 'votesCountByStep',
                        'nested_filter' => ['term' => ['votesCountByStep.step.id' => $stepId]]
                    ],
                    'createdAt' => ['order' => 'desc']
                ];

            case 'comments':
                return [
                    'commentsCount' => ['order' => 'desc'],
                    'createdAt' => ['order' => 'desc']
                ];

            case 'expensive':
                return [
                    'estimation' => ['order' => 'desc'],
                    'createdAt' => ['order' => 'desc']
                ];
            case 'cheap':
                return [
                    'estimation' => ['order' => 'asc'],
                    'createdAt' => ['order' => 'desc']
                ];
            default:
                throw new \RuntimeException('Unknown order: ' . $order);
        }

        return [$sortField => ['order' => $sortOrder]];
    }

    private function getFilters(array $providedFilters): array
    {
        $filters = [];
        $filters['draft'] = false;
        $filters['published'] = true;

        if (isset($providedFilters['trashedStatus'])) {
            if (ProposalTrashedStatus::TRASHED === $providedFilters['trashedStatus']) {
                $filters['trashed'] = true;
            } elseif (ProposalTrashedStatus::NOT_TRASHED === $providedFilters['trashedStatus']) {
                $filters['trashed'] = false;
            }
        }

        if (isset($providedFilters['step'])) {
            $filters['step.id'] = GlobalId::fromGlobalId($providedFilters['step'])['id'];
        }

        if (isset($providedFilters['selectionStep']) && !empty($providedFilters['selectionStep'])) {
            $filters['selections.step.id'] = $providedFilters['selectionStep'];
            if (isset($providedFilters['status'])) {
                $filters['selections.status.id'] = $providedFilters['status'];
            }
        } elseif (isset($providedFilters['status'])) {
            $filters['status.id'] = $providedFilters['status'];
        }

        if (isset($providedFilters['proposalForm'])) {
            $filters['proposalForm.id'] = $providedFilters['proposalForm'];
        }

        if (isset($providedFilters['district'])) {
            $filters['district.id'] = $providedFilters['district'];
        }
        if (isset($providedFilters['themes'])) {
            $filters['theme.id'] = $providedFilters['themes'];
        }
        if (isset($providedFilters['types']) && $providedFilters['types'] > 0) {
            $filters['author.userType.id'] = $providedFilters['types'];
        }
        if (isset($providedFilters['category'])) {
            $filters['category.id'] = $providedFilters['category'];
        }
        if (isset($providedFilters['author'])) {
            $filters['author.id'] = $providedFilters['author'];
        }
        if (isset($providedFilters['published'])) {
            $filters['published'] = $providedFilters['published'];
        }
        if (isset($providedFilters['includeDraft']) && true === $providedFilters['includeDraft']) {
            unset($filters['draft'], $filters['published']);
        }

        if (isset($providedFilters['state'])) {
            switch ($providedFilters['state']) {
                case ProposalsState::ALL:
                    $filters['draft'] = true;
                    $filters['published'] = true;
                    $filters['trashed'] = true;

                    break;
                case ProposalsState::DRAFT:
                    $filters['draft'] = true;
                    $filters['published'] = false;
                    $filters['trashed'] = false;

                    break;
                case ProposalsState::TRASHED:
                    $filters['draft'] = false;
                    $filters['published'] = true;
                    $filters['trashed'] = true;

                    break;
                case ProposalsState::PUBLISHED:
                    $filters['draft'] = false;
                    $filters['published'] = true;
                    $filters['trashed'] = false;

                    break;
            }
        }

        return $filters;
    }
}
