<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\CapcoAppBundleEvents;
use Capco\AppBundle\Event\CommentChangedEvent;
use Capco\AppBundle\Form\CommentType as CommentForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommentController extends Controller
{
    /**
     * @Route("/comments/{objectType}/{objectId}/login", name="app_comment_login")
     *
     * @param $objectType
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginToCommentAction($objectType, $objectId)
    {
        if (!$this->get('security.token_storage')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', [], 'CapcoAppBundle'));
        }

        return $this->redirect($this->get('capco.comment.resolver')->getUrlOfObjectByTypeAndId($objectType, $objectId));
    }

    /**
     * @Template("CapcoAppBundle:Comment:list.html.twig")
     *
     * @param $object
     *
     * @return array
     */
    public function showCommentsAction($object)
    {
        $comments = $this->get('capco.comment.resolver')->getCommentsByObject($object);

        return [
            'comments' => $comments,
        ];
    }

    /**
     * @Route("/comments/{commentId}/edit", name="app_comment_edit")
     * @Template("CapcoAppBundle:Comment:update.html.twig")
     *
     * @param $commentId
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateCommentAction($commentId, Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', [], 'CapcoAppBundle'));
        }

        $comment = $this->getDoctrine()->getRepository('CapcoAppBundle:Comment')->getOneById($commentId);

        if (!$comment) {
            throw $this->createNotFoundException($this->get('translator')->trans('comment.error.not_found', [], 'CapcoAppBundle'));
        }

        if (false === $comment->canContribute()) {
            throw new AccessDeniedException($this->get('translator')->trans('comment.error.no_contribute', [], 'CapcoAppBundle'));
        }

        $userCurrent = $this->getUser();
        $userPostComment = $comment->getAuthor();

        if ($userCurrent !== $userPostComment) {
            throw new AccessDeniedException($this->get('translator')->trans('comment.error.not_author', [], 'CapcoAppBundle'));
        }

        $form = $this->createForm(new CommentForm($userCurrent), $comment, ['actionType' => 'edit']);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $comment->resetVotes();
                $em->persist($comment);
                $em->flush();
                $this->get('event_dispatcher')->dispatch(
                    CapcoAppBundleEvents::COMMENT_CHANGED,
                    new CommentChangedEvent($comment, 'update')
                );
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('comment.update.success'));

                return $this->redirect($this->get('capco.comment.resolver')->getUrlOfRelatedObject($comment));
            }
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('comment.update.error'));
        }

        return [
            'form' => $form->createView(),
            'comment' => $comment,
        ];
    }

    /**
     * @Route("/comments/{commentId}/delete", name="app_comment_delete")
     * @Template("CapcoAppBundle:Comment:delete.html.twig")
     *
     * @param $commentId
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCommentAction($commentId, Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', [], 'CapcoAppBundle'));
        }

        $comment = $this->getDoctrine()->getRepository('CapcoAppBundle:Comment')->getOneById($commentId);

        if (null === $comment) {
            throw $this->createNotFoundException($this->get('translator')->trans('comment.error.not_found', [], 'CapcoAppBundle'));
        }

        if (false === $comment->canContribute()) {
            throw new AccessDeniedException($this->get('translator')->trans('comment.error.no_contribute', [], 'CapcoAppBundle'));
        }

        $userCurrent = $this->getUser()->getId();
        $userPostComment = $comment->getAuthor()->getId();

        if ($userCurrent !== $userPostComment) {
            throw new AccessDeniedException($this->get('translator')->trans('comment.error.not_author', [], 'CapcoAppBundle'));
        }

        //Champ CSRF
        $form = $this->createFormBuilder()->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($comment);
                $this->get('event_dispatcher')->dispatch(
                    CapcoAppBundleEvents::COMMENT_CHANGED,
                    new CommentChangedEvent($comment, 'remove')
                );
                $em->flush();

                $this->get('session')->getFlashBag()->add('info', $this->get('translator')->trans('comment.delete.success'));

                return $this->redirect($this->get('capco.comment.resolver')->getUrlOfRelatedObject($comment));
            }
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('comment.delete.error'));
        }

        return [
            'form' => $form->createView(),
            'comment' => $comment,
        ];
    }
}
