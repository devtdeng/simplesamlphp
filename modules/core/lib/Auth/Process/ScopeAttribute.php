<?php
/**
 * Add a scoped variant of an attribute.
 *
 * @package SimpleSAMLphp
 */
class sspmod_core_Auth_Process_ScopeAttribute extends SimpleSAML_Auth_ProcessingFilter
{
    /**
     * The attribute we extract the scope from.
     *
     * @var string
     */
    private $scopeAttribute;

    /**
     * The attribute we want to add scope to.
     *
     * @var string
     */
    private $sourceAttribute;

    /**
     * The attribute we want to add the scoped attributes to.
     *
     * @var string
     */
    private $targetAttribute;

    /**
     * Only modify targetAttribute if it doesn't already exist.
     *
     * @var bool
     */
    private $onlyIfEmpty = false;

    /**
     * Initialize this filter, parse configuration
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct(array $config, $reserved)
    {
        parent::__construct($config, $reserved);

        $config = SimpleSAML_Configuration::loadFromArray($config, 'ScopeAttribute');

        $this->scopeAttribute = $config->getString('scopeAttribute');
        $this->sourceAttribute = $config->getString('sourceAttribute');
        $this->targetAttribute = $config->getString('targetAttribute');
        $this->onlyIfEmpty = $config->getBoolean('onlyIfEmpty', false);
    }

    /**
     * Apply this filter to the request.
     *
     * @param array &$request  The current request
     */
    public function process(array &$request)
    {
        assert(array_key_exists('Attributes', $request));

        $attributes =& $request['Attributes'];

        if (!isset($attributes[$this->scopeAttribute])) {
            return;
        }

        if (!isset($attributes[$this->sourceAttribute])) {
            return;
        }

        if (!isset($attributes[$this->targetAttribute])) {
            $attributes[$this->targetAttribute] = array();
        }

        if ($this->onlyIfEmpty && count($attributes[$this->targetAttribute]) > 0) {
            return;
        }

        foreach ($attributes[$this->scopeAttribute] as $scope) {
            if (strpos($scope, '@') !== false) {
                $scope = explode('@', $scope, 2);
                $scope = $scope[1];
            }

            foreach ($attributes[$this->sourceAttribute] as $value) {
                $value = $value . '@' . $scope;

                if (in_array($value, $attributes[$this->targetAttribute], true)) {
                    // Already present
                    continue;
                }

                $attributes[$this->targetAttribute][] = $value;
            }
        }
    }
}
