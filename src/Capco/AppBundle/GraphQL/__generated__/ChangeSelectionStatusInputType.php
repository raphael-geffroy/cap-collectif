<?php
namespace Capco\AppBundle\GraphQL\__GENERATED__;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\LazyConfig;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Definition\Type\GeneratedTypeInterface;

/**
 * THIS FILE WAS GENERATED AND SHOULD NOT BE MODIFIED!
 */
final class ChangeSelectionStatusInputType extends InputObjectType implements GeneratedTypeInterface
{
    const NAME = 'ChangeSelectionStatusInput';

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'ChangeSelectionStatusInput',
            'description' => null,
            'fields' => function () use ($globalVariable) {
                return [
                'stepId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => null,
                ],
                'proposalId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => null,
                ],
                'statusId' => [
                    'type' => Type::id(),
                    'description' => null,
                ],
                'clientMutationId' => [
                    'type' => Type::string(),
                    'description' => null,
                ],
            ];
            },
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
