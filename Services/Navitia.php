<?php

namespace CanalTP\NmmPortalBundle\Services;

class Navitia
{
    protected $navitia_component;

    public function __construct($navitia)
    {
        $this->navitia_component = $navitia;
    }

    /**
     * Set token.
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $config = $this->navitia_component->getConfiguration();
        $config['token'] = $token;
        $this->navitia_component->setConfiguration($config);
    }

    /**
     * Retourn les lignes navitia rattachées à une région et un réseau.
     *
     * @param string   $externalCoverageId
     * @param string   $networkId
     * @param int      $depth
     * @param int|null $count
     *
     * @return array
     */
    public function getLines($externalCoverageId, $networkId, $depth = 0, $count = null)
    {
        $parameters = '?depth='.$depth;
        $parameters .= ($count !== null) ? '&count='.$count : '';
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => 'lines',
                'path_filter' => 'networks/'.$networkId,
                'parameters' => $parameters,
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Return Line by $coverageId $networkId $lineId.
     *
     * @param string $coverageId
     * @param string $networkId
     * @param string $lineId
     * @param int    $depth
     *
     * @return stdClass
     */
    public function getLine($coverageId, $networkId, $lineId, $depth = 0)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'action' => 'lines',
                'path_filter' => 'networks/'.$networkId.'/lines/'.$lineId,
                'parameters' => '?depth='.$depth,
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Retourne les networks navitia rattachés à une région (sim).
     *
     * @param string $externalCoverageId
     * @param array  $params             Parameters (like count)
     *
     * @return array
     */
    private function getNavitiaNetwork($externalCoverageId, array $params = array())
    {
        // Récupération des réseaux selectionnables pour la sim depuis navitia
        $aQueryParameters = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $externalCoverageId,
                'action' => 'networks',
            ),
        );

        if (!empty($params)) {
            $aQueryParameters['parameters']['parameters'] = '?'.http_build_query($params);
        }

        return $this->navitia_component->call($aQueryParameters);
    }

    /**
     * Récupération des réseaux sélectionnables depuis naviatia.
     *
     * @param string $externalCoverageId
     * @param array  $params             Parameters (like count)
     *
     * @return array
     */
    public function getNetWorks($externalCoverageId, array $params = array())
    {
        if (!isset($params['count'])) {
            $params['count'] = '1000';
        }

        $oRegionNetworks = $this->getNavitiaNetwork($externalCoverageId, $params);
        $aRegionNetwork = array();
        foreach ($oRegionNetworks->networks as $oNetwork) {
            $aRegionNetwork[$oNetwork->id] = $oNetwork->name;
        }

        return $aRegionNetwork;
    }

    /**
     * Récupération des labels des réseaux sélectionnables depuis naviatia.
     *
     * @param string $externalCoverageId
     * @param array  $network
     *
     * @return array
     */
    public function getNetworkWithLabel($externalCoverageId, $network)
    {
        $oRegionNetworks = $this->getNavitiaNetwork($externalCoverageId);
        $aRegionNetwork = array();

        if ($network != null) {
            foreach ($oRegionNetworks->networks as $oNetwork) {
                if (in_array($oNetwork->id, $network)) {
                    $aRegionNetwork[$oNetwork->id] = $oNetwork->name;
                }
            }
        }

        return $aRegionNetwork;
    }

    /**
     * Retourne les stop points.
     *
     * @param string $coverageId
     * @param string $networkId
     * @param string $lineId
     * @param string $routeId
     *
     * @return array
     */
    public function getStopPoints($coverageId, $networkId, $lineId, $routeId)
    {
        $pathFilter = 'networks/'.$networkId.'/lines/'.$lineId.'/routes/'.$routeId;

        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'action' => 'route_schedules',
                'path_filter' => $pathFilter,
                'parameters' => '?depth=0',
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Get one Stop Point.
     *
     * @param string $coverageId
     * @param string $stopPointId
     *
     * @return stdClass
     */
    public function getStopPoint($coverageId, $stopPointId)
    {
        $pathFilter = 'stop_points/'.$stopPointId;

        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'path_filter' => $pathFilter,
                'parameters' => '?depth=0',
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns modes (commercial|physical) used on a network.
     *
     * @param string $coverageId
     * @param string $networkId
     * @param bool   $commercial if true commercial_modes returned, else physical_modes
     *
     * @return array
     */
    public function getNetworkModes($coverageId, $networkId, $commercial = true)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'action' => $commercial ? 'commercial_modes' : 'physical_modes',
                'path_filter' => 'networks/'.$networkId,
            ),
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Returns Basic Route data with corresponding line data.
     *
     * @param string $coverageId
     * @param string $routeId
     * @param int    $depth
     *
     * @return stdClass
     */
    public function getRoute($coverageId, $routeId, $depth = 1)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region'      => $coverageId,
                'path_filter' => 'routes/'.$routeId,
                'parameters'  => '?depth='.$depth
            )
        );

        return $this->navitia_component->call($query);
    }

    /**
     * Get Navitia Component.
     *
     * @return ServiceFacade Navitia Component Facade
     */
    public function getNavitiaComponent()
    {
        return $this->navitia_component;
    }

    /**
     * Returns coverages.
     *
     * @return array
     */
    public function getCoverages()
    {
        $query = array('api' => 'coverage');

        return $this->navitia_component->call($query);
    }

    /**
     * Gets status by coverage id.
     *
     * @param string $coverageId Navitia Coverage Id
     *
     * @return array
     */
    public function getStatusByCoverageId($coverageId)
    {
        $query = array(
            'api' => 'coverage',
            'parameters' => array(
                'region' => $coverageId,
                'path_filter' => 'status',
            ),
        );

        return $this->navitia_component->call($query);
    }
}
