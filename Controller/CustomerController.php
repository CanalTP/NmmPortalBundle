<?php

namespace CanalTP\NmmPortalBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Criteria;
use CanalTP\NmmPortalBundle\Entity\Customer as CustomerEntity;
use CanalTP\NmmPortalBundle\Form\Type\CustomerType;
use CanalTP\SamCoreBundle\Event\SamCoreEvents;
use CanalTP\NmmPortalBundle\Event\CustomerEvent;
use CanalTP\SamCoreBundle\Exception\CustomerEventException;

/**
 * Description of CustomerController
 *
 * @author Kévin ZIEMIANSKI <kevin.ziemianski@canaltp.fr>
 */
class CustomerController extends \CanalTP\SamCoreBundle\Controller\AbstractController
{
    public function listAction()
    {
        $this->isGranted('BUSINESS_MANAGE_CLIENT');

        $customers = $this->getDoctrine()
            ->getManager()
            ->getRepository('CanalTPNmmPortalBundle:Customer')
            ->findAll();

        return $this->render(
            'CanalTPSamCoreBundle:Customer:list.html.twig',
            array(
                'customers' => $customers
            )
        );
    }

    public function showAction($id)
    {
        $customer = $this->getCustomerById($id);

        return $this->render('CanalTPSamCoreBundle:Customer:show.html.twig', ['customer' => $customer]);
    }

