<?php

/**
 * The main CreateEventCalendar service class.
 *
 * @package createeventcalendar
 */
class CreateEventCalendar
{
    public $modx = null;
    public $namespace = 'createeventcalendar';
    public $cache = null;
    public $options = array();

    public function __construct(modX &$modx, array $options = array())
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, 'createeventcalendar');

        $corePath = $this->getOption(
            'core_path',
            $options,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/createeventcalendar/'
        );
        $assetsPath = $this->getOption(
            'assets_path',
            $options,
            $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/createeventcalendar/'
        );
        $assetsUrl = $this->getOption(
            'assets_url',
            $options,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/createeventcalendar/'
        );

        /* loads some default paths for easier management */
        $this->options = array_merge(array(
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ), $options);

        $this->modx->addPackage('createeventcalendar', $this->getOption('modelPath'));
        $this->modx->lexicon->load('createeventcalendar:default');
    }

    /*
     * Sanitize and set filename.
     */
    public function setFilename($name)
    {
        $name = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", html_entity_decode($name, ENT_QUOTES)));
        $name = $name . ".ics";

        return $name;
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = array(), $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    /**
     * Geocode location and return imploded coordinates.
     *
     * @param $location
     *
     * @return string
     */
    public function getCoordinatesFromLocation($location)
    {
        $coordinates       = '';
        $reverseGeocodeUrl = strip_tags(
            'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($location)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $reverseGeocodeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = json_decode(curl_exec($ch), true);
        if ($response['status'] == 'OK') {
            $geoCodeResults = array(
                'street'      => $response['results'][0]['address_components'][1]['long_name'],
                'housenumber' => $response['results'][0]['address_components'][0]['long_name'],
                'zipcode'     => $response['results'][0]['address_components'][6]['long_name'],
                'city'        => $response['results'][0]['address_components'][2]['long_name'],
                'state'       => $response['results'][0]['address_components'][4]['long_name'],
                'country'     => $response['results'][0]['address_components'][5]['long_name'],
                'latitude'    => str_replace(",", ".", $response['results'][0]['geometry']['location']['lat']),
                'longitude'   => str_replace(",", ".", $response['results'][0]['geometry']['location']['lng'])
            );

            $coordinates = $geoCodeResults['latitude'].','.$geoCodeResults['longitude'];
        }

        return $coordinates;
    }

    /**
     * Format timestamp to date format.
     *
     * @param $timestamp
     *
     * @return bool|string
     */
    public function dateToCal($timestamp)
    {
        return date('Ymd\THis\Z', $timestamp);
    }
}
