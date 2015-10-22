<?php

namespace Capco\AppBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionVersion;

class ArgumentSerializationListener implements EventSubscriberInterface
{
    private $router;
    private $tokenStorage;

    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage)
    {
        if (getenv('SYMFONY_USE_SSL')) {
            $router->getContext()->setScheme('https');
        }

        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'class' => 'Capco\AppBundle\Entity\Argument',
                'method' => 'onPostArgument',
            ],
        ];
    }

    public function onPostArgument(ObjectEvent $event)
    {
        $argument = $event->getObject();
        $opinion = $argument->getLinkedOpinion();
        $opinionType = $opinion->getOpinionType();
        $step = $opinion->getStep();
        $project = $step->getProjectAbstractStep()->getProject();
        $user = $this->tokenStorage->getToken()->getUser();

        $showUrl = '';

        $parent = $argument->getParent();
        if ($parent instanceof Opinion) {
            $showUrl = $this->router->generate('app_project_show_opinion', [
                'projectSlug' => $project->getSlug(),
                'stepSlug' => $step->getSlug(),
                'opinionTypeSlug' => $opinionType->getSlug(),
                'opinionSlug' => $opinion->getSlug(),
            ], true).'#arg-'.$argument->getId();
        } elseif ($parent instanceof OpinionVersion) {
            $showUrl = $this->router->generate('app_project_show_opinion_version', [
                'projectSlug' => $project->getSlug(),
                'stepSlug' => $step->getSlug(),
                'opinionTypeSlug' => $opinionType->getSlug(),
                'opinionSlug' => $opinion->getSlug(),
                'versionSlug' => $parent->getSlug(),
            ], true).'#arg-'.$argument->getId();
        }

        $event->getVisitor()->addData(
            '_links', [
                'show' => $showUrl,
                'edit' => $this->router->generate('app_project_edit_argument', [
                    'projectSlug' => $project->getSlug(),
                    'stepSlug' => $step->getSlug(),
                    'opinionTypeSlug' => $opinionType->getSlug(),
                    'opinionSlug' => $opinion->getSlug(),
                    'argumentId' => $argument->getId(),
                ], true),
                'report' => $this->router->generate('app_report_argument', [
                    'projectSlug' => $project->getSlug(),
                    'stepSlug' => $step->getSlug(),
                    'opinionTypeSlug' => $opinionType->getSlug(),
                    'opinionSlug' => $opinion->getSlug(),
                    'argumentId' => $argument->getId(),
                ], true),
            ]
        );

        $event->getVisitor()->addData(
            'has_user_voted', $user === 'anon.' ? false : $argument->userHasVote($user)
        );

        $event->getVisitor()->addData(
            'has_user_reported', $user === 'anon.' ? false : $argument->userHasReport($user)
        );
    }
}
