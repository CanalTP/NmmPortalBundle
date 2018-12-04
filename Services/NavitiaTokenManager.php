<?php

namespace CanalTP\NmmPortalBundle\Services;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CanalTP\SamCoreBundle\Entity\Customer;
use CanalTP\SamCoreBundle\Entity\Application;
use CanalTP\SamCoreBundle\Entity\CustomerApplication;

/**
 * Create navitia user, navitia token and add permission on token
 */
class NavitiaTokenManager
{
    private $tyrUrl = '';
    private $version = 'v0';
    private $timeout = 5000;
    private $instances = array();
    private $user = null;
    protected $samNavitia = null;

    const STATE_UPDATED = 2;
    const STATE_CREATED = 1;
    const STATE_FAILED = 0;

    public function __construct($tyrUrl, $samNavitia)
    {
        $this->tyrUrl = $tyrUrl;
        $this->samNavitia = $samNavitia;
    }

    /**
     * Make calls to WS
     *
     * @param string $url
     * @param string $method
     * @return boolean if no result or string if succes
     * @throws \Exception if navitia call fail
     */
    private function call($url, $method = 'GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        //Timeout in 5s
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
        $response = curl_exec($ch);
        $errorMsg = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) {
            $response = json_decode($response);
            if (isset($response->error)) {
                $errorMsg .= '(' . $response->error . ')';
            }

            throw new \Exception($httpCode . ' : ' . $errorMsg);
        }

        if ($response === false) {
            return false;
        }

