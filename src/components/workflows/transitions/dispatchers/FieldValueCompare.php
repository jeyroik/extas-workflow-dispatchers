<?php
namespace extas\components\workflows\transitions\dispatchers;

use extas\components\conditions\ConditionParameter;
use extas\components\errors\Error;
use extas\interfaces\workflows\entities\IEntity;
use extas\interfaces\workflows\transitions\dispatchers\ITransitionDispatcherExecutor;
use extas\interfaces\workflows\transits\ITransitResult;


/**
 * Class ConditionFieldValueCompare
 *
 * @package extas\components\plugins\workflows\conditions
 * @author jeyroik@gmail.com
 */
class FieldValueCompare extends TransitionDispatcherExecutor implements ITransitionDispatcherExecutor
{
    public const TYPE__STRING = 'string';
    public const TYPE__NUMBER = 'number';

    /**
     * @param ITransitResult $result
     * @param IEntity $entityEdited
     * @return bool
     * @throws
     */
    public function __invoke(ITransitResult &$result, IEntity &$entityEdited): bool
    {
        $fieldName = $this->getParameterValue('field_name');
        $fieldValue = $this->getParameterValue('field_value');
        $fieldCompare = $this->getParameterValue('field_compare');

        $entityValue = isset($entityEdited[$fieldName])
            ? $entityEdited[$fieldName->getValue()]
            : null;

        $withCondition = new ConditionParameter([
            ConditionParameter::FIELD__VALUE => $fieldValue,
            ConditionParameter::FIELD__CONDITION => $fieldCompare
        ]);

        $valid = true;
        if (!$withCondition->isConditionTrue($entityValue)) {
            $valid = false;
            $result->addError(new Error([
                Error::FIELD__NAME => 'compare_failed',
                Error::FIELD__TITLE => 'Incorrect data',
                Error::FIELD__DESCRIPTION => $entityValue . ' is not ' . $fieldCompare . ' to (than) ' . $fieldValue
            ]));
        }

        return $valid;
    }
}
