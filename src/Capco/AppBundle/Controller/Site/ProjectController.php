<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Form\ProjectSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\Serializer\SerializationContext;

class ProjectController extends Controller
{
    /**
     * @Cache(expires="+1 minutes", maxage="60", smaxage="60", public="true")
     * @Template("CapcoAppBundle:Project:lastProjects.html.twig")
     *
     * @param $max
     * @param $offset
     *
     * @return array
     */
    public function lastProjectsAction($max = 4, $offset = 0)
    {
        $projects = $this->getDoctrine()->getRepository('CapcoAppBundle:Project')->getLastPublished($max, $offset);

        return [
            'projects' => $projects,
        ];
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/projects/{projectSlug}/votes", name="app_project_show_user_votes")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     *
     * @param Project $project
     */
    public function showUserVotesAction(Project $project)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('jms_serializer');

        $votableSteps = $serializer->serialize([
            'votableSteps' => $this
                ->get('capco.proposal_votes.resolver')
                ->getVotableStepsForProject($project),
        ], 'json', SerializationContext::create()->setGroups(['Steps', 'UserVotes']));

        $districts = $serializer->serialize([
            'districts' => $em->getRepository('CapcoAppBundle:District')->findAll(),
        ], 'json', SerializationContext::create()->setGroups(['Districts']));

        $themes = $serializer->serialize([
            'themes' => $em->getRepository('CapcoAppBundle:Theme')->findAll(),
        ], 'json', SerializationContext::create()->setGroups(['Themes']));

        $response = $this->render('CapcoAppBundle:Project:show_user_votes.html.twig', [
            'project' => $project,
            'themes' => $themes,
            'districts' => $districts,
            'votableSteps' => $votableSteps,
        ]);

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
            $response->setPublic();
            $response->setSharedMaxAge(60);
        }

