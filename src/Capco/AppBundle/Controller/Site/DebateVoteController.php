<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\GraphQL\Resolver\Debate\DebateUrlResolver;
use Capco\AppBundle\Manager\TokenManager;
use FOS\UserBundle\Security\LoginManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DebateVoteController extends AbstractController
{
    /**
     * @Route("/voteByToken", name="capco_app_debate_vote_by_token", options={"i18n" = false})
     */
    public function voteByToken(
        TokenManager $tokenManager,
        Request $request,
        DebateUrlResolver $debateUrlResolver,
        LoginManagerInterface $loginManager,
        TranslatorInterface $translator
    ) {
        $forOrAgainst = $request->get('value') ?? '';
        $token = $request->get('token') ?? '';

        try {
            $debateVote = $tokenManager->consumeVoteToken($token, $forOrAgainst);
        } catch (\Exception $exception) {
            $this->addFlash(
                'danger',
                $translator->trans($exception->getMessage(), [], 'CapcoAppBundle')
            );

            return $this->redirectToRoute('app_homepage');
        }

        $response = $this->redirect($debateUrlResolver->__invoke($debateVote->getDebate()));
        $this->addFlash('success', $translator->trans('vote.add_success', [], 'CapcoAppBundle'));
        if (null === $this->getUser()) {
            $loginManager->loginUser('main', $debateVote->getUser(), $response);
        }

        return $response;
    }
}
