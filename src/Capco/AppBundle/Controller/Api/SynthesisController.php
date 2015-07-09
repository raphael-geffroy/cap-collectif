<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\ConsultationStep;
use Capco\AppBundle\Entity\Synthesis\Synthesis;
use Capco\AppBundle\Entity\Synthesis\SynthesisElement;
use Capco\AppBundle\Entity\Synthesis\SynthesisDivision;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;

use Capco\AppBundle\Form\Api\SynthesisType as SynthesisForm;
use Capco\AppBundle\Form\Api\SynthesisElementType as SynthesisElementForm;
use Capco\AppBundle\Form\Api\SynthesisDivisionType as SynthesisDivisionForm;

class SynthesisController extends FOSRestController
{
    /**
     * Get syntheses.
     *
     * @return array|\Capco\AppBundle\Entity\Synthesis\Synthesis[]
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get all the syntheses",
     *  output="Capco\AppBundle\Entity\Synthesis\Synthesis",
     *  statusCodes={
     *    200 = "Syntheses found",
     *    404 = "No syntheses",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Get("/syntheses")
     * @View(serializerGroups={"Syntheses", "Elements"})
     */
    public function getSynthesesAction()
    {
        return $this->get('capco.synthesis.synthesis_handler')->getAllSyntheses();
    }

    /**
     * Create a synthesis from submitted data.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Create a synthesis",
     *  statusCodes={
     *    201 = "Returned when successful",
     *    400 = "Returned when creation fail",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Post("/syntheses")
     * @ParamConverter("synthesis", converter="fos_rest.request_body")
     */
    public function createSynthesisAction(Synthesis $synthesis, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new BadRequestHttpException($validationErrors->__toString());
        }

        $synthesis = $this->get('capco.synthesis.synthesis_handler')->createSynthesis($synthesis);

