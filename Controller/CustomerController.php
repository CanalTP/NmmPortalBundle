<?php

namespace CanalTP\NmmPortalBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Criteria;
use CanalTP\NmmPortalBundle\Entity\Customer as CustomerEntity;
use CanalTP\SamCoreBundle\Entity\Application as ApplicationEntity;
use CanalTP\NmmPortalBundle\Entity\Perimeter;
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
        $this->isGranted('BUSINESS_MANAGE_CLIENT');

        $customer = $this->getDoctrine()
            ->getManager()
            ->getRepository('CanalTPNmmPortalBundle:Customer')
            ->find($id);

        if (is_null($customer)) {
            throw $this->createNotFoundException();
        }
        return $this->render(
            'CanalTPSamCoreBundle:Customer:show.html.twig',
            array(
                'customer' => $customer
            )
        );
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
        } catch (\Navitia\Component\Exception\NavitiaException $e) {
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
        } catch (\Navitia\Component\Exception\NavitiaException $e) {
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
}