    /**
     * Remove a client
     *
     * @param integer $id customer ID
     *
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        $response = new JsonResponse();
        $errorMsgPrefix = $this->get('translator')->trans('customer.delete.error') . ' : ';

        try {
            $notGrantedMsg = $this->get('translator')->trans('customer.delete.not_allowed');

            $customer = $this->getCustomerById($id, $notGrantedMsg);
            $customerName=$customer->getName();

            $statusCode = Response::HTTP_OK;
            $message = $this->get('translator')->trans('customer.delete.success', ['name' => $customerName]);
        } catch (AccessDeniedException $e) {
            $statusCode = Response::HTTP_FORBIDDEN;
            $message = $errorMsgPrefix . $e->getMessage();
        } catch (NotFoundHttpException $e) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $message = $errorMsgPrefix . $e->getMessage();
        } catch (\Exception $e) {
            $statusCode =  Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = $errorMsgPrefix . $e->getMessage();
        }

        $response->setData(['message' => $message]);
        $response->setStatusCode($statusCode);

        return $response;
    }

    private function dispatchEvent($form, $type)
    {
        $event = new CustomerEvent($form->getData());
        try {
            $this->get('event_dispatcher')->dispatch($type, $event);
        } catch (CustomerEventException $e) {
            $this->addFlashMessage('danger', $e->getMessage());
            return (false);
        }
        return (true);
    }

    public function editAction(Request $request, CustomerEntity $customer = null)
    {
        $this->isGranted(array('BUSINESS_MANAGE_CLIENT', 'BUSINESS_CREATE_CLIENT'));

        $coverage = $this->get('sam_navitia')->getCoverages();
        $form = $this->createForm(
            new CustomerType(
                $this->getDoctrine()->getManager(),
                $coverage->regions,
                $this->get('sam_navitia'),
                $this->get('sam_core.customer.application.transformer'),
                $this->get('nmm.customer.application.transformer_with_token'),
                ($this->get('service_container')->getParameter('nmm.tyr.url') != null)
            ),
            $customer
        );

        $form->handleRequest($request);
        if ($form->isValid() && $this->dispatchEvent($form, SamCoreEvents::EDIT_CLIENT)) {
            $this->get('sam_core.customer')->save($form->getData());
            $this->addFlashMessage('success', 'customer.flash.edit.success');

            return $this->redirect($this->generateUrl('sam_customer_list'));
        }

        return $this->render(
            'CanalTPNmmPortalBundle:Customer:form.html.twig',
            array(
                'title' => 'customer.edit.title',
                'logoPath' => $customer->getWebLogoPath(),
                'form' => $form->createView()
            )
        );
    }

    public function newAction(Request $request)
    {
        $this->isGranted('BUSINESS_CREATE_CLIENT');

        $coverage = $this->get('sam_navitia')->getCoverages();
        $form = $this->createForm(
            new CustomerType(
                $this->getDoctrine()->getManager(),
                $coverage->regions,
                $this->get('sam_navitia'),
                $this->get('sam_core.customer.application.transformer'),
                $this->get('nmm.customer.application.transformer_with_token'),
                ($this->get('service_container')->getParameter('nmm.tyr.url') != null)
            )
        );

        $form->handleRequest($request);
        if ($form->isValid() && $this->dispatchEvent($form, SamCoreEvents::CREATE_CLIENT)) {
            $this->get('sam_core.customer')->save($form->getData());
            $this->addFlashMessage('success', 'customer.flash.creation.success');

            return $this->redirect($this->generateUrl('sam_customer_list'));
        }

        return $this->render(
            'CanalTPSamCoreBundle:Customer:form.html.twig',
            array(
                'logoPath' => null,
                'title' => 'customer.new.title',
                'form' => $form->createView()
            )
        );
    }

    // TODO: Duplicate in CanalTPMttBundle:Network (controller)
    public function byCoverageAction($externalCoverageId)
    {
        $response = new JsonResponse();
        $navitia = $this->get('sam_navitia');
        $nmmToken = $this->get('service_container')->getParameter('nmm.navitia.token');
        $status = Response::HTTP_FORBIDDEN;

        $navitia->setToken($nmmToken);
        try {
            $networks = $navitia->getNetworks($externalCoverageId);
            asort($networks);
        } catch(\Navitia\Component\Exception\NavitiaException $e) {
            $response->setData(array('status' => $status));
            $response->setStatusCode($status);

            return $response;
        }

        $status = Response::HTTP_OK;
        $response->setData(
            array(
                'status' => $status,
                'networks' => $networks
            )
        );
        $response->setStatusCode($status);

        return $response;
    }

    public function checkAllowedToNetworkAction($externalCoverageId, $externalNetworkId, $token)
    {
        return;

        $response = new JsonResponse();
        $navitia = $this->get('sam_navitia');
        $status = Response::HTTP_FORBIDDEN;

        $navitia->setToken($token);
        try {
            $networks = $navitia->getNetworks($externalCoverageId);
        } catch(\Navitia\Component\Exception\NavitiaException $e) {
            $response->setData(array('status' => $status));
            $response->setStatusCode($status);

            return $response;
        }

        if (isset($networks[$externalNetworkId])) {
            $status = Response::HTTP_OK;
        }

        $response->setData(array('status' => $status));
        $response->setStatusCode($status);

        return $response;
    }

    public function listTokensAction(CustomerEntity $customer)
    {

        $criteriaOrder = Criteria::create()
            ->orderBy(array('created' => Criteria::DESC));

        return $this->render(
            'CanalTPNmmPortalBundle:Customer:listToken.html.twig',
            array(
                'applicationsTokens' => $customer->getApplications()->matching($criteriaOrder),
                'perimeters' => $customer->getNavitiaEntity()->getPerimeters(),
                'customerId' => $customer->getId(),
                'withTyr' => ($this->get('service_container')->getParameter('nmm.tyr.url') != null)
            )
        );
    }

    public function regenerateTokensAction(CustomerEntity $customer)
    {
        $custoService = $this->get('sam_core.customer');

        //keep all applications
        $applications = $custoService->getApplications($customer);

        //disable all tokens
        $custoService->disableTokens($customer);

        //generate new token
        $custoService->initTokenManager($customer->getNameCanonical(), $customer->getEmailCanonical(), $customer->getNavitiaEntity()->getPerimeters());
        $custoService->generateTokens($customer, $applications);

        return $this->redirect($this->generateUrl('sam_customer_listtokens', array('id' => $customer->getId())));
    }

    public function regenerateTokenAction(CustomerEntity $customer, $appId)
    {
        $custoService = $this->get('sam_core.customer');

        $application = $this->getDoctrine()
            ->getManager()
            ->getRepository('CanalTPSamCoreBundle:Application')->find($appId);

        //disable tokens for application
        $custoService->disableTokens($customer, $application);

        //generate new token
        $custoService->initTokenManager($customer->getNameCanonical(), $customer->getEmailCanonical(), $customer->getNavitiaEntity()->getPerimeters());
        $custoService->generateToken($customer, $application);

        return $this->redirect($this->generateUrl('sam_customer_listtokens', array('id' => $customer->getId())));
    }

    /**
     * Retrieves customer by ID
     *
     * @param integer $id customerId
     * @param string $notGrantedMsg exception message if not granted
     * @return CustomerEntity
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    private function getCustomerById($id, $notGrantedMsg = '')
    {
        $this->isGranted('BUSINESS_MANAGE_CLIENT', null, $notGrantedMsg);

        $customer = $this->getDoctrine()
            ->getManager()
            ->getRepository('CanalTPNmmPortalBundle:Customer')
            ->find($id);

        if (!$customer instanceof CustomerEntity) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('customer.delete.could_not_be_found')
            );
        }

        return $customer;
    }
}