        $view = $this->view($synthesis, Codes::HTTP_CREATED);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('SynthesisDetails', 'Elements')));
        $url = $this->generateUrl('get_synthesis', ['id' => $synthesis->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $view->setHeader('Location', $url);

        return $view;
    }

    /**
     * Create a synthesis from submitted data and consultation step.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Create a synthesis from a consultation step",
     *  statusCodes={
     *    201 = "Returned when successful",
     *    400 = "Returned when creation fail",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Post("/syntheses/from-consultation-step/{id}")
     * @ParamConverter("consultationStep", options={"mapping": {"id": "id"}})
     * @ParamConverter("synthesis", converter="fos_rest.request_body")
     */
    public function createSynthesisFromConsultationStepAction(ConsultationStep $consultationStep, Synthesis $synthesis, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new BadRequestHttpException($validationErrors->__toString());
        }
        $synthesis = $this->get('capco.synthesis.synthesis_handler')->createSynthesisFromConsultationStep($synthesis, $consultationStep);

        $view = $this->view($synthesis, Codes::HTTP_CREATED);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('SynthesisDetails', 'Elements')));
        $url = $this->generateUrl('get_synthesis', ['id' => $synthesis->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $view->setHeader('Location', $url);

        return $view;
    }

    /**
     * Get a synthesis by id.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a synthesis with all elements",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Synthesis does not exist",
     *  }
     * )
     *
     * @Get("/syntheses/{id}")
     * @View(serializerGroups={"SynthesisDetails", "Elements"})
     */
    public function getSynthesisAction($id)
    {
        return $this->get('capco.synthesis.synthesis_handler')->getSynthesis($id);
    }

    /**
     * Update a synthesis.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Update a synthesis",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    400 = "Returned when update fail",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Put("/syntheses/{id}")
     * @ParamConverter("synthesis", options={"mapping": {"id": "id"}})
     * @View(serializerGroups={"SynthesisDetails", "Elements"})
     */
    public function updateSynthesisAction(Request $request, Synthesis $synthesis)
    {
        $form = $this->createForm(new SynthesisForm(), $synthesis);
        $form->submit($request->request->all(), false);
        if ($form->isValid()) {
            $synthesis = $this->get('capco.synthesis.synthesis_handler')->updateSynthesis($synthesis);

            return $synthesis;
        }

        return $form;
    }

    /**
     * Get updated synthesis by id.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get updated synthesis with all elements",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Synthesis does not exist",
     *  }
     * )
     *
     * @Get("/syntheses/{id}/updated")
     * @View(serializerGroups={"SynthesisDetails", "Elements"})
     */
    public function getUpdatedSynthesisAction($id)
    {
        return $this->get('capco.synthesis.synthesis_handler')->getUpdatedSynthesis($id);
    }

    /**
     * Get synthesis elements filtered by type.
     *
     * @return array|\Capco\AppBundle\Entity\Synthesis\SynthesisElement[]
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get the elements of a synthesis, filtered by type",
     *  statusCodes={
     *    200 = "Syntheses element found",
     *    404 = "Synthesis not found",
     *  }
     * )
     *
     * @ParamConverter("synthesis", options={"mapping": {"id": "id"}})
     * @QueryParam(name="type", nullable=true)
     * @Get("/syntheses/{id}/elements")
     * @View(serializerGroups={"ElementDetails", "UserDetails"})
     */
    public function getSynthesisElementsAction(ParamFetcherInterface $paramFetcher, Synthesis $synthesis)
    {
        $type = $paramFetcher->get('type');
        if ($type !== 'archived' && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return $this->get('capco.synthesis.synthesis_element_handler')->getElementsFromSynthesisByType($synthesis, $type);
    }

    /**
     * Count synthesis elements filtered by type.
     *
     * @return array|\Capco\AppBundle\Entity\Synthesis\SynthesisElement[]
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Count the elements of a synthesis, filtered by type",
     *  statusCodes={
     *    200 = "Success",
    *     404 = "Synthesis not found",
     *  }
     * )
     *
     * @ParamConverter("synthesis", options={"mapping": {"id": "id"}})
     * @QueryParam(name="type", nullable=true)
     * @Get("/syntheses/{id}/elements/count")
     * @View()
     */
    public function countSynthesisElementsAction(ParamFetcherInterface $paramFetcher, Synthesis $synthesis)
    {
        $type = $paramFetcher->get('type');
        if ($type !== 'archived' && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        return ['count' => $this->get('capco.synthesis.synthesis_element_handler')->countElementsFromSynthesisByType($synthesis, $type)];
    }

    /**
     * Get a synthesis element by id.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get the synthesis element",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Synthesis element does not exist",
     *  }
     * )
     *
     * @Get("/syntheses/{synthesis_id}/elements/{element_id}")
     * @ParamConverter("synthesis", options={"mapping": {"synthesis_id": "id"}})
     * @ParamConverter("element", options={"mapping": {"element_id": "id"}})
     * @View(serializerGroups={"ElementDetails", "UserDetails"})
     */
    public function getSynthesisElementAction(Synthesis $synthesis, SynthesisElement $element)
    {
        return $element;
    }

    /**
     * Create a synthesis element.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Create a synthesis element",
     *  statusCodes={
     *    201 = "Returned when successful",
     *    400 = "Returned when creation fail",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Post("/syntheses/{id}/elements")
     * @ParamConverter("synthesis", options={"mapping": {"id": "id"}})
     * @ParamConverter("element", converter="fos_rest.request_body")
     */
    public function createSynthesisElementAction(Synthesis $synthesis, SynthesisElement $element, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new BadRequestHttpException($validationErrors->__toString());
        }

        $element = $this->get('capco.synthesis.synthesis_element_handler')->createElementInSynthesis($element, $synthesis);

        $view = $this->view($element, Codes::HTTP_CREATED);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('ElementDetails', 'UserDetails')));
        $url = $this->generateUrl('get_synthesis_element', ['synthesis_id' => $synthesis->getId(), 'element_id' => $element->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $view->setHeader('Location', $url);

        return $view;
    }

    /**
     * Update a synthesis element.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Update a synthesis element",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    400 = "Returned when update fail",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Put("/syntheses/{synthesis_id}/elements/{element_id}")
     * @ParamConverter("synthesis", options={"mapping": {"synthesis_id": "id"}})
     * @ParamConverter("element", options={"mapping": {"element_id": "id"}})
     * @View(serializerGroups={"ElementDetails", "UserDetails"})
     */
    public function updateSynthesisElementAction(Request $request, Synthesis $synthesis, SynthesisElement $element)
    {
        $form = $this->createForm(new SynthesisElementForm(), $element);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $element = $this->get('capco.synthesis.synthesis_element_handler')->updateElementInSynthesis($element, $synthesis);

            return $element;
        }

        return $form;
    }

    /**
     * Divide a synthesis element.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Divide a synthesis element",
     *  statusCodes={
     *    201 = "Returned when successful",
     *    400 = "Returned when division fail",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Post("/syntheses/{synthesis_id}/elements/{element_id}/divisions")
     * @ParamConverter("synthesis", options={"mapping": {"synthesis_id": "id"}})
     * @ParamConverter("element", options={"mapping": {"element_id": "id"}})
     * @ParamConverter("division", converter="fos_rest.request_body")
     * @View(serializerGroups={"SynthesisDetails", "Elements"})
     */
    public function divideSynthesisElementAction(Request $request, Synthesis $synthesis, SynthesisElement $element, SynthesisDivision $division)
    {
        $form = $this->createForm(new SynthesisDivisionForm(), $division);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $division = $this->get('capco.synthesis.synthesis_element_handler')->createDivisionFromElementInSynthesis($division, $element, $synthesis);

            $url = $this->generateUrl('get_synthesis', ['id' => $synthesis->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return $this->redirectView($url, Codes::HTTP_CREATED);
        }

        return $form;
    }

    /**
     * Get history of a synthesis element.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get history of a synthesis element",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Returned when element is not found",
     *  }
     * )
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Get("/syntheses/{synthesis_id}/elements/{element_id}/history")
     * @ParamConverter("synthesis", options={"mapping": {"synthesis_id": "id"}})
     * @ParamConverter("element", options={"mapping": {"element_id": "id"}})
     * @View(serializerGroups={"Elements", "LogDetails"})
     */
    public function getSynthesisElementHistoryAction(Request $request, Synthesis $synthesis, SynthesisElement $element)
    {
        $logs = $this->get('capco.synthesis.synthesis_element_handler')->getLogsForElement($element);

        return $logs;
    }
}
