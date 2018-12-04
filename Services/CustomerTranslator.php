<?php

namespace CanalTP\NmmPortalBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

/**
 * CustomerTranslator.(Overload Symfony\Component\Translation\Translator)
 */
class CustomerTranslator extends Translator implements TranslatorInterface, TranslatorBagInterface
{
    private $customerId = null;

    private function initCustomerDomain($id, $locale, $domain = null)
    {
        $token = $this->container->get('security.context')->getToken();

        if ($token instanceof TokenInterface && $token->getUser() instanceof User) {
            if ($this->customerId === null && $token->getUser() != 'anon.') {
                $this->customerId = $token->getUser()->getCustomer()->getIdentifier();
            }
        }
        
        if ($domain === null && $this->customerId !== null && $this->catalogues[$locale]->has((string) $id, $this->customerId)) {
            $domain = $this->customerId;
        }
        
        return ($domain);
    }

    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        } else {
            $this->assertValidLocale($locale);
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        $domain = $this->initCustomerDomain($id, $locale, $domain);
        if ($domain === null) {
            $domain = 'messages';
        }

        return strtr($this->catalogues[$locale]->get((string) $id, $domain), $parameters);
    }
}