        return $response;
    }

    /**
     * Get instances
     * http://tyr.dev.canaltp.fr/v0/instances/
     *
     * @return array
     */
    private function getInstances()
    {
        $response = $this->call($this->tyrUrl . '/' . $this->version . '/instances/');

        $instancesN = json_decode($response);
        if (is_array($instancesN)) {
            $instances = array();
            foreach ($instancesN as $instance) {
                $instances[$instance->id] = $instance->name;
            }
        }

        return $instances;
    }

    /**
     * Get navitia user detail (with keys and authorizations)
     * http://tyr.dev.canaltp.fr/v0/users/26/
     * {
     *      keys: [
     *         {
     *              valid_until: null,
     *              token: '',
     *              id:
     *         }
     *      ],
     *      login: '',
     *      authorizations: [
     *          {
     *              instance: {
     *              id: ,
     *              is_free: ,
     *              name: ''
     *          },
     *          api: {
     *              id: ,
     *              name: ''
     *          }
     *      }
     *      ],
     *      id: ,
     *      email: ''
     *}
     *
     * @param int $navitiaUserid
     * @return StdClass
     */
    private function getUser($navitiaUserid)
    {
        $response = $this->call($this->tyrUrl . '/' . $this->version . '/users/' . $navitiaUserid . '/');

        return json_decode($response);
    }

    /**
     * Get navitia user by email
     * {
     *      login: ,
     *      id: ,
     *      email:
     * }
     *
     * @param string $email
     * @return StdClass
     */
    private function findUser($email)
    {
        $response = $this->call($this->tyrUrl . '/' . $this->version . '/users/?email=' . $email);

        $user = json_decode($response);
        if (!empty($user)) {
            return $user[0];
        }

        return false;
    }

    /**
     * Create a new token
     * POST /v0/users/$USERID/keys?app_name=applicationName
     *
     * @return String
     */
    public function generateToken($appName)
    {
        $response = $this->call($this->tyrUrl . '/' . $this->version . '/users/' . $this->user->id . '/keys?app_name=' . $appName, 'POST');
        $response = json_decode($response);
        $newKey = end($response->keys);

        return $newKey->token;
    }

    private function findToken($token)
    {
        $response = $this->call($this->tyrUrl . '/' . $this->version . '/users/' . $this->user->id . '/keys/', 'GET');
        $tokenObjects = json_decode($response);
        $tokenId = null;

        foreach ($tokenObjects as $tokenObject) {
            if ($tokenObject->token == $token) {
                return ($tokenObject);
            }
        }
        return (null);
    }

    /**
     * Delete token
     * POST /v0/users/$USERID/keys/$KEYID
     *
     * @return String
     */
    public function deleteToken($token)
    {
        $tokenObject = $this->findToken($token);

        if ($tokenObject == null) {
            return (false);
        }
        $this->call($this->tyrUrl . '/' . $this->version . '/users/' . $this->user->id . '/keys/' . $tokenObject->id .'/', 'DELETE');
        return true;
    }

    private function createUser($email, $username)
    {
        $response = $this->call($this->tyrUrl . '/' . $this->version . '/users/?email=' . $email . '&login=' . $username, 'POST');
        $response = json_decode($response);

        return $response;
    }

    /**
     * From perimeters names, find navitia instances ids for add permissions
     *
     * @param array $coverages
     */
    private function getInstancesIdsFromExternalsCoveragesIds(\Traversable $perimeters)
    {
        $userPerimeterNavitiaIds = array();
        $instances = $this->getInstances();

        foreach ($perimeters as $perimeter) {
            $userPerimeterNavitiaId = array_search($perimeter->getExternalCoverageId(), $instances);
            if ($userPerimeterNavitiaId) {
                $userPerimeterNavitiaIds[] = array_search($perimeter->getExternalCoverageId(), $instances);
            } else {
                throw new NotFoundHttpException("Coverage not found: " . $perimeter->getExternalCoverageId() . " in tyr api (" . $this->tyrUrl . ") ");
            }
        }
        return $userPerimeterNavitiaIds;
    }

    /**
     * Return meta api 'ALL' id
     *
     * @return type
     */
    private function getNavitiaApiAllId()
    {
        $response = $this->call($this->tyrUrl . '/' . $this->version . '/api/');
        $apis = json_decode($response);

        foreach ($apis as $api) {
            if ($api->name == 'ALL') {
                return $api;
            }
        }
    }

    /**
     * Add permissions to navitia user
     *
     * @param int $userId
     * @param int $instanceId
     * @return boolean
     */
    private function createAuthorization($userId, $instanceId)
    {
        $apiAll = $this->getNavitiaApiAllId();
        $this->call($this->tyrUrl . '/' . $this->version . '/users/' . $userId . '/authorizations/?api_id=' . $apiAll->id . '&instance_id=' . $instanceId, 'POST');

        return true;
    }

    /**
     * Compare new (from NMM) and old (in Navitia) authorizations and keep it up to date
     *
     * @param int $navitiaUserId
     * @param array $userCoverageNavitiaIds
     */
    private function updateAuthorizations($userPerimeterNavitiaIds)
    {
        $detailUser = $this->getUser($this->user->id);

        $authorizationsInstanceIds = array();
        foreach ($detailUser->authorizations as $authorization) {
            $authorizationsInstanceIds[] = $authorization->instance->id;
        }

        // Compare nmm user's coverages and navitia user's coverages
        // Add authorization in navitia if delta
        foreach ($userPerimeterNavitiaIds as $coverageNavitiaId) {
            if (!in_array($coverageNavitiaId, $authorizationsInstanceIds)) {
                $this->createAuthorization($this->user->id, $coverageNavitiaId);
            }
        }

        // Compare navitia user's coverages and nmm user's coverages
        // Remove authorization in navitia if delta
        // @TODO Not possible to remove authorization in navita for the moment
        // foreach ($authorizationsInstanceIds as $authorizationInstanceId) {
        //     if (!in_array($authorizationInstanceId, $userCoverageNavitiaIds)) {
        //         $this->removeAuthorization($this->user->id, $authorizationInstanceId);
        //     }
        // }
    }

    /**
     *
     *
     * @param type $customer
     * @throws \Exception
     */
    public function initUser($username, $email)
    {
        try {
            $this->user = $this->findUser($email);
        } catch (\Exception $e) {
            throw new \Exception('Navitia error : ' . $e->getMessage());
        }

        if (!$this->user) {
            $this->user = $this->createUser($email, $username);
        }
    }

    public function initInstanceAndAuthorizations($perimeters)
    {
        $this->updateAuthorizations(
            $this->getInstancesIdsFromExternalsCoveragesIds($perimeters)
        );
    }

    public function checkAllowedToNetworkAction($externalCoverageId, $externalNetworkId, $token)
    {
        $response = false;

        $this->samNavitia->setToken($token);
        try {
            $networks = $this->samNavitia->getNetWorks($externalCoverageId);
        } catch (\Navitia\Component\Exception\NavitiaException $e) {
            return $response;
        }

        if (!is_null($externalNetworkId) && isset($networks[$externalNetworkId])) {
            $response = true;
        }

        return true;
    }
}
