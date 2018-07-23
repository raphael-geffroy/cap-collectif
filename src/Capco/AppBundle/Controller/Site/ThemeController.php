<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Form\ThemeSearchType;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ThemeController extends Controller
{
    /**
     * @Route("/themes/{page}", name="app_theme", requirements={"page" = "\d+"}, defaults={"page" = 1, "_feature_flags" = "themes"} )
     * @Route("/themes/search/{term}/{page}", name="app_theme_search", requirements={"page" = "\d+"}, defaults={"page" = 1, "_feature_flags" = "themes"} )
     * @Template("CapcoAppBundle:Theme:index.html.twig")
     *
     * @param mixed      $page
     * @param null|mixed $term
     */
    public function indexAction(Request $request, $page, $term = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUrl = $this->generateUrl('app_theme');

        $form = $this->createForm(ThemeSearchType::class, null, [
            'action' => $currentUrl,
            'method' => 'POST',
        ]);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // redirect to the results page (avoids reload alerts)
                $data = $form->getData();

                return $this->redirect($this->generateUrl('app_theme_search', [
                    'term' => $data['term'],
                ]));
            }
        } else {
            $form->setData(['term' => $term]);
        }

        $pagination = $this->get('capco.site_parameter.resolver')->getValue('themes.pagination');

        $themes = $em->getRepository('CapcoAppBundle:Theme')->getSearchResultsWithCounters($pagination, $page, $term);

        //Avoid division by 0 in nbPage calculation
        $nbPage = 1;
        if (null !== $pagination && 0 !== $pagination) {
            $nbPage = ceil(\count($themes) / $pagination);
        }

        return [
            'themes' => $themes,
            'form' => $form->createView(),
            'page' => $page,
            'nbPage' => $nbPage,
        ];
    }

    /**
     * @Route("/themes/{slug}", name="app_theme_show", defaults={"_feature_flags" = "themes"})
     * @ParamConverter("theme", class="CapcoAppBundle:Theme", options={"repository_method" = "getOneBySlug"})
     * @Template("CapcoAppBundle:Theme:show.html.twig")
     */
    public function showAction(Theme $theme)
    {
        if (!$theme->canDisplay()) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('restricted-access', [], 'CapcoAppBundle'));
        }

        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');

        $projectProps = $serializer->serialize([
            'projects' => $em
                ->getRepository('CapcoAppBundle:Project')
                ->getProjectsByTheme($theme),
        ], 'json', SerializationContext::create()->setGroups(['Projects', 'UserDetails', 'Steps', 'Themes', 'ProjectType']));

        $ideaCreationProps = $serializer->serialize([
            'themes' => $em->getRepository('CapcoAppBundle:Theme')->findAll(),
            'themeId' => $theme->getId(),
        ], 'json', SerializationContext::create()->setGroups(['ThemeDetails']));

        return [
            'theme' => $theme,
            'maxProjectsDisplayed' => 12,
            'projectProps' => $projectProps,
            'ideaCreationProps' => $ideaCreationProps,
        ];
    }
}