        return $response;
    }

    /**
     * @Route("/projects/{projectSlug}/stats", name="app_project_show_stats")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     *
     * @param Project $project
     */
    public function showStatsAction(Project $project)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->get('doctrine.orm.entity_manager');

        $steps = $serializer->serialize([
            'steps' => $this
                ->get('capco.project_stats.resolver')
                ->getStepsWithStatsForProject($project),
        ], 'json', SerializationContext::create());

        $districts = $serializer->serialize([
            'districts' => $em->getRepository('CapcoAppBundle:District')->findAll(),
        ], 'json', SerializationContext::create()->setGroups(['Districts']));

        $themes = $serializer->serialize([
            'themes' => $em->getRepository('CapcoAppBundle:Theme')->findAll(),
        ], 'json', SerializationContext::create()->setGroups(['Themes']));

        $response = $this->render('CapcoAppBundle:Project:show_stats.html.twig', [
            'project' => $project,
            'steps' => $steps,
            'districts' => $districts,
            'themes' => $themes,
            'currentStep' => 'stats_step',
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(60);

        return $response;
    }

    /**
     * @Route("/projects/{projectSlug}/trashed", name="app_project_show_trashed", defaults={"_feature_flags" = "project_trash"} )
     * @Route("/consultations/{projectSlug}/trashed", name="app_consultation_show_trashed", defaults={"_feature_flags" = "project_trash"} )
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_trashed.html.twig")
     *
     * @param Project $project
     *
     * @return array
     */
    public function showTrashedAction(Project $project)
    {
        if (false == $project->canDisplay()) {
            throw $this->createNotFoundException($this->get('translator')->trans('project.error.not_found', [], 'CapcoAppBundle'));
        }

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', [], 'CapcoAppBundle'));
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $opinions = $em->getRepository('CapcoAppBundle:Opinion')->getTrashedOrUnpublishedByProject($project);
        $versions = $em->getRepository('CapcoAppBundle:OpinionVersion')->getTrashedOrUnpublishedByProject($project);
        $arguments = $em->getRepository('CapcoAppBundle:Argument')->getTrashedOrUnpublishedByProject($project);
        $sources = $em->getRepository('CapcoAppBundle:Source')->getTrashedOrUnpublishedByProject($project);

        $proposals = $em->getRepository('CapcoAppBundle:Proposal')->getTrashedOrUnpublishedByProject($project);

        return [
            'project' => $project,
            'opinions' => $opinions,
            'versions' => $versions,
            'arguments' => $arguments,
            'sources' => $sources,
            'proposals' => $proposals,
            'argumentsLabels' => Argument::$argumentTypesLabels,
            'currentStep' => 'trash_step',
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/consultation/{stepSlug}/download/{format}", name="app_project_download")
     * @Route("/consultations/{projectSlug}/consultation/{stepSlug}/download/{format}", name="app_consultation_download")
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @ParamConverter("step", class="CapcoAppBundle:Steps\AbstractStep", options={"mapping": {"stepSlug": "slug"}})
     *
     * @param Project      $project
     * @param AbstractStep $step
     * @param $format
     *
     * @return Response $response
     */
    public function downloadAction(Project $project, AbstractStep $step, $format)
    {
        if (!$project || !$step) {
            throw $this->createNotFoundException($this->get('translator')->trans('project.error.not_found', [], 'CapcoAppBundle'));
        }

        if (!$project->isExportable() && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException($this->get('translator')->trans('project.error.not_exportable', [], 'CapcoAppBundle'));
        }

        $resolver = $this->get('capco.project.download.resolver');
        $content = $resolver->getContent($step, $format);

        if (!$content) {
            throw new NotFoundHttpException('Wrong format');
        }

        $response = new Response($content);
        $contentType = $resolver->getContentType($format);
        $filename = $project->getSlug().'_'.$step->getSlug().'.'.$format;
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }

    /**
     * @Route("/projects/{projectSlug}/events", name="app_project_show_events", defaults={"_feature_flags" = "calendar"})
     * @Route("/consultations/{projectSlug}/events", name="app_consultation_show_events", defaults={"_feature_flags" = "calendar"})
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_events.html.twig")
     *
     * @param $project
     *
     * @return array
     */
    public function showEventsAction(Project $project)
    {
        $groupedEvents = $this->get('capco.event.resolver')->getEventsGroupedByYearAndMonth(null, null, $project->getSlug(), null);
        $nbEvents = $this->get('capco.event.resolver')->countEvents(null, null, $project->getSlug(), null);

        return [
            'project' => $project,
            'years' => $groupedEvents,
            'nbEvents' => $nbEvents,
            'currentStep' => 'events_step',
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/posts/{page}", name="app_project_show_posts", requirements={"page" = "\d+"}, defaults={"_feature_flags" = "blog", "page" = 1} )
     * @Route("/consultations/{projectSlug}/posts/{page}", name="app_consultation_show_posts", requirements={"page" = "\d+"}, defaults={"_feature_flags" = "blog", "page" = 1} )
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_posts.html.twig")
     *
     * @param $page
     * @param $project
     *
     * @return array
     */
    public function showPostsAction(Project $project, $page)
    {
        $pagination = $this->get('capco.site_parameter.resolver')->getValue('blog.pagination.size');

        $posts = $this->get('capco.blog.post.repository')->getSearchResults(
            $pagination,
            $page,
            null,
            $project->getSlug()
        );

        //Avoid division by 0 in nbPage calculation
        $nbPage = 1;
        if ($pagination !== null && $pagination !== 0) {
            $nbPage = ceil(count($posts) / $pagination);
        }

        return [
            'project' => $project,
            'posts' => $posts,
            'page' => $page,
            'nbPage' => $nbPage,
            'currentStep' => 'posts_step',
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/participants/{page}", name="app_project_show_contributors", requirements={"page" = "\d+"}, defaults={"page" = 1} )
     * @Route("/consultations/{projectSlug}/participants/{page}", name="app_consultation_show_contributors", requirements={"page" = "\d+"}, defaults={"page" = 1} )
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_contributors.html.twig")
     *
     * @param $page
     * @param $project
     *
     * @return array
     */
    public function showContributorsAction(Project $project, $page)
    {
        $pagination = $this->get('capco.site_parameter.resolver')->getValue('contributors.pagination');

        $contributors = $this->get('capco.contribution.resolver')->getProjectContributorsOrdered($project, $pagination, $page);
        //Avoid division by 0 in nbPage calculation
        $nbPage = 1;
        if ($pagination !== null && $pagination !== 0) {
            $nbPage = ceil(count($contributors) / $pagination);
        }

        return [
            'project' => $project,
            'contributors' => $contributors,
            'page' => $page,
            'pagination' => $pagination,
            'nbPage' => $nbPage,
            'currentStep' => 'contributors_step',
        ];
    }

    /**
     * @Template("CapcoAppBundle:Project:show_meta.html.twig")
     *
     * @param $projectSlug
     * @param $currentStepSlug
     *
     * @return array
     */
    public function showMetaAction($projectSlug, $currentStepSlug)
    {
        $em = $this->getDoctrine();
        $project = $em->getRepository('CapcoAppBundle:Project')->getOneBySlugWithStepsAndEventsAndPosts($projectSlug);

        return [
            'project' => $project,
            'currentStep' => $currentStepSlug,
            'stepStatus' => AbstractStep::$stepStatus,
        ];
    }

    /**
     * @Route("/projects/{page}", name="app_project", requirements={"page" = "\d+"}, defaults={"page" = 1} )
     * @Route("/projects/{theme}/{sort}/{page}", name="app_project_search", requirements={"page" = "\d+"}, defaults={"page" = 1, "theme" = "all"} )
     * @Route("/projects/{theme}/{sort}/{term}/{page}", name="app_project_search_term", requirements={"page" = "\d+"}, defaults={"page" = 1, "theme" = "all"} )
     * @Template("CapcoAppBundle:Project:index.html.twig")
     *
     * @param $page
     * @param $request
     * @param $theme
     * @param $sort
     * @param $term
     *
     * @return array
     */
    public function indexAction(Request $request, $page, $theme = null, $sort = null, $term = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUrl = $this->generateUrl('app_project');
        $toggleManager = $this->get('capco.toggle.manager');
        $themesActivated = $toggleManager->isActive('themes');
        $formActivated = $toggleManager->isActive('projects_form');

        if ($formActivated) {
            $form = $this->createForm(new ProjectSearchType($this->get('capco.toggle.manager')), null, [
                'action' => $currentUrl,
                'method' => 'POST',
            ]);
        }

        $themesActivated = $toggleManager->isActive('themes');

        if ($request->getMethod() == 'POST' && $formActivated) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // redirect to the results page (avoids reload alerts)
                $data = $form->getData();

                return $this->redirect($this->generateUrl('app_project_search_term', [
                    'theme' => ($themesActivated && array_key_exists('theme', $data) && $data['theme']) ? $data['theme']->getSlug() : Theme::FILTER_ALL,
                    'sort' => $data['sort'],
                    'term' => $data['term'],
                ]));
            }
        } else {
            if ($formActivated) {
                $form->setData([
                    'theme' => $themesActivated ? $em->getRepository('CapcoAppBundle:Theme')->findOneBySlug($theme) : null,
                    'sort' => $sort,
                    'term' => $term,
                ]);
            }
        }

        $pagination = $this->get('capco.site_parameter.resolver')->getValue('projects.pagination');

        $projects = $em->getRepository('CapcoAppBundle:Project')->getSearchResults($pagination, $page, $theme, $sort, $term);

        //Avoid division by 0 in nbPage calculation
        $nbPage = 1;
        if ($pagination !== null && $pagination !== 0) {
            $nbPage = ceil(count($projects) / $pagination);
        }

        $parameters = [
            'projects' => $projects,
            'page' => $page,
            'nbPage' => $nbPage,
        ];

        if ($formActivated) {
            $parameters['form'] = $form->createView();
        }

        return $parameters;
    }

    /**
     * @Cache(expires="+1 minutes", maxage="60", smaxage="60", public="true")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:votes_widget.html.twig")
     *
     * @param $max
     * @param $offset
     *
     * @return array
     */
    public function showVotesWidgetAction(Project $project)
    {
        $serializer = $this->get('jms_serializer');
        $votableSteps = $serializer->serialize([
            'votableSteps' => $this
                ->get('capco.proposal_votes.resolver')
                ->getVotableStepsForProject($project),
        ], 'json', SerializationContext::create()->setGroups(['Steps', 'UserVotes']));
        $image = $serializer->serialize([
            'image' => $this
            ->get('capco.site_image.resolver')
            ->getMedia('image.votes_bar'),
        ], 'json', SerializationContext::create());

        return [
            'votableSteps' => $votableSteps,
            'project' => $project,
            'image' => $image,
        ];
    }
}
