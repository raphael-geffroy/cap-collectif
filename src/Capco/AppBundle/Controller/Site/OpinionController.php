<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Consultation;
use Capco\AppBundle\Entity\ConsultationStep;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionType;
use Capco\AppBundle\Entity\OpinionAppendix;
use Capco\AppBundle\Form\OpinionsType as OpinionForm;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OpinionController extends Controller
{
    /**
     * @Route("/consultations/{consultationSlug}/consultation/{stepSlug}/opinions/{opinionTypeSlug}/{opinionSlug}/versions/{versionSlug}", name="app_consultation_show_opinion_version")
     * @Template("CapcoAppBundle:Opinion:show_version.html.twig")
     */
    public function showOpinionVersionAction(Request $request, $consultationSlug, $stepSlug, $opinionTypeSlug, $opinionSlug, $versionSlug)
    {
        $opinion = $this->getDoctrine()->getRepository('CapcoAppBundle:Opinion')->getOneBySlugJoinUserReports($opinionSlug, $this->getUser());

        $version = $this->getDoctrine()->getRepository('CapcoAppBundle:OpinionVersion')->findOneBySlug($versionSlug);

        if (!$opinion || !$opinion->canDisplay()) {
            throw $this->createNotFoundException($this->get('translator')->trans('opinion.error.not_found', array(), 'CapcoAppBundle'));
        }

        $currentStep = $opinion->getStep();
        $sources = $this->getDoctrine()->getRepository('CapcoAppBundle:Source')->getByOpinionJoinUserReports($opinion, $this->getUser());

        $steps = $this->getDoctrine()->getRepository('CapcoAppBundle:AbstractStep')->getByConsultation($consultationSlug);

        $nav = $this->get('capco.opinion_types.resolver')->getNavForStep($currentStep);

        return [
            'version' => $version,
            'currentStep' => $currentStep,
            'consultation' => $currentStep->getConsultation(),
            'opinion' => $opinion,
            'sources' => $sources,
            'opinionType' => $opinion->getOpinionType(),
            'votes' => $opinion->getVotes(),
            'consultation_steps' => $steps,
            'nav' => $nav,
        ];
    }

    /**
     * @Route("/consultations/{consultationSlug}/consultation/{stepSlug}/opinions/{opinionTypeSlug}/add", name="app_consultation_new_opinion")
     * @ParamConverter("consultation", class="CapcoAppBundle:Consultation", options={"mapping": {"consultationSlug": "slug"}})
     * @ParamConverter("currentStep", class="CapcoAppBundle:ConsultationStep", options={"mapping": {"stepSlug": "slug"}})
     * @ParamConverter("opinionType", class="CapcoAppBundle:OpinionType", options={"mapping": {"opinionTypeSlug": "slug"}})
     *
     * @param $opinionType
     * @param $consultation
     * @param $currentStep
     * @param $request
     * @Template("CapcoAppBundle:Opinion:create.html.twig")
     *
     * @return array
     */
    public function createOpinionAction(Consultation $consultation, ConsultationStep $currentStep, OpinionType $opinionType, Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', array(), 'CapcoAppBundle'));
        }

        if (false == $currentStep->canContribute()) {
            throw new AccessDeniedException($this->get('translator')->trans('consultation.error.no_contribute', array(), 'CapcoAppBundle'));
        }

        if (!$opinionType->getIsEnabled()) {
            throw new NotFoundHttpException();
        }

        $opinion = new Opinion();
        $opinion->setAuthor($this->getUser());
        $opinion->setOpinionType($opinionType);
        $opinion = $this->createAppendicesForOpinion($opinion);
        $opinion->setIsEnabled(true);
        $opinion->setStep($currentStep);

        $form = $this->createForm(new OpinionForm('create'), $opinion);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $maxPos = $this->get('capco.opinion_types.resolver')
                    ->getMaximumPositionByOpinionTypeAndStep($opinionType, $currentStep);
                $opinion->setPosition($maxPos + 1);
                $em->persist($opinion);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('opinion.create.success'));

                return $this->redirect($this->generateUrl('app_consultation_show_opinion', ['consultationSlug' => $consultation->getSlug(), 'stepSlug' => $currentStep->getSlug(), 'opinionTypeSlug' => $opinionType->getSlug(), 'opinionSlug' => $opinion->getSlug()]));
            } else {
                $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('opinion.create.error'));
            }
        }

        return [
            'consultation' => $consultation,
            'currentStep' => $currentStep,
            'opinionType' => $opinionType,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/consultations/{consultationSlug}/consultation/{stepSlug}/opinions/{opinionTypeSlug}/{opinionSlug}/delete", name="app_consultation_delete_opinion")
     *
     * @param $consultationSlug
     * @param $stepSlug
     * @param $opinionTypeSlug
     * @param $consultationSlug
     * @param $opinionSlug
     * @param $request
     * @Template("CapcoAppBundle:Opinion:delete.html.twig")
     *
     * @return array
     */
    public function deleteOpinionAction($consultationSlug, $stepSlug, $opinionTypeSlug, $opinionSlug, Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', array(), 'CapcoAppBundle'));
        }

        $opinion = $this->getDoctrine()->getRepository('CapcoAppBundle:Opinion')->getOneBySlug($opinionSlug);

        if ($opinion == null) {
            throw $this->createNotFoundException($this->get('translator')->trans('opinion.error.not_found', array(), 'CapcoAppBundle'));
        }

        if (false == $opinion->canContribute()) {
            throw new AccessDeniedException($this->get('translator')->trans('opinion.error.no_contribute', array(), 'CapcoAppBundle'));
        }

        $opinionType = $opinion->getOpinionType();
        $currentStep = $opinion->getStep();
        $consultation = $currentStep->getConsultation();

        $userCurrent = $this->getUser()->getId();
        $userPostOpinion = $opinion->getAuthor()->getId();

        if ($userCurrent !== $userPostOpinion) {
            throw new AccessDeniedException($this->get('translator')->trans('opinion.error.not_author', array(), 'CapcoAppBundle'));
        }

        //Champ CSRF
        $form = $this->createFormBuilder()->getForm();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($opinion);
                $em->flush();

                $this->get('session')->getFlashBag()->add('info', $this->get('translator')->trans('opinion.delete.success'));

                return $this->redirect($this->generateUrl('app_consultation_show', ['consultationSlug' => $consultation->getSlug(), 'stepSlug' => $currentStep->getSlug()]));
            } else {
                $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('opinion.delete.error'));
            }
        }

        return array(
            'opinion' => $opinion,
            'consultation' => $consultation,
            'currentStep' => $currentStep,
            'opinionType' => $opinionType,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/consultations/{consultationSlug}/consultation/{stepSlug}/opinions/{opinionTypeSlug}/{opinionSlug}/edit", name="app_consultation_edit_opinion")
     * @Template("CapcoAppBundle:Opinion:update.html.twig")
     *
     * @param $consultationSlug
     * @param $stepSlug
     * @param $opinionTypeSlug
     * @param $opinionSlug
     * @param $request
     *
     * @return array
     */
    public function updateOpinionAction($consultationSlug, $stepSlug, $opinionTypeSlug, $opinionSlug, Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', array(), 'CapcoAppBundle'));
        }

        $opinion = $this->getDoctrine()->getRepository('CapcoAppBundle:Opinion')->getOneBySlug($opinionSlug);

        if ($opinion == null) {
            throw $this->createNotFoundException($this->get('translator')->trans('opinion.error.not_found', array(), 'CapcoAppBundle'));
        }

        if (false == $opinion->canContribute()) {
            throw new AccessDeniedException($this->get('translator')->trans('opinion.error.no_contribute', array(), 'CapcoAppBundle'));
        }

        $opinionType = $opinion->getOpinionType();
        $currentStep = $opinion->getStep();
        $consultation = $currentStep->getConsultation();

        $userCurrent = $this->getUser()->getId();
        $userPostOpinion = $opinion->getAuthor()->getId();

        if ($userCurrent !== $userPostOpinion) {
            throw new AccessDeniedException($this->get('translator')->trans('opinion.error.not_author', array(), 'CapcoAppBundle'));
        }

        $form = $this->createForm(new OpinionForm('edit'), $opinion);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $opinion->resetVotes();
                $em->persist($opinion);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('opinion.update.success'));

                return $this->redirect($this->generateUrl('app_consultation_show_opinion', ['consultationSlug' => $consultation->getSlug(), 'stepSlug' => $currentStep->getSlug(), 'opinionTypeSlug' => $opinionType->getSlug(), 'opinionSlug' => $opinion->getSlug()]));
            } else {
                $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('opinion.update.error'));
            }
        }

        return [
            'form' => $form->createView(),
            'opinion' => $opinion,
            'consultation' => $consultation,
            'currentStep' => $currentStep,
            'opinionType' => $opinionType,
        ];
    }

    /**
     * Page opinion.
     *
     * @Route("/consultations/{consultationSlug}/consultation/{stepSlug}/opinions/{opinionTypeSlug}/{opinionSlug}", name="app_consultation_show_opinion")
     * @Route("/consultations/{consultationSlug}/consultation/{stepSlug}/opinions/{opinionTypeSlug}/{opinionSlug}/sort_arguments/{argumentSort}", name="app_consultation_show_opinion_sortarguments", requirements={"argumentsSort" = "popularity|date"})
     *
     * @param $consultationSlug
     * @param $stepSlug
     * @param $opinionTypeSlug
     * @param $opinionSlug
     * @param $request
     * @param $argumentSort
     * @Template("CapcoAppBundle:Opinion:show.html.twig")
     *
     * @return array
     */
    public function showOpinionAction($consultationSlug, $stepSlug, $opinionTypeSlug, $opinionSlug, Request $request)
    {
        $opinion = $this->getDoctrine()->getRepository('CapcoAppBundle:Opinion')->getOneBySlugJoinUserReports($opinionSlug, $this->getUser());

        if (!$opinion || !$opinion->canDisplay()) {
            throw $this->createNotFoundException($this->get('translator')->trans('opinion.error.not_found', array(), 'CapcoAppBundle'));
        }

        $currentUrl = $this->generateUrl('app_consultation_show_opinion', ['consultationSlug' => $consultationSlug, 'stepSlug' => $stepSlug, 'opinionTypeSlug' => $opinionTypeSlug, 'opinionSlug' => $opinionSlug]);
        $currentStep = $opinion->getStep();

        $steps = $this->getDoctrine()->getRepository('CapcoAppBundle:AbstractStep')->getByConsultation($consultationSlug);

        $nav = $this->get('capco.opinion_types.resolver')->getNavForStep($currentStep);

        return [
            'currentUrl' => $currentUrl,
            'currentStep' => $currentStep,
            'consultation' => $currentStep->getConsultation(),
            'opinion' => $opinion,
            'opinionType' => $opinion->getOpinionType(),
            'consultation_steps' => $steps,
            'nav' => $nav,
        ];
    }

    private function createAppendicesForOpinion(Opinion $opinion)
    {
        $appendixTypes = $this->get('doctrine.orm.entity_manager')
            ->getRepository('CapcoAppBundle:OpinionTypeAppendixType')
            ->findBy(
                ['opinionType' => $opinion->getOpinionType()],
                ['position' => 'ASC']
            );
        foreach ($appendixTypes as $otat) {
            $app = new OpinionAppendix();
            $app->setAppendixType($otat->getAppendixType());
            $app->setOpinion($opinion);
            $opinion->addAppendice($app);
        }
        return $opinion;
    }
}
