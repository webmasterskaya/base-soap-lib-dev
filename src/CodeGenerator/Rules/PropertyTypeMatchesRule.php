<?php

namespace Webmasterskaya\Soap\Base\Dev\CodeGenerator\Rules;

use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\PropertyContext;
use Phpro\SoapClient\CodeGenerator\Rules\RuleInterface;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;

class PropertyTypeMatchesRule implements RuleInterface
{
    /**
     * @var string
     */
    private $regex;

    /**
     * @var RuleInterface
     */
    private $subRule;

    public function __construct(RuleInterface $subRule, string $regex)
    {
        $this->subRule = $subRule;
        $this->regex = $regex;
    }
    public function appliesToContext(ContextInterface $context): bool
    {
        if (!$context instanceof PropertyContext) {
            return false;
        }

        $property = $context->getProperty();
        if (!preg_match($this->regex, Normalizer::getClassNameFromFQN($property->getType()))) {
            return false;
        }

        return $this->subRule->appliesToContext($context);
    }

    public function apply(ContextInterface $context)
    {
        $this->subRule->apply($context);
    }
}
