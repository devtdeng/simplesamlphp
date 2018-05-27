<?php
/**
 * Log a line in the STAT log with one attribute.
 *
 * @package SimpleSAMLphp
 */
class sspmod_core_Auth_Process_StatisticsWithAttribute extends SimpleSAML_Auth_ProcessingFilter
{
    /**
     * The attribute to log
     * @var string|null
     */
    private $attribute = null;

    /**
     * @var string
     */
    private $typeTag = 'saml20-idp-SSO';

    /**
     * @var bool
     */
    private $skipPassive = false;


    /**
     * Initialize this filter.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct(array $config, $reserved)
    {
        parent::__construct($config, $reserved);

        if (array_key_exists('attributename', $config)) {
            $this->attribute = $config['attributename'];
            if (!is_string($this->attribute)) {
                throw new Exception('Invalid attribute name given to core:StatisticsWithAttribute filter.');
            }
        }

        if (array_key_exists('type', $config)) {
            $this->typeTag = $config['type'];
            if (!is_string($this->typeTag)) {
                throw new Exception('Invalid typeTag given to core:StatisticsWithAttribute filter.');
            }
        }

        if (array_key_exists('skipPassive', $config)) {
            $this->skipPassive = (bool) $config['skipPassive'];
        }
    }

    /**
     * Log line.
     *
     * @param array &$state  The current state.
     */
    public function process(array &$state)
    {
        assert(array_key_exists('Attributes', $state));

        $logAttribute = 'NA';
        $isPassive = '';

        if (array_key_exists('isPassive', $state) && $state['isPassive'] === true) {
            if ($this->skipPassive === true) {
                // We have a passive request. Skip logging statistics
                return;
            }
            $isPassive = 'passive-';
        }

        if (array_key_exists($this->attribute, $state['Attributes'])) {
            $logAttribute = $state['Attributes'][$this->attribute][0];
        }

        $source = $this->setIdentifier('Source', $state);
        $dest = $this->setIdentifier('Destination', $state);

        if (!array_key_exists('PreviousSSOTimestamp', $state)) {
            // The user hasn't authenticated with this SP earlier in this session
            SimpleSAML\Logger::stats($isPassive.$this->typeTag.'-first '.$dest.' '.$source.' '. $logAttribute);
        }

        SimpleSAML\Logger::stats($isPassive.$this->typeTag.' '.$dest.' '.$source.' '.$logAttribute);
    }

    /**
     * @param string &$direction  Either 'Source' or 'Destination'.
     * @param array $state  The current state.
     *
     * @return string
     */
    private function setIdentifier($direction, array $state)
    {
        if (array_key_exists($direction, $state)) {
            if (isset($state[$direction]['core:statistics-id'])) {
                return $state[$direction]['core:statistics-id'];
            } else {
                return $state[$direction]['entityid'];
            }
        }
        return 'NA';
    }
}
